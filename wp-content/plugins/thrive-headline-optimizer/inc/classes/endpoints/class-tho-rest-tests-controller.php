<?php

/**
 * Created by PhpStorm.
 * User: sala
 * Date: 01-Feb-16
 * Time: 18:12
 */
class THO_REST_Tests_Controller extends THO_REST_Controller {

	public $base = 'tests';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		parent::register_routes();

		register_rest_route( self::$NAMESPACE . self::$VERSION, '/' . $this->base . '/log_inconclusive_test', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'log_inconclusive_test' ),
				'permission_callback' => array( $this, 'log_inconclusive_test_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * @param $request
	 *
	 * @return bool
	 */
	public function log_inconclusive_test_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function log_inconclusive_test( $request ) {
		$test_id = $request->get_param( 'test_id' );

		if ( ! empty( $test_id ) && is_numeric( $test_id ) ) {

			$inconclusive_tests_option = tho_get_option( THO_INCONCLUSIVE_TESTS_OPTION );
			$inconclusive_tests        = array();
			if ( ! is_array( $inconclusive_tests_option ) ) {
				$inconclusive_tests = explode( ',', tho_get_option( THO_INCONCLUSIVE_TESTS_OPTION ) );
			}

			if ( ! in_array( $test_id, $inconclusive_tests ) ) {
				$inconclusive_tests[] = $test_id;
			}

			$result = tho_update_option( THO_INCONCLUSIVE_TESTS_OPTION, implode( ',', $inconclusive_tests ) );

			$return = array( 'result' => $result, 'redirect_url' => admin_url( 'admin.php?page=tho_admin_dashboard#test/' . $test_id ) );

			return new WP_REST_Response( $return, 200 );
		}

		return new WP_Error( 'cant-log-inconclusive-test', __( 'ERROR: Invalid parameters', TVO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Get a collection of tests
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		/* @var Tho_Db */
		global $thodb;

		$filters = array(
			'status' => THO_TEST_STATUS_ACTIVE,
		);

		$items        = $thodb->get_tests( $filters );
		$active_tests = array();

		foreach ( $items as $item ) {
			$active_tests[] = $this->prepare_response_for_collection( $item );
		}

		$completed_tests = $thodb->get_completed_test_posts();

		foreach ( $completed_tests as $k => $v ) {
			$labels = get_post_type_object( $v->post_type );
			if ( ! empty( $labels->labels->name ) ) {
				$completed_tests[ $k ]->post_type = $labels->labels->name;
			}
		}

		$filters = array(
			'group_by'   => array( 'log_type', 'date_interval' ),
			'interval'   => 'day',
			'start-date' => date( 'Y-m-d' ),
			'end-date'   => date( 'Y-m-d' ),
		);

		/* Get daily reports  */
		$daily_report = $thodb->get_chart_data_count_log_type( $filters );
		$views        = 0;
		$engagements  = 0;
		foreach ( $daily_report as $r ) {
			if ( $r->log_type == THO_LOG_IMPRESSION ) {
				$views = $r->log_count;
			} else {
				$engagements = $r->log_count;
			}
		}
		$engagement_rate = tho_engagement_rate( $views, $engagements );

		$data = array(
			'active'    => $active_tests,
			'completed' => $completed_tests,
			'daily'     => array(
				'views'           => $views,
				'engagements'     => $engagements,
				'engagement_rate' => $engagement_rate,
			),
		);

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get all the data required for a test -> active test, variations, chart data and completed tests
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$url_params = $request->get_url_params();

		$data = $this->prepare_item_for_response( $url_params['id'], $request );

		if ( ! empty( $data ) ) {
			return new WP_REST_Response( $data, 200 );
		} else {
			return new WP_Error( '420', __( 'Something went wrong', THO_TRANSLATE_DOMAIN ), array() );
		}
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

		/* @var Tho_Db */
		global $thodb;

		$tests = $thodb->get_tests( array( 'post_id' => $id ) );

		foreach ( $tests as $test ) {
			$thodb->delete_test( $test->id );
		}

		$thodb->delete_log_data( array( 'post_id' => $id ) );

		return new WP_REST_Response( 1, 200 );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $test_id
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed
	 */
	public function prepare_item_for_response( $test_id, $request ) {

		$active_test = tho_get_running_test( array(
			'test_id' => $test_id,
			'status'  => '',
		) );

		if ( empty( $active_test ) ) {
			return array();
		}

		$active_test->title = get_the_title( $active_test->post_id );

		$interval      = $request->get_param( 'interval' );
		$onlyChartData = $request->get_param( 'onlyChart' );

		/* @var Tho_Db */
		global $thodb;

		$filters = array(
			'post_id'    => $active_test->post_id,
			'test_id'    => $test_id,
			'interval'   => $interval,
			'start_date' => $active_test->date_started,
			'end_date'   => empty( $active_test->date_completed ) ? date( 'Y-m-d' ) . ' 23:59:59' : $active_test->date_completed,
		);

		$chart_data = tho_get_test_chart_data( $filters );

		/* when we update the date interval, we only need the chart data, the rest remains the same, so we don't send it */
		if ( ! empty( $onlyChartData ) ) {
			return $chart_data;
		}

		$variations = tho_get_test_items( $test_id, true );
		/* Construct the stopped variation array */
		$stopped_variations = array();
		foreach ( $variations as $key => $test ) {
			$variations[ $key ]->key = $key;
			if ( $variations[ $key ]->active == 0 ) {
				if ( ! empty( $variations[ $key ]->stopped_date ) ) {
					$variations[ $key ]->stopped_date = date( 'd-m-Y', strtotime( $variations[ $key ]->stopped_date ) );
				}
				$stopped_variations[] = $variations[ $key ];
				unset( $variations[ $key ] );
			}
		}
		/* Reset the keys */
		$variations = array_values( $variations );

		$completed_tests = $thodb->get_tests(
			array(
				'post_id' => $active_test->post_id,
				'status'  => THO_TEST_STATUS_COMPLETED,
			) );
		/* If current test is completed, remove it from this list so it won't be displayed twice */
		foreach ( $completed_tests as $key => $test ) {

			$test->date_started   = date( 'd F, Y', strtotime( $test->date_started ) );
			$test->date_completed = date( 'd F, Y', strtotime( $test->date_completed ) );

			if ( $test->id == $active_test->id ) {
				unset( $completed_tests[ $key ] );
			}
		}

		return array(
			'active_test'        => $active_test,
			'variations'         => $variations,
			'stopped_variations' => $stopped_variations,
			'chart_data'         => $chart_data,
			'completed_tests'    => $completed_tests,
		);
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

	public function get_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Prepare a response for inserting into a collection.
	 *
	 * @param $item Object Response object.
	 *
	 * @return array Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $item ) {
		$labels = get_post_type_object( get_post_type( $item->post_id ) );

		if ( empty( $labels ) || empty( $labels->labels->name ) ) {
			$post_type = get_post_type( $item->post_id );
		} else {
			$post_type = $labels->labels->name;
		}

		$test = array(
			'id'              => $item->id,
			'title'           => get_the_title( $item->post_id ),
			'start_date'      => date( 'd F, Y', strtotime( $item->date_started ) ),
			'link'            => get_edit_post_link( $item->post_id, '' ),
			'post_type'       => $post_type,
			'views'           => $item->views,
			'engagements'     => $item->engagements,
			'engagement_rate' => tho_engagement_rate( $item->views, $item->engagements ),
		);

		return $test;
	}


	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {

		$items = json_decode( $request->get_body(), true );

		$ids = array();

		foreach ( $items as $item ) {
			if ( ! empty( $item['variations'] ) ) {
				$variations = $item['variations'];
			} else {
				$variations = array();
			}

			if ( ! empty( $item['test_criteria'] ) ) {
				$use_default_engagement_settings = $item['test_criteria']['use_default_engagement_settings'];
				unset( $item['test_criteria']['use_default_engagement_settings'] );

				$automatic_winner_settings                            = array();
				$automatic_winner_settings['enable_automatic_winner'] = $item['test_criteria']['enable_automatic_winner'];
				$automatic_winner_settings['minimum_engagements']     = $item['test_criteria']['minimum_engagements'];
				$automatic_winner_settings['minimum_duration']        = $item['test_criteria']['minimum_duration'];
				$automatic_winner_settings['chance_to_beat_original'] = $item['test_criteria']['chance_to_beat_original'];

				if ( $use_default_engagement_settings == 1 ) {
					$ids[] = tho_save_test( $item['id'], $variations, array(), $automatic_winner_settings );
				} else {
					$ids[] = tho_save_test( $item['id'], $variations, $item['test_criteria'], $automatic_winner_settings );
				}
			}
		}


		return new WP_REST_Response( $ids, 200 );
	}

	/**
	 * Update one test from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {

		$test = $this->prepare_test_for_save( $request );

		if ( ! empty( $test ) ) {
			/* @var Tho_Db */
			global $thodb;

			$response = $thodb->save_test( $test );

			return new WP_REST_Response( $response, 200 );
		}

		return new WP_Error( 'cant-update', __( 'message', THO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );

	}

	/**
	 * Prepare test model to be saved
	 *
	 * @param $request
	 *
	 * @return array
	 */
	private function prepare_test_for_save( $request ) {
		$item = json_decode( $request->get_body() );

		$test = array(
			'id'             => $item->id,
			'post_id'        => $item->post_id,
			'date_started'   => $item->date_started,
			'date_completed' => empty( $item->date_completed ) ? null : $item->date_completed,
			'config'         => serialize( array(
				'click_through'                => $item->click_through,
				'scrolling_signal'             => $item->scrolling_signal,
				'scrolling_signal_value'       => $item->scrolling_signal_value,
				'time_on_content_signal'       => $item->time_on_content_signal,
				'time_on_content_signal_value' => $item->time_on_content_signal_value,
				'enable_automatic_winner'      => $item->enable_automatic_winner,
				'minimum_engagements'          => $item->minimum_engagements,
				'minimum_duration'             => $item->minimum_duration,
				'chance_to_beat_original'      => $item->chance_to_beat_original,
			) ),
			'status'         => $item->status,
		);

		/* The test has completed, so we also have to modify the winning variation */
		if ( $test['status'] == THO_TEST_STATUS_COMPLETED ) {
			$test['date_completed'] = date( 'Y-m-d H:i:s' );

			if ( ! empty( $item->winning_variation ) ) {

				/* @var Tho_Db */
				global $thodb;
				$variation = $thodb->get_test_item( $item->winning_variation );

				tho_set_test_winner( (object) $test, $variation );
			}
		}

		return $test;
	}
}