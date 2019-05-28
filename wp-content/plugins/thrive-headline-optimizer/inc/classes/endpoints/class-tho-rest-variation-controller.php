<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/13/2016
 * Time: 12:16 PM
 */
class THO_REST_Variation_Controller extends THO_REST_Controller {
	public $base = 'variation';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Set a variation as inactive
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {
		$params = $this->prepare_variation_for_being_stopped( $request );

		if ( ! empty( $params['id'] ) && is_numeric( $params['id'] ) && ! empty( $params['test_id'] ) && is_numeric( $params['test_id'] ) ) {

			global $thodb;

			$variation               = new stdClass();
			$variation->id           = $params['id'];
			$variation->active       = 0;
			$variation->stopped_date = date( 'Y-m-d H:i:s' );
			$return                  = $thodb->save_test_item( $variation );

			if ( $return ) {

				$test_items = $thodb->get_test_items( $params['test_id'], true );
				if ( count( $test_items ) == 1 ) {
					$test = tho_get_running_test( array(
						'test_id' => $params['test_id'],
						'status'  => THO_TEST_STATUS_ACTIVE,
					) );
					tho_set_test_winner( $test, $test_items[0] );
				}
			}

			return new WP_REST_Response( $params['test_id'], 200 );

		}

		return new WP_Error( 'cant-update', __( 'Error while deleting the variation. Invalid parameters', THO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Prepare variation for being stopped
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object $prepared_item
	 */
	private function prepare_variation_for_being_stopped( $request ) {
		return array(
			'id'      => $request->get_param( 'id' ),
			'test_id' => $request->get_param( 'test_id' ),
		);
	}

}