<?php

/**
 * Class TVO_REST_Settings_Controller
 */
class TVO_REST_Settings_Controller extends TVO_REST_Controller {

	public $base = 'settings';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		//TODO: if some routes are already registered in the parrent class, call parent::register_routes(); and define only the new ones. check the comments controller @ovidiu

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/' . 'landing_testimonial_autocomplete', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'landing_testimonial_autocomplete' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $args = array( 'post_type' => array( 'post', 'page' ), 'post_status' => 'publish', 'posts_per_page' => - 1 ),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/' . 'get_post_name_by_id', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'get_post_name_by_id' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $args = array( 'post_type' => array( 'post', 'page' ), 'post_status' => 'publish', 'posts_per_page' => - 1 ),
			),
		) );


		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
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

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/' . 'api', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'new_api' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/api/activate', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_active_service' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/api/testconnection', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'test_connection' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/email/config', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_email_template' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/landing-page/config', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_landing_page_settings' ),
				'permission_callback' => array( $this, 'update_landing_page_settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/landing-page/config', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_landing_page_settings' ),
				'permission_callback' => array( $this, 'get_landing_page_settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/email/test', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'test_confirmation_email' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/email/process', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'process_confirmation_email_template' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/default-placeholder', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_image_placeholder' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	public function landing_testimonial_autocomplete( $request ) {
		if ( ! $this->param( 'q' ) ) {
			wp_die( 0 );
		}
		$s = wp_unslash( $this->param( 'q' ) );
	 
		$posts = get_posts(array(
			'posts_per_page' => 10,
			's'              => $s,
		));

		$json = array();
		foreach ( $posts as $post ) {
			if( $post->ID ) {
				$json [] = array(
					'id'    => $post->ID,
					'label' => $post->post_title,
					'value' => $post->post_title
				);
			}
		}

		wp_send_json( $json );
	}

	public function get_post_name_by_id( $request ) {

		$postId = (int) $request->get_param( 'postId' );
		$title = '';

		if( $postId ) {
			$post = get_post( $postId );
			if($post->post_title){
				$title = $post->post_title;
			}
		}

		wp_send_json( $title );
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param $key
	 * @param null $default
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$image_placeholder = tvo_get_default_image_placeholder();

		$email_template         = tvo_get_option( TVO_EMAIL_TEMPLATE_OPTION );
		$email_template_subject = tvo_get_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION );

		/*Email stuff icons*/
		$landing_page_settings = tvo_get_option( TVO_LANDING_PAGE_SETTINGS_OPTION );
		$settings              = array(
			'tvo_completed_landing_page' => ! empty( $landing_page_settings['approve'] ) && ! empty( $landing_page_settings['not_approve'] ),
			'tvo_completed_email_config' => ! empty( $email_template ) && ! empty( $email_template_subject ),
			'tvo_image_placeholder'      => $image_placeholder,
		);

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

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}


	/**
	 * @return bool
	 */
	public function get_landing_page_settings_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get landing page settings
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_landing_page_settings() {
		$settings = tvo_get_option( TVO_LANDING_PAGE_SETTINGS_OPTION );

		return new WP_REST_Response( $settings, 200 );

	}

	/**
	 * @return bool
	 */
	public function update_landing_page_settings_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Update landing page settings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_landing_page_settings( $request ) {
		$landing_page_settings = $this->prepare_landing_page_settings_for_database( $request );

		$response = tvo_update_option( TVO_LANDING_PAGE_SETTINGS_OPTION, $landing_page_settings );

		if ( $response ) {
			return new WP_REST_Response( $landing_page_settings, 200 );
		}

		return new WP_Error( 'cant-update-landing-settings', __( 'Error while updating the landing page settings', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$email_template         = tvo_get_option( TVO_EMAIL_TEMPLATE_OPTION );
		$email_template_subject = tvo_get_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION );
		$image_placeholder      = tvo_get_default_image_placeholder();

		/*Email stuff icons*/
		$landing_page_settings = tvo_get_option( TVO_LANDING_PAGE_SETTINGS_OPTION );
		$settings              = array(
			'tvo_completed_landing_page' => ! empty( $landing_page_settings['approve'] ) && ! empty( $landing_page_settings['not_approve'] ),
			'tvo_completed_email_config' => ! empty( $email_template ) && ! empty( $email_template_subject ),
			'tvo_image_placeholder'      => $image_placeholder,
		);

		return new WP_REST_Response( $settings, 200 );

	}

	/**
	 * Update default image placeholder
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_image_placeholder( $request ) {

		$url = $request->get_param( 'image' );

		$response = tvo_update_option( TVO_DEFAULT_PLACEHOLDER, $url );

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * add new email service connection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function new_api( $request ) {
		define( 'DOING_AJAX', true );
		$connection = $request->get_param( 'api' );
		$api        = Thrive_List_Manager::connectionInstance( $connection );

		update_option( 'tvo_api_delivery_service', $connection );

		$connect = $api->readCredentials();

		return new WP_REST_Response( json_encode( $connect ), 200 );
	}

	/**
	 * update active email service connection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function test_connection( $request ) {
		define( 'DOING_AJAX', true );
		$connection = $request->get_param( 'connection' );

		$api  = Thrive_List_Manager::connectionInstance( $connection );
		$test = $api->testConnection();
		if ( $test === true ) {
			$class = 'updated';

			$result = '<div class="' . $class . '"><p>' . __( 'Connection was made successfully', TVO_TRANSLATE_DOMAIN ) . '</p></div>';
		} else {
			$class = 'error';

			$result = '<div class="' . $class . '"><p>' . $test . '</p></div>';
		}

		return new WP_REST_Response( json_encode( $result ), 200 );
	}

	/**
	 * update active email service connection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_active_service( $request ) {

		$connection = $request->get_param( 'connection' );

		if ( ! empty( $connection ) ) {
			$result = update_option( 'tvo_api_delivery_service', $connection );

			return new WP_REST_Response( json_encode( $result ), 200 );
		}

		return new WP_Error( 'cant-update', __( 'Error while updating the email template', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Update confirmation email template
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_email_template( $request ) {
		$email_template = $request->get_param( 'template' );
		$email_subject  = $request->get_param( 'subject' );

		if ( ! empty( $email_template ) && ! empty( $email_subject ) ) {
			$result_template = tvo_update_option( TVO_EMAIL_TEMPLATE_OPTION, $email_template );
			$result_subject  = tvo_update_option( TVO_EMAIL_TEMPLATE_SUBJECT_OPTION, $email_subject );

			return new WP_REST_Response( json_encode( $result_template && $result_subject ), 200 );
		}

		return new WP_Error( 'cant-update', __( 'The email subject and message are mandatory. Error while updating the email template', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * process confirmation email template
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function process_confirmation_email_template( $request ) {
		$params = $request->get_params();
		if ( empty( $params['template'] ) ) {
			$params['template'] = tvo_get_email_template();
		}
		if ( empty( $params['subject'] ) ) {
			$params['subject'] = tvo_get_email_template_subject();
		}
		if ( empty( $params['data'] ) ) {
			$params['data'] = array();
		}
		$data['template'] = tvo_process_approval_email_content( $params['template'], $params['data'] );
		$data['subject']  = tvo_process_approval_email_subject( $params['subject'], $params['data'] );
		if ( ! empty( $data['template'] ) && ! empty( $data['subject'] ) ) {
			return new WP_REST_Response( $data, 200 );
		}

		return new WP_Error( 'cant-update', __( 'Error', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Test confirmation email template
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function test_confirmation_email( $request ) {
		$email_template      = $request->get_param( 'template' );
		$email_subject       = $request->get_param( 'subject' );
		$email_template_text = strip_tags( $email_template );
		$email_address       = $request->get_param( 'email' );

		if ( ! empty( $email_template_text ) && ! empty( $email_address ) && ! empty( $email_subject ) ) {
			$connection = get_option( 'tvo_api_delivery_service', false );
			if ( ! $connection ) {
				return new WP_Error( 'cant-update', __( 'No active connection set', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
			}
			$api = Thrive_List_Manager::connectionInstance( $connection );

			$data['html_content'] = $email_template;
			$data['text_content'] = $email_template_text;
			$data['subject']      = $email_subject;
			$data['email']        = $email_address;
			$sent                 = $api->sendCustomEmail( $data );

			if ( $sent === true ) {
				return new WP_REST_Response( json_encode( $sent ), 200 );
			}

			return new WP_Error( 'cant-update', __( 'Sending test email failed', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );

		}

		return new WP_Error( 'cant-update', __( 'Sending test email failed', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	private function prepare_landing_page_settings_for_database( $request ) {
		$settings = array(
			'approve'              => $request->get_param( 'approve' ),
			'approve_url'          => $request->get_param( 'approve_url' ),
			'approve_post_id'      => $request->get_param( 'approve_post_id' ),
			'approve_post_val'     => $request->get_param( 'approve_post_val' ),
			'not_approve'          => $request->get_param( 'not_approve' ),
			'not_approve_url'      => $request->get_param( 'not_approve_url' ),
			'not_approve_post_id'  => $request->get_param( 'not_approve_post_id' ),
			'not_approve_post_val' => $request->get_param( 'not_approve_post_val' ),
		);

		return $settings;
	}
}
