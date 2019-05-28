<?php

class TVO_REST_Tags_Controller extends TVO_REST_Controller {

	public $base = 'tags';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();

		//TODO: please choose a shorter name for the route :) @ovidiu
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/add_multiple_tags_to_multiple_testimonials', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'add_multiple_tags_to_multiple_testimonials' ),
				'permission_callback' => array( $this, 'add_multiple_tags_to_multiple_testimonials_permissions_check' ),
			),
		) );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$tags = array();

		$terms = get_terms( TVO_TESTIMONIAL_TAG_TAXONOMY, array( 'hide_empty' => false ) );
		foreach ( $terms as $t ) {
			if ( ! empty( $t->count ) ) {
				$tags[] = array(
					'id'   => $t->term_id,
					'name' => $t->name,
				);
			}
		}

		return new WP_REST_Response( $tags, 200 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}


	/**
	 * Adds / Deletes tags to / from multiple testimonials
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function add_multiple_tags_to_multiple_testimonials( $request ) {

		$testimonial_ids = $request->get_param( 'tvo_testimonial_ids' );
		$tags_ids        = $request->get_param( 'tvo_tags_ids' );
		$delete_tags_ids = $request->get_param( 'tvo_delete_tags_ids' );
		$return          = array();

		if ( is_array( $testimonial_ids ) ) {
			foreach ( $testimonial_ids as $testimonial_id ) {

				$testimonial_tags = tvo_get_testimonial_tags_ids( $testimonial_id );
				$tags_to_add      = array();
				$tags_to_delete   = array();

				if ( is_array( $tags_ids ) ) {
					foreach ( $tags_ids as $tag_id ) {
						if ( ! in_array( $tag_id, $testimonial_tags ) ) {
							$tags_to_add[] = $tag_id;
						}
					}
				}

				if ( is_array( $delete_tags_ids ) ) {
					foreach ( $delete_tags_ids as $tag_id ) {
						if ( in_array( $tag_id, $testimonial_tags ) ) {
							$tags_to_delete[] = $tag_id;
						}
					}
				}

				foreach ( $tags_to_add as $tag ) {
					$result              = tvo_attach_tag_to_testimonial( $testimonial_id, $tag );
					$obj                 = new stdClass();
					$obj->testimonial_id = $testimonial_id;
					$obj->tag_id         = $result['tag']->term_id;
					$obj->tag_name       = $result['tag']->name;
					$return[]            = $obj;
				}

				foreach ( $tags_to_delete as $tag ) {
					tvo_delete_testimonial_tag( $testimonial_id, $tag );
					$return[] = $tag;
				}
			}
		}

		return new WP_REST_Response( $return, 200 );
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$params = $request->get_params();

		$result = tvo_save_testimonial_tag( $params );

		if ( $result['status'] == 'error' ) {
			return new WP_Error( 'cant-update', $result['message'], array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Detach one tag belonging to post
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$params = $request->get_body_params();
		if ( empty( $params['post_id'] ) || empty( $params['id'] ) ) {
			return new WP_Error( 'cant-delete', __( 'Missing ID from parameter list', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
		}
		$result = tvo_delete_testimonial_tag( $params['post_id'], $params['id'] );
		if ( $result['status'] == 'error' ) {
			return new WP_Error( 'cant-delete', $result['message'], array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param $request
	 *
	 * @return bool
	 */
	public function add_multiple_tags_to_multiple_testimonials_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}
}
