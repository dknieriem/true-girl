<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/19/2016
 * Time: 10:40 AM
 */
class TVO_REST_Post_Meta_Controller extends TVO_REST_Controller {
	public $base = 'postmeta';

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
		$params = $this->prepare_item_for_database( $request );

		$testimonial = get_post( $params['key'] );
		if ( ! empty( $testimonial ) && $testimonial->post_type == TVO_TESTIMONIAL_POST_TYPE && is_numeric( $params['meta_value'] ) && in_array( $params['meta_value'], tvo_testimonial_statuses() ) ) {

			/*Updates testimonial status changes -> activity log*/
			do_action( 'tvo_log_testimonial_status_activity', array( 'id' => $params['key'], 'status' => $params['meta_value'] ) );
			update_post_meta( $params['key'], $params['meta_key'], $params['meta_value'] );

			$activity_log = tvo_get_testimonial_activity_log( $testimonial->ID );
			$data         = array(
				'activityLog'      => ! empty( $activity_log['activity_log'] ) ? $activity_log['activity_log'] : array(),
				'activityLogCount' => ! empty( $activity_log['total_count'] ) ? $activity_log['total_count'] : array(),
			);

			return new WP_REST_Response( $data, 200 );
		}

		return new WP_Error( 'cant-update', __( 'Error while updating the meta data', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}


	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {

		$testimonial = array(
			'key'        => $request->get_param( 'key' ),
			'meta_key'   => $request->get_param( 'meta_key' ),
			'meta_value' => $request->get_param( 'meta_value' ),
		);

		return $testimonial;
	}
}
