<?php

/**
 * Class TVO_REST_Shortcodes_Controller
 */
class TVO_REST_Shortcodes_Controller extends TVO_REST_Controller {

	public $base = 'shortcodes';

	private static $shortcode_meta_ids = 'tvo_shortcode_ids';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/frontend/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item_frontend' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
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

		$type = $request->get_param( 'type' );

		$items = tvo_get_shortcodes( $type );

		if ( ! is_array( $items ) ) {
			return new WP_Error( 'cant-get-data', __( 'message', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $items, 200 );
	}

	/**
	 * Get item config
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item_frontend( $request ) {
		$id = $request->get_param( 'id' );

		$config = tvo_get_shortcode_config( $id );

		$template = tvo_render_shortcode( $config );

		return new WP_REST_Response( array(
			'config'   => $config,
			'template' => $template,
		), 200 );
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
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {

		$params    = $request->get_params();
		$shortcode = array(
			'post_status'  => 'draft',
			'post_content' => $params['content'],
			'post_title'   => $params['name'],
			'post_type'    => TVO_SHORTCODE_POST_TYPE,
			'meta_input'   => array(
				'tvo_shortcode_type'   => $params['type'],
				'tvo_shortcode_config' => empty( $params['config'] ) ? array() : $params['config'],
			),
		);

		$shortcode_id = wp_insert_post( $shortcode, true );

		if ( ! empty( $shortcode_id ) && ! is_wp_error( $shortcode_id ) ) {
			$shortcode['url']    = get_permalink( $shortcode_id );
			$shortcode['id']     = $shortcode_id;
			$shortcode['config'] = tvo_get_shortcode_config( $shortcode_id, $params['type'] );

			return new WP_REST_Response( $shortcode, 200 );
		}

		return new WP_Error( 'cant-create', __( 'message', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {
		$post = $this->prepare_item_for_database( $request );

		$post_id = wp_update_post( $post, true );

		if ( is_wp_error( $post_id ) ) {
			$error = $post_id->get_error_message();

			return new WP_Error( 'cant-update', __( $error, TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
		} else {
			return new WP_REST_Response( $post_id, 200 );
		}
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {

		$id     = $request->get_param( 'id' );
		$title  = $request->get_param( 'name' );
		$config = $request->get_param( 'config' );

		$post = array( 'ID' => $id );

		/* update shortcode title from admin area */
		if ( ! empty( $title ) ) {
			$post['post_title'] = $title;
		}

		/* update config shortcode */
		if ( ! empty( $config ) ) {
			$post['meta_input']['tvo_shortcode_config'] = $config;
		}

		return $post;

	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}


	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$shortcode = get_post( $id );

		if ( ! empty( $shortcode ) ) {
			if ( wp_trash_post( $id ) != false ) {
				return new WP_REST_Response( true, 200 );
			}
		}

		return new WP_Error( 'Invalid shortcode ID', __( 'Delete action failed', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->create_item_permissions_check( $request );
	}
}
