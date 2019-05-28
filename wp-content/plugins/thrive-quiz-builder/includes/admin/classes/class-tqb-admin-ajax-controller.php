<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/2/2016
 * Time: 11:35 AM
 *
 * @package Thrive Quiz Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Admin_Ajax_Controller
 *
 * Ajax controller to handle admin ajax requests
 * Specially built for backbone models
 */
class TQB_Admin_Ajax_Controller {

	/**
	 * @var TQB_Admin_Ajax_Controller $instance
	 */
	protected static $instance;

	/**
	 * TQB_Admin_Ajax_Controller constructor.
	 * Protected constructor because we want to use it as singleton
	 */
	protected function __construct() {
	}

	/**
	 * Gets the SingleTone's instance
	 *
	 * @return TQB_Admin_Ajax_Controller
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new TQB_Admin_Ajax_Controller();
		}

		return self::$instance;
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param string $message the error message.
	 * @param string $status the error status.
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status );
		echo esc_attr( $message );
		wp_die();
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
		if ( ! check_ajax_referer( 'tqb_admin_ajax_request', '_nonce', false ) ) {
			$this->error( sprintf( __( 'Invalid request.', Thrive_Quiz_Builder::T ) ) );
		}

		$route = $this->param( 'route' );

		$route       = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$method_name = $route . '_action';

		if ( ! method_exists( $this, $method_name ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', Thrive_Quiz_Builder::T ), $method_name ) );
		}

		return $this->{$method_name}();
	}

	/**
	 * Performs actions for Quizzes based on request's method and model
	 * Dies with error if the operation was not executed
	 *
	 * @return mixed
	 */
	protected function quiz_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		if ( ( $custom = $this->param( 'custom' ) ) ) {
			switch ( $custom ) {
				case 'chart_data':
					return tqb_get_chart_data( $this->param( 'ID' ) );
					break;
				case 'update_order':
					$ordered = $this->param( 'new_order', array() );
					foreach ( $ordered as $post_id => $order ) {
						TQB_Post_meta::update_quiz_order( $post_id, $order );
					}

					return $ordered;
					break;
				case 'wizard_complete':
					$quiz_id = $this->param( 'ID' );
					if ( ! empty( $quiz_id ) && is_numeric( $quiz_id ) ) {
						return TQB_Post_meta::update_wizard_meta( $quiz_id );
					}

					return false;
					break;
				default:
					return array();
			}
		}

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$model        = json_decode( file_get_contents( 'php://input' ), true );
				$quiz_manager = new TQB_Quiz_Manager();
				if ( ! ( $quiz_id = $quiz_manager->save_quiz( $model ) ) ) {
					$this->error( __( 'Could not save', Thrive_Quiz_Builder::T ) );
				}

				/**
				 * Add a default image with blank template
				 * so user can be redirected to this new post when he clicks on
				 * create new social share badge
				 */
				$default_badge = array(
					'post_parent' => $quiz_id,
					'template'    => 'blank',
				);
				tie_save_image( $default_badge );

				return $quiz_id;
				break;
			case 'DELETE':
				$quiz_manager = new TQB_Quiz_Manager( $this->param( 'ID', true ) );
				$deleted      = $quiz_manager->delete_quiz();
				if ( ! $deleted ) {
					$this->error( __( 'Could not delete', Thrive_Quiz_Builder::T ) );
				}

				return $deleted;
				break;
			case 'GET':
				$quiz_manager = new TQB_Quiz_Manager( $this->param( 'ID', 0 ) );
				$quiz         = $quiz_manager->get_quiz();

				if ( $quiz === false ) {
					$this->error( __( 'Quiz not found', Thrive_Quiz_Builder::T ) );
				}

				return $quiz;
				break;
		}
	}

	/**
	 * Style action route
	 *
	 * @return array
	 */
	protected function style_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':

				if ( is_numeric( $model['style'] ) && ! empty( $model['quiz_id'] ) ) {
					/* Modify the meta only if the style was changed */
					if ( $model['style'] != TQB_Post_meta::get_quiz_style_meta( $model['quiz_id'] ) ) {
						TQB_Post_meta::update_quiz_style_meta( $model['quiz_id'], $model );
					}
				} else {
					$this->error( __( 'Style could not be saved', Thrive_Quiz_Builder::T ) );
				}

				return array( 'style' => $model['style'] );
				break;
			case 'DELETE':
				break;
			case 'GET':
				break;
		}
	}

	/**
	 * GET quiz list
	 *
	 * @return array
	 */
	protected function quizzes_action() {
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				break;
			case 'DELETE':
				break;
			case 'GET':
				return TQB_Quiz_Manager::get_quizzes();
				break;
		}
	}

	/**
	 * Reporting action route
	 *
	 * @return array
	 */
	protected function reporting_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				break;
			case 'GET':
				if ( isset( $model['quiz_id'] ) && ! empty( $model['report_type'] ) ) {

					$filter['date']       = empty( $model['date'] ) ? false : $model['date'];
					$filter['interval']   = empty( $model['interval'] ) ? false : $model['interval'];
					$filter['start_date'] = empty( $model['start_date'] ) ? null : $model['start_date'];
					$filter['end_date']   = empty( $model['end_date'] ) ? null : $model['end_date'];
					$reporting_manager    = new TQB_Reporting_Manager( $model['quiz_id'], $model['report_type'] );
					$data                 = $reporting_manager->get_report( $filter );
				} else {
					$this->error( __( 'Something went wrong', Thrive_Quiz_Builder::T ) );
				}

				return $data;
				break;
		}
	}

	/**
	 * Users reporting action route
	 *
	 * @return array
	 */
	protected function usersreporting_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				break;
			case 'GET':
				if ( ! empty( $model['quiz_id'] ) && isset( $model['per_page'] ) && isset( $model['current_page'] ) ) {
					$reporting_manager = new TQB_Reporting_Manager( $model['quiz_id'], 'users' );
					$data              = $reporting_manager->get_users_report( $model );
				} else {
					$this->error( __( 'Something went wrong', Thrive_Quiz_Builder::T ) );
				}

				return $data;
				break;
		}
	}

	/**
	 * Users reporting action route
	 *
	 * @return array
	 */
	protected function useranswers_action() {
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				break;
			case 'GET':
				$quiz_id = $this->param( 'quiz_id', true );
				$user_id = $this->param( 'user_id', true );
				if ( ! empty( $quiz_id ) && ! empty( $user_id ) ) {
					$reporting_manager = new TQB_Reporting_Manager( $quiz_id, 'users' );
					$data              = $reporting_manager->get_users_answers( $user_id );
				} else {
					$this->error( __( 'Something went wrong', Thrive_Quiz_Builder::T ) );
				}

				return $data;
				break;
		}
	}

	/**
	 * Structure action route
	 *
	 * @return array
	 */
	protected function structure_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
				if ( ! empty( $model['ID'] ) ) {
					$model['last_modified'] = current_time( 'timestamp' );
					$quiz_structure         = new TQB_Structure_Manager( $model['ID'] );
					$model                  = $quiz_structure->update_quiz_structure( $model );
				} else {
					$this->error( __( 'Structure could not be saved', Thrive_Quiz_Builder::T ) );
				}

				return $model;
				break;
			case 'PATCH':
			case 'DELETE':
				break;
			case 'GET':
				break;
		}
	}

	/**
	 * Quiz type route action
	 *
	 * @return mixed
	 */
	protected function type_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$return = array();
				if ( ! empty( $model['type'] ) && ! empty( $model['ID'] ) ) {

					$quiz_results_modified = false;
					if ( $model['type'] === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) {

						$quiz_manager = new TQB_Quiz_Manager( $model['ID'] );
						if ( is_array( $model['results'] ) && ! empty( $model['results'] ) ) {
							$prev_quiz_results = $quiz_manager->get_results();
							if ( ! empty( $prev_quiz_results ) && ( $prev_quiz_results === $model['results'] ) === false ) {
								$quiz_results_modified = true;
							}
						}
						$return['returned_results'] = $quiz_manager->save_results( $model['results'], $prev_quiz_results );
					}

					TQB_Post_meta::update_quiz_type_meta( $model['ID'], $model );

					if ( $quiz_results_modified ) {
						do_action( 'tqb-quiz-results-modified', $model['ID'], $prev_quiz_results, $return['returned_results'] );
						$return['responseText'] = __( 'The list of results has changed. Your questions flow and results page might be affected by this change.', Thrive_Quiz_Builder::T );
					} else {
						$return['responseText'] = __( 'Changes were saved!', Thrive_Quiz_Builder::T );
					}

					$return['model_id'] = $model['ID'];

				} else {
					$this->error( __( 'Type could not be saved. There was an error while saving the quiz type. Please contact the support team.', Thrive_Quiz_Builder::T ) );
				}

				return $return;
				break;
			case 'DELETE':
				break;
			case 'GET':
				break;
		}
	}

	/**
	 * The page action methods
	 *
	 * @return array|bool|null|WP_Post
	 */
	protected function page_action() {
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		if ( ( $custom = $this->param( 'custom_action' ) ) ) {
			switch ( $custom ) {
				case 'check_variations_content':
					$page_id           = $this->param( 'ID', 0 );
					$quiz_id           = intval( $_POST['quiz_id'] );
					$variation_manager = new TQB_Variation_Manager( $quiz_id, $page_id );
					$page_variations   = $variation_manager->get_page_variations( array(
						'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
					) );
					$return            = array();
					foreach ( $page_variations as $page_variation ) {
						if ( empty( $page_variation['content'] ) ) {
							$return[] = $page_variation['id'];
						}
					}

					return $return;
					break;
				default:
					return array();
			}
		}

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				break;
			case 'DELETE':
				break;
			case 'GET':
				$id     = $this->param( 'ID', 0 );
				$viewed = $this->param( 'viewed', false );
				if ( ! empty( $id ) ) {
					$page_manager = new TQB_Page_Manager( $id );
					$page         = $page_manager->get_page( false, $viewed );
					if ( ! $page ) {
						$this->error( __( 'Item not found', Thrive_Quiz_Builder::T ) );
					}
				} else {
					$this->error( __( 'Item not found', Thrive_Quiz_Builder::T ) );
				}

				return $page;
				break;
		}
	}

	/**
	 * @return array|bool|null|WP_Post
	 */
	protected function variation_action() {
		$model         = json_decode( file_get_contents( 'php://input' ), true );
		$method        = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		$custom_action = $this->param( 'custom_action' );

		if ( ! empty( $custom_action ) ) {
			switch ( $custom_action ) {
				case 'clone_variation':
					if ( $this->param( 'id', 0 ) == $_POST['id'] && ! empty( $_POST['id'] ) && ! empty( $_POST['quiz_id'] ) ) {
						$manager                     = new TQB_Variation_Manager( $_POST['quiz_id'], $_POST['page_id'] );
						$variation                   = $manager->clone_variation( $_POST );
						$variation['tcb_editor_url'] = TQB_Variation_Manager::get_editor_url( $variation['page_id'], $variation['id'] );

						return $variation;
					}
					break;
				case 'reset_statistics':
					if ( $this->param( 'id', 0 ) == $_POST['id'] && ! empty( $_POST['id'] ) && ! empty( $_POST['quiz_id'] ) ) {
						$model = $this->prepare_variation_for_database( $_POST );
						$model = $this->reset_variation_statistics( $model );

						$variation = new TQB_Variation_Manager( $model['quiz_id'], $model['page_id'] );
						$model     = $variation->save_variation( $model, true );

						return $model;
					}
					break;
				case 'generate_first_variation':
					if ( ! empty( $_POST['quiz_id'] ) && is_numeric( $_POST['quiz_id'] ) && empty( $_POST['id'] ) ) {
						$model     = $_POST;
						$structure = new TQB_Structure_Manager( $_POST['quiz_id'] );
						$model     = $structure->generate_first_variation( $model );

						return $model;
					}

					break;
			}

			return false;
		}

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( ! empty( $model['quiz_id'] ) ) {

					$change_control = false;
					$variation      = new TQB_Variation_Manager( $model['quiz_id'], $model['page_id'] );

					if ( ! empty( $model['id'] ) ) {
						$model = $this->prepare_variation_for_database( $model );
					} else {
						if ( ! $variation->has_control( $model['page_id'] ) ) {
							$model['is_control'] = 1;
						}
					}

					if ( $model['post_status'] === Thrive_Quiz_Builder::VARIATION_STATUS_ARCHIVE && $model['is_control'] == 1 ) {
						$model['is_control'] = 0;
						$change_control      = true;
					}

					$page_type = get_post_type( $model['page_id'] );
					$templates = TQB_Template_Manager::get_templates( $page_type, $model['quiz_id'] );

					/**
					 * If there is only one template available in the system, add to the variation that template.
					 */
					if ( count( $templates ) == 1 && empty( $model['id'] ) ) {
						$model[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] = $templates[ key( $templates ) ] ['key'];
						$model[ Thrive_Quiz_Builder::FIELD_CONTENT ]  = TCB_Hooks::tqb_editor_get_template_content( $model, $model[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );
						$model                                        = $variation->save_variation( $model, false );
					} else {
						$model = $variation->save_variation( $model, true );
					}


					if ( $model['post_status'] === Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH ) {
						$model['tcb_editor_url'] = TQB_Variation_Manager::get_editor_url( $model['page_id'], $model['id'] );
					} elseif ( $model['post_status'] === Thrive_Quiz_Builder::VARIATION_STATUS_ARCHIVE ) {
						$model['tcb_preview_url'] = TQB_Variation_Manager::get_preview_url( $model['page_id'], $model['id'] );
					}

					/**
					 * Changes the control
					 */
					if ( $change_control ) {
						$new_control_id = $variation->change_control( $model['page_id'] );
						if ( ! empty( $new_control_id ) ) {
							$model['new_control_id'] = $new_control_id;
						}
					}
				} else {
					$this->error( __( 'Error while inserting a new variation', Thrive_Quiz_Builder::T ) );
				}

				return $model;
				break;
			case 'DELETE':
				$return = TQB_Variation_Manager::delete_variation( array( 'id' => $this->param( 'id', 0 ) ) );
				if ( empty( $return ) ) {
					$this->error( __( 'Error while deleting the variation', Thrive_Quiz_Builder::T ) );

					return false;
				}

				return $return;
				break;
			case 'GET':
				break;
		}

	}

	/**
	 * Resets variation statistics
	 *
	 * @param array $model
	 *
	 * @return array
	 */
	private function reset_variation_statistics( $model = array() ) {

		$model['cache_impressions']               = 0;
		$model['cache_optins']                    = 0;
		$model['cache_optins_conversions']        = 0;
		$model['cache_social_shares']             = 0;
		$model['cache_social_shares_conversions'] = 0;

		$model['cache_optin_conversion_rate']        = 'N/A';
		$model['cache_social_share_conversion_rate'] = 'N/A';

		return $model;
	}

	/**
	 * Prepares the variation for save.
	 *
	 * @param array $model
	 *
	 * @return array
	 */
	private function prepare_variation_for_database( $model = array() ) {
		/**
		 * Unset the TCB content and fields
		 */
		unset( $model['content'] );
		unset( $model['tcb_fields'] );

		/**
		 * Unset the impressions and conversions
		 */
		unset( $model['cache_impressions'] );
		unset( $model['cache_optins'] );
		unset( $model['cache_optins_conversions'] );
		unset( $model['cache_social_shares'] );
		unset( $model['cache_social_shares_conversions'] );

		return $model;
	}

	/**
	 * @return array|bool|null|WP_Post
	 */
	protected function test_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$test  = new TQB_Test_Manager( $model['id'] );
				$model = $test->save_test( $model );

				return $model;
				break;
			case 'DELETE':
				$test = new TQB_Test_Manager( $this->param( 'id', 0 ) );

				return $test->delete_test();
				break;
			case 'GET':
				$test  = new TQB_Test_Manager( $this->param( 'id', 0 ) );
				$model = $test->get_test( array( 'id' => $this->param( 'id', 0 ) ) );
				if ( ! $model ) {
					$this->error( __( 'Test not found', Thrive_Quiz_Builder::T ) );
				}

				return $model;
				break;
		}

	}

	/**
	 * @return array|bool|null|WP_Post
	 */
	protected function testitem_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$test  = new TQB_Test_Manager( $model['id'] );
				$model = $test->save_test_item( $model );

				return $model;
				break;
			case 'DELETE':
				break;
			case 'GET':
				break;
		}
	}

	/**
	 * Performs actions for Variation test chart
	 *
	 * @return array
	 */
	public function chartAPI_action() {
		$chart_type = $this->param( 'chart_type', '' );
		switch ( $chart_type ) {
			case 'testChart':
				$test = new TQB_Test_Manager( $this->param( 'id', 0 ) );

				return $test->get_test_chart_data( $this->param( 'interval', 'day' ) );
				break;
		}
	}

	/**
	 * Performs actions for Quizzes based on request's method and model
	 * Dies with error if the operation was not executed
	 *
	 * @return array
	 */
	protected function settings_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? $_SERVER['REQUEST_METHOD'] : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']; // TODO: Change this!
		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':

				if ( ! is_array( $model ) ) {
					return array();
				}
				tqb_update_option( Thrive_Quiz_Builder::PLUGIN_SETTINGS, $model, true );

				return $model;
				break;
			case 'DELETE':
			case 'GET':
				return array();
				break;
		}
	}
}
