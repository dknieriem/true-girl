<?php

namespace PixelYourSite;

use URL;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function isPysProActive() {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    return is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' );

}

function isPinterestActive( $checkCompatibility = true ) {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$active = is_plugin_active( 'pixelyoursite-pinterest/pixelyoursite-pinterest.php' );
	
	if ( $checkCompatibility ) {
		return $active && ! isPinterestVersionIncompatible();
	} else {
		return $active;
	}
	
}

function isPinterestVersionIncompatible() {
    
    if ( ! function_exists( 'get_plugin_data' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    
    $data = get_plugin_data( WP_PLUGIN_DIR . '/pixelyoursite-pinterest/pixelyoursite-pinterest.php', false, false );
    
    return ! version_compare( $data['Version'], PYS_FREE_PINTEREST_MIN_VERSION, '>=' );
    
}

function isSuperPackActive( $checkCompatibility = true ) {
    
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    
    $active = is_plugin_active( 'pixelyoursite-super-pack/pixelyoursite-super-pack.php' );
    
    if ( $checkCompatibility ) {
        return $active && function_exists( 'PixelYourSite\SuperPack' ) && ! isSuperPackVersionIncompatible();
    } else {
        return $active;
    }
    
}

/**
 * Check if WooCommerce plugin installed and activated.
 *
 * @return bool
 */
function isWooCommerceActive() {
    return function_exists( 'WC' );
}

/**
 * Check if Easy Digital Downloads plugin installed and activated.
 *
 * @return bool
 */
function isEddActive() {
    return function_exists( 'EDD' );
}

/**
 * Check if Product Catalog Feed Pro plugin installed and activated.
 *
 * @return bool
 */
function isProductCatalogFeedProActive() {
	return class_exists( 'wpwoof_product_catalog' );
}

/**
 * Check if EDD Products Feed Pro plugin installed and activated.
 *
 * @return bool
 */
function isEddProductsFeedProActive() {
	return class_exists( 'Wpeddpcf_Product_Catalog' );
}

function isBoostActive() {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	return is_plugin_active( 'boost/boost.php' );

}

/**
 * Check if Smart OpenGraph plugin installed and activated.
 *
 * @return bool
 */
function isSmartOpenGraphActive() {
    
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    
    return is_plugin_active( 'smart-opengraph/catalog-plugin.php' );
    
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function deepSanitizeTextField( $var ) {

    if ( is_array( $var ) ) {
        return array_map( 'deepSanitizeTextField', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }

}

function getAvailableUserRoles() {

	$wp_roles   = new \WP_Roles();
	$user_roles = array();

	foreach ( $wp_roles->get_names() as $slug => $name ) {
		$user_roles[ $slug ] = $name;
	}

	return $user_roles;

}

function isDisabledForCurrentRole() {

	$user = wp_get_current_user();
	$disabled_for = PYS()->getOption( 'do_not_track_user_roles' );

	foreach ( (array) $user->roles as $role ) {

		if ( in_array( $role, $disabled_for ) ) {

			add_action( 'wp_head', function() {
				echo "<script type='text/javascript'>console.warn('PixelYourSite is disabled for current user role.');</script>\r\n";
			} );

			return true;

		}

	}

	return false;

}

/**
 * Retrieves parameters values for for current queried object
 *
 * @return array
 */
function getTheContentParams( $allowedContentTypes = array() ) {
	global $post;

	$defaults = array(
		'on_posts_enabled'      => true,
		'on_pages_enables'      => true,
		'on_taxonomies_enabled' => true,
		'on_cpt_enabled'        => true,
		'on_woo_enabled'        => true,
		'on_edd_enabled'        => true,
	);

	$contentTypes = wp_parse_args( $allowedContentTypes, $defaults );

	$params = array();
	$cpt = get_post_type();

	/**
	 * POSTS
	 */
	if ( $contentTypes['on_posts_enabled'] && is_singular( 'post' ) ) {

		$params['post_type']    = 'post';
		$params['post_id']      = $post->ID;
		$params['content_name'] = $post->post_title;
		$params['categories']   = implode( ', ', getObjectTerms( 'category', $post->ID ) );
		$params['tags']         = implode( ', ', getObjectTerms( 'post_tag', $post->ID ) );

		return $params;

	}

	/**
	 * PAGES or FRONT PAGE
	 */
	if ( $contentTypes['on_pages_enables'] && ( is_singular( 'page' ) || is_home() ) ) {

		$params['post_type']    = 'page';
		$params['post_id']      = is_home() ? null : $post->ID;
		$params['content_name'] = is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;

		return $params;

	}

	// WooCommerce Shop page
	if ( $contentTypes['on_pages_enables'] && isWooCommerceActive() && is_shop() ) {

		$page_id = (int) wc_get_page_id( 'shop' );

		$params['post_type'] = 'page';
		$params['post_id']   = $page_id;
		$params['content_name'] = get_the_title( $page_id );

		return $params;

	}

	/**
	 * TAXONOMIES
	 */
	if ( $contentTypes['on_taxonomies_enabled'] && ( is_category() || is_tax() || is_tag() ) ) {

		if ( is_category() ) {

			$cat  = get_query_var( 'cat' );
			$term = get_category( $cat );

			$params['post_type']    = 'category';
			$params['post_id']      = $cat;
			$params['content_name'] = $term->name;

		} elseif ( is_tag() ) {

			$slug = get_query_var( 'tag' );
			$term = get_term_by( 'slug', $slug, 'post_tag' );

			$params['post_type']    = 'tag';
			$params['post_id']      = $term->term_id;
			$params['content_name'] = $term->name;

		} else {
            
            $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
            
            $params['post_type'] = get_query_var( 'taxonomy' );
            
            if ( $term ) {
                $params['post_id'] = $term->term_id;
                $params['content_name'] = $term->name;
            }

		}

		return $params;

	}

	// WooCommerce Products
	if ( $contentTypes['on_woo_enabled'] && isWooCommerceActive() && $cpt == 'product' ) {

		$params['post_type']    = 'product';
		$params['post_id']      = $post->ID;
		$params['content_name'] = $post->post_title;

		$params['categories'] = implode( ', ', getObjectTerms( 'product_cat', $post->ID ) );
		$params['tags']       = implode( ', ', getObjectTerms( 'product_tag', $post->ID ) );

		return $params;

	}

	// Easy Digital Downloads
	if ( $contentTypes['on_edd_enabled'] && isEddActive() && $cpt == 'download' ) {

		$params['post_type']    = 'download';
		$params['post_id']      = $post->ID;
		$params['content_name'] = $post->post_title;

		$params['categories'] = implode( ', ', getObjectTerms( 'download_category', $post->ID ) );
		$params['tags']       = implode( ', ', getObjectTerms( 'download_tag', $post->ID ) );

		return $params;

	}

	/**
	 * Custom Post Type should be last one.
	 */

	// Custom Post Type
	if ( $contentTypes['on_cpt_enabled'] && $cpt ) {

		// skip products and downloads is plugins are activated
		if ( ( isWooCommerceActive() && $cpt == 'product' ) || ( isEddActive() && $cpt == 'download' ) ) {
			return $params;
		}
        
        // fixes issue #88 from PRO
        if ( ! $post instanceof \WP_Post ) {
            return $params;
        }

		$params['post_type']    = $cpt;
		$params['post_id']      = $post->ID;
		$params['content_name'] = $post->post_title;

		$params['tags'] = implode( ', ', getObjectTerms( 'post_tag', $post->ID ) );

		$taxonomies = get_post_taxonomies( get_post() );

		if ( ! empty( $taxonomies ) && $terms = getObjectTerms( $taxonomies[0], $post->ID ) ) {
			$params['categories'] = implode( ', ', $terms );
		} else {
			$params['categories'] = array();
		}

		return $params;

	}

	return array();

}

/**
 * @param string $taxonomy Taxonomy name
 *
 * @return array Array of object term names
 */
function getObjectTerms( $taxonomy, $post_id ) {

	$terms   = get_the_terms( $post_id, $taxonomy );
	$results = array();

	if ( is_wp_error( $terms ) || empty ( $terms ) ) {
		return array();
	}

	// decode special chars
	foreach ( $terms as $term ) {
		$results[] = html_entity_decode( $term->name );
	}

	return $results;

}

/**
 * Sanitize event name. Only letters, numbers and underscores allowed.
 *
 * @param string $name
 *
 * @return string
 */
function sanitizeKey( $name ) {

	$name = str_replace( ' ', '_', $name );
	$name = preg_replace( '/[^0-9a-zA-z_]/', '', $name );

	return $name;

}

function getCommonEventParams() {

	$user = wp_get_current_user();

	if ( $user->ID !== 0 ) {
		$user_roles = implode( ',', $user->roles );
	} else {
		$user_roles = 'guest';
	}

	return array(
		'domain'     => substr( get_home_url( null, '', 'http' ), 7 ),
		'user_roles' => $user_roles,
		'plugin'     => 'PixelYourSite',
	);

}

function sanitizeParams( $params ) {

	$sanitized = array();

	foreach ( $params as $key => $value ) {

		// skip empty (but not zero)
		if ( ! isset( $value ) && ! is_numeric( $value ) ) {
			continue;
		}

		$key = sanitizeKey( $key );

		if ( is_array( $value ) ) {
			$sanitized[ $key ] = sanitizeParams( $value );
		} elseif ( $key == 'value' ) {
			$sanitized[ $key ] = (float) $value; // do not encode value to avoid error messages on Pinterest
		} elseif ( is_bool( $value ) ) {
			$sanitized[ $key ] = (bool) $value;
		} else {
			$sanitized[ $key ] = html_entity_decode( $value );
		}

	}

	return $sanitized;

}

/**
 * Checks if specified event enabled at least for one configured pixel
 *
 * @param string $eventName
 *
 * @return bool
 */
function isEventEnabled( $eventName ) {

	foreach ( PYS()->getRegisteredPixels() as $pixel ) {
		/** @var Pixel|Settings $pixel */

		if ( $pixel->configured() && $pixel->getOption( $eventName ) ) {
			return true;
		}

	}

	return false;

}

function startsWith( $haystack, $needle ) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos( $haystack, $needle, - strlen( $haystack ) ) !== false;
}

function endsWith( $haystack, $needle ) {
    // search forward starting from end minus needle length characters
    return $needle === "" || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && strpos( $haystack, $needle,
                $temp ) !== false );
}

function getCurrentPageUrl() {
    return untrailingslashit( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
}

function removeProtocolFromUrl( $url ) {
    
    if ( extension_loaded( 'mbstring' ) ) {
        
        $un = new URL\Normalizer();
        $un->setUrl( $url );
        $url = $un->normalize();
        
    }
    
    // remove fragment component
    $url_parts = parse_url( $url );
    if ( isset( $url_parts['fragment'] ) ) {
        $url = preg_replace( '/#' . $url_parts['fragment'] . '$/', '', $url );
    }
    
    // remove scheme and www and current host if any
    $url = str_replace( array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' ), '', $url );
    $url = trim( $url );
    $url = ltrim( $url, '/' );
    $url = rtrim( $url, '/' );
    
    return $url;
    
}

/**
 * Compare single URL or array of URLs with base URL. If base URL is not set, current page URL will be used.
 *
 * @param string|array $url
 * @param string       $base
 * @param string       $rule
 *
 * @return bool
 */
function compareURLs( $url, $base = '', $rule = 'match' ) {
    
    // use current page url if not set
    if ( empty( $base ) ) {
        $base = getCurrentPageUrl();
    }
    
    $base = removeProtocolFromUrl( $base );
    
    if ( is_string( $url ) ) {
        
        if ( empty( $url ) || '*' === $url ) {
            return true;
        }
        
        $url = rtrim( $url, '*' );  // for backward capability
        $url = removeProtocolFromUrl( $url );
        
        if ( $rule == 'match' ) {
            return $base == $url;
        }
        
        if ( $rule == 'contains' ) {
            
            if ( $base == $url ) {
                return true;
            }
            
            if ( strpos( $base, $url ) !== false ) {
                return true;
            }
            
            return false;
            
        }
        
        return false;
        
    } else {
        
        // recursively compare each url
        foreach ( $url as $single_url ) {
            
            if ( compareURLs( $single_url['value'], $base, $single_url['rule'] ) ) {
                return true;
            }
            
        }
        
        return false;
        
    }
    
}