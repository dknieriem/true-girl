<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/3/2016
 * Time: 9:33 AM
 */
class TVO_REST_Comments_Controller extends TVO_REST_Controller {
	public $base = 'comments';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/import_testimonial', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'import_testimonial' ),
				'permission_callback' => array( $this, 'import_testimonial_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/import_edit_testimonial', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'import_edit_testimonial' ),
				'permission_callback' => array( $this, 'import_edit_testimonial_permissions_check' ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/ask_permission_email', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'ask_permission_email' ),
				'permission_callback' => array( $this, 'ask_permission_email_permissions_check' ),
			),
		) );
	}

	/**
	 * @param $request
	 *
	 * @return bool
	 */
	public function import_edit_testimonial_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}


	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function import_edit_testimonial( $request ) {
		$testimonial_data = $request->get_param( 'testimonial_obj' );
		$response         = array( 'code' => 1, 'html' => '', 'email_sent' => 0 );

		if ( ! empty( $testimonial_data ) ) {

			$testimonial    = $this->prepare_import_edit_testimonial( $testimonial_data );
			$testimonial_id = wp_insert_post( $testimonial );

			if ( ! empty( $testimonial_id ) ) {

				if ( ! empty( $testimonial_data['tags'] ) ) {
					tvo_update_testimonial_tags( $testimonial_id, $testimonial_data['tags'] );
				}

				$connection = get_option( 'tvo_api_delivery_service', false );
				if ( $testimonial_data['send_email'] && $connection != false ) {

					$response = array_merge( $response, array(
						'class'       => 'notice-success',
						'notice_text' => __( 'Approval email successfully sent to ' . $testimonial_data['email'], TVO_TRANSLATE_DOMAIN ),
						'email_sent'  => 1,
					) );

					add_post_meta( $testimonial_id, TVO_STATUS_META_KEY, TVO_STATUS_AWAITING_APPROVAL, true ) or update_post_meta( $testimonial_id, TVO_STATUS_META_KEY, TVO_STATUS_AWAITING_APPROVAL );

					/*Send user emails CODE BEGIN*/
					$api = Thrive_List_Manager::connectionInstance( $connection );

					$email_template       = tvo_get_email_template();
					$testimonial_info     = array(
						'name'    => $testimonial_data['name'],
						'content' => $testimonial_data['content'],
						'id'      => $testimonial_id,
					);
					$data['html_content'] = tvo_process_approval_email_content( $email_template, $testimonial_info );
					$subject              = tvo_get_email_template_subject();
					$data['subject']      = tvo_process_approval_email_subject( $subject, $testimonial_info );
					$data['email']        = strip_tags( $testimonial_data['email'] );
					$sent                 = $api->sendCustomEmail( $data );

					if ( $sent !== true ) {
						$response = array_merge( $response, array(
							'class'       => 'notice-error',
							'notice_text' => __( 'An error has occurred while trying to send email to ' . $testimonial_data['email'] . ' ', TVO_TRANSLATE_DOMAIN ) . '<a href="' . admin_url( 'admin.php?page=tve_dash_api_error_log' ) . '">' . __( 'Click here for more information', TVO_TRANSLATE_DOMAIN ) . '</a>',
						) );
					}
					/*Send user emails CODE ENDS*/

				} else {
					add_post_meta( $testimonial_id, TVO_STATUS_META_KEY, TVO_STATUS_READY_FOR_DISPLAY, true ) or update_post_meta( $testimonial_id, TVO_STATUS_META_KEY, TVO_STATUS_READY_FOR_DISPLAY );
					$response = array_merge( $response, array(
						'class'       => 'notice-success',
						'notice_text' => __( 'The testimonial was successfully created! Click ', TVO_TRANSLATE_DOMAIN ) . '<a href="' . admin_url( 'admin.php?page=tvo_admin_dashboard' ) . '">' . __( 'here', TVO_TRANSLATE_DOMAIN ) . '</a>' . __( ' to view the testimonial.', TVO_TRANSLATE_DOMAIN ),
					) );
				}

				do_action( 'tvo_log_testimonial_source_activity', array(
					'id'          => $testimonial_id,
					'source_type' => TVO_SOURCE_COMMENTS,
					'comment_id'  => $testimonial_data['comment_id'],
				) );

				$response['html'] = '<p class="tvo-green-text"><span class="dashicons dashicons-yes"></span> ' . __( 'Saved', TVO_TRANSLATE_DOMAIN ) . '</p>';
			}

			return new WP_REST_Response( $response, 200 );
		}

		return new WP_Error( 'cant-edit-import', __( 'ERROR: Empty testimonial', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );

	}


	/**
	 * @param $request
	 *
	 * @return bool
	 */
	public function ask_permission_email_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function ask_permission_email( $request ) {
		$permission    = $request->get_param( 'tvo_permission' );
		$email_name    = $request->get_param( 'tvo_email_name' );
		$email_content = $request->get_param( 'tvo_email_content' );

		$delivery_service = get_option( 'tvo_api_delivery_service', false );

		$ask_permission_email_response = tvo_get_ask_permission_email_response( $delivery_service, $email_data = array(
			'name'    => $email_name,
			'content' => strip_tags( $email_content ),
		), $permission );

		return new WP_REST_Response( $ask_permission_email_response, 200 );
	}

	/**
	 * Prepares the testimonial for DB
	 *
	 * @param array $testimonial_data
	 *
	 * @return array
	 */
	private function prepare_import_edit_testimonial( $testimonial_data = array() ) {
		return array(
			'post_title'   => $testimonial_data['title'],
			'post_content' => $testimonial_data['content'],
			'post_status'  => 'publish',
			'post_type'    => TVO_TESTIMONIAL_POST_TYPE,
			'meta_input'   => array(
				TVO_POST_META_KEY   => array(
					'name'        => $testimonial_data['name'],
					'email'       => $testimonial_data['email'],
					'website_url' => $testimonial_data['website_url'],
					'picture_url' => $testimonial_data['picture_url'],
					'role'        => $testimonial_data['role'],
					'comment_id'  => $testimonial_data['comment_id'],
				),
				TVO_SOURCE_META_KEY => TVO_SOURCE_COMMENTS,
			),
		);
	}
}
