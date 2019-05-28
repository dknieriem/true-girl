<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TGE_Ajax_Controller
 *
 * Ajax controller to handle admin ajax requests
 * Specially built for backbone models
 */
class TGE_Ajax_Controller {

	/**
	 * @var TGE_Ajax_Controller $instance
	 */
	protected static $instance;

	/**
	 * TGE_Ajax_Controller constructor.
	 * Protected constructor because we want to use it as singleton
	 */
	protected function __construct() {
	}

	/**
	 * Gets the SingleTone's instance
	 *
	 * @return TGE_Ajax_Controller
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param string $message the error message.
	 * @param string $status the error status.
	 *
	 * @return null
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status );
		wp_send_json_error( array( 'message' => $message ) );

		return null;
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param int $key the parameter kew.
	 * @param null $default the default value.
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {
		if ( ! check_ajax_referer( TGE_Ajax::AJAX_NONCE_NAME, '_nonce', false ) ) {
			$this->error( __( 'Invalid request.', Thrive_Graph_Editor::T ) );
		}

		$route = $this->param( 'route' );

		$route    = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$function = $route . '_action';

		if ( ! method_exists( $this, $function ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', Thrive_Graph_Editor::T ), $function ) );
		}

		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		$model  = json_decode( file_get_contents( 'php://input' ), true );

		return call_user_func( array( $this, $function ), $method, $model );
	}

	protected function settings_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
				update_post_meta( $model['quiz_id'], 'tge_display_weight', $model['display_weight'] );

				return $model;
		}
	}

	protected function question_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
				if ( empty( $model['quiz_id'] ) ) {
					$this->error( __( 'Question actions cannot be performed without quiz_id', Thrive_Graph_Editor::T ) );
				}
				$question_manager = new TGE_Question_Manager( $model['quiz_id'] );

				$question = $question_manager->save_question( $model );

				if ( $question ) {
					return $question_manager->prepare_question( $question );
				}

				return $this->error( __( 'Question could not be saved' ), Thrive_Graph_Editor::T );
				break;
			case 'DELETE':
				$id               = intval( $this->param( 'id' ) );
				$question_manager = new TGE_Question_Manager();
				$deleted          = $question_manager->delete_question( $id );

				return $deleted;
				break;
		}

		$this->error( __( 'No action could be executed on question route', Thrive_Graph_Editor::T ) );
	}

	protected function connection_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
				$link_manager = new TGE_Link_Manager( $model['source'], $model['target'] );

				return $link_manager->connect();
				break;
		}
		$this->error( __( 'No action could be executed on connection route', Thrive_Graph_Editor::T ) );
	}

	protected function disconnection_action( $method, $model ) {
		switch ( $method ) {
			case 'PUT':
				$link_manager = new TGE_Link_Manager( $model['source'], $model['target'] );

				$saved = $link_manager->disconnect();

				return $saved;
				break;
		}
		$this->error( __( 'No action could be executed on connection route', Thrive_Graph_Editor::T ) );
	}
}
