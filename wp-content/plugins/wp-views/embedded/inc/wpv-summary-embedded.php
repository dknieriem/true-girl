<?php

/**
 * wpv-summary-embedded.php
 *
 * Summary functions for sections and filters
 *
 * @since 1.6.2
 */

/* ************************************************************************* *\
        Sections summaries
        TODO: Move these functions inside a class to allow unit testing.
\* ************************************************************************* */

/**
 * wpv_get_query_type_summary
 *
 * Returns the query type summary for a View
 *
 * @param $view_settings
 *
 * @returns string $summary
 *
 * @since 1.6.0
 */
function wpv_get_query_type_summary( $view_settings, $context = 'listing' ) {
	$view_settings = wpv_post_default_settings( $view_settings );
	$return = '';
	if (
		! isset( $view_settings['query_type'] )
		|| (
			isset( $view_settings['query_type'] )
			&& $view_settings['query_type'][0] == 'posts'
		)
	) {
		$selected = isset( $view_settings['post_type'] ) ? $view_settings['post_type'] : array();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$rfg_post_types = array();
		if ( apply_filters( 'toolset_is_m2m_enabled', false ) ) {
			do_action( 'toolset_do_m2m_full_init' );
			$rfg_post_types = get_post_types( array( Toolset_Post_Type_From_Types::DEF_IS_REPEATING_FIELD_GROUP => true ), 'objects' );
		}
		$selected_post_types = sizeof( $selected );
		switch ( $selected_post_types ) {
			case 0:
				if ( $context == 'embedded-info' ) {
					$return .= __('all post types', 'wpv-views');
				} else {
					$return .= __('All post types', 'wpv-views');
				}
				break;
			case 1:
				if ( isset( $post_types[ $selected[0] ] ) ) {
					$name = $post_types[ $selected[0] ]->labels->name;
				} else if ( isset( $rfg_post_types[ $selected[0] ] ) ) {
					$name = $rfg_post_types[ $selected[0] ]->labels->name;
				} else {
					$name = sprintf( __( '%s (missing post type)', 'wpv-views' ), $selected[0] );
				}
				if ( $name == 'any' ) {
					$name = __('All post types', 'wpv-views');
				}
				$return .= esc_html( $name );
				break;
			default:
				$name_array = array();
				foreach ( $selected as $select_pt ) {
					if ( isset( $post_types[ $select_pt ] ) ) {
						$name_array[] = $post_types[$select_pt]->labels->name;
					} else if ( isset( $rfg_post_types[ $select_pt ] ) ) {
						$name_array[] = $rfg_post_types[ $select_pt ]->labels->name;
					} else {
						$name_array[] = sprintf( __( '%s (missing post type)', 'wpv-views' ), $select_pt );
					}
				}
				$return .= esc_html( implode( ', ', $name_array ) );
				break;
		}
	}
	if (
		isset( $view_settings['query_type'] )
		&& $view_settings['query_type'][0] == 'taxonomy'
	) {
		$view_settings = wpv_taxonomy_default_settings( $view_settings );
		$selected = $view_settings['taxonomy_type'];
		if ( isset( $selected[0] ) && !empty( $selected[0] ) && taxonomy_exists( $selected[0] ) ) {
			$taxonomies = get_taxonomies( '', 'objects' );
			if ( isset( $taxonomies[$selected[0]] ) ) {
				$name = $taxonomies[$selected[0]]->labels->name;
			} else {
				$name = $selected[0];
			}
			$name = esc_html( $name );
			if ( $context == 'embedded-info' ) {
				$return .= sprintf( __( 'terms of the taxonomy %s', 'wpv-views' ), $name );
			} else {
				$return .= sprintf( __( 'Terms of the taxonomy %s', 'wpv-views' ), $name );
			}
		} else {
			if ( $context == 'embedded-info' ) {
				$return .= __( 'terms of a taxonomy that no longer exists', 'wpv-views' );
			} else {
				$return .= __( 'Terms of a taxonomy that no longer exists', 'wpv-views' );
			}
		}
	}
	if (
		isset( $view_settings['query_type'] )
		&& $view_settings['query_type'][0] == 'users'
	) {
		$user_role = '';
		if ( isset( $view_settings['roles_type'][0] ) ) {
			$user_role = esc_html( $view_settings['roles_type'][0] );
		}
		if ( $context == 'embedded-info' ) {
			if ( $user_role == 'any' ) {
				$return .= __( 'users with any role', 'wpv-views' );
			} else {
				$return .= sprintf( __( 'users with role %s', 'wpv-views' ),  $user_role );
			}
		} else {
			if ( $user_role == 'any' ) {
				$return .= __( 'This View selects users with any role', 'wpv-views' );
			} else {
				$return .= sprintf( __( 'This View selects users with role %s', 'wpv-views' ),  $user_role );
			}
		}
    }
	return $return;
}

/**
* wpv_get_ordering_summary
*
* Returns the sorting summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.0
*/

function wpv_get_ordering_summary( $view_settings, $context = 'listing' ) {
	$view_settings = apply_filters( 'wpv_filter_wpv_get_sorting_defaults', $view_settings );
	$return = '';
	if (
		! isset( $view_settings['query_type'] )
		|| (
			isset($view_settings['query_type'] )
			&& $view_settings['query_type'][0] == 'posts'
		)
	) {
		switch( $view_settings['orderby'] ) {
			case 'post_date':
				$order_by = __('post date', 'wpv-views');
				break;
			case 'post_title':
				$order_by = __('post title', 'wpv-views');
				break;
			case 'ID':
				$order_by = __('post ID', 'wpv-views');
				break;
			case 'post_author':
				$order_by = __( 'post author', 'wpv-views' );
				break;
			case 'post_type':
				$order_by = __( 'post type', 'wpv-views' );
				break;
			case 'menu_order':
				$order_by = __('menu order', 'wpv-views');
				break;
			case 'rand':
				$order_by = __('random order', 'wpv-views');
				break;
			default:
				$order_by = str_replace( 'field-', '', $view_settings['orderby'] );
				$order_by = sprintf( __('Field - %s', 'wpv-views'), $order_by );
				break;
		}
		$order = __('descending', 'wpv-views');
		if ( $view_settings['order'] == 'ASC' ) {
			$order = __( 'ascending', 'wpv-views' );
		}
    }
    if (
		isset( $view_settings['query_type'] )
		&& $view_settings['query_type'][0] == 'taxonomy'
	) {
		$order_by = '';
		switch( $view_settings['taxonomy_orderby'] ) {
			case 'count':
				$order_by = __('term count', 'wpv-views');
				break;
			case 'name':
				$order_by = __('term name', 'wpv-views');
				break;
			case 'slug':
				$order_by = __('term slug', 'wpv-views');
				break;
			case 'term_group':
				$order_by = __('term group', 'wpv-views');
				break;
			case 'none':
				$order_by = __('no specific criteria', 'wpv-views');
				break;
		}
		$order = __('descending', 'wpv-views');
		if ( $view_settings['taxonomy_order'] == 'ASC' ) {
			$order = __( 'ascending', 'wpv-views' );
		}
    }
    if (
		isset( $view_settings['query_type'] )
		&& $view_settings['query_type'][0] == 'users'
	) {
		$order_by = '';
		switch( $view_settings['users_orderby'] ) {
			case 'user_login':
				$order_by = __('user login', 'wpv-views');
				break;
			case 'ID':
				$order_by = __('user ID', 'wpv-views');
				break;
			case 'user_name':
				$order_by = __('user name', 'wpv-views');
				break;
			case 'display_name':
				$order_by = __('display name', 'wpv-views');
				break;
			case 'user_nicename':
				$order_by = __('user nicename', 'wpv-views');
				break;
			case 'user_email':
				$order_by = __('user email', 'wpv-views');
				break;
			case 'user_url':
				$order_by = __('user url', 'wpv-views');
				break;
			case 'user_registered':
				$order_by = __('user registered date', 'wpv-views');
				break;
			case 'post_count':
				$order_by = __('user post count', 'wpv-views');
				break;
		}
		$order = __('descending', 'wpv-views');
		if ( $view_settings['users_order'] == 'ASC' ) {
			$order = __( 'ascending', 'wpv-views' );
		}
	}
	$order_by = esc_html( $order_by );
	$order = esc_html( $order );
	if ( $context == 'embedded-info' ) {
		$return .= sprintf( __( 'ordered by <strong>%s</strong> in <strong>%s</strong> order', 'wpv-views' ), $order_by, $order );
	} else {
		$return .= sprintf( __( ' ordered by %s, %s', 'wpv-views' ), $order_by, $order );
	}
	return $return;
}

/**
* wpv_get_limit_offset_summary
*
* Returns the limit and offset summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.0
*/
function wpv_get_limit_offset_summary( $view_settings, $context = 'listing' ) {
	$view_settings = wpv_limit_offset_default_settings( $view_settings );
	$output = '';
	$limit = 0;
	$offset = 0;
	if (
		! isset( $view_settings['query_type'] )
		|| (
			isset($view_settings['query_type'] )
			&& $view_settings['query_type'][0] == 'posts'
		)
	) {
		$limit = intval( $view_settings['limit'] );
		$offset = intval( $view_settings['offset'] );
	}
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'taxonomy' ) {
		$limit = intval( $view_settings['taxonomy_limit'] );
		$offset = intval( $view_settings['taxonomy_offset'] );
	}
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'users' ) {
		$limit = intval( $view_settings['users_limit'] );
		$offset = intval( $view_settings['users_offset'] );
	}
	if ( $context == 'embedded-info' ) {
		if ( $limit > 0 || $offset > 0 ) {
			if ( $offset > 0 ) {
				$output .= sprintf( _n( 'First result skipped', 'First %d results skipped', $offset, 'wpv-views' ), $offset );
				if ( $limit > 0 ) {
					$output .= sprintf( _n( ', then one result loaded', ', then %d results loaded', $limit, 'wpv-views' ), $limit );
				}
			} else if ( $limit > 0 ) {
				$output .= sprintf( _n( 'First result loaded', 'First %d results loaded', $limit, 'wpv-views' ), $limit );
			}
		} else {
			$output .= __( 'All results loaded', 'wpv-views' );
		}
	} else {
		if ( $limit > 0 ) {
			$output .= sprintf( _n( ', limit to 1 item', ', limit to %d items', $limit, 'wpv-views' ), $limit );
		}
		if ( $offset > 0 ) {
			$output .= sprintf( _n( ', skip first item', ', skip %d items', $offset, 'wpv-views' ), $offset );
		}
	}
	$output = esc_html( $output );
	return $output;
}

/**
* wpv_get_pagination_summary
*
* Returns the pagination summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.2
*
* @todo add the view ID so we can get third party effects
* @todo unify cntxts, there is no need for two different ones; watch out as this is used directly on embedded listings
*/
function wpv_get_pagination_summary( $view_settings, $context = 'listing' ) {
	$return = '';
	if ( $view_settings['pagination']['type'] != 'disabled' ) {
		$pagination_type	= $view_settings['pagination']['type'];
		$posts_per_page		= $view_settings['pagination']['posts_per_page'];
		$selected_effect	= $view_settings['pagination']['effect'];
		/*
		$ajax_effects			= apply_filters( 'wpv_filter_wpv_pagination_ajax_effects', self::$pagination_ajax_effects, $view_id );
		$rollover_effects		= apply_filters( 'wpv_filter_wpv_pagination_rollover_effects', self::$pagination_rollover_effects, $view_id );
		*/
		$ajax_effects = array(
			'fade'				=> __('Fade', 'wpv-views'),
			'fadefast'			=> __('Fade', 'wpv-views'),
			'fadeslow'			=> __('Fade', 'wpv-views'),
			'slideh'			=> __('Slide horizontally', 'wpv-views'),
			'slidev'			=> __('Slide vertically', 'wpv-views'),
			'slideleft'			=> __('Slide Left', 'wpv-views'),
			'slideright'		=> __('Slide Right (backwards)', 'wpv-views'),
			'sliderightforward'	=> __('Slide Right', 'wpv-views'),
			'slideup'			=> __('Slide Up', 'wpv-views'),
			'slidedown'			=> __('Slide Down (backwards)', 'wpv-views'),
			'slidedownforward'	=> __('Slide Down', 'wpv-views'),
		);
		$pagination_effect = isset( $ajax_effects[ $selected_effect ] ) ? $ajax_effects[ $selected_effect ] : '';
		switch ( $pagination_type ) {
			case 'paged':
				if ( $context == 'embedded-info' ) {
					$return .= sprintf( _n( 'Manual pagination, 1 item per page', 'Manual pagination, %s items per page', $posts_per_page, 'wpv-views' ), $posts_per_page );
				} else {
					$return .= ', ' . sprintf( _n( '1 item per page with manual pagination', '%s items per page with manual pagination', $posts_per_page, 'wpv-views' ), $posts_per_page );
				}
				break;
			case 'ajaxed':
				if ( $context == 'embedded-info' ) {
					$return .= sprintf( _n( '%s, 1 item per page', '%s, %s items per page', $posts_per_page, 'wpv-views' ), $pagination_effect, $posts_per_page );
				} else {
					$return .= ', ' . sprintf( _n( '1 item per page with manual AJAX', '%s items per page with manual AJAX', $posts_per_page, 'wpv-views' ), $posts_per_page );
				}
				break;
			case 'rollover':
				if ( $context == 'embedded-info' ) {
					$return .= sprintf( _n( '%s automatically, 1 item per page', '%s automatically, %s items per page', $posts_per_page, 'wpv-views' ), $pagination_effect, $posts_per_page );
				} else {
					$return .= ', ' . sprintf( _n( '1 item per page with automatic AJAX', '%s items per page with automatic AJAX', $posts_per_page, 'wpv-views' ), $posts_per_page );
				}
				break;
		}
	} else {
		if ( $context == 'embedded-info' ) {
			$return .= __( 'No pagination', 'wpv-views' );
		}
	}
	$return = esc_html( $return );
	return $return;
}


/* ************************************************************************* *\
        Filter summaries
\* ************************************************************************* */


/**
* wpv_get_filter_status_summary_txt
*
* Returns the status filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_status_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['post_status'] ) ) {
		return;
	} else {
		$selected = $view_settings['post_status'];
	}
	ob_start();
	if ( sizeof( $selected ) ) {
		$first = true;
		$status_list = '';
		foreach( $selected as $value ) {
			if ( $first ) {
				$status_list .= '<strong>' . esc_html( $value ) . '</strong>';
				$first = false;
			} else {
				$status_list .= __( ' or ', 'wpv-views' ) . '<strong>' . esc_html( $value ) . '</strong>';
			}
		}
		if ( $short ) {
			echo sprintf( __( 'status of %s.', 'wpv-views' ), $status_list );
		} else {
			echo sprintf( __( 'Select posts with status of %s.', 'wpv-views' ), $status_list );
		}
	} else { // !TODO review this wording: this filter is not applied and indeed disapears from the edit screen on save
		if ( $short ) {
			_e( 'any status.', 'wpv-views' );
		} else {
			_e( 'Do not apply any filter based on status.', 'wpv-views' );
		}
	}
	$data = ob_get_clean();
	return $data;
}

/**
 * wpv_get_filter_sticky_summary_txt
 *
 * Returns a summary of Sticky Posts Filter in a View
 *
 * @param array $view_settings
 * @param bool $short
 */

function wpv_get_filter_sticky_summary_txt( $view_settings, $short = false ) {
	if ( ! isset( $view_settings['post_sticky'] ) ) {
		return;
	} else {
		$selected = $view_settings['post_sticky'];
	}
	ob_start();
	if ( $selected == 'include' ) {
        _e( 'Select <strong>sticky posts only</strong>.', 'wpv-views' );
	} else {
		_e( 'Select any post <strong>but the sticky ones</strong>.', 'wpv-views' );
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_post_author_summary_txt
*
* Returns the author filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_post_author_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['author_mode'] ) ) {
		return;
	}
	if ( isset( $_GET['post'] ) ) {
		$view_name = get_the_title( intval( $_GET['post'] ) );
	} else {
		if ( isset( $_GET['view_id'] ) ) {
			$view_name = get_the_title( intval( $_GET['view_id'] ) );
		} else {
			$view_name = 'view-name';
		}
	}
	$view_name = esc_html( $view_name );
	ob_start();
	switch ( $view_settings['author_mode'] ) {
		case 'current_user':
			_e( 'Select posts with the <strong>author</strong> the same as the <strong>current logged in user</strong>.', 'wpv-views' );
			break;
		case 'this_user':
			if ( isset( $view_settings['author_id'] ) && $view_settings['author_id'] > 0 ) {
				global $wpdb;
				$selected_author = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT display_name FROM {$wpdb->users}
						WHERE ID = %d
						LIMIT 1",
						$view_settings['author_id']
					)
				);
			} else {
				$selected_author = 'None';
			}
			$selected_author = esc_html( $selected_author );
			echo sprintf( __( 'Select posts with <strong>%s</strong> as the <strong>author</strong>.', 'wpv-views'), $selected_author );
			break;
		case 'parent_view': // @deprecated in 1.12.1
		case 'parent_user_view':
			_e( 'Select posts with the <strong>author set by the parent User View</strong>.', 'wpv-views' );
			break;
		case 'top_current_post':
			_e( 'Select posts with the <strong>author the same as the page where this View is shown</strong>.', 'wpv-views' );
			break;
		case 'current_page': // @deprecated in 1.12.1
		case 'current_post_or_parent_post_view':
			_e( 'Select posts with the <strong>author the same as the current post in the loop</strong>.', 'wpv-views' );
			break;
		case 'by_url':
			if ( isset( $view_settings['author_url'] ) && '' != $view_settings['author_url'] ) {
				$url_author = esc_html( $view_settings['author_url'] );
			} else {
				$url_author = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if ( isset( $view_settings['author_url_type'] ) && '' != $view_settings['author_url_type'] ) {
				$url_author_type = esc_html( $view_settings['author_url_type'] );
				switch ( $url_author_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$url_author_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}
			echo sprintf( __( 'Select posts with the author\'s <strong>%s</strong> determined by the URL parameter <strong>"%s"</strong>', 'wpv-views' ), $url_author_type, $url_author );
			if ( '' != $example ) {
				echo sprintf( __( ' eg. <span class="wpv-code">yoursite/page-with-this-view/?<strong>%s</strong>=%s</span>', 'wpv-views' ), $url_author, $example );
			}
			break;
		case 'shortcode':
			if ( isset( $view_settings['author_shortcode'] ) && '' != $view_settings['author_shortcode'] ) {
				$auth_short = esc_html( $view_settings['author_shortcode'] );
			} else {
				$auth_short = __( 'None', 'wpv-views' );
			}
			if ( isset( $view_settings['author_shortcode_type'] ) && '' != $view_settings['author_shortcode_type'] ) {
				$shortcode_author_type = esc_html( $view_settings['author_shortcode_type'] );
				switch ( $shortcode_author_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$shortcode_author_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}
			echo sprintf( __( 'Select posts which author\'s <strong>%s</strong> is set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), $shortcode_author_type, $auth_short );
			if ( '' != $example ) {
				echo sprintf( __( ' eg. <span class="wpv-code">[wpv-view name="%s" <strong>%s</strong>="%s"]</span>', 'wpv-views' ), $view_name, $auth_short, $example );
			}
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				if ( isset( $view_settings['author_framework'] ) && '' != $view_settings['author_framework'] ) {
					$author_framework = esc_html( $view_settings['author_framework'] );
				} else {
					$author_framework = __( 'None', 'wpv-views' );
				}
				if ( isset( $view_settings['author_framework_type'] ) && '' != $view_settings['author_framework_type'] ) {
					$author_framework_type = esc_html( $view_settings['author_framework_type'] );
				} else {
					$author_framework_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				}
				echo sprintf( __( 'Select posts which author\'s <strong>%s</strong> is set by the Framework option <strong>"%s"</strong>', 'wpv-views' ), $author_framework_type, $author_framework );
			} else {
				$WP_Views_fapi->framework_missing_message_for_filters();
			}
			break;
		default:
			_e( 'Oops! It seems there is a filter by post author that is missing some options', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	if ( $short ) {
		// this happens on the Views table under Filter column
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
* wpv_get_filter_taxonomy_summary_txt
*
* Returns the taxonomies filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*
* @todo improve, avoid loading the taxonomies if there is no filter at all
*/

function wpv_get_filter_taxonomy_summary_txt( $view_settings ) {
	$result = '';
	$taxonomies = get_taxonomies( '', 'objects' );
	foreach ( $taxonomies as $category_slug => $category ) {
		$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		if ( isset( $view_settings[$relationship_name] ) ) {
			if ( !isset( $view_settings[$save_name] ) ) {
				$view_settings[$save_name] = array();
			}
			$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
			if ( $result != '' ) {
				if ( $view_settings['taxonomy_relationship'] == 'OR' ) {
					$result .= __( ' OR ', 'wpv-views' );
				} else {
					$result .= __( ' AND ', 'wpv-views' );
				}
			}

			$result .= wpv_get_taxonomy_summary( $name, $view_settings, $view_settings[$save_name] );

		}
	}
	return $result;
}

/**
* wpv_get_taxonomy_summary
*
* Returns each taxonomy filter summary for a View
*
* @param $type (string) post_category | tax_input[{$category->name}]
* @param $view_settings
* @param $category_selected (array) selected terms when using IN or AND modes
*
* @returns (string) $summary
*
* @since unknown
*
* @todo improve this, we should not need to loop over all the taxes (we already checked all of this on the previous function, FGS
*/

function wpv_get_taxonomy_summary( $type, $view_settings, $category_selected ) {
	// find the matching category/taxonomy
	//$taxonomy = 'category';
	$taxonomy = '';
	$taxonomy_name = __( 'Categories', 'wpv-views' );
	$taxonomies = get_taxonomies( '', 'objects' );
	foreach ( $taxonomies as $category_slug => $category ) {
		$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
		if ( $name == $type ) {
			// it's a category type.
			$taxonomy = esc_html( $category->name );
			$taxonomy_name = esc_html( $category->label );
			break;
		}
	}
	if ( '' == $taxonomy ) {
		return;
	}
	if ( ! isset( $view_settings['tax_' . $taxonomy . '_relationship'] ) ) {
		$view_settings['tax_' . $taxonomy . '_relationship'] = 'IN';
	}
	if ( ! isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] ) ) {
		$view_settings['taxonomy-' . $taxonomy . '-attribute-url'] = '';
	}
	if ( ! isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] ) ) {
		$view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] = 'IN';
	}
	if ( ! isset( $view_settings['taxonomy-' . $taxonomy . '-framework'] ) ) {
		$view_settings['taxonomy-' . $taxonomy . '-framework'] = '';
	}

	$cat_text = '';
	$origin_text = '';
	$operator_text = '';
	if ( in_array( $view_settings['tax_' . $taxonomy . '_relationship'], array( 'IN', 'NOT IN', 'AND' ) ) ) {
		foreach ( $category_selected as $cat ) {
			// WordPress 4.2 compatibility - split terms
			$candidate_term_id_splitted = wpv_compat_get_split_term( $cat, $taxonomy );
			if ( $candidate_term_id_splitted ) {
				$cat = $candidate_term_id_splitted;
			}
			// get_term() handles WPML support
			$term = get_term( $cat, $taxonomy );
			if ( $term ) {
				if ( $cat_text != '' ) {
					$cat_text .= ', ';
				}
				$cat_text .= esc_html( $term->name );
			}
		}
	} else if ( in_array( $view_settings['tax_' . $taxonomy . '_relationship'], array( 'FROM ATTRIBUTE', 'FROM URL' ) ) ) {
		if ( isset( $view_settings['taxonomy-' . $category->name . '-attribute-url-format'] ) ) {
			$url_format = esc_html( $view_settings['taxonomy-' . $category->name . '-attribute-url-format'][0] );
		} else {
			$url_format = 'name';
		}
		if ( $url_format == 'slug' ) {
			$origin_text = __( 'slug', 'wpv-views' );
		} else if ( $url_format == 'name' ) {
			$origin_text = __( 'name', 'wpv-views' );
		}
		switch ( $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] ) {
			case 'IN':
				$operator_text = __( 'one', 'wpv-views' );
				break;
			case 'NOT IN':
				$operator_text = __( 'no one', 'wpv-views' );
				break;
			case 'AND':
				$operator_text = __( 'all', 'wpv-views' );
				break;
		}
	}
	$taxonomy_attribute_url = esc_html( $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] );
	$taxonomy_framework = esc_html( $view_settings['taxonomy-' . $taxonomy . '-framework'] );
	ob_start();
	?>
	<span class="wpv-filter-multiple-summary-item">
	<?php
	switch ( $view_settings['tax_' . $taxonomy . '_relationship'] ) {
		case 'IN':
			echo sprintf( __( '<strong>%s</strong> in <strong>one</strong> of these: <strong>%s</strong>', 'wpv-views' ), $taxonomy_name, $cat_text );
			break;
		case "AND":
			echo sprintf( __( '<strong>%s</strong> in <strong>all</strong> of these: <strong>%s</strong>', 'wpv-views' ), $taxonomy_name, $cat_text );
			break;
		case "NOT IN":
			echo sprintf( __( '<strong>%s</strong> in <strong>no one</strong> of these: <strong>%s</strong>', 'wpv-views' ), $taxonomy_name, $cat_text );
			break;
		case 'top_current_post':
			echo sprintf( __( '<strong>%s</strong> the same as the <strong>page where this View is shown</strong>', 'wpv-views' ), $taxonomy_name );
			break;
		case "FROM PAGE": // @deprecated in 1.12.1
		case 'current_post_or_parent_post_view':
			echo sprintf( __( '<strong>%s</strong> the same as the <strong>current post in the loop</strong>', 'wpv-views' ), $taxonomy_name );
			break;
		case "FROM ARCHIVE":
			echo sprintf( __( '<strong>%s</strong> the same as the <strong>current archive page</strong>', 'wpv-views' ), $taxonomy_name );
			break;
		case "FROM ATTRIBUTE":
			echo sprintf( __( '<strong>%s</strong> <strong>%s</strong> in <strong>%s</strong> of those set by the View shortcode attribute <strong>%s</strong>', 'wpv-views' ), $taxonomy_name, $origin_text, $operator_text, $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] );
			echo '<br /><code>' . sprintf( __( 'eg. [wpv-view name="view-name" <strong>%s="xxxx"</strong>]', 'wpv-views' ), $taxonomy_attribute_url ) . '</code>';
			break;
		case "FROM URL":
			echo sprintf( __( '<strong>%s</strong> <strong>%s</strong> in <strong>%s</strong> of those set by the URL parameter <strong>%s</strong>', 'wpv-views' ), $taxonomy_name, $origin_text, $operator_text, $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] );
			echo '<br /><code>' . sprintf( __( 'eg. http://www.example.com/page/?<strong>%s=xxxx</strong>', 'wpv-views' ), $taxonomy_attribute_url ) . '</code>';
			break;
		case "FROM PARENT VIEW": // @deprecated on 1.12.1
		case 'current_taxonomy_view':
			echo sprintf( __( '<strong>%s</strong> set by the <strong>parent Taxonomy View</strong>', 'wpv-views' ), $taxonomy_name );
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				echo sprintf( __( '<strong>%s</strong> set by the Framework option <strong>%s</strong>', 'wpv-views' ), $taxonomy_name, $taxonomy_framework );
			} else {
				$WP_Views_fapi->framework_missing_message_for_filters( $taxonomy_name );
			}
			break;
		default:
			echo sprintf( __( 'Oops! It seems there is a filter by %s that is missing some options', 'wpv-views' ), $taxonomy_name );
			break;
	}
	?>
	</span>
	<?php
	$buffer = ob_get_clean();
	return $buffer;
}

/**
* wpv_get_filter_post_relationship_summary_txt
*
* Returns the post relationship filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_post_relationship_summary_txt( $view_settings, $short = false ) {
	$is_enabled_m2m = apply_filters( 'toolset_is_m2m_enabled', false );

	if ( !isset( $view_settings['post_relationship_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['post_relationship_mode'] ) ) {
		$view_settings['post_relationship_mode'] = $view_settings['post_relationship_mode'][0];
	}
	if ( !isset( $view_settings['post_relationship_shortcode_attribute'] ) ) {
		$view_settings['post_relationship_shortcode_attribute'] = '';
	}
	if ( !isset( $view_settings['post_relationship_url_parameter'] ) ) {
		$view_settings['post_relationship_url_parameter'] = '';
	}
	if ( !isset( $view_settings['post_relationship_framework'] ) ) {
		$view_settings['post_relationship_framework'] = '';
	}
	ob_start();

	if ( $is_enabled_m2m ) { // m2m enabled.
		do_action( 'toolset_do_m2m_full_init' );
		$relationship_repository = Toolset_Relationship_Definition_Repository::get_instance();
		if ( toolset_getarr( $view_settings, 'post_relationship_slug', '' ) ) {
			$definition = $relationship_repository->get_definition( $view_settings['post_relationship_slug'] );
			$relationship_display_name = $definition ? $definition->get_display_name() : '';
			$relationship_origin = $definition ? $definition->get_origin()->get_origin_keyword() : '';
			
			// translators: parent, child or both
			$type_relationship_summary = sprintf( __( 'Select posts in a <strong>%s</strong> relationship ', 'wpv-views' ), $relationship_display_name );
			
			if ( Toolset_Relationship_Origin_Repeatable_Group::ORIGIN_KEYWORD === $relationship_origin ) {
				$child_post_types = $definition->get_child_type()->get_types();
				$child_post_type_object = get_post_type_object( $child_post_types[0] );
				if ( null != $child_post_type_object ) {
					$type_relationship_summary = sprintf( __( 'Select items from the <strong>%s</strong> group ', 'wpv-views' ), $child_post_type_object->labels->name );
				}
			}
		} else {
			$type_relationship_summary = __( 'Select posts in <strong>Any</strong> relationship ', 'wpv-views' );
		}
		switch ( $view_settings['post_relationship_mode'] ) {
			case 'current_page': // @deprecated in 1.12.1
			case 'top_current_post':
				// translators: parent, child or both
				echo __( $type_relationship_summary . 'that are <strong>related</strong> to the <strong>Post where this View is shown</strong>.', 'wpv-views' );
				break;
			case 'parent_view': // @deprecated in 1.12.1
			case 'current_post_or_parent_post_view':
				echo __( $type_relationship_summary . 'that are a <strong>related</strong> to the <strong>current post in the loop</strong>.', 'wpv-views' );
				break;
			case 'shortcode_attribute':
				echo sprintf( __( $type_relationship_summary . 'that are <strong>related</strong> to the <strong>Post with ID set by the shortcode attribute %s</strong>.', 'wpv-views' ), esc_html( $view_settings['post_relationship_shortcode_attribute'] ) );
				echo '<br /><code>' . sprintf( __( ' eg. [wpv-view name="view-name" <strong>%s="123"</strong>]', 'wpv-views' ), $view_settings['post_relationship_shortcode_attribute'] ) . '</code>';
				break;
			case 'url_parameter':
				echo sprintf( __( $type_relationship_summary . 'that are <strong>related</strong> to the <strong>Post with ID set by the URL parameter %s</strong>.', 'wpv-views' ), esc_html( $view_settings['post_relationship_url_parameter'] ) );
				echo '<br /><code>' . sprintf( __( ' eg. http://www.example.com/my-page/?<strong>%s=123</strong>', 'wpv-views' ), esc_html( $view_settings['post_relationship_url_parameter'] ) ) . '</code>';
				break;
			case 'this_page':
				if (
					isset( $view_settings['post_relationship_id'] )
					&& $view_settings['post_relationship_id'] > 0
				) {
					$wpv_wpml_integration = WPV_WPML_Integration_Embedded::get_instance();
					$view_settings['post_relationship_id'] = $wpv_wpml_integration->wpml_get_user_admin_language_post_id( $view_settings['post_relationship_id'] );
					global $wpdb;
					$selected_title = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT post_title FROM {$wpdb->posts}
							WHERE ID = %d
							LIMIT 1",
							$view_settings['post_relationship_id']
						)
					);
				} else {
					$selected_title = 'None';
				}
				echo sprintf( __( $type_relationship_summary . 'that are related to <strong>%s</strong>.', 'wpv-views' ), esc_html( $selected_title ) );
				break;
			case 'framework':
				global $WP_Views_fapi;
				if ( $WP_Views_fapi->framework_valid ) {
					if ( isset( $view_settings['post_relationship_framework'] ) && '' != $view_settings['post_relationship_framework'] ) {
						$post_relationship_framework = $view_settings['post_relationship_framework'];
					} else {
						$post_relationship_framework = __( 'None', 'wpv-views' );
					}
					echo sprintf( $type_relationship_summary . __( 'that are <strong>related</strong> to the <strong>Post with ID is set by the Framework option "%s"</strong>', 'wpv-views' ), esc_html( $post_relationship_framework ) );
				} else {
					$WP_Views_fapi->framework_missing_message_for_filters();
				}
				break;
			default:
				_e( 'Oops! It seems there is a filter by post relationship that is missing some options', 'wpv-views' );
				break;
		}
	} else { // m2m not enabled.
		switch ( $view_settings['post_relationship_mode'] ) {
			case 'current_page': // @deprecated in 1.12.1
			case 'top_current_post':
				_e( 'Select posts that are <strong>children</strong> of the <strong>Post where this View is shown</strong>.', 'wpv-views' );
				break;
			case 'parent_view': // @deprecated in 1.12.1
			case 'current_post_or_parent_post_view':
				_e( 'Select posts that are a <strong>children</strong> of the <strong>current post in the loop</strong>.', 'wpv-views' );
				break;
			case 'shortcode_attribute':
				echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with ID set by the shortcode attribute %s</strong>.', 'wpv-views' ), esc_html( $view_settings['post_relationship_shortcode_attribute'] ) );
				echo '<br /><code>' . sprintf( __( ' eg. [wpv-view name="view-name" <strong>%s="123"</strong>]', 'wpv-views' ), $view_settings['post_relationship_shortcode_attribute'] ) . '</code>';
				break;
			case 'url_parameter':
				echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with ID set by the URL parameter %s</strong>.', 'wpv-views' ), esc_html( $view_settings['post_relationship_url_parameter'] ) );
				echo '<br /><code>' . sprintf( __( ' eg. http://www.example.com/my-page/?<strong>%s=123</strong>', 'wpv-views' ), esc_html( $view_settings['post_relationship_url_parameter'] ) ) . '</code>';
				break;
			case 'this_page':
				if (
					isset( $view_settings['post_relationship_id'] )
					&& $view_settings['post_relationship_id'] > 0
				) {
					$wpv_wpml_integration = WPV_WPML_Integration_Embedded::get_instance();
					$view_settings['post_relationship_id'] = $wpv_wpml_integration->wpml_get_user_admin_language_post_id( $view_settings['post_relationship_id'] );
					global $wpdb;
					$selected_title = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT post_title FROM {$wpdb->posts}
							WHERE ID = %d
							LIMIT 1",
							$view_settings['post_relationship_id']
						)
					);
				} else {
					$selected_title = 'None';
				}
				echo sprintf( __( 'Select posts that are children of <strong>%s</strong>.', 'wpv-views' ), esc_html( $selected_title ) );
				break;
			case 'framework':
				global $WP_Views_fapi;
				if ( $WP_Views_fapi->framework_valid ) {
					if ( isset( $view_settings['post_relationship_framework'] ) && '' != $view_settings['post_relationship_framework'] ) {
						$post_relationship_framework = $view_settings['post_relationship_framework'];
					} else {
						$post_relationship_framework = __( 'None', 'wpv-views' );
					}
					echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with IDs is set by the Framework option "%s"</strong>', 'wpv-views' ), esc_html( $post_relationship_framework ) );
				} else {
					$WP_Views_fapi->framework_missing_message_for_filters();
				}
				break;
			default:
				_e( 'Oops! It seems there is a filter by post relationship that is missing some options', 'wpv-views' );
				break;
		}
	}
	$data = ob_get_clean();
	if ( $short ) {
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
 * Get the summary for the query filter by post type.
 *
 * @since 2.4.0
 *
 * @note WIP
 */

function wpv_get_filter_post_type_summary_txt( $view_settings ) {
	$return = '';
	if (
		! isset( $view_settings['post_type_filter'] )
		|| ! isset( $view_settings['post_type_filter']['mode'] )
	) {
		return $return;
	}
	$defaults = array(
		'url'		=> '',
		'shortcode'	=> '',
		'framework'	=> ''
	);
	$view_settings['post_type_filter']	= wp_parse_args( $view_settings['post_type_filter'], $defaults );
	switch ( $view_settings['post_type_filter']['mode'] ) {
		case 'url':
			$return = sprintf(
				__( 'Select posts on the post types set by the URL parameter <strong>%1$s</strong>', 'wpv-views' ),
				$view_settings['post_type_filter']['url']
			);
			break;
		case 'shortcode':
			$return = sprintf(
				__( 'Select posts on the post types set by the shortcode attribute <strong>%1$s</strong>', 'wpv-views' ),
				$view_settings['post_type_filter']['shortcode']
			);
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				$return = sprintf(
					__( 'Select posts on the post types set by the Framework option <strong>%1$s</strong>', 'wpv-views' ),
					$view_settings['post_type_filter']['framework']
				);
			} else {
				$return = $WP_Views_fapi->get_framework_missing_message_for_filters();
			}
			break;
	}
	return $return;
}

/**
* wpv_get_filter_post_id_summary_txt
*
* Returns the post id filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_post_id_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['id_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['id_mode'] ) ) {
		$view_settings['id_mode'] = $view_settings['id_mode'][0];
	}
	if ( isset( $_GET['post'] ) ) {
		$view_name = get_the_title( $_GET['post'] );
	} else {
		$view_name = 'view-name';
	}
	$view_name = esc_html( $view_name );
	$defaults = array(
		'id_in_or_out' => 'in',
		'id_mode' => 'by_ids',
		'post_id_ids_list' =>'',
		'post_ids_url' => 'post_ids',
		'post_ids_shortcode' => 'ids',
		'post_ids_framework' => ''
	);
	$view_settings = wp_parse_args( $view_settings, $defaults );
	$summary_prefix = '';
	ob_start();
	switch ( $view_settings['id_in_or_out'] ) {
		case 'in':
			$summary_prefix = __( 'Include only posts ', 'wpv-views' );
			break;
		case 'out':
			$summary_prefix = __( 'Exclude posts ', 'wpv-views' );
			break;
	}
	switch ( $view_settings['id_mode'] ) {
		case 'by_ids':
			echo $summary_prefix;
			if ( isset( $view_settings['post_id_ids_list'] ) && '' != $view_settings['post_id_ids_list'] ) {
				$ids_list = esc_html( $view_settings['post_id_ids_list'] );
			} else {
				$ids_list = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'with the following <strong>IDs</strong>: %s', 'wpv-views' ), $ids_list );
			break;
		case 'by_url':
			echo $summary_prefix;
			if ( isset( $view_settings['post_ids_url'] ) && '' != $view_settings['post_ids_url'] ) {
				$url_ids = esc_html( $view_settings['post_ids_url'] );
			} else {
				$url_ids = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'with IDs determined by the URL parameter <strong>"%s"</strong>', 'wpv-views' ), $url_ids );
			echo sprintf( __( ' eg. <span class="wpv-code">yoursite/page-with-this-view/?<strong>%s</strong>=1</span>', 'wpv-views' ), $url_ids );
			break;
		case 'shortcode':
			echo $summary_prefix;
			if ( isset( $view_settings['post_ids_shortcode'] ) && '' != $view_settings['post_ids_shortcode'] ) {
				$id_short = esc_html( $view_settings['post_ids_shortcode'] );
			} else {
				$id_short = __( 'None', 'wpv-views' );
			}
			echo sprintf( __( 'with IDs set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), $id_short );
			echo sprintf( __( ' eg. <span class="wpv-code">[wpv-view name="%s" <strong>%s</strong>="1"]</span>', 'wpv-views' ), $view_name, $id_short );
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				echo $summary_prefix;
				if ( isset( $view_settings['post_ids_framework'] ) && '' != $view_settings['post_ids_framework'] ) {
					$post_ids_framework = esc_html( $view_settings['post_ids_framework'] );
				} else {
					$post_ids_framework = __( 'None', 'wpv-views' );
				}
				echo sprintf( __( 'with IDs set by the Framework option <strong>"%s"</strong>', 'wpv-views' ), $post_ids_framework );
			} else {
				$WP_Views_fapi->framework_missing_message_for_filters();
			}
			break;
		default:
			_e( 'Oops! It seems there is a filter by post IDs that is missing some options', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	if ( $short ) {
		// this happens on the Views table under Filter column
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
 * wpv_get_filter_post_parent_summary_txt
 *
 * Returns the parent filter summary for a View
 *
 * @param $view_settings
 * @param bool $short (bool) maybe DEPRECATED
 *
 * @return array (string) $summary
 * @since unknown
 */
function wpv_get_filter_post_parent_summary_txt( $view_settings, $short = false ) {
	$is_enabled_m2m = apply_filters( 'toolset_is_m2m_enabled', false );
	if ( ! isset( $view_settings['parent_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['parent_mode'] ) ) {
		$view_settings['parent_mode'] = $view_settings['parent_mode'][0];
	}
	if ( ! isset( $view_settings['parent_shortcode_attribute'] ) ) {
		$view_settings['parent_shortcode_attribute'] = '';
	}
	if ( ! isset( $view_settings['parent_url_parameter'] ) ) {
		$view_settings['parent_url_parameter'] = '';
	}
	if ( ! isset( $view_settings['parent_framework'] ) ) {
		$view_settings['parent_framework'] = '';
	}
	ob_start();
	switch ( $view_settings['parent_mode'] ) {
		case 'top_current_post':
			if ( $short ) {
				_e( 'parent is the <strong>page where the View is shown</strong>', 'wpv-views' );
			} else {
				_e( 'Select posts whose parent is the <strong>page where the View is shown</strong>.', 'wpv-views' );
			}
			break;
		case 'current_page':
		case 'current_post_or_parent_post_view':
			if ( $short ) {
				_e( 'parent is the <strong>current post in the loop</strong>', 'wpv-views' );
			} else {
				_e( 'Select posts whose parent is the <strong>current post in the loop</strong>.', 'wpv-views' );
			}
			break;
		case 'this_page':
			if ( isset( $view_settings['parent_id'] ) && $view_settings['parent_id'] > 0 ) {
				$wpv_wpml_integration = WPV_WPML_Integration_Embedded::get_instance();
				$view_settings['parent_id'] = $wpv_wpml_integration->wpml_get_user_admin_language_post_id( $view_settings['parent_id'] );
				global $wpdb;
				$selected_title = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_title FROM {$wpdb->posts}
						WHERE ID = %d
						LIMIT 1",
						$view_settings['parent_id']
					)
				);
			} else {
				$selected_title = __( 'None', 'wpv-views' );
			}
			if ( $short ) {
				echo sprintf( __( 'parent is <strong>%s</strong>', 'wpv-views' ), esc_html( $selected_title ) );
			} else {
				echo sprintf( __( 'Select posts whose parent is <strong>%s</strong>.', 'wpv-views' ), esc_html( $selected_title ) );
			}
			break;
		case 'no_parent':
			if ( $short ) {
				echo __( 'has no parent', 'wpv-views' );
			} else {
				echo __( 'Select top-level posts with no parent.', 'wpv-views' );
			}
			break;
		case 'shortcode_attribute':
			if ( $is_enabled_m2m ) {
				echo sprintf( __( 'Select posts that are <strong>related</strong> to the <strong>Post with ID set by the shortcode attribute %s</strong>.', 'wpv-views' ), esc_html( $view_settings['parent_shortcode_attribute'] ) );
			} else {
				echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with ID set by the shortcode attribute %s</strong>.', 'wpv-views' ), esc_html( $view_settings['parent_shortcode_attribute'] ) );
			}
			echo '<br /><code>' . sprintf( __( ' eg. [wpv-view name="view-name" <strong>%s="123"</strong>]', 'wpv-views' ), esc_html( $view_settings['parent_shortcode_attribute'] ) ) . '</code>';
			break;
		case 'url_parameter':
			if ( $is_enabled_m2m ) {
				echo sprintf( __( 'Select posts that are <strong>related</strong> to the <strong>Post with ID set by the URL parameter %s</strong>.', 'wpv-views' ), esc_html( $view_settings['parent_url_parameter'] ) );
			} else {
				echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with ID set by the URL parameter %s</strong>.', 'wpv-views' ), esc_html( $view_settings['parent_url_parameter'] ) );
			}
			echo '<br /><code>' . sprintf( __( ' eg. http://www.example.com/my-page/?<strong>%s=123</strong>', 'wpv-views' ), esc_html( $view_settings['parent_url_parameter'] ) ) . '</code>';
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				if (
					isset( $view_settings['parent_framework'] )
					&& '' != $view_settings['parent_framework']
				) {
					$parent_framework = esc_html( $view_settings['parent_framework'] );
				} else {
					$parent_framework = __( 'None', 'wpv-views' );
				}
				if ( $is_enabled_m2m ) {
					echo sprintf( __( 'Select posts that are <strong>related</strong> to the <strong>Post with IDs set by the Framework option "%s"</strong>', 'wpv-views' ), $parent_framework );
				} else {
					echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with IDs set by the Framework option "%s"</strong>', 'wpv-views' ), $parent_framework );
				}
			} else {
				$WP_Views_fapi->framework_missing_message_for_filters();
			}
			break;
		default:
			_e( 'Oops! It seems there is a filter by post parent that is missing some options', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	return $data;
}

/**
 * wpv_get_filter_taxonomy_parent_summary_txt
 *
 * Returns the taxonomy parent filter summary for a View
 *
 * @param $view_settings
 * @return array (string) $summary
 *
 * @since unknown
 */
function wpv_get_filter_taxonomy_parent_summary_txt( $view_settings ) {
	if ( !isset( $view_settings['taxonomy_type'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_type'] ) && sizeof( $view_settings['taxonomy_type'] ) > 0 ) {
		$view_settings['taxonomy_type'] = $view_settings['taxonomy_type'][0];
		if ( ! taxonomy_exists( $view_settings['taxonomy_type'] ) ) {
			return;
		}
	}
	if ( !isset( $view_settings['taxonomy_parent_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_parent_mode'] ) ) {
		$view_settings['taxonomy_parent_mode'] = $view_settings['taxonomy_parent_mode'][0];
	}
	if (
		isset( $view_settings['taxonomy_parent_id'] )
		&& ! empty( $view_settings['taxonomy_parent_id'] )
	) {
		// WordPress 4.2 compatibility - split terms
		$candidate_term_id_splitted = wpv_compat_get_split_term( $view_settings['taxonomy_parent_id'], $view_settings['taxonomy_type'] );
		if ( $candidate_term_id_splitted ) {
			$view_settings['taxonomy_parent_id'] = $candidate_term_id_splitted;
		}
		// Adjust for WPML support
		$view_settings['taxonomy_parent_id'] = apply_filters( 'translate_object_id', $view_settings['taxonomy_parent_id'], $view_settings['taxonomy_type'], true, null );
	}
	ob_start();
	if ( in_array( $view_settings['taxonomy_parent_mode'], array( 'current_view', 'current_taxonomy_view' ) ) ) {
		_e( 'Select taxonomy terms whose parent is the value set by the <strong>parent view</strong>.', 'wpv-views' );
	} else if ( $view_settings['taxonomy_parent_mode'] == 'current_archive_loop' ) {
		_e( 'Select taxonomy terms whose parent is the <strong>current taxonomy archive</strong>.', 'wpv-views' );
	} else {
		if ( isset( $view_settings['taxonomy_parent_id'] ) && $view_settings['taxonomy_parent_id'] > 0 ) {
			$selected_taxonomy = get_term( $view_settings['taxonomy_parent_id'], $view_settings['taxonomy_type'] );
			if ( null ==  $selected_taxonomy ) { // TODO Review this
				$selected_taxonomy = __( 'None', 'wpv-views' );
			} else {
				$selected_taxonomy = esc_html( $selected_taxonomy->name );
			}
		} else {
			$selected_taxonomy = __( 'None', 'wpv-views' );
		}
		echo sprintf( __( 'Select taxonomy terms whose parent is <strong>%s</strong>.', 'wpv-views' ), $selected_taxonomy );
	}
	$data = ob_get_clean();
	return $data;
}


/**
 * wpv_get_filter_post_search_summary_txt
 *
 * Returns the search filter summary for a View
 *
 * @param $view_settings
 * @param bool|maybe $short maybe DEPRECATED
 *
 * @return array (string) $summary
 *
 * @since unknown
 */
function wpv_get_filter_post_search_summary_txt( $view_settings, $short = false ) {
	if ( ! isset( $view_settings['search_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['search_mode'] ) ) {
		$view_settings['search_mode'] = $view_settings['search_mode'][0];
	}
	if ( ! isset( $view_settings['post_search_value'] ) ) {
		$view_settings['post_search_value'] = '';
	}
	if ( ! isset( $view_settings['post_search_content'] ) ) {
		$view_settings['post_search_content'] = 'full_content';
	}
	if ( isset( $_GET['view_id'] ) ) {
		$view_name = get_the_title( intval( $_GET['view_id'] ) );
	} else {
		$view_name = 'view-name';
	}
	$view_name = esc_html( $view_name );

	$post_search_content_options = WPV_Search_Frontend_Filter::get_post_search_content_options();
	$post_search_content = isset( $post_search_content_options[ $view_settings['post_search_content'] ] ) ? $view_settings['post_search_content'] : 'full_content';

	$search_where = $post_search_content_options[ $post_search_content ]['summary'];

	$summary = '';
	ob_start();
	switch ( $view_settings['search_mode'] ) {
		case 'specific':
			$term = esc_html( $view_settings['post_search_value'] );
			if ( $term == '' ) {
				$term = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if ( $short ) {
				echo sprintf( __( 'Filter %s by <strong>search</strong> term: <strong>%s</strong>', 'wpv-views' ), $search_where, $term );
			} else {
				echo sprintf( __( 'Filter %s by this search term: <strong>%s</strong>.', 'wpv-views' ), $search_where, $term );
			}
			break;
		case 'shortcode':
			$term = esc_html( $view_settings['post_search_shortcode'] );
			if ( $term == '' ) {
				$term = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if ( $short ) {
				echo sprintf( __( 'Filter %s by this <strong>shortcode argument specified</strong> term: <strong>%s</strong>', 'wpv-views' ), $search_where, $term );
				echo sprintf( __( ' eg. <span class="wpv-code">[wpv-view name="%s" <strong>%s</strong>="search term"]</span>', 'wpv-views' ), $view_name, $term );
			} else {
				echo sprintf( __( 'Filter %s by a search term set by the View shortcode attribute: <strong>%s</strong>.', 'wpv-views' ), $search_where, $term );
				echo sprintf( __( ' eg. <span class="wpv-code">[wpv-view name="%s" <strong>%s</strong>="search term"]</span>', 'wpv-views' ), $view_name, $term );
			}
			break;
		case 'visitor':
		case 'manual':
			if ( $short ) {
				echo sprintf( __( 'Filter %s by <strong>text search</strong>', 'wpv-views' ), $search_where );
			} else {
				echo sprintf( __( 'Filter %s by a text search that will be added <strong>manually</strong> using the shortcode <span class="wpv-code">[wpv-filter-search-box]</span>.', 'wpv-views' ), $search_where );
			}
			break;
	}
	$summary .= ob_get_clean();
	$summary = apply_filters( 'wpv_filter_wpv_extend_post_search_summary', $summary, $view_settings );
	return $summary;
}

/**
* wpv_get_filter_post_date_summary_txt
*
*
* @since 1.8.0
*/
function wpv_get_filter_post_date_summary_txt( $view_settings ) {
	if (
		! isset( $view_settings['date_filter'] )
		|| ! is_array( $view_settings['date_filter'] )
		|| ! isset( $view_settings['date_filter']['date_conditions'] )
		|| ! is_array( $view_settings['date_filter']['date_conditions'] )
	) {
		return;
	}
	$defaults = array(
		'date_operator' => '=',
		'date_column' => 'post_date',
		'date_multiple_selected' => 'year',
		'year' => '',
		'month' => '',
		'week' => '',
		'day' => '',
		'dayofyear' => '',
		'dayofweek' => '',
		'hour' => '',
		'minute' => '',
		'second' => ''
	);
	$date_operator = array(
		'single' => array(
			'=' => __( 'equal to', 'wpv-views' ),
			'!=' => __( 'different from', 'wpv-views' ),
			'<' => __( 'before', 'wpv-views' ),
			'<=' => __( 'before or equal to', 'wpv-views' ),
			'>' => __( 'after', 'wpv-views' ),
			'>=' => __( 'after or equal to', 'wpv-views' )
		),
		'group' => array(
			'IN' => __( 'in any of those', 'wpv-views' ),
			'NOT IN' => __( 'not in any of those', 'wpv-views' ),
			'BETWEEN' => __( 'between those', 'wpv-views' ),
			'NOT BETWEEN' => __( 'not between those', 'wpv-views' )
		),
	);
	$date_options = array(
		'year' => __( 'year', 'wpv-views' ),
		'month' => __( 'month', 'wpv-views' ),
		'week' => __( 'week', 'wpv-views' ),
		'day' => __( 'day', 'wpv-views' ),
		'dayofyear' => __( 'day of the year', 'wpv-views' ),
		'dayofweek' => __( 'day of the week', 'wpv-views' ),
		'hour' => __( 'hour', 'wpv-views' ),
		'minute' => __( 'minute', 'wpv-views' ),
		'second' => __( 'second', 'wpv-views' )
	);
	$date_columns = array(
		'post_date' => __( 'Published date', 'wpv-views' ),
		'post_date_gmt' => __( 'Published date GMT', 'wpv-views' ),
		'post_modified' => __( 'Modified date', 'wpv-views' ),
		'post_modified_gmt' => __( 'Modified date GMT', 'wpv-views' )
	);
	$date_cond_counter = 0;
	if ( ! isset( $view_settings['date_filter']['date_relation'] ) ) {
		$view_settings['date_filter']['date_relation'] = 'AND';
	}
	ob_start();
	_e( 'Select posts whose', 'wpv-views' );
	foreach ( $view_settings['date_filter']['date_conditions'] as $date_condition ) {
		$date_condition = wp_parse_args( $date_condition, $defaults );
		if ( $date_cond_counter > 0 ) {
			echo esc_html( $view_settings['date_filter']['date_relation'] );
		}
		echo '<span class="wpv-filter-multiple-summary-item">';
		if ( isset( $date_operator['single'][$date_condition['date_operator']] ) ) {
			$date_cond_item = array();
			foreach ( $date_options as $date_opt => $date_opt_label ) {
				if (
					isset( $date_condition[$date_opt] )
					&& $date_condition[$date_opt] != ''
				) {
					$date_cond_item[] = $date_opt_label . ':' . $date_condition[$date_opt];
				}
			}
			echo sprintf(
				__( '<strong>%s</strong> is <strong>%s</strong>: %s', 'wpv-views' ),
				esc_html( $date_columns[$date_condition['date_column']] ),
				esc_html( $date_operator['single'][$date_condition['date_operator']] ),
				esc_html( implode( ', ', $date_cond_item ) )
			);
		} else {
			echo sprintf(
				__( '<strong>%s</strong> belongs to a <strong>%s</strong> <strong>%s</strong>: %s', 'wpv-views'),
				esc_html( $date_columns[$date_condition['date_column']] ),
				esc_html( $date_options[$date_condition['date_multiple_selected']] ),
				esc_html( $date_operator['group'][$date_condition['date_operator']] ),
				esc_html( $date_condition[$date_condition['date_multiple_selected']] )
			);
		}
		echo '</span>';
		$date_cond_counter++;
	}
	$data = ob_get_clean();
	return $data;
}

/**
 * wpv_get_filter_taxonomy_search_summary_txt
 *
 * Returns the taxonomy search filter summary for a View
 *
 * @param $view_settings
 *
 * @return array (string) $summary
 *
 * @since unknown
 */
function wpv_get_filter_taxonomy_search_summary_txt( $view_settings ) {
	if ( !isset( $view_settings['taxonomy_search_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_search_mode'] ) ) {
		$view_settings['taxonomy_search_mode'] = $view_settings['taxonomy_search_mode'][0];
	}
	if ( !isset( $view_settings['taxonomy_search_value'] ) ) {
		$view_settings['taxonomy_search_value'] = '';
	}
	if ( isset( $_GET['view_id'] ) ) {
		$view_name = get_the_title( intval( $_GET['view_id'] ) );
	} else {
		$view_name = 'view-name';
	}
	$view_name = esc_html( $view_name );

	ob_start();
	switch ( $view_settings['taxonomy_search_mode'] ) {
		case 'specific':
			$term = esc_html( $view_settings['taxonomy_search_value'] );
			if ( $term == '' ) {
				$term = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'Filter by this search term: <strong>%s</strong>.', 'wpv-views' ), $term );
			break;
		case 'shortcode':
			$term = esc_html( $view_settings['taxonomy_search_shortcode'] );
			if ( $term == '' ) {
				$term = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'Filter by a search term set by the View shortcode attribute: <strong>%s</strong>.', 'wpv-views' ), $term );
			echo sprintf( __( ' eg. <span class="wpv-code">[wpv-view name="%s" <strong>%s</strong>="search term"]</span>', 'wpv-views' ), $view_name, $term );
			break;
		case 'visitor':
			echo __( 'Show a <strong>text search</strong> for visitors.', 'wpv-views' );
			break;
		case 'manual':
			echo __( 'The text search will be added <strong>manually</strong> using the shortcode <span class="wpv-code">eg. [wpv-filter-search-box]</span>', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	return $data;
}


/**
 * wpv_get_filter_taxonomy_term_summary_txt
 *
 * Returns the taxonomy term filter summary for a View
 *
 * @param $view_settings
 *
 * @return array (string) $summary
 *
 * @since unknown
 */
function wpv_get_filter_taxonomy_term_summary_txt( $view_settings ) {
	if ( !isset( $view_settings['taxonomy_type'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_type'] ) ) {
		$view_settings['taxonomy_type'] = $view_settings['taxonomy_type'][0];
		if ( ! taxonomy_exists( $view_settings['taxonomy_type'] ) ) {
			return;
		}
	}
	if ( ! isset( $view_settings['taxonomy_terms_mode'] ) ) {
		return;
	}
	if ( ! isset( $view_settings['taxonomy_terms'] ) ) {
		$view_settings['taxonomy_terms'] = array();
	}
	if ( ! empty( $view_settings['taxonomy_terms'] ) ) {
		$adjusted_term_ids = array();
		foreach ( $view_settings['taxonomy_terms'] as $candidate_term_id ) {
			// WordPress 4.2 compatibility - split terms
			$candidate_term_id_splitted = wpv_compat_get_split_term( $candidate_term_id, $view_settings['taxonomy_type'] );
			if ( $candidate_term_id_splitted ) {
				$candidate_term_id = $candidate_term_id_splitted;
			}
			// WPML support
			$candidate_term_id = apply_filters( 'translate_object_id', $candidate_term_id, $view_settings['taxonomy_type'], true, null );
			$adjusted_term_ids[] = $candidate_term_id;
		}
		$view_settings['taxonomy_terms'] = $adjusted_term_ids;

	}
	ob_start();
	switch ( $view_settings['taxonomy_terms_mode'] ) {
		case 'THESE':
			$cat_text = '';
			$category_selected = $view_settings['taxonomy_terms'];
			$taxonomy = $view_settings['taxonomy_type'];
			foreach ( $category_selected as $cat ) {
				$term_check = term_exists( (int) $cat, $taxonomy );
				if (
					$term_check !== 0
					&& $term_check !== null
				) {
					$term = get_term( $cat, $taxonomy );
					if ( $cat_text != '' ) {
						$cat_text .= ', ';
					}
					$cat_text .= esc_html( $term->name );
				}
			}
			echo sprintf( __( 'Taxonomy is <strong>One</strong> of these: <strong>%s</strong>', 'wpv-views' ), $cat_text );
			break;
		case 'top_current_post':
			echo __( 'Taxonomy is set by the page where this View is inserted', 'wpv-views' );
			break;
		case 'CURRENT_PAGE': // @deprecated on 1.12.1
		case 'current_post_or_parent_post_view':
			echo __( 'Taxonomy is set by the current post', 'wpv-views' );
			break;
		case 'by_url':
			$taxonomy_terms_url_parameter = __( '#undefined#', 'wpv-views' );
			if (
				isset( $view_settings['taxonomy_terms_url'] )
				&& '' != $view_settings['taxonomy_terms_url']
			) {
				$taxonomy_terms_url_parameter = $view_settings['taxonomy_terms_url'];
			}
			echo sprintf( __( 'Taxonomy term ID is set by the URL parameter <strong>"%s"</strong>', 'wpv-views' ), $taxonomy_terms_url_parameter );
			break;
		case 'shortcode':
			$taxonomy_terms_shortcode_attr = __( '#undefined#', 'wpv-views' );
			if (
				isset( $view_settings['taxonomy_terms_shortcode'] )
				&& '' != $view_settings['taxonomy_terms_shortcode']
			) {
				$taxonomy_terms_shortcode_attr = $view_settings['taxonomy_terms_shortcode'];
			}
			echo sprintf( __( 'Taxonomy term ID is set by the shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), $taxonomy_terms_shortcode_attr );
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				if (
					isset( $view_settings['taxonomy_terms_framework'] )
					&& '' != $view_settings['taxonomy_terms_framework']
				) {
					$taxonomy_terms_framework = esc_html( $view_settings['taxonomy_terms_framework'] );
				} else {
					$taxonomy_terms_framework = __( 'None', 'wpv-views' );
				}
				echo sprintf( __( 'Taxonomy is set by the Framework option <strong>"%s"</strong>', 'wpv-views' ), $taxonomy_terms_framework );
			} else {
				$WP_Views_fapi->framework_missing_message_for_filters();
			}
			break;
		default:
			_e( 'Oops! It seems there is a filter by taxonomy terms that is missing some options', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_users_summary_txt
*
* Returns the users filter summary for a View
*
* @param $view_settings
* @param $short maybe DEPRECATED
* @param $post_id
*
* @returns (string) $summary
*
* @since unknown
*
* @todo check where this $post_id comes from
* @todo check where all those $_GET and $_POST are coming from
*/

function wpv_get_filter_users_summary_txt( $view_settings, $short=false, $post_id='' ) {
	global $WP_Views;
	if ( isset( $_GET['post'] ) ) {
		$view_name = get_the_title( intval( $_GET['post'] ) );
	} else {
		if ( isset( $_GET['view_id'] ) ) {
			$view_name = get_the_title( intval( $_GET['view_id'] ) );
		} else {
			$view_name = 'view-name';
		}
	}
	$view_name = esc_html( $view_name );
	if ( ! isset( $view_settings['users_mode'] ) ) {
        return;
    } elseif ( is_array( $view_settings['users_mode'] ) ) {
		$view_settings['users_mode'] = $view_settings['users_mode'][0];
	}
	if ( isset( $_GET['view_id'] ) ) {
		$_view_settings = $WP_Views->get_view_settings( intval( $_GET['view_id'] ) );
	}
	if ( isset( $_POST['id'] ) ) {
		$_view_settings = $WP_Views->get_view_settings( intval( $_POST["id"] ) );
	}
    if (
		! isset( $_view_settings )
		&& ! empty( $post_id )
	) {
        $_view_settings = $WP_Views->get_view_settings( $post_id );
    }

	$user_role = isset( $view_settings['roles_type'] )
		? wpv_get_filter_users_roles_txt( $view_settings['roles_type'] )
		: wpv_get_filter_users_roles_txt( $_view_settings['roles_type'] );

	if( ! $user_role ) {
		$user_role = 'administrator';
	}

	ob_start();
	switch ( $view_settings['users_mode'] ) {
		case 'this_user':
			if (
				isset( $view_settings['users_id'] )
				&& $view_settings['users_id'] > 0
			) {
				if ( $view_settings['users_query_in'] == 'include' ) {
					echo sprintf( __( 'Select users <strong>(%s)</strong> who have role <strong>%s</strong>', 'wpv-views' ), esc_html( $_view_settings['users_name'] ), esc_html( $user_role ) );
				} else {
					echo sprintf( __( 'Select all users with role <strong>%s</strong>, except of <strong>(%s)</strong>', 'wpv-views' ), esc_html( $user_role ), esc_html( $_view_settings['users_name'] ) );
				}
			} else {
				echo sprintf( __( 'Select all users with role <strong>%s</strong>', 'wpv-views' ), esc_html( $user_role ) );
			}
			break;
		case 'by_url':
			if (
				isset( $view_settings['users_url'] )
				&& '' != $view_settings['users_url']
			) {
				$url_users = esc_html( $view_settings['users_url'] );
			} else {
				$url_users = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if (
				isset( $view_settings['users_url_type'] )
				&& '' != $view_settings['users_url_type']
			) {
				$url_users_type = esc_html( $view_settings['users_url_type'] );
				switch ( $url_users_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$url_users_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}

			if ( $view_settings['users_query_in'] == 'include' ) {
				echo sprintf( __( 'Select users with the <strong>%s</strong> determined by the URL parameter <strong>"%s"</strong> and with role <strong>"%s"</strong>', 'wpv-views' ), $url_users_type, $url_users, esc_html( $user_role ) );
			} else {
				echo sprintf( __( 'Select all users with role <strong>%s</strong>, except of <strong>%s</strong> determined by the URL parameter <strong>"%s"</strong>', 'wpv-views' ), esc_html( $user_role ), $url_users_type, $url_users );
			}
			if ( '' != $example ) {
				echo '<br /><code>' . sprintf( __( ' eg. yoursite/page-with-this-view/?<strong>%s</strong>=%s', 'wpv-views' ), $url_users, $example ) . '</code>';
			}
			break;
		case 'shortcode':
			if (
				isset( $view_settings['users_shortcode'] )
				&& '' != $view_settings['users_shortcode']
			) {
				$auth_short = esc_html( $view_settings['users_shortcode'] );
			} else {
				$auth_short = __( 'None', 'wpv-views' );
			}
			if (
				isset( $view_settings['users_shortcode_type'] )
				&& '' != $view_settings['users_shortcode_type']
			) {
				$shortcode_users_type = esc_html( $view_settings['users_shortcode_type'] );
				switch ( $shortcode_users_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$shortcode_users_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}
			if ( $view_settings['users_query_in'] == 'include' ) {
				echo sprintf( __( 'Select users with <strong>%s</strong> set by the View shortcode attribute <strong>"%s"</strong> and with role <strong>"%s"</strong>', 'wpv-views' ), $shortcode_users_type, $auth_short, esc_html( $user_role ) );
			} else {
				echo sprintf( __( 'Select all users with role <strong>%s</strong>, except of <strong>%s</strong> set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), esc_html( $user_role ), $shortcode_users_type, $auth_short );
			}
			if ( '' != $example ) {
				echo '<br /><code>' . sprintf( __( ' eg. [wpv-view name="%s" <strong>%s</strong>="%s"]', 'wpv-views' ), $view_name, $auth_short, $example ) . '</code>';
			}
			break;
		case 'framework':
			global $WP_Views_fapi;
			if ( $WP_Views_fapi->framework_valid ) {
				if (
					isset( $view_settings['users_framework'] )
					&& '' != $view_settings['users_framework']
				) {
					$auth_framework = esc_html( $view_settings['users_framework'] );
				} else {
					$auth_framework = __( 'None', 'wpv-views' );
				}
				if (
					isset( $view_settings['users_framework_type'] )
					&& '' != $view_settings['users_framework_type']
				) {
					$auth_framework_type = esc_html( $view_settings['users_framework_type'] );
				} else {
					$auth_framework_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				}
				if ( $view_settings['users_query_in'] == 'include' ) {
					echo sprintf( __( 'Select users with <strong>%s</strong> set by the Framework option <strong>"%s"</strong> and with role <strong>"%s"</strong>', 'wpv-views' ), $auth_framework_type, $auth_framework, esc_html( $user_role ) );
				} else {
					echo sprintf( __( 'Select all users with role <strong>%s</strong>, except of <strong>%s</strong> set by the Framework option <strong>"%s"</strong>', 'wpv-views' ), esc_html( $user_role ), $auth_framework_type, $auth_framework );
				}
			} else {
				$WP_Views_fapi->framework_missing_message_for_filters();
			}
			break;
		default:
			_e( 'Oops! It seems there is a filter by taxonomy terms that is missing some options', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	if ( $short ) {
		// this happens on the Views table under Filter column
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
 * Returns the users roles text to be used in the filter summary for a View
 *
 * @param array $roles_type
 *
 * @returns (string) $user_role
 *
 * @since 2.4.1
 */
function wpv_get_filter_users_roles_txt( $roles_type ) {
	if( ! is_array( $roles_type ) ) {
		return null;
	}

	return '(' . implode( ', ', $roles_type ) . ')';
}

/**
* wpv_get_filter_meta_field_summary_txt
*
* Returns the termmeta fields filter summary for a View
*
* @param $view_settings	array
* @param $meta_type		string
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_meta_field_summary_txt( $view_settings, $meta_type ) {
	$result = '';
	$count = 0;
	foreach ( array_keys( $view_settings ) as $key ) {
		if (
			strpos( $key, $meta_type . '-field-' ) === 0
			&& strpos( $key, '_compare' ) === strlen( $key ) - strlen( '_compare' )
		) {
			$name = substr( $key, 0, strlen( $key ) - strlen( '_compare' ) );
			$count++;
			if ( $result != '' ) {
				if (
					isset( $view_settings[ $meta_type . '_fields_relationship' ] )
					&& $view_settings[ $meta_type . '_fields_relationship' ] == 'OR'
				) {
					$result .= __( ' OR', 'wpv-views' );
				} else {
					$result .= __( ' AND', 'wpv-views' );
				}
			}
			$result .= wpv_get_meta_field_summary( $name, $view_settings, $meta_type );
		}
	}
	return $result;
}

/**
* wpv_get_meta_field_summary
*
* Returns each meta field filter summary for a View
*
* @paran $type				string {$meta_type}-field-{$field-name}
* @param $view_settings		array
* @param $meta_type			string
*
* @returns (string) $summary
*
* @since 1.12
*/

function wpv_get_meta_field_summary( $type, $view_settings = array(), $meta_type ) {
	global $WP_Views_fapi;
	$field_name = substr( $type, strlen( $meta_type . '-field-' ) );
	$field_nicename = wpv_types_get_field_name( $field_name );
	$compare = array(
		'='				=> __( 'equal to', 'wpv-views' ),
		'!='			=> __( 'different from', 'wpv-views' ),
		'>'				=> __( 'greater than', 'wpv-views' ),
		'>='			=> __( 'greater than or equal', 'wpv-views' ),
		'<'				=> __( 'lower than', 'wpv-views' ),
		'<='			=> __( 'lower than or equal', 'wpv-views' ),
		'LIKE'			=> __( 'like', 'wpv-views' ),
		'NOT LIKE'		=> __( 'not like', 'wpv-views' ),
		'IN'			=> __( 'in', 'wpv-views' ),
		'NOT IN'		=> __( 'not in', 'wpv-views' ),
		'BETWEEN'		=> __( 'between', 'wpv-views' ),
		'NOT BETWEEN'	=> __( 'not between', 'wpv-views' )
	);
	$types = array(
		'CHAR' 			=> __( 'string', 'wpv-views' ),
		'NUMERIC'		=> __( 'number', 'wpv-views' ),
		'BINARY'		=> __( 'boolean', 'wpv-views' ),
		'DECIMAL'		=> 'DECIMAL',
		'DATE'			=> 'DATE',
		'DATETIME'		=> 'DATETIME',
		'TIME'			=> 'TIME',
		'SIGNED'		=> 'SIGNED',
		'UNSIGNED'		=> 'UNSIGNED'
	);
	if ( isset( $compare[ $view_settings[ $type . '_compare' ] ] ) ) {
		$compare_selected = esc_html( $compare[ $view_settings[ $type . '_compare' ] ] );
	} else {
		$compare_selected = __( 'related', 'wpv-views' );
	}
	if ( isset( $types[ $view_settings[ $type . '_type' ] ] ) ) {
		$type_selected = esc_html( $types[ $view_settings[ $type . '_type' ] ] );
	} else {
		$type_selected = __( 'value', 'wpv-views' );
	}
	$value_selected = esc_html( str_replace( ',', ', ', $view_settings[ $type . '_value' ] ) );
	if ( isset( $view_settings[ $type . '_decimals' ] ) && 'DECIMAL' == $type_selected ) {
		$decimals_selected = __( 'with ' . $view_settings[ $type . '_decimals' ] . ' decimal places', 'wpv-views' );
	} else {
		$decimals_selected = '';
	}
	ob_start();
	?>
	<span class="wpv-filter-multiple-summary-item">
	<?php
	if (
		! $WP_Views_fapi->framework_valid
		&& strpos( $value_selected, 'FRAME_KEY' ) !== false
	) {
		$WP_Views_fapi->framework_missing_message_for_filters( $field_nicename );
	} else {
		echo sprintf( __( '<strong>%s</strong> is a %s %s <strong>%s</strong> <strong>%s</strong>', 'wpv-views' ), $field_nicename, $type_selected, $decimals_selected, $compare_selected, $value_selected );
	}
	?>
	</span>
	<?php
	$buffer = ob_get_clean();
	return $buffer;
}


/* ************************************************************************* *\
        Summary filter hooks
\* ************************************************************************* */

// TODO Can we adjust filter priorities? currently the output depends on the order of add_filter() calls. This can break easily.

/**
 * wpv_query_type_summary_filter
 *
 * Returns the query type part when building the summary for a View.
 *
 * @param $summary
 * @param $view_id
 * @param $view_settings
 *
 * @return string $summary
 *
 * @since 1.6.0
 */
add_filter( 'wpv-view-get-content-summary', 'wpv_query_type_summary_filter', 5, 3 );

function wpv_query_type_summary_filter( $summary, $view_id, $view_settings ) {
    $summary .= wpv_get_query_type_summary( $view_settings );
    return $summary;
}

/**
 * wpv_pagination_summary_filter
 *
 * Returns the pagination part when building the summary for a View.
 *
 * @param $summary
 * @param $view_id
 * @param $view_settings
 *
 * @return string $summary
 *
 * @since unknown
 */
add_filter( 'wpv-view-get-content-summary', 'wpv_pagination_summary_filter', 6, 3 );

function wpv_pagination_summary_filter( $summary, $view_id, $view_settings ) {
    $summary .= wpv_get_pagination_summary( $view_settings );
    return $summary;
}


/**
 * wpv_limit_offset_summary_filter
 *
 * Returns the Limit and Offset part when building the summary for a View.
 *
 * @param $summary
 * @param $view_id
 * @param $view_settings
 *
 * @returns string $summary
 *
 * @since 1.6.0
 */
add_filter( 'wpv-view-get-content-summary', 'wpv_limit_offset_summary_filter', 5, 3 );

function wpv_limit_offset_summary_filter( $summary, $view_id, $view_settings ) {
    $summary .= wpv_get_limit_offset_summary( $view_settings );
    return $summary;
}


/**
 * wpv_ordering_summary_filter
 *
 * Returns the sorting part when building the summary for a View.
 *
 * @param $summary
 * @param $view_id
 * @param $view_settings
 *
 * @return string $summary
 *
 * @since 1.6.0
 */
add_filter( 'wpv-view-get-content-summary', 'wpv_ordering_summary_filter', 5, 3 );

function wpv_ordering_summary_filter( $summary, $view_id, $view_settings ) {
    $summary .= wpv_get_ordering_summary( $view_settings );
    return $summary;
}
