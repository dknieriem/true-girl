<?php

/**
 * Created by PhpStorm.
 * User: sala
 * Date: 09-Feb-16
 * Time: 10:14
 */
class THO_REST_Logs_Controller extends THO_REST_Controller {

	public $base = 'logs';

	private static function getChartTypes() {

		$charts = array(
			THO_ENGAGEMENT_REPORT,
			THO_ENGAGEMENT_RATE_REPORT,
			THO_CUMULATIVE_ENGAGEMENT_REPORT,
			THO_CLICK_THROUGH_RATE_REPORT,
			THO_TIME_ON_CONTENT_REPORT,
			THO_SCROLL_REPORT
		);

		return implode( '|', $charts );
	}

	private static function getEngagementTypes() {
		$engagements = array(
			THO_CLICK_ENGAGEMENT,
			THO_SCROLL_ENGAGEMENT,
			THO_TIME_ENGAGEMENT
		);

		return $engagements;
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( self::$NAMESPACE . self::$VERSION, '/' . $this->base . '/(' . self::getChartTypes() . ')/([\d]+|' . THO_SOURCE_REPORT_ALL . ')', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			)
		) );

		register_rest_route( self::$NAMESPACE . self::$VERSION, '/' . $this->base . '/table', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_table_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			)
		) );


		register_rest_route( self::$NAMESPACE . self::$VERSION, '/' . $this->base, array(
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'create_item' ),
				'args'     => $this->get_log_params(),
			),
		) );


	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {

		$data      = $this->prepare_item_for_database( $request );
		$test_id   = $request->get_param( 'test_id' );
		$is_single = $request->get_param( 'is_single' );

		/* @var Tho_Db */
		global $thodb;

		/* If we get an array this means that instead of doing more then 1 call, we get everything together and do it all in one. */
		if ( is_array( $data['engagement_type'] ) && $is_single ) {

			/* so we log for each engagement an event. most of the data is the some, the only difference is in log type and engagement type */
			$engagements = $data['engagement_type'];
			foreach ( $engagements as $e ) {
				$data['engagement_type'] = $e;
				$data['log_type']        = $e == THO_CLICK_ENGAGEMENT ? THO_LOG_ENGAGEMENT : THO_LOG_IMPRESSION;

				$log_id = $thodb->insert_event( $data );
			}

		} else {
			$log_id = $thodb->insert_event( $data );
		}

		/* Check if the auto win settings have been enabled */
		tho_check_test_auto_win( $test_id );

		/*Stop underperforming variations*/
		tho_stop_underperforming_variations( $test_id );

		if ( is_int( $log_id ) ) {
			return new WP_REST_Response( 1, 200 );
		}

		return new WP_Error( 'cant-create', __( 'message', THO_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		$post_id   = $request->get_param( 'post_id' );
		$eng_type  = $request->get_param( 'eng_type' );
		$log_type  = $request->get_param( 'log_type' );
		$variation = $request->get_param( 'variation' );
		$referrer  = $request->get_param( 'referrer' );

		$log_model = array(
			'date'            => date( 'Y-m-d H:i:s' ),
			'log_type'        => $log_type,
			'engagement_type' => $eng_type,
			'post_id'         => $post_id,
			'variation'       => $variation,
			'post_type'       => get_post_type( $post_id ),
			'referrer'        => tho_check_referrer( $referrer ) ? $referrer : '',
			'archived'        => 0
		);

		return $log_model;
	}

	public function get_table_items( $request ) {
		$queryParams = $request->get_query_params();

		$filters = array(
			'report_type'     => $queryParams['report_type'],
			'interval'        => $queryParams['tho-chart-interval'],
			'start-date'      => $queryParams['tho-report-start-date'],
			'end-date'        => $queryParams['tho-report-end-date'],
			'source-type'     => $queryParams['tho-source-type'],
			'engagement_type' => $queryParams['tho-engagement-type-select'],
			"page"            => $queryParams['page'],
			"itemsPerPage"    => $queryParams['itemsPerPage'],
			"order_by"        => $queryParams['order_by'],
			"order_dir"       => $queryParams['order_dir'],
		);


		$response = call_user_func( 'tho_get_table_' . $queryParams['report_type'], $filters );

		return new WP_REST_Response( $response, 200 );

	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$urlParams   = $request->get_url_params();
		$queryParams = $request->get_query_params();

		if ( ! in_array( $queryParams['tho-engagement-type-select'], self::getEngagementTypes() ) ) {
			$queryParams['tho-engagement-type-select'] = "0";
		}

		$filters = array(
			'report_type'     => $queryParams['report_type'],
			'interval'        => $queryParams['tho-chart-interval'],
			'start-date'      => $queryParams['tho-report-start-date'],
			'end-date'        => $queryParams['tho-report-end-date'],
			'source-type'     => intval( $urlParams[2] ),
			'engagement_type' => $queryParams['tho-engagement-type-select']
		);

		$reportType = $urlParams[1];
		$response   = call_user_func( 'tho_get_' . $reportType, $filters );

		return new WP_REST_Response( $response, 200 );
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
	 * Return params that are expected from the API call
	 * @return array
	 */
	private function get_log_params() {
		$params = array();

		$params['post_id'] = array(
			'type'              => 'integer',
			'default'           => null,
			'sanitize_callback' => 'absint',
		);

		$params['source'] = array(
			'type'              => 'integer',
			'default'           => null,
			'sanitize_callback' => 'absint',
			'enum'              => array( THO_CLICK_ENGAGEMENT, THO_SCROLL_ENGAGEMENT, THO_TIME_ENGAGEMENT )
		);

		$params['source'] = array(
			'type'              => 'integer',
			'default'           => null,
			'sanitize_callback' => 'absint',
			'enum'              => array( THO_LOG_IMPRESSION, THO_LOG_ENGAGEMENT )
		);

		return $params;
	}


}