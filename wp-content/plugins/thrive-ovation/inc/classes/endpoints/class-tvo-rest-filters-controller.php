<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 6/15/2016
 * Time: 10:27 AM
 */
class TVO_REST_Filters_Controller extends TVO_REST_Controller {
	public $base = 'filters';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();
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
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {
		$params   = $this->prepare_item_for_database( $request );
		$response = tvo_update_option( TVO_FILTERS_OPTION, $params );

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {

		$filters = array(
			'show_hide_tags'      => $request->get_param( 'show_hide_tags' ),
			'show_hide_type'      => $request->get_param( 'show_hide_type' ),
			'show_hide_status'    => $request->get_param( 'show_hide_status' ),
			'testimonial_content' => $request->get_param( 'testimonial_content' ),
		);

		return $filters;
	}
}
