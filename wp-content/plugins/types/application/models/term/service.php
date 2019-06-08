<?php

/**
 * Class Types_Term_Service
 *
 * @since 2.3
 */
class Types_Term_Service {
	/**
	 * Returns WP_User of $params['user_id'] | $params['user_name'] | $params['user_current'].
	 * If all keys not set it will return the author of the current post.
	 *
	 * @param $params
	 *
	 * @return null|WP_User
	 */
	public function find_by_user_params( $params ) {
		if ( isset( $params['term_id'] ) && $params['term_id']  ) {
			// by user param id
			return get_term( $params['term_id'] );
		}

		// by Views loop
		global $WP_Views;

		if ( class_exists( 'WP_Views' )
		     && $WP_Views instanceof WP_Views
		     && isset( $WP_Views->taxonomy_data['term']->term_id )
		     && ! empty( $WP_Views->taxonomy_data['term']->term_id )
		) {
			return get_term( $WP_Views->taxonomy_data['term']->term_id );
		}

		// by current page
		if ( is_tax() || is_category() || is_tag() ) {
			global $wp_query;
			$term = $wp_query->get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				return get_term( $term->term_id );
			}
		}

		return false;
	}
}