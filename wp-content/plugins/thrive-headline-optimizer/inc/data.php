<?php
/**
 * Functions that work with the DB
 *
 * e.g.: CRUD functions
 */

/**
 * Builds the test model and triggers the saving
 *
 * @param $postID
 * @param array $variations
 * @param array $default_settings
 *
 * @return int $test_id
 */
function tho_save_test( $postID, $variations = array(), $default_settings = array(), $automatic_winner_settings = array() ) {

	/* fix for pages that automatically create a menu item */
	if ( get_post_type( $postID ) == 'nav_menu_item' ) {
		return;
	}

	/* @var Tho_Db */
	global $thodb;

	$tho_default_settings = tho_get_default_values( THO_SETTINGS_OPTION );
	if ( empty( $default_settings ) ) {
		$settings = tho_get_option( THO_SETTINGS_OPTION, $tho_default_settings );
		$settings = array_merge( $settings, $automatic_winner_settings );
	} else {
		$settings = array_merge( $tho_default_settings, array_merge( $default_settings, $automatic_winner_settings ) );
	}

	$test_model = array(
		'post_id'      => $postID,
		'date_started' => date( 'Y-m-d H:i:s' ),
		'config'       => serialize( $settings ),
		'status'       => THO_TEST_STATUS_ACTIVE
	);

	$test_id = $thodb->save_test( $test_model );

	$test_control_item_model = array(
		'test_id'         => $test_id,
		'variation_title' => get_the_title( $postID ),
		'is_control'      => 1
	);
	$thodb->save_test_item( $test_control_item_model );

	foreach ( $variations as $variation ) {
		$test_item_model = array( 'test_id' => $test_id, 'variation_title' => stripslashes_deep( $variation ) );
		$thodb->save_test_item( $test_item_model );
	}

	/*
	 * Clear the cache
	 */
	$cache_plugin = tve_dash_detect_cache_plugin();
	if ( $cache_plugin ) {
		tve_dash_cache_plugin_clear( $cache_plugin );
	}

	return $test_id;
}

/**
 * Returns the running test for a post
 *
 * @param array $filters
 *
 * @return array
 */
function tho_get_running_test( $filters = array() ) {

	/* @var Tho_Db */
	global $thodb;

	$defaults = array(
		'status' => THO_TEST_STATUS_ACTIVE
	);

	$filters = array_merge( $defaults, $filters );

	$active_test = $thodb->get_tests( $filters, true );

	if ( empty( $active_test ) ) {
		return;
	}

	$config = maybe_unserialize( $active_test->config );
	foreach ( $config as $k => $v ) {
		$active_test->$k = $v;
	}

	unset( $active_test->config );

	return $active_test;
}

/**
 * Returns the running test ids
 *
 * @param array $filters
 *
 * @return array
 */
function tho_get_running_test_ids( $filters = array() ) {

	/* @var Tho_Db */
	global $thodb;

	$defaults = array(
		'status' => THO_TEST_STATUS_ACTIVE,
	);
	$filters  = array_merge( $defaults, $filters );

	$running_tests    = $thodb->get_tests( $filters, false );
	$running_test_ids = array();
	foreach ( $running_tests as $test ) {
		array_push( $running_test_ids, $test->post_id );
	}

	return $running_test_ids;
}

/**
 * Get test variations
 *
 * @param $test_id
 * @param bool $get_test_data
 *
 * @return array|null|object
 */
function tho_get_test_items( $test_id, $get_test_data = false, $get_active_items = false ) {

	/* @var Tho_Db */
	global $thodb;

	$variations = $thodb->get_test_items( $test_id, $get_active_items );

	if ( $get_test_data ) {

		foreach ( $variations as $key => $item ) {
			if ( $item->is_control == 1 ) {
				$control = $item;
			}

			$variations[ $key ]->engagement_rate = tho_engagement_rate( $item->views, $item->engagements, '' );
		}

		if ( empty( $control ) ) {
			/* it should not get here, but just in case */
			return $variations;
		}

		foreach ( $variations as $key => $item ) {
			if ( $item->is_control == 1 ) {
				continue;
			}

			/* Percentage improvement = engagement rate of variation - engagement rate of control */
			if ( empty( $control->engagement_rate ) || $control->engagement_rate == 'N/A' ) {
				$variations[ $key ]->percentage_improvement = 'N/A';
			} else {
				$variations[ $key ]->percentage_improvement = round( ( ( $item->engagement_rate - $control->engagement_rate ) * 100 ) / $control->engagement_rate, 2 );
			}

			$variations[ $key ]->beat_original = tho_test_item_beat_original( $item->engagement_rate, $item->views, $control->engagement_rate, $control->views );
		}
	}

	return $variations;
}

/**
 * Get an array with test_id as key and winning variation title as value for all the tests
 *
 * @param $post_id
 *
 * @return array
 */
function tho_get_winner_titles( $post_id ) {

	/* @var Tho_Db */
	global $thodb;

	$items = $thodb->get_winner_titles( $post_id );

	$winners = array();
	if ( ! empty( $items ) ) {
		foreach ( $items as $item ) {
			$winners[ $item->test_id ] = $item->title;
		}
	}

	return $winners;
}

/**
 * Get the variations of the current running tests
 * @return array
 */
function tho_get_active_tests_variations() {

	$filters = array(
		'status' => THO_TEST_STATUS_ACTIVE
	);

	/* @var Tho_Db */
	global $thodb;

	$active_tests = $thodb->get_tests( $filters );
	$variations   = array();

	foreach ( $active_tests as $test ) {

		$variations[ $test->post_id ] = array(
			'variations' => array(),
			'test_id'    => $test->id,
			'winners'    => tho_get_winner_titles( $test->post_id )
		);

		$test_items = tho_get_test_items( $test->id, false, true );

		foreach ( $test_items as $item ) {
			$variations[ $test->post_id ]['variations'][ $item->id ] = $item->variation_title;
		}
	}

	return $variations;
}

/**
 * Get test chart data
 *
 * @param $filters array
 *
 * @return array
 */
function tho_get_test_chart_data( $filters ) {

	$defaults = array(
		'interval'   => 'day',
		'start_date' => date( "Y-m-d H:i:s", strtotime( '-7 days' ) ),
		'end_date'   => date( "Y-m-d" ) . ' 23:59:59'
	);

	$filters = array_merge( $defaults, $filters );

	/* @var Tho_Db */
	global $thodb;

	$log_data = $thodb->get_test_chart_data( $filters );

	/* generate dates for X Axis and to later fill the empty data */
	$dates = tho_generate_dates_interval( $filters['start_date'], $filters['end_date'], $filters['interval'] );

	$test_items = tho_get_test_items( $filters['test_id'] );
	$variations = array();
	foreach ( $test_items as $item ) {
		$variations[ $item->id ] = $item->variation_title;
	}

	/* STEP 1. Store the impressions and engagements for each variation on each date interval */
	$chart_data_temp = array();
	foreach ( $log_data as $interval ) {
		if ( empty( $variations[ intval( $interval->variation ) ] ) ) {
			/* this should not happen */
			continue;
		}

		/* store specific data for each variation */
		if ( empty( $chart_data_temp[ $interval->variation ] ) ) {
			$chart_data_temp[ $interval->variation ]['id']                 = intval( $interval->variation );
			$chart_data_temp[ $interval->variation ]['name']               = $variations[ intval( $interval->variation ) ];
			$chart_data_temp[ $interval->variation ][ THO_LOG_ENGAGEMENT ] = array();
			$chart_data_temp[ $interval->variation ][ THO_LOG_IMPRESSION ] = array();
		}

		/* store the date interval so we can add it as X Axis in the chart */
		if ( $filters['interval'] == 'day' ) {
			$interval->date_interval = date( "d M, Y", strtotime( $interval->date_interval ) );
		}

		$chart_data_temp[ $interval->variation ][ intval( $interval->log_type ) ][ $interval->date_interval ] = intval( $interval->log_count );
	}

	/* STEP 2. Take each variation and fill empty dates with zero.
				Calculate the engagement rate as a sum of all the impression and engagements until that date
	*/
	$chart_data = array();
	foreach ( $variations as $id => $name ) {

		if ( empty( $chart_data[ $id ] ) ) {
			$chart_data[ $id ]['id']   = $id;
			$chart_data[ $id ]['name'] = $name;
			$chart_data[ $id ]['data'] = array();
		}

		$impressions = $engagements = 0;

		foreach ( $dates as $key => $date ) {

			$impressions += empty( $chart_data_temp[ $id ][ THO_LOG_IMPRESSION ][ $date ] ) ? 0 : $chart_data_temp[ $id ][ THO_LOG_IMPRESSION ][ $date ];
			$engagements += empty( $chart_data_temp[ $id ][ THO_LOG_ENGAGEMENT ][ $date ] ) ? 0 : $chart_data_temp[ $id ][ THO_LOG_ENGAGEMENT ][ $date ];

			if ( empty( $impressions ) || empty( $engagements ) ) {
				/* Complete with zero on empty dates */
				$chart_data[ $id ]['data'][] = 0;
			} else {
				$chart_data[ $id ]['data'][] = (float) tho_engagement_rate( $impressions, $engagements, '', 2 );
			}
		}
	}

	return array(
		'id'     => $filters['test_id'],
		'title'  => __( 'Engagement rate over time', THO_TRANSLATE_DOMAIN ),
		'data'   => $chart_data,
		'x_axis' => $dates,
		'y_axis' => __( 'Engagement rate', THO_TRANSLATE_DOMAIN ) . ' (%)'
	);

}


/**
 * Engagement Report
 *
 * @param array $filter
 *
 * @return array
 */
function tho_get_engagement_report( $filter = array() ) {
	global $thodb;

	$defaults = array(
		'group_by'     => array( 'date_interval' ),
		'log_type'     => THO_LOG_ENGAGEMENT,
		'chart_title'  => __( get_engagement_name( $filter['engagement_type'] ) . ' Engagement report', THO_TRANSLATE_DOMAIN ),
		'chart_y_axis' => __( 'Engagements', THO_TRANSLATE_DOMAIN )
	);
	/*if source type = all, group by post*/
	if ( empty( $filter['source-type'] ) || $filter['source-type'] == THO_SOURCE_REPORT_ALL ) {
		$defaults['group_by'] = array( 'date_interval' );
		$name                 = __( 'Engagement report', THO_TRANSLATE_DOMAIN );
	} else {
		$defaults['post_id'] = $filter['source-type'];
		$name                = get_the_title( $filter['source-type'] );
	}

	if ( ! empty( $filter['engagement_type'] ) ) {
		$defaults['group_by'] = array( 'engagement_type', 'date_interval' );
	}

	$filter = array_merge( $defaults, $filter );

	//generate interval to fill empty dates.
	$dates       = tho_generate_dates_interval( $filter['start-date'], $filter['end-date'], $filter['interval'] );
	$report_data = $thodb->get_chart_data_count_log_type( $filter );

	$colors      = tho_get_chart_colors();
	$countColors = count( $colors );

	$chart_data_temp = array();
	foreach ( $report_data as $key => $value ) {
		$dateWithFormat                        = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );
		$chart_data_temp[ $dateWithFormat[0] ] = intval( $value->log_count );
	}

	$chart_data   = array();
	$chart_colors = array();
	$color_index  = 0;

	$chart_data['id']    = 1;
	$chart_data['name']  = $name;
	$chart_data['color'] = $colors[ $color_index % $countColors ];
	$chart_data['data']  = array();


	foreach ( $dates as $date ) {
		if ( ! isset( $chart_data_temp[ $date ] ) ) {
			$chart_data_temp[ $date ] = 0;
		}

		$chart_data['data'][] = $chart_data_temp[ $date ];
	}


	return array(
		'chart_title'  => $filter['chart_title'],
		'chart_data'   => array( $chart_data ),
		'chart_x_axis' => $dates,
		'chart_y_axis' => $filter['chart_y_axis'],
		'chart_colors' => $chart_colors
	);
}

/**
 * Engagement Rate Report
 *
 * @param array $filter
 *
 * @return array
 */
function tho_get_engagement_rate_report( $filter = array() ) {
	global $thodb;

	$defaults = array(
		'group_by'     => array( 'log_type', 'date_interval' ),
		'chart_title'  => __( get_engagement_name( $filter['engagement_type'] ) . ' Engagement rate report', THO_TRANSLATE_DOMAIN ),
		'chart_y_axis' => __( 'Engagement Rate', THO_TRANSLATE_DOMAIN )
	);
	/*if source type = all, group by post*/
	if ( empty( $filter['source-type'] ) || $filter['source-type'] == THO_SOURCE_REPORT_ALL ) {
		$defaults['group_by'] = array( 'log_type', 'date_interval' );
		$title                = __( 'Engagement rate report', THO_TRANSLATE_DOMAIN );
	} else {
		$defaults['post_id'] = $filter['source-type'];
		$title               = get_the_title( $filter['source-type'] );
	}

	$filter = array_merge( $defaults, $filter );

	//generate interval to fill empty dates.
	$dates       = tho_generate_dates_interval( $filter['start-date'], $filter['end-date'], $filter['interval'] );
	$report_data = $thodb->get_chart_data_count_log_type( $filter );

	$colors      = tho_get_chart_colors();
	$countColors = count( $colors );

	$chart_data_temp = array();

	foreach ( $report_data as $key => $value ) {
		$dateWithFormat                                            = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );
		$chart_data_temp[ $dateWithFormat[0] ][ $value->log_type ] = intval( $value->log_count );
	}

	$chart_data   = array();
	$chart_colors = array();
	$color_index  = 0;

	$chart_data['id']    = 1;
	$chart_data['name']  = $title;
	$chart_data['color'] = $colors[ $color_index % $countColors ];
	$chart_data['data']  = array();

	foreach ( $dates as $date ) {
		/**
		 * 1 - engagements: THO_LOG_ENGAGEMENT
		 * 2 - views: THO_LOG_IMPRESSION
		 */
		if ( ! isset( $chart_data_temp[ $date ][ THO_LOG_ENGAGEMENT ] ) ) {
			$chart_data_temp[ $date ][ THO_LOG_ENGAGEMENT ] = 0;
		}

		if ( ! isset( $chart_data_temp[ $date ][ THO_LOG_IMPRESSION ] ) ) {
			$chart_data_temp[ $date ][ THO_LOG_IMPRESSION ] = 1;
		}


		$chart_data['data'][] = (float) tho_engagement_rate( $chart_data_temp[ $date ][ THO_LOG_IMPRESSION ], $chart_data_temp[ $date ][ THO_LOG_ENGAGEMENT ], '' );
	}


	return array(
		'chart_title'  => $filter['chart_title'],
		'chart_data'   => array( $chart_data ),
		'chart_x_axis' => $dates,
		'chart_y_axis' => $filter['chart_y_axis'],
		'chart_colors' => $chart_colors
	);

}

/**
 * Cumulative engagement report
 *
 * @param array $filter
 *
 * @return array
 */
function tho_get_cumulative_engagement_report( $filter = array() ) {
	global $thodb;

	$defaults = array(
		'group_by' => array( 'date_interval' ),
		'log_type' => THO_LOG_ENGAGEMENT
	);
	/*if source type = all, group by post*/
	if ( empty( $filter['source-type'] ) || $filter['source-type'] == THO_SOURCE_REPORT_ALL ) {
		$defaults['group_by'] = array( 'date_interval' );
		$name                 = __( 'Cumulative engagements over time', THO_TRANSLATE_DOMAIN );
	} else {
		$defaults['post_id'] = $filter['source-type'];
		$name                = get_the_title( $filter['source-type'] );
	}

	$filter = array_merge( $defaults, $filter );

	//generate interval to fill empty dates.
	$dates       = tho_generate_dates_interval( $filter['start-date'], $filter['end-date'], $filter['interval'] );
	$report_data = $thodb->get_chart_data_count_log_type( $filter );
	$colors      = tho_get_chart_colors();
	$countColors = count( $colors );

	$chart_data_temp = array();
	foreach ( $report_data as $key => $value ) {
		$dateWithFormat                        = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );
		$chart_data_temp[ $dateWithFormat[0] ] = intval( $value->log_count );
	}

	$chart_data   = array();
	$chart_colors = array();
	$color_index  = 0;

	$chart_data['id']    = 1;
	$chart_data['name']  = $name;
	$chart_data['color'] = $colors[ $color_index % $countColors ];
	$chart_data['data']  = array();

	for ( $i = 0; $i < count( $dates ); $i ++ ) {

		if ( $i == 0 ) {
			if ( ! isset( $chart_data_temp[ $dates[ $i ] ] ) ) {
				$chart_data_temp[ $dates[ $i ] ] = 0;
			}
			$chart_data['data'][] = $chart_data_temp[ $dates[ $i ] ];
		} else {
			if ( ! isset( $chart_data_temp[ $dates[ $i ] ] ) ) {
				$chart_data_temp[ $dates[ $i ] ] = $chart_data_temp[ $dates[ $i - 1 ] ];
				$chart_data['data'][]            = $chart_data_temp[ $dates[ $i ] ];
			} else {
				$chart_data_temp[ $dates[ $i ] ] += $chart_data_temp[ $dates[ $i - 1 ] ];
				$chart_data['data'][] = $chart_data_temp[ $dates[ $i ] ];
			}
		}
	}


	return array(
		'chart_title'  => __( get_engagement_name( $filter['engagement_type'] ) . ' Cumulative engagement report', THO_TRANSLATE_DOMAIN ),
		'chart_data'   => array( $chart_data ),
		'chart_x_axis' => $dates,
		'chart_y_axis' => __( 'Engagements', THO_TRANSLATE_DOMAIN ),
		'chart_colors' => $chart_colors
	);

}

/**
 * Parses the test statistics and builds a array for meta box
 *
 * @param $post_id
 *
 * @return array
 */
function tho_get_test_statistics( $post_id ) {
	global $thodb;

	$resultStatistics  = $thodb->get_post_test_statistics( $post_id );
	$total_engagements = $total_views = $click_engagements = $scroll_engagements = $time_engagements = 0;

	$engagementType = array(
		THO_CLICK_ENGAGEMENT  => 0,
		THO_SCROLL_ENGAGEMENT => 0,
		THO_TIME_ENGAGEMENT   => 0
	);

	$viewType = array(
		THO_CLICK_ENGAGEMENT  => 0,
		THO_SCROLL_ENGAGEMENT => 0,
		THO_TIME_ENGAGEMENT   => 0
	);

	foreach ( $resultStatistics as $statistics ) {
		switch ( $statistics->log_type ) {
			case THO_LOG_ENGAGEMENT:
				$total_engagements += $statistics->s;
				$engagementType[ $statistics->engagement_type ] = $statistics->s;
				break;
			case THO_LOG_IMPRESSION:
				$total_views += $statistics->s;
				$viewType[ $statistics->engagement_type ] = $statistics->s;
				break;
			default:
				break;
		}
	}


	$return = array(
		'total_engagements'   => $total_engagements,
		'total_views'         => $total_views,
		'click_engagements'   => $engagementType[ THO_CLICK_ENGAGEMENT ],
		'scroll_engagements'  => $engagementType[ THO_SCROLL_ENGAGEMENT ],
		'time_engagements'    => $engagementType[ THO_TIME_ENGAGEMENT ],
		'click_views'         => $viewType[ THO_CLICK_ENGAGEMENT ],
		'scroll_views'        => $viewType[ THO_SCROLL_ENGAGEMENT ],
		'time_views'          => $viewType[ THO_TIME_ENGAGEMENT ],
		'click_engagement_p'  => tho_engagement_rate( $viewType[ THO_CLICK_ENGAGEMENT ], $engagementType[ THO_CLICK_ENGAGEMENT ] ),
		'scroll_engagement_p' => tho_engagement_rate( $viewType[ THO_SCROLL_ENGAGEMENT ], $engagementType[ THO_SCROLL_ENGAGEMENT ] ),
		'time_engagement_p'   => tho_engagement_rate( $viewType[ THO_TIME_ENGAGEMENT ], $engagementType[ THO_TIME_ENGAGEMENT ] ),
		'engagement_rate'     => tho_engagement_rate( $total_views, $total_engagements )
	);

	return $return;

}

/**
 * Table for Engagement Report
 *
 * @param array $filter
 *
 * @return array
 */
function tho_get_table_engagement_report( $filter = array() ) {
	global $thodb;

	$defaults = array(
		'group_by' => array( 'date_interval' ),
		'log_type' => THO_LOG_ENGAGEMENT

	);
	/*if source type = all, group by post*/
	if ( empty( $filter['source-type'] ) || $filter['source-type'] == THO_SOURCE_REPORT_ALL ) {
		$defaults['group_by'] = array( 'date_interval' );
	} else {
		$defaults['post_id'] = $filter['source-type'];
	}

	$filter = array_merge( $defaults, $filter );

	$report_data = $thodb->get_chart_data_count_log_type( $filter );


	$table_data = array();
	foreach ( $report_data as $key => $value ) {
		$dateWithFormat = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );

		$obj              = new stdClass();
		$obj->date        = $dateWithFormat[0];
		$obj->engagements = $value->log_count;

		$table_data[] = $obj;
	}

	$count_table_data = count( $table_data );

	if ( ! empty( $table_data ) ) {
		$table_pages = array_chunk( $table_data, $filter['itemsPerPage'] );
		$table_data  = $table_pages[ $filter['page'] - 1 ];
	}

	return array(
		'items'       => $table_data,
		'total_count' => $count_table_data
	);

}

/**
 * Table for Engagement rate report
 *
 * @param array $filter
 *
 * @return array
 */
function tho_get_table_engagement_rate_report( $filter = array() ) {
	global $thodb;

	$defaults = array(
		'group_by' => array( 'log_type', 'date_interval' )
	);
	/*if source type = all, group by post*/
	if ( empty( $filter['source-type'] ) || $filter['source-type'] == THO_SOURCE_REPORT_ALL ) {
		$defaults['group_by'] = array( 'log_type', 'date_interval' );
	} else {
		$defaults['post_id'] = $filter['source-type'];
	}

	$filter      = array_merge( $defaults, $filter );
	$report_data = $thodb->get_chart_data_count_log_type( $filter );

	$table_data_temp = array();
	foreach ( $report_data as $key => $value ) {
		$dateWithFormat                                            = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );
		$table_data_temp[ $dateWithFormat[0] ][ $value->log_type ] = intval( $value->log_count );
	}

	$table_data       = array();
	$table_data_check = array();

	foreach ( $report_data as $key => $value ) {
		$dateWithFormat = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );

		if ( ! isset( $table_data_temp[ $dateWithFormat[0] ][ THO_LOG_ENGAGEMENT ] ) ) {
			$table_data_temp[ $dateWithFormat[0] ][ THO_LOG_ENGAGEMENT ] = 0;
		}

		if ( ! isset( $table_data_temp[ $dateWithFormat[0] ][ THO_LOG_IMPRESSION ] ) ) {
			$table_data_temp[ $dateWithFormat[0] ][ THO_LOG_IMPRESSION ] = 1;
		}

		if ( empty( $table_data_check[ $dateWithFormat[0] ] ) ) {

			$obj       = new stdClass();
			$obj->date = $dateWithFormat[0];
			$obj->rate = (float) tho_engagement_rate( $table_data_temp[ $dateWithFormat[0] ][ THO_LOG_IMPRESSION ], $table_data_temp[ $dateWithFormat[0] ][ THO_LOG_ENGAGEMENT ], '' );

			$table_data_check[ $dateWithFormat[0] ] = $obj;
			$table_data[]                           = $obj;
		}
	}

	$count_table_data = count( $table_data );

	if ( ! empty( $table_data ) ) {
		$table_pages = array_chunk( $table_data, $filter['itemsPerPage'] );
		$table_data  = $table_pages[ $filter['page'] - 1 ];
	}

	return array(
		'items'       => $table_data,
		'total_count' => $count_table_data
	);

}

/**
 * Table for Cumulative engagement report
 *
 * @param array $filter
 *
 * @return array
 */
function tho_get_table_cumulative_engagement_report( $filter = array() ) {
	global $thodb;

	$defaults = array(
		'group_by' => array( 'date_interval' ),
		'log_type' => THO_LOG_ENGAGEMENT
	);
	/*if source type = all, group by post*/
	if ( empty( $filter['source-type'] ) || $filter['source-type'] == THO_SOURCE_REPORT_ALL ) {
		$defaults['group_by'] = array( 'date_interval' );
	} else {
		$defaults['post_id'] = $filter['source-type'];
	}

	$filter = array_merge( $defaults, $filter );

	$report_data = $thodb->get_chart_data_count_log_type( $filter );
	foreach ( $report_data as $key => $value ) {
		$dateWithFormat                        = tho_generate_dates_interval( $value->date_interval, $value->date_interval, $filter['interval'] );
		$chart_data_temp[ $dateWithFormat[0] ] = intval( $value->log_count );
	}

	$engagements = 0;
	$table_data  = array();
	$dates       = tho_generate_dates_interval( $filter['start-date'], $filter['end-date'], $filter['interval'] );
	foreach ( $dates as $date ) {
		if ( ! empty( $chart_data_temp[ $date ] ) ) {
			$engagements += $chart_data_temp[ $date ];
			$obj              = new stdClass();
			$obj->date        = $date;
			$obj->engagements = $engagements;

			$table_data[] = $obj;
		}
	}

	$count_table_data = count( $table_data );

	if ( $filter['order_by'] == 'log_count' && $filter['order_dir'] == 'ASC' ) {
		$table_data = array_reverse( $table_data );
	}

	if ( ! empty( $table_data ) ) {
		$table_pages = array_chunk( $table_data, $filter['itemsPerPage'] );
		$table_data  = array_reverse( $table_pages[ $filter['page'] - 1 ] );
	}

	return array(
		'items'       => $table_data,
		'total_count' => $count_table_data
	);
}

/**
 * Gets the running tests for inconclusive test check
 *
 * @return array|void
 */
function tho_get_running_inconclusive_tests() {
	/* @var Tho_Db */
	global $thodb;

	$return       = array();
	$active_tests = $thodb->get_tests( array( 'status' => THO_TEST_STATUS_ACTIVE ), false );

	if ( empty( $active_tests ) ) {
		return;
	}

	foreach ( $active_tests as $active_test ) {
		$config = maybe_unserialize( $active_test->config );
		foreach ( $config as $k => $v ) {
			$active_test->$k = $v;
		}

		unset( $active_test->config );

		$minimum_duration_doubled = intval( $active_test->minimum_duration ) * 2;
		if ( $active_test->minimum_engagements * 2 <= $active_test->engagements && date( 'Y-m-d', strtotime( $active_test->date_started . ' + ' . $minimum_duration_doubled . ' days' ) ) <= date( 'Y-m-d' ) ) {
			$return[] = $active_test;
		}
	}

	return $return;
}
