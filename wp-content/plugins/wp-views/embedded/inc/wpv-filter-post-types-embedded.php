<?php

/**
* wpv_post_default_settings
*
* Sets the default settings for Views listing posts
*
* @since unknown
 *
* TODO: Move all the filters to a class to allow proper unit testing.
*/

add_filter( 'wpv_view_settings', 'wpv_post_default_settings' );

function wpv_post_default_settings($view_settings) {
	if (!isset($view_settings['post_type'])) {
		$view_settings['post_type'] = array( 'any' );
	}
	if (!isset($view_settings['post_type_dont_include_current_page'])) {
		$view_settings['post_type_dont_include_current_page'] = true;
	}
	return $view_settings;
}

add_filter( 'wpv_filter_query', 'wpv_filter_get_post_types_arg', 10, 2 );

/**
 * Apply the 'post_type_dont_include_current_page' View setting.
 *
 * Maybe exclude the current post from the Views query based when needed.
 *
 * @since unknown
 * @since 2.3.0 Add support for Views displayed directly inside archive pages, 
 *     not in a nested structure, not inside a post of the native archive loop, 
 *     where the "current" post or page has no meaning, so nothing should be excluded.
 */

function wpv_filter_get_post_types_arg( $query, $view_settings ) {
    
    global $post;
    
    $post_type = $query['post_type'];
    // See if the post_type is exposed as a url arg.
    if (isset($view_settings['post_type_expose_arg']) && $view_settings['post_type_expose_arg']) {
        if ($_GET['wpv_post_type']) {
            $post_type = $_GET['wpv_post_type'];
        }
    }
    $query['post_type'] = $post_type;
    if ( 
		! isset( $view_settings['post_type_dont_include_current_page'] ) 
		|| $view_settings['post_type_dont_include_current_page']
	) {

		if ( isset( $_GET['wpv_aux_current_post_id'] ) ) { 
			// In AJAX pagination is_single() and is_page() do not work as expected, but it seems they return TRUE here anyway
			// @todo this works for the top_current_page, but not for the current_page...
			if ( isset( $query['post__not_in'] ) ) {
				$query['post__not_in'] = array_merge( (array) $query['post__not_in'], array( $_GET['wpv_aux_current_post_id'] ) );
			} else {
				$query['post__not_in'] = array( $_GET['wpv_aux_current_post_id'] );
			}
        } else if ( 
			is_single() 
			|| is_page() 
		) {
        	global $wp_query;
            if ( isset( $wp_query->posts[0] ) ) {
                $current_post = $wp_query->posts[0];
                $post_not_in_list = $current_post ? array( $current_post->ID ) : array();
				if ( isset( $query['post__not_in'] ) ) {
					$query['post__not_in'] = array_merge( (array) $query['post__not_in'], $post_not_in_list );
				} else {
					$query['post__not_in'] = $post_not_in_list;
				}
            }
		} else if (
			count( apply_filters( 'wpv_filter_wpv_get_current_views_tree', array() ) ) === 1 
			&& (
				! did_action( 'loop_start' )
				|| (
					did_action( 'loop_start' ) 
					&& did_action( 'loop_end' )
				)
			) && ( 
				is_archive() 
				|| is_search() 
				|| is_home() 
			)
		) {
			return $query;
        } else {
			global $post;
			if ( $post instanceof WP_Post ) {
				$post_not_in_list = array( $post->ID );
				if ( isset( $query['post__not_in'] ) ) {
					$query['post__not_in'] = array_merge( (array) $query['post__not_in'], $post_not_in_list );
				} else {
					$query['post__not_in'] = $post_not_in_list;
				}
			}
		}
    }
    
    return $query;

}

/**
* wpv_filter_post_exclude_current_requires_current_page
*
* Filter hooked to wpv_filter_requires_current_page.
* When the option post_type_dont_include_current_page is checked, we need to pass the wpv_aux_current_post_id value in an input when doing AJAX pagination
*
* @since 1.5.0
*/

add_filter( 'wpv_filter_requires_current_page', 'wpv_filter_post_exclude_current_requires_current_page', 10, 2 );

function wpv_filter_post_exclude_current_requires_current_page( $state, $view_settings ) {
	if ( $state ) {
		return $state; // Already set
	}

	$query_type = 'posts';

	if (
		is_array( $view_settings )
		&& isset( $view_settings['view-query-mode'] )
	) {
		switch ( $view_settings['view-query-mode'] ) {
			case 'normal':
				if ( isset( $view_settings['query_type'][0] ) ) {
					$query_type = $view_settings['query_type'][0];
				}
				break;
		}
	}
	if ( 'posts' === $query_type
		&& (
			! isset( $view_settings['post_type_dont_include_current_page'] )
			|| $view_settings['post_type_dont_include_current_page']
		)
	) {
		$state = true;
	}
	return $state;
}

