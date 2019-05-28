<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Quiz_Manager
 *
 * Handles Quiz operations
 */
class TQB_Quiz_Manager {

	/**
	 * @var TQB_Quiz_Manager $instance
	 */
	protected $quiz;

	/**
	 * @var TQB_Database
	 */
	protected $tqbdb;

	/**
	 * TQB_Quiz_Manager constructor.
	 */
	public function __construct( $quiz_id = null ) {
		global $tqbdb;
		$this->tqbdb = $tqbdb;
		$this->quiz  = get_post( $quiz_id );
	}


	/**
	 * Get the list of quizzes based on filters param
	 *
	 * @param array $filters allows passing query values to the get_posts function, and some extra values.
	 *
	 * @return array $posts
	 */
	public static function get_quizzes( $filters = array() ) {

		$defaults = array(
			'posts_per_page' => - 1,
			'post_type'      => TQB_Post_types::QUIZ_POST_TYPE,
			'orderby'        => 'post_date',
			'order'          => 'ASC',
		);
		$filters  = array_merge( $defaults, $filters );
		$posts    = get_posts( $filters );

		foreach ( $posts as $post ) {
			$post->order           = (int) TQB_Post_meta::get_quiz_order( $post->ID );
			$post_type_meta        = TQB_Post_meta::get_quiz_type_meta( $post->ID );
			$post->type            = isset( $post_type_meta['type'] ) ? $post_type_meta['type'] : '';
			$post->completed_count = TQB_Quiz_Manager::get_completed_quiz_count( $post->ID );
			$post->users_started   = TQB_Quiz_Manager::get_quiz_users_count( $post->ID );

			$post->social_shares = TQB_Quiz_Manager::get_quiz_social_shares_count( $post->ID );
			$structure           = new TQB_Structure_Manager( $post->ID );
			$post->validation    = $structure->get_display_availability();

			$structure_data    = $structure->get_quiz_structure_meta();
			$post->subscribers = TQB_Quiz_Manager::get_quiz_subscribers( $structure_data );
		}

		return $posts;
	}

	public static function get_page_subscribers( $id ) {
		global $tqbdb;

		return $tqbdb->get_page_subscribers( $id );
	}

	/**
	 * Get quiz subscribers count
	 */
	public static function get_quiz_subscribers( $structure ) {

		if ( empty( $structure ) ) {
			return 0;
		}
		$optin_subscribers   = 0;
		$results_subscribers = 0;
		if ( is_numeric( $structure['optin'] ) ) {
			$optin_subscribers = TQB_Quiz_Manager::get_page_subscribers( $structure['optin'] );
		}

		if ( is_numeric( $structure['results'] ) ) {
			$results_subscribers = TQB_Quiz_Manager::get_page_subscribers( $structure['results'] );
		}

		return $results_subscribers + $optin_subscribers;
	}

	/**
	 * Gets only the valid quizzes
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public static function get_valid_quizzes( $filters = array() ) {
		$quizzes = self::get_quizzes( $filters );
		foreach ( $quizzes as $key => $quiz ) {
			if ( ! $quiz->validation['valid'] ) {
				unset( $quizzes[ $key ] );
			}
		}

		return $quizzes;
	}

	/**
	 * Get a quiz based on filters
	 *
	 * @return false|WP_Post on success or false on error
	 */
	public function get_quiz() {

		if ( empty( $this->quiz ) || $this->quiz->post_type !== TQB_Post_types::QUIZ_POST_TYPE ) {
			return false;
		}

		$type = TQB_Post_meta::get_quiz_type_meta( $this->quiz->ID );
		if ( ! empty( $type ) ) {
			$this->quiz->type    = $type['type'];
			$this->quiz->results = $this->get_results();
		}

		$this->quiz->page_variations = $this->tqbdb->count_page_variations( array(
			'quiz_id' => $this->quiz->ID,
		), OBJECT );

		$style = TQB_Post_meta::get_quiz_style_meta( $this->quiz->ID );
		if ( is_numeric( $style ) ) {
			$this->quiz->style = $style;
		}

		$this->quiz->wizard_complete = TQB_Post_meta::get_wizard_meta( $this->quiz->ID );

		$quiz_structure = new TQB_Structure_Manager( $this->quiz->ID );
		$structure      = $quiz_structure->get_quiz_structure_meta();
		if ( is_array( $structure ) && ! empty( $structure ) ) {
			$this->quiz->structure                  = $structure;
			$pages                                  = tqb()->get_structure_internal_identifiers();
			$this->quiz->structure['running_tests'] = array();
			foreach ( $pages as $page ) {
				if ( is_numeric( $structure[ $page ] ) ) {
					$page_manager                                    = new TQB_Page_Manager( $structure[ $page ] );
					$this->quiz->structure['running_tests'][ $page ] = $page_manager->get_tests_for_page( array(
						'page_id' => $structure[ $page ],
						'status'  => 1,
					), true );

					$this->quiz->structure['nr_of_variations'][ $page ] = $this->tqbdb->count_page_variations( array(
						'quiz_id'     => $this->quiz->ID,
						'post_id'     => $structure[ $page ],
						'post_status' => Thrive_Quiz_Builder::VARIATION_STATUS_PUBLISH,
					), OBJECT );
				}
			}
			$this->quiz->structure['tge_question_number'] = tge()->count_questions( $this->quiz->ID );
		}

		$tpl = TQB_Post_meta::get_quiz_tpl_meta( $this->quiz->ID );
		if ( ! empty( $tpl ) ) {
			$this->quiz->tpl = $tpl;
		}

		tie()->set_images( $this->quiz );

		$this->quiz->tge_url = tge()->editor_url( $this->quiz );

		return $this->quiz;
	}

	/**
	 * Save a quiz
	 *
	 * @param array $model WP post object.
	 *
	 * @return false|int id of model or false on error
	 */
	public function save_quiz( $model ) {
		if ( ! empty( $model['ID'] ) ) {
			$item = get_post( $model['ID'] );
			if ( $item && get_post_type( $item ) === TQB_Post_types::QUIZ_POST_TYPE ) {
				$data = array( 'ID' => $model['ID'], 'post_title' => $model['post_title'] );
				$id   = wp_update_post( $data );
			}
		} else {
			$default = array(
				'post_type'   => TQB_Post_types::QUIZ_POST_TYPE,
				'post_status' => 'publish',
			);

			$id = wp_insert_post( array_merge( $default, $model ) );
			TQB_Post_meta::update_quiz_tpl_meta( $id, $model );

		}

		if ( empty( $id ) || is_wp_error( $id ) ) {
			return false;
		}

		if ( isset( $model['order'] ) ) {
			TQB_Post_meta::update_quiz_order( $id, (int) $model['order'] );
		}

		return $id;
	}

	/**
	 * Set post's status on trash
	 *
	 * @param bool $force_delete whether or not to bypass trash and delete the quiz permanently
	 *
	 * @return false | int number of deleted rows or false on error
	 */
	public function delete_quiz( $force_delete = true ) {

		if ( empty( $this->quiz ) || $this->quiz->post_type !== TQB_Post_types::QUIZ_POST_TYPE ) {
			return false;
		}

		if ( $force_delete ) {
			/*Delete Variations*/
			TQB_Variation_Manager::delete_variation( array( 'quiz_id' => $this->quiz->ID ) );

			/*Deletes the quiz child posts*/
			$this->delete_quiz_pages();

			/*Delete quiz answers and quiz questions from graph editor*/
			tge()->delete_all_quiz_dependencies( $this->quiz->ID );

			/*Deletes quiz results and answers*/
			$this->delete_quiz_results_and_user_answers( $this->quiz->ID );

			$deleted = wp_delete_post( $this->quiz->ID, true );
		} else {
			$this->quiz->post_status = 'trash';
			$deleted                 = wp_update_post( $this->quiz );
		}

		$deleted = $deleted === 0 || is_wp_error( $deleted ) ? false : true;

		if ( $deleted && $force_delete ) {
			tie()->delete_images( $this->quiz );
		}

		return $deleted;
	}

	/**
	 * Deletes quiz pages
	 */
	public function delete_quiz_pages() {

		$posts = TQB_Page_Manager::get_quiz_pages( $this->quiz->ID );

		if ( is_array( $posts ) && count( $posts ) > 0 ) {
			TQB_Page_Manager::delete_quiz_pages( $posts );
		}
	}

	/**
	 * run do_shortcode on the whole quiz future content
	 */
	public static function run_shortcodes_on_quiz_content( $quiz_id ) {

		$structure      = new TQB_Structure_Manager( $quiz_id );
		$structure_data = $structure->get_quiz_structure_meta();
		$array          = tqb()->get_structure_internal_identifiers();
		global $tqbdb;
		$all_content = '';
		foreach ( $array as $page_type ) {
			if ( isset( $structure_data[ $page_type ] ) && is_numeric( $structure_data[ $page_type ] ) ) {
				$variations = $tqbdb->get_page_variations( array( 'post_id' => $structure_data[ $page_type ] ) );
				foreach ( $variations as $variation ) {
					if ( ! empty( $variation['content'] ) ) {
						$all_content .= $variation['content'];
					}
				}
			}
		}
		tve_parse_events( $all_content );
		do_shortcode( $all_content );
	}

	/**
	 * Main decision making regarding shortcode content
	 */
	public static function get_shortcode_content( $quiz_id, $page_type = null, $answer_id = null, $user_unique = null, $variation = null, $post_id = 0 ) {

		$quiz = get_post( $quiz_id );
		if ( empty( $quiz ) ) {
			return array( 'error' => tqb_create_frontend_error_message( array( __( 'The shortcode is broken', Thrive_Quiz_Builder::T ) ) ) );
		}

		$structure  = new TQB_Structure_Manager( $quiz_id );
		$validation = $structure->get_display_availability();
		if ( ! $validation['valid'] ) {
			$errors = tqb_create_frontend_error_message( $validation['error'] );

			return array( 'error' => $errors );
		}

		if ( empty( $user_unique ) ) {
			$user_unique = uniqid( 'tqb-user-', true );
		} else {
			$user_id = TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id );
		}

		$shortcode_content['page']        = null;
		$shortcode_content['question']    = null;
		$shortcode_content['user_unique'] = $user_unique;
		$shortcode_content['user_id']     = ( ! empty( $user_id ) ) ? $user_id : null;
		switch ( $page_type ) {

			case null:
				$shortcode_content['page'] = $structure->get_page_content( 'splash', null, $post_id );

				if ( ! empty( $shortcode_content['page'] ) ) {
					$shortcode_content['page_type'] = 'splash';
					do_action( 'tqb_register_impression', $shortcode_content['page'], $user_unique );
					break;
				}

			case 'splash':
				$question_manager = new TGE_Question_Manager( $quiz_id );
				// register the answer
				if ( ! empty( $answer_id ) ) {
					TQB_Quiz_Manager::register_answer( $answer_id, $user_unique, $quiz_id );
				}

				$shortcode_content['question'] = $question_manager->get_question_content( $answer_id );
				if ( ! empty( $shortcode_content['question'] ) ) {
					$shortcode_content['page_type']           = 'splash';
					$shortcode_content['question']['page_id'] = $quiz_id;
					$shortcode_content['question']['quiz_id'] = $quiz_id;

					if ( isset( $variation['id'] ) && $variation['id'] ) {
						$variation['quiz_id'] = $quiz_id;
						do_action( 'tqb_register_conversion', $variation, $user_unique );
					}

					do_action( 'tqb_register_impression', $shortcode_content['question'], $user_unique );

					break;
				} else {
					$shortcode_content['page_type'] = 'qna';
				}

			case 'qna':
				TQB_Quiz_Manager::save_user_points( $user_unique, $quiz_id );
				$shortcode_content['page'] = $structure->get_page_content( 'optin', null, $post_id );
				do_action( 'tqb_register_conversion', array( 'quiz_id' => $quiz_id, 'page_id' => $quiz_id, 'id' => null ), $user_unique );
				if ( ! empty( $shortcode_content['page'] ) ) {
					TQB_Quiz_Manager::tqb_register_quiz_completion( $user_unique, $shortcode_content['page']['page_id'] );
					$shortcode_content['page_type'] = 'optin';
					do_action( 'tqb_register_impression', $shortcode_content['page'], $user_unique );
					break;
				}
			case 'optin':
				$points                    = TQB_Quiz_Manager::calculate_user_points( $user_unique, $quiz_id );
				$shortcode_content['page'] = $structure->get_page_content( 'results', $points, $post_id );
				do_action( 'tqb_register_impression', $shortcode_content['page'], $user_unique );
				TQB_Quiz_Manager::tqb_register_quiz_completion( $user_unique, $shortcode_content['page']['page_id'] );
				if ( isset( $variation['id'] ) && $variation['id'] ) {
					$variation['quiz_id'] = $quiz_id;
					do_action( 'tqb_register_skip_optin', $variation, $user_unique );

				}
				$variation_arr                                 = TQB_Variation_Manager::get_variation( $shortcode_content['page']['variation_id'] );
				$shortcode_content['page']['has_social_badge'] = ( isset( $variation_arr['tcb_fields']['social_share_badge'] ) ) ? $variation_arr['tcb_fields']['social_share_badge'] : 0; // can be 0 or 1
				$shortcode_content['page_type']                = 'results';

				if ( $shortcode_content['page']['has_social_badge'] ) {
					global $tqbdb;
					$result                                         = $tqbdb->get_explicit_result( $points );
					$shortcode_content['page']['result']            = str_replace( ' %', '', $result );
					$shortcode_content['page']['social_loader_url'] = tqb()->plugin_url( 'assets/images/social-sharing-badge-loader.gif' );

					$image_post = get_posts( array( 'post_parent' => $quiz_id, 'post_type' => TIE_Post_Types::THRIVE_IMAGE ) );
					if ( ! empty( $image_post[0] ) ) {
						$tie_image                                = new TIE_Image( $image_post[0] );
						$shortcode_content['page']['fonts']       = array_merge( $shortcode_content['page']['fonts'], array_values( $tie_image->get_settings()->get_data( 'fonts' ) ) );
						$shortcode_content['page']['fonts'][]     = '//fonts.googleapis.com/css?family=Roboto'; //default font for BE
						$shortcode_content['page']['html_canvas'] = str_replace( Thrive_Quiz_Builder::QUIZ_RESULT_SHORTCODE, $result, $tie_image->get_html_canvas_content() );
					}
				}
				break;
		}
		if ( ! empty( $validation['error'] ) ) {
			$shortcode_content['error'] = tqb_create_frontend_error_message( array( __( 'There is an error in the quiz structure', Thrive_Quiz_Builder::T ) ) );
		}

		return $shortcode_content;
	}

	/**
	 * Register quiz question answer
	 */
	public static function register_answer( $answer_id, $user_unique, $quiz_id ) {
		global $tgedb;
		// get answer check if valid
		$answer = $tgedb->get_answers( array( 'id' => $answer_id ), true );

		if ( empty( $answer ) ) {
			return false;
		}

		// get user check if existing
		global $tqbdb;
		$user = $tqbdb->get_quiz_user( $user_unique, $quiz_id );

		if ( empty( $user ) ) {
			return false;
		}
		$tqbdb->save_user_answer( array(
			'quiz_id'     => $user['quiz_id'],
			'user_id'     => $user['id'],
			'answer_id'   => $answer['id'],
			'question_id' => $answer['question_id'],
		) );

		return true;
	}

	/**
	 * Register a page impression
	 */
	public static function tqb_register_impression( $variation, $user_unique ) {

		if ( current_user_can( 'manage_options' ) || tve_dash_is_crawler() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-impression-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-impression-' . $variation['page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_IMPRESSION;
		$data['variation_id'] = isset( $variation['variation_id'] ) ? $variation['variation_id'] : null;
		$data['user_unique']  = $user_unique;
		$data['page_id']      = $variation['page_id'];

		$page_manager = new TQB_Page_Manager( $variation['page_id'] );
		$active_test  = $page_manager->get_tests_for_page( array(
			'page_id' => $variation['page_id'],
			'status'  => 1,
		), true );

		if ( isset( $variation['variation_id'] ) && ! isset( $data['duplicate'] ) ) {
			$update_data = array( 'variation_id' => $variation['variation_id'], 'impression' => true );
			$tqbdb->update_variation_cached_counter( $update_data );
			if ( ! empty( $active_test ) ) {
				$update_data['test_id'] = $active_test['id'];
				$tqbdb->update_test_item_action_counter( $update_data );

				/*Check for test auto win*/
				$test_manager = new TQB_Test_Manager( $active_test['id'] );
				$test_manager->check_test_auto_win();
				$test_manager->stop_underperforming_variations();
			}
		}

		$tqbdb->create_event_log_entry( $data );

		setcookie( 'tqb-impression-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-impression-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-impression-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-impression-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] = true;
	}

	/**
	 * Register a page conversion
	 */
	public static function tqb_register_conversion( $variation, $user_unique ) {
		if ( current_user_can( 'manage_options' ) || tve_dash_is_crawler() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_CONVERSION;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $user_unique; //TQB_Quiz_Manager::get_quiz_user( $user_unique, $variation['quiz_id'] );
		$data['page_id']      = $variation['page_id'];

		$page_manager = new TQB_Page_Manager( $variation['page_id'] );
		$active_test  = $page_manager->get_tests_for_page( array(
			'page_id' => $variation['page_id'],
			'status'  => 1,
		), true );

		if ( isset( $variation['id'] ) && ! isset( $data['duplicate'] ) ) {
			$update_data = array( 'variation_id' => $variation['id'], 'conversion' => true );
			$tqbdb->update_variation_cached_counter( $update_data );
			if ( ! empty( $active_test ) ) {
				$update_data['test_id'] = $active_test['id'];
				$tqbdb->update_test_item_action_counter( $update_data );

				/*Check for test auto win*/
				$test_manager = new TQB_Test_Manager( $active_test['id'] );
				$test_manager->check_test_auto_win();
				$test_manager->stop_underperforming_variations();
			}
		}

		$tqbdb->create_event_log_entry( $data );

		setcookie( 'tqb-conversion-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] = true;
	}

	/**
	 * Register a page conversion
	 */
	public static function tqb_register_optin_conversion( $post ) {
		if ( current_user_can( 'manage_options' ) || tve_dash_is_crawler() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $post['tqb-variation-page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $post['tqb-variation-page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		$page = get_post( $post['tqb-variation-page_id'] );

		if ( empty( $page ) ) {
			return;
		}

		global $tqbdb;
		$variation = $tqbdb->get_variation( $post['tqb-variation-variation_id'] );

		if ( empty( $variation ) ) {
			return;
		}

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_CONVERSION;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $post['tqb-variation-user_unique']; //TQB_Quiz_Manager::get_quiz_user( $post['tqb-variation-user_unique'], $page->post_parent );
		$data['page_id']      = $variation['page_id'];
		$data['optin']        = 1;

		$result = $tqbdb->create_event_log_entry( $data );

		$page_manager = new TQB_Page_Manager( $variation['page_id'] );
		$active_test  = $page_manager->get_tests_for_page( array(
			'page_id' => $variation['page_id'],
			'status'  => 1,
		), true );

		if ( isset( $variation['id'] ) && ! is_array( $result ) && ! isset( $data['duplicate'] ) ) {
			$update_data = array( 'variation_id' => $variation['id'], 'conversion' => true );
			$tqbdb->update_variation_cached_counter( $update_data );
			if ( ! empty( $active_test ) ) {
				$update_data['test_id'] = $active_test['id'];
				$tqbdb->update_test_item_action_counter( $update_data );
			}
		}
		$user_id = TQB_Quiz_Manager::get_quiz_user( $data['user_unique'], $page->post_parent );
		$tqbdb->save_quiz_user( array( 'id' => $user_id, 'email' => $post['email'] ) );

		setcookie( 'tqb-conversion-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] = true;
	}

	/**
	 * Register a page conversion
	 */
	public static function tqb_register_social_media_conversion( $post ) {

		if ( current_user_can( 'manage_options' ) || tve_dash_is_crawler() ) {
			return;
		}

		$page_id      = ( is_numeric( $post['page_id'] ) && ! empty( $post['page_id'] ) ) ? $post['page_id'] : 0;
		$quiz_id      = ( is_numeric( $post['quiz_id'] ) && ! empty( $post['quiz_id'] ) ) ? $post['quiz_id'] : 0;
		$variation_id = ( is_numeric( $post['variation_id'] ) && ! empty( $post['variation_id'] ) ) ? $post['variation_id'] : 0;

		$page = get_post( $page_id );

		if ( empty( $page ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-social-media-' . $page_id . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] ) || $page->post_type !== Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-social-media-' . $page_id ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;
		$variation = $tqbdb->get_variation( $variation_id );

		if ( empty( $variation ) ) {
			return;
		}

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_CONVERSION;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $post['tqb-variation-user_unique'];
		$data['page_id']      = $variation['page_id'];
		$data['social_share'] = 1;

		$result = $tqbdb->create_event_log_entry( $data );
		if ( isset( $variation['id'] ) && ! is_array( $result ) && ! isset( $data['duplicate'] ) ) {
			$variation_manager = new TQB_Variation_Manager( $quiz_id, $page_id );
			$variation_manager->update_social_share_conversion( $variation_id );
		}

		setcookie( 'tqb-conversion-social-media-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-social-media-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-social-media-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-social-media-' . $variation['page_id'] . '-' . str_replace( '.', '_', $post['tqb-variation-user_unique'] ) ] = true;
	}

	/**
	 * Register optin skip event
	 */
	public static function tqb_register_skip_optin_event( $variation, $user_unique ) {
		if ( current_user_can( 'manage_options' ) || tve_dash_is_crawler() ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] ) ) {
			$data['duplicate'] = 1;
		}

		global $tqbdb;

		$data['date']         = date( 'Y-m-d H:i:s', time() );
		$data['event_type']   = Thrive_Quiz_Builder::TQB_SKIP_OPTIN;
		$data['variation_id'] = $variation['id'];
		$data['user_unique']  = $user_unique;
		$data['page_id']      = $variation['page_id'];

		$tqbdb->create_event_log_entry( $data );

		setcookie( 'tqb-conversion-' . $variation['page_id'], 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] ] = true;

		setcookie( 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ), 1, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'tqb-conversion-' . $variation['page_id'] . '-' . str_replace( '.', '_', $user_unique ) ] = true;
	}

	/**
	 * Register a quiz completion
	 */
	public static function tqb_register_quiz_completion( $user_unique, $page_id ) {
		global $tqbdb;
		$page = get_post( $page_id );

		if ( ! empty( $page ) && ( $page->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN || $page->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) ) {
			$user_id = TQB_Quiz_Manager::get_quiz_user( $user_unique, $page->post_parent );
			$tqbdb->save_quiz_user( array( 'id' => $user_id, 'completed_quiz' => 1 ) );
		}
	}

	/**
	 * Get quiz user using unique identifier and quiz id
	 */
	public static function get_quiz_user( $user_unique = null, $quiz_id = null ) {

		$ignore_user = null;
		if ( current_user_can( 'manage_options' ) || tve_dash_is_crawler() ) {
			$ignore_user = 1;
		}

		global $tqbdb;
		$user = $tqbdb->get_quiz_user( $user_unique, $quiz_id );

		if ( empty( $user ) ) {
			return $tqbdb->save_quiz_user( array(
				'random_identifier' => $user_unique,
				'quiz_id'           => $quiz_id,
				'ip_address'        => $_SERVER['REMOTE_ADDR'],
				'ignore_user'       => $ignore_user,
			) );
		} else {
			return $user['id'];
		}
	}

	public function save_results( $results = array(), $prev_results = array() ) {

		if ( ! empty( $prev_results ) ) {
			$aux_prev = array();
			foreach ( $prev_results as $prev_item ) {
				$aux_prev[ $prev_item['id'] ] = $prev_item;
			}

			foreach ( $results as $key => $value ) {
				if ( ! empty( $value['id'] ) ) {
					unset( $aux_prev[ $value['id'] ] );
				}

				if ( empty( $value['text'] ) ) {
					unset( $results[ $key ] );
				}
			}

			foreach ( $aux_prev as $aux_p ) {
				$this->tqbdb->delete_quiz_results( array( 'id' => $aux_p['id'] ) );
			}
		}

		return $this->tqbdb->save_quiz_results( $this->quiz->ID, $results );
	}

	public function get_results() {

		return $this->tqbdb->get_quiz_results( $this->quiz->ID );
	}

	/**
	 * Get count of completed quizzes
	 */
	public static function get_completed_quiz_count( $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_completed_quiz_count( $quiz_id );
	}

	/**
	 * Calculate a certain user's points on a quiz
	 */
	public static function calculate_user_points( $user_unique, $quiz_id ) {
		global $tqbdb;

		return $tqbdb->calculate_user_points( $user_unique, $quiz_id );
	}

	/**
	 * Get a certain user's points on a quiz
	 */
	public static function get_user_points( $user_unique, $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_user_points( $user_unique, $quiz_id );
	}

	/**
	 * Save a certain user's points on a quiz
	 */
	public static function save_user_points( $user_unique, $quiz_id ) {
		global $tqbdb;

		$points = TQB_Quiz_Manager::calculate_user_points( $user_unique, $quiz_id );

		return $tqbdb->save_quiz_user( array(
			'id'     => TQB_Quiz_Manager::get_quiz_user( $user_unique, $quiz_id ),
			'points' => $tqbdb->get_explicit_result( $points ),
		) );
	}

	/**
	 * Get quiz social share count
	 */
	public static function get_quiz_social_shares_count( $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_quiz_social_shares_count( $quiz_id );
	}

	/**
	 * Get total quiz users count
	 */
	public static function get_quiz_users_count( $quiz_id ) {
		global $tqbdb;

		return $tqbdb->get_quiz_users_count( $quiz_id );
	}

	/**
	 * Deletes quiz results and user answers
	 *
	 * @param int $quiz_id
	 *
	 * @return int
	 */
	public function delete_quiz_results_and_user_answers( $quiz_id = 0 ) {
		if ( empty( $quiz_id ) ) {
			$quiz_id = $this->quiz->ID;
		}
		global $tqbdb;

		$deleted_results = $tqbdb->delete_quiz_results( array(
			'quiz_id' => $quiz_id,
		) );

		$delete_users = $tqbdb->delete_quiz_users( array(
			'quiz_id' => $quiz_id,
		) );

		$deleted_answers = $tqbdb->delete_user_answers( array(
			'quiz_id' => $quiz_id,
		) );

		return $deleted_results && $deleted_answers;
	}
}
