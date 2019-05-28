<?php
/**
 * Keep this file only for helpers
 */

/**
 * Thrive Headline Test URL
 *
 * @param $file
 *
 * @return string THT URL
 */
function tho_plugin_url( $file = '' ) {
	return plugin_dir_url( dirname( __FILE__ ) ) . ltrim( $file, " /" );
}

/**
 * Return complete url for route endpoint
 *
 * @param $endpoint string
 * @param $id int
 * @param $args array
 *
 * @return string
 */
function tho_get_route_url( $endpoint, $id = 0, $args = array() ) {

	$url = get_rest_url() . THO_REST_NAMESPACE . '/' . $endpoint;

	if ( ! empty( $id ) && is_numeric( $id ) ) {
		$url .= '/' . $id;
	}

	if ( ! empty( $args ) ) {
		add_query_arg( $args, $url );
	}

	return $url;
}

/**
 * wrapper over the wp_enqueue_script function
 * it will add the plugin version to the script source if no version is specified
 *
 * @param $handle
 * @param string $src
 * @param array $deps
 * @param bool $ver
 * @param bool $in_footer
 */
function tho_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = THO_VERSION;
	}

	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '#\.min\.js$#', '.js', $src );
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * wrapper over the wp_enqueue_style function
 * it will add the plugin version to the style link if no version is specified
 *
 * @param $handle
 * @param string|bool $src
 * @param array $deps
 * @param bool|string $ver
 * @param string $media
 */
function tho_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = THO_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * appends the WordPress tables prefix and the default tho_ prefix to the table name
 *
 * @param string $table
 *
 * @return string the modified table name
 */
function tho_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . THO_DB_PREFIX . $table;
}

/**
 * return a formatted conversion rate based on $views and $engagements
 *
 * @param int $views
 * @param int $engagements
 * @param string $suffix
 * @param string $decimals
 *
 * @return string $rate the calculated conversion rate
 */
function tho_engagement_rate( $views, $engagements, $suffix = '%', $decimals = '2' ) {
	if ( $engagements == 0 || $views == 0 ) {
		return 'N/A';
	}

	return round( 100 * ( $engagements / $views ), $decimals ) . $suffix;
}


/**
 * @param $option_name
 * @param array $default_values
 *
 * @return array|mixed
 */
function tho_get_option( $option_name, $default_values = array() ) {

	$option = maybe_unserialize( get_option( $option_name ) );

	if ( empty( $option ) ) {

		add_option( $option_name, $default_values );

		$option = $default_values;
	}

	return $option;

}

/**
 * Wrapper over the update option
 *
 * @param $option_name
 * @param array|object $value
 * @param boolean $serialize
 *
 * @return array|mixed
 */
function tho_update_option( $option_name, $value, $serialize = false ) {

	if ( empty( $option_name ) || empty( $value ) ) {
		return false;
	}

	$old_value = tho_get_option( $option_name );

	/* Check to see if the old value is the same as the new one */
	if ( is_array( $old_value ) && is_array( $value ) ) {
		$diff = array_diff_assoc( $old_value, $value );
	} else if ( is_object( $old_value ) && is_object( $value ) ) {
		$diff = array_diff_assoc( get_object_vars( $old_value ), get_object_vars( $value ) );
	} else {
		$diff = ! ( $old_value == $value );
	}

	/* If the new value is the same with the old one, return true and don't update */
	if ( empty( $diff ) ) {
		return true;
	}

	if ( $serialize ) {
		$value = serialize( $value );
	}

	return update_option( $option_name, $value );

}

/**
 * @param $option
 *
 * @return array
 */
function tho_get_default_values( $option ) {

	switch ( $option ) {
		case THO_SETTINGS_OPTION:
			return array(
				'click_through'                => 1,
				'scrolling_signal'             => 1,
				'scrolling_signal_value'       => '30',
				'time_on_content_signal'       => 1,
				'time_on_content_signal_value' => '78',
				'enable_automatic_winner'      => 1,
				'minimum_engagements'          => '200',
				'minimum_duration'             => '14',
				'chance_to_beat_original'      => '97.5',
			);
			break;
		default:
			return array();
	}
}

/**
 * Return an array with the colors used by highcharts
 * @return array
 */
function tho_get_chart_colors() {
	return array(
		'#20a238',
		'#2f82d7',
		'#fea338',
		'#dd383d',
		'#ab31a4',
		'#95d442',
		'#36c4e2',
		'#525252',
		'#f3643e',
		'#e26edd'
	);
}

/**
 * Get collection of all posts from DB filtered by params
 *
 * @param $filters array
 * @param $get_total_count boolean
 *
 * @return array
 */
function tho_get_posts( $filters, $get_total_count ) {

	$args = array(
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		'exclude'        => $filters['exclude_list'],
		'orderby'        => 'id',
		'order'          => 'desc',
	);

	if ( ! empty( $filters['search_by'] ) ) {
		$args['s'] = $filters['search_by'];
	}

	if ( ! empty( $filters['post_types'] ) ) {
		$args['post_type'] = $filters['post_types'];
	}

	if ( ! $get_total_count ) {
		$args['posts_per_page'] = $filters['per_page'];
		$args['paged']          = $filters['page'];
	}

	$posts_array = get_posts( $args );

	return $posts_array;
}

/**
 * Checks to see if you are in the right hook
 *
 * @param $filter
 *
 * @return bool
 */
function tho_are_we_in( $filter ) {
	$backtrace = defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ? debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) : debug_backtrace();
	foreach ( $backtrace as $f ) {
		$all[] = $f['function'];
	}
	if ( in_array( $filter, $all ) ) {
		return true;
	}

	return false;
}

/**
 * Calculate the chance of a variation to beat the original during a test
 *
 * @param $variation_engagement_rate
 * @param $variation_views
 * @param $control_engagement_rate
 * @param $control_views
 * @param $suffix
 *
 * @return string $confidence_level
 */
function tho_test_item_beat_original( $variation_engagement_rate, $variation_views, $control_engagement_rate, $control_views, $suffix = '%' ) {
	if ( $variation_views == 0 || $control_views == 0 ) {
		return 'N/A';
	}

	$variation_engagement_rate = $variation_engagement_rate / 100;
	$control_engagement_rate   = $control_engagement_rate / 100;

	//standard deviation = sqrt((conversionRate*(1-conversionRate)/uniqueImpressions)
	$variation_standard_deviation = sqrt( ( $variation_engagement_rate * ( 1 - $variation_engagement_rate ) / $variation_views ) );
	$control_standard_deviation   = sqrt( ( $control_engagement_rate * ( 1 - $control_engagement_rate ) / $control_views ) );

	if ( ( $variation_standard_deviation == 0 && $control_standard_deviation == 0 ) || ( is_nan( $variation_standard_deviation ) || is_nan( $control_standard_deviation ) ) ) {
		return 'N/A';
	}
	//z-score = (control_engagement_rate - variation_engagement_rate) / sqrt((controlStandardDeviation^2)+(variationStandardDeviation^2))
	$z_score = ( $control_engagement_rate - $variation_engagement_rate ) / sqrt( pow( $control_standard_deviation, 2 ) + pow( $variation_standard_deviation, 2 ) );

	if ( is_nan( $z_score ) ) {
		return 'N/A';
	}

	//Confidence_level (which is synonymous with “chance to beat original”)  = normdist(z-score)
	$confidence_level = tho_norm_dist( $z_score );

	return number_format( round( ( 1 - $confidence_level ) * 100, 2 ), 2 ) . $suffix;
}

/**
 * Function that will generate a cumulative normal distribution and return the confidence level as a number between 0 and 1
 *
 * @param $x
 *
 * @return float
 */
function tho_norm_dist( $x ) {
	$b1 = 0.319381530;
	$b2 = - 0.356563782;
	$b3 = 1.781477937;
	$b4 = - 1.821255978;
	$b5 = 1.330274429;
	$p  = 0.2316419;
	$c  = 0.39894228;

	if ( $x >= 0.0 ) {
		if ( ( 1.0 + $p * $x ) == 0 ) {
			return 'N/A';
		}
		$t = 1.0 / ( 1.0 + $p * $x );

		return ( 1.0 - $c * exp( - $x * $x / 2.0 ) * $t * ( $t * ( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ) );
	} else {
		if ( ( 1.0 - $p * $x ) == 0 ) {
			return 'N/A';
		}
		$t = 1.0 / ( 1.0 - $p * $x );

		return ( $c * exp( - $x * $x / 2.0 ) * $t * ( $t * ( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ) );
	}
}

/**
 * Generate an array of dates between $start_date and $end_date depending on the $interval
 * @author: Andrei
 *
 * @param $start_date
 * @param $end_date
 * @param string $interval - can be 'day', 'week', 'month'
 *
 * @return array $dates
 */
function tho_generate_dates_interval( $start_date, $end_date, $interval = 'day' ) {

	/* just to make sure the end day has the latest hour */
	$end_date = date( 'Y-m-d', strtotime( $end_date ) ) . ' 23:59:59';

	switch ( $interval ) {
		case 'day':
			$date_format = 'd M, Y';
			break;
		case 'week':
			$date_format = '\W\e\e\k W, o';
			//TODO: the labels should be translated
			break;
		case 'month':
			$date_format = 'F Y';
			break;
		default:
			$date_format = 'Y-m-d';
			break;
	}

	$dates = array();
	for ( $i = 0; strtotime( $start_date . ' + ' . $i . 'day' ) <= strtotime( $end_date ); $i ++ ) {
		$timestamp = strtotime( $start_date . ' + ' . $i . 'day' );
		$date      = date( $date_format, $timestamp );

		//remove the 0 from the week number
		if ( $interval == 'week' ) {
			$date = str_replace( 'Week 0', 'Week ', $date );
		}
		if ( ! in_array( $date, $dates ) ) {
			$dates[] = $date;
		}
	}

	return $dates;
}

/**
 * @param array $post_ids
 *
 * @return array
 */
function tho_get_post_by_type( $post_ids = array() ) {

	$return = array();
	foreach ( $post_ids as $id ) {

		$post_type = get_post_type( $id );

		if ( ! array_key_exists( $post_type, $return ) ) {
			$return[ $post_type ] = array();
		}

		$return[ $post_type ][ $id ] = get_the_title( $id );
	}

	return $return;

}

/**
 * Check if we have a valid referrer or it's just someone from the same site
 *
 * @param $referrer
 *
 * @return boolean
 */
function tho_check_referrer( $referrer ) {

	$referrer = preg_replace( '#http(s)?://#', '', rtrim( $referrer, '/' ) );

	if ( $referrer ) {
		$trimmed = preg_replace( '#http(s)?://#', '', trim( $referrer, '/' ) );
		$host    = preg_replace( '#http(s)?://#', '', trim( $_SERVER['HTTP_HOST'], '/' ) );
		if ( strpos( $trimmed, $host ) !== 0 ) { /* the referrer is different than the current domain */
			return true;
		}
	}

	return false;
}

/**
 * Return a structure of breadcrumbs containing title, url and descendants
 * @return array
 */
function tho_get_default_breadcrumbs() {

	$plugin_url = menu_page_url( 'tho_admin_dashboard', false );

	return array(
		array(
			'key'   => 'base',
			'title' => __( 'Thrive Dashboard', THO_TRANSLATE_DOMAIN ),
			'url'   => menu_page_url( 'tve_dash_section', false ),
			'kids'  => array(
				array(
					'key'   => 'dashboard',
					'title' => __( 'Thrive Headline Optimizer', THO_TRANSLATE_DOMAIN ),
					'url'   => $plugin_url,
					'kids'  => array(
						array(
							'key'   => 'createTest',
							'title' => __( 'Create Test', THO_TRANSLATE_DOMAIN ),
							'url'   => '',
							'kids'  => array(
								array(
									'key'   => 'selectPosts',
									'title' => __( 'Select Posts', THO_TRANSLATE_DOMAIN ),
									'url'   => $plugin_url . '#createTest/selectPosts',
									'kids'  => array(
										array(
											'key'   => 'addVariations',
											'title' => __( 'Add Variations', THO_TRANSLATE_DOMAIN ),
											'url'   => $plugin_url . '#createTest/addVariations',
											'kids'  => array(
												array(
													'key'   => 'setCriteria',
													'title' => __( 'Set Test Criteria', THO_TRANSLATE_DOMAIN ),
													'url'   => $plugin_url . '#createTest/setCriteria',
													'kids'  => false
												)
											)
										)
									)
								)
							)
						),
						array(
							'key'   => 'test',
							'title' => __( 'Headline Test', THO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#test',
							'kids'  => false
						),
						array(
							'key'   => 'settings',
							'title' => __( 'Content Engagement Settings', THO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#settings',
							'kids'  => false
						),
						array(
							'key'   => 'reporting',
							'title' => __( 'Global Reports', THO_TRANSLATE_DOMAIN ),
							'url'   => $plugin_url . '#reporting',
							'kids'  => array(
								array(
									'key'   => 'postReports',
									'title' => __( 'Report', THO_TRANSLATE_DOMAIN ),
									'url'   => '',
									'kids'  => false
								)
							)
						),
					)
				)
			)
		)
	);
}

/**
 * Get the engagement name from the engagement ID
 *
 * @param $engagement_id
 *
 * @return string|void
 */
function get_engagement_name( $engagement_id ) {

	switch ( $engagement_id ) {
		case THO_CLICK_ENGAGEMENT:
			$engagement_name = __( 'Click', THO_TRANSLATE_DOMAIN );
			break;
		case THO_SCROLL_ENGAGEMENT:
			$engagement_name = __( 'Scroll', THO_TRANSLATE_DOMAIN );
			break;
		case THO_TIME_ENGAGEMENT:
			$engagement_name = __( 'Time on content', THO_TRANSLATE_DOMAIN );
			break;
		default:
			$engagement_name = '';
			break;
	}

	return $engagement_name;
}

/**
 * Chose to ignore logs or not
 * @return bool
 */
function tho_ignore_log() {
	return current_user_can( 'edit_posts' );
}

/**
 * Generate random log data
 */
function tho_generate_test_data() {
	/* @var Tho_Db */
	global $thodb;
	global $wpdb;

	//erase all data from the test before adding new one.
	$reset_data        = true;
	$test_status       = THO_TEST_STATUS_ACTIVE;
	$number_of_tests   = 10;
	$number_of_entries = 10000;
	$engagement_rate   = array();
	$sql               = 'SELECT * FROM `wp_tho_test_items` INNER JOIN (SELECT id AS tid, post_id, date_started, date_completed FROM wp_tho_tests ' . ( empty( $test_status ) ? '' : 'WHERE status=' . $test_status ) . ' ORDER BY id DESC LIMIT ' . $number_of_tests . ') AS ids ON ids.tid=test_id ';
	$items             = $wpdb->get_results( $sql );
	foreach ( $items as $i => $item ) {
		if ( $reset_data == true ) {
			$wpdb->query( 'DELETE FROM `wp_tho_event_log` WHERE variation=' . $item->id );
			$wpdb->query( 'UPDATE `wp_tho_test_items` SET `views` = 0, `engagements`=0  WHERE `id` =' . $item->id );
		}
		$modulo                = ( $i > 9 ) ? $i % 10 : $i;
		$engagement_rate[ $i ] = ( $modulo + 1 ) * 3;
	}
	shuffle( $engagement_rate );
	$wpdb->query( 'START TRANSACTION' );
	for ( $i = 1; $i <= $number_of_entries; $i ++ ) {
		$index            = mt_rand( 0, count( $items ) - 1 );
		$random_test_item = $items[ $index ];
		//generate random date between when the test was created and when it has finished or today
		$int = mt_rand(
			strtotime( $random_test_item->date_started ),
			$random_test_item->date_completed == null ? time() : strtotime( $random_test_item->date_completed )
		);

		$engagement_type = rand( 1, 3 );

		$log_model = array(
			'date'            => date( "Y-m-d H:i:s", $int ),
			'log_type'        => THO_LOG_IMPRESSION,
			'engagement_type' => $engagement_type,
			'post_id'         => $random_test_item->post_id,
			'variation'       => $random_test_item->id,
			'post_type'       => get_post_type( $random_test_item->post_id ),
			'referrer'        => '',
			'archived'        => 0
		);
		$thodb->insert_event( $log_model );

		if ( mt_rand() % $engagement_rate[ $index ] == 0 ) {
			$log_model = array(
				'date'            => date( "Y-m-d H:i:s", $int ),
				'log_type'        => THO_LOG_ENGAGEMENT,
				'engagement_type' => $engagement_type,
				'post_id'         => $random_test_item->post_id,
				'variation'       => $random_test_item->id,
				'post_type'       => get_post_type( $random_test_item->post_id ),
				'referrer'        => '',
				'archived'        => 0
			);
			$thodb->insert_event( $log_model );
		}
	}
	$wpdb->query( 'COMMIT' );
}


/**
 * Displays notices of inconclusive tests
 *
 * @param $id
 */
function tho_inconclusive_tests_notice() {
	$running_tests = tho_get_running_inconclusive_tests();

	$inconclusive_tests_option = tho_get_option( THO_INCONCLUSIVE_TESTS_OPTION );
	$inconclusive_tests        = array();
	if ( ! is_array( $inconclusive_tests_option ) ) {
		$inconclusive_tests = explode( ',', $inconclusive_tests_option );
	}

	foreach ( $running_tests as $tests ) {
		$id = $tests->id;
		if ( ! in_array( $id, $inconclusive_tests ) ) {
			?>
			<div data-test-id="<?php echo $id; ?>" class="notice-error notice tho_error_inconclusive_test_notice is-dismissible">
				<p><?php echo __( 'One of your active A/B tests in Thrive Headline Optimizer appears to be inconclusive. The test has reached double the threshold time and engagement numbers, but no clear winner has been found. <a href="javascript:void(0);" onclick="ThriveHeadInconclusive.inconclusive_tests.trigger_dismiss_notice(' . $id . ')">Click here to view the test</a>.', THO_TRANSLATE_DOMAIN ); ?></p>
			</div>
			<?php
		}
	}
}