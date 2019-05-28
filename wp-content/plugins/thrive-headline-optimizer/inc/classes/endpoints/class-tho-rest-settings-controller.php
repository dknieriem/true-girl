<?php

/**
 * Created by PhpStorm.
 * User: sala
 * Date: 02-Feb-16
 * Time: 14:12
 */
class THO_REST_Settings_Controller extends THO_REST_Controller {

	public $base = 'settings';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( self::$NAMESPACE . self::$VERSION, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( false ),
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


		$default_settings = tho_get_default_values( THO_SETTINGS_OPTION );
		$settings         = tho_get_option( THO_SETTINGS_OPTION, $default_settings );

		return new WP_REST_Response( $settings, 200 );
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

	public function update_item_permissions_check( $request ) {
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

		$settings = $this->prepare_item_for_database( $request );

		$response = tho_update_option( THO_SETTINGS_OPTION, $settings, true );

		if ( $response ) {
			return new WP_REST_Response( $settings, 200 );
		}

		return new WP_Error( 'cant-update', __( 'Error while updating the settings', THO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );

	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {

		$settings = array(
			'click_through'                => $request->get_param( 'click_through' ),
			'scrolling_signal'             => $request->get_param( 'scrolling_signal' ),
			'scrolling_signal_value'       => $request->get_param( 'scrolling_signal_value' ),
			'time_on_content_signal'       => $request->get_param( 'time_on_content_signal' ),
			'time_on_content_signal_value' => $request->get_param( 'time_on_content_signal_value' ),
			'enable_automatic_winner'      => $request->get_param( 'enable_automatic_winner' ),
			'minimum_engagements'          => $request->get_param( 'minimum_engagements' ),
			'minimum_duration'             => $request->get_param( 'minimum_duration' ),
			'chance_to_beat_original'      => $request->get_param( 'chance_to_beat_original' ),
		);

		return $settings;
	}
}