<?php
/**
 * Global functions file.
 *
 * @package Thrive Quiz Builder
 */

/**
 * Wrapper over the wp_enqueue_style function.
 * it will add the plugin version to the style link if no version is specified
 *
 * @param string $handle Name of the stylesheet. Should be unique.
 * @param string|bool $src Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
 * @param array $deps Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
 * @param bool|string $ver Optional. String specifying stylesheet version number.
 * @param string $media Optional. The media for which this stylesheet has been defined.
 */
function tqb_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( false === $ver ) {
		$ver = Thrive_Quiz_Builder::V;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * Wrapper over the wp_enqueue_script function.
 * It will add the plugin version to the script source if no version is specified.
 *
 * @param string $handle Name of the script. Should be unique.
 * @param string $src Full URL of the script, or path of the script relative to the WordPress root directory.
 * @param array $deps Optional. An array of registered script handles this script depends on. Default empty array.
 * @param bool $ver Optional. String specifying script version number, if it has one, which is added to the URL.
 * @param bool $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
 */
function tqb_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {

	if ( false === $ver ) {
		$ver = Thrive_Quiz_Builder::V;
	}

	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '#\.min\.js$#', '.js', $src );
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * TODO: NOT USED SO FAR!!!!!!!
 *
 * Run a MySQL transaction query, if supported
 *
 * @param string $type start (default), commit, rollback.
 *
 * @since 2.5.0
 */
function tqb_transaction_query( $type = 'start' ) {
	global $wpdb;

	$wpdb->hide_errors();

	if ( ! defined( 'TQB_USE_TRANSACTIONS' ) ) {
		define( 'TQB_USE_TRANSACTIONS', true );
	}

	if ( TQB_USE_TRANSACTIONS ) {
		switch ( $type ) {
			case 'commit' :
				$wpdb->query( 'COMMIT' );
				break;
			case 'rollback' :
				$wpdb->query( 'ROLLBACK' );
				break;
			default :
				$wpdb->query( 'START TRANSACTION' );
				break;
		}
	}
}

/**
 * Appends the WordPress tables prefix and the default tqb_ prefix to the table name
 *
 * @param string $table name of the table.
 *
 * @return string the modified table name
 */
function tqb_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . Thrive_Quiz_Builder::DB_PREFIX . $table;
}

/**
 * Checks if we are editing a design
 */
function tqb_is_editor_page() {
	global $variation;

	return isset( $_GET[ TVE_EDITOR_FLAG ] ) && ! empty( $variation ) && TCB_Hooks::is_editable( get_the_ID() );
}

/**
 * Enqueues scripts and styles for a specific variation
 *
 * @param array $for_variation
 *
 * @return array
 */
function tqb_enqueue_variation_scripts( $for_variation = null ) {

	if ( empty( $for_variation ) ) {
		global $variation;
		$for_variation = $variation;
	}
	if ( empty( $for_variation ) || empty( $for_variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ) {
		return array(
			'fonts' => array(),
			'css'   => array(),
			'js'    => array(),
		);
	}

	/** enqueue Custom Fonts, if any */
	$fonts = TCB_Hooks::tqb_editor_enqueue_custom_fonts( $for_variation );

	$config = TCB_Hooks::tqb_editor_get_template_config( $for_variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );

	/** custom fonts for the form */
	if ( ! empty( $config['fonts'] ) ) {
		foreach ( $config['fonts'] as $font ) {
			$fonts[ 'tqb-font-' . md5( $font ) ] = $font;
			wp_enqueue_style( 'tqb-font-' . md5( $font ), $font );
		}
	}

	$quiz_style_meta   = TQB_Post_meta::get_quiz_style_meta( $for_variation['quiz_id'] );
	$template_css_file = tqb()->get_style_css( $quiz_style_meta );

	/* include also the CSS for each variation template */
	if ( ! empty( $template_css_file ) ) {
		$template_css_file_path = tqb()->plugin_url( 'tcb-bridge/editor-templates/css/' . TQB_Template_Manager::type( $for_variation['post_type'] ) . '/' . $template_css_file );
		$css_handle             = 'tqb-' . TQB_Template_Manager::type( $for_variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) . '-' . str_replace( '.css', '', $template_css_file );

		tqb_enqueue_style( $css_handle, $template_css_file_path );
		$css = array(
			$css_handle => $template_css_file_path,
		);
	}


	$js = array();

	if ( ! empty( $for_variation[ Thrive_Quiz_Builder::FIELD_ICON_PACK ] ) ) {
		tve_enqueue_icon_pack();
	}

	if ( ! empty( $for_variation[ Thrive_Quiz_Builder::FIELD_MASONRY ] ) ) {
		wp_enqueue_script( 'jquery-masonry' );
		$js['jquery-masonry'] = includes_url( 'js/jquery/jquery.masonry.min.js' );
	}

	if ( ! empty( $for_variation[ Thrive_Quiz_Builder::FIELD_TYPEFOCUS ] ) ) {
		tqb_enqueue_script( 'tve_typed', tve_editor_js() . '/typed.min.js', array(), false, true );
		$js['tve_typed'] = tve_editor_js() . '/typed.min.js';
	}

	return array(
		'fonts' => $fonts,
		'js'    => $js,
		'css'   => $css,
	);
}

/**
 * Enqueue the default styles when they are needed
 *
 * @return array the enqueued styles
 */
function tqb_enqueue_default_scripts() {

	$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

	/* flat is the default style */
	global $tve_style_family_classes;
	$tve_style_families = tve_get_style_families();
	$style_family       = 'Flat';
	$style_key          = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $style_family ] );

	/** Style family */
	wp_style_is( $style_key ) || tve_enqueue_style( $style_key, $tve_style_families[ $style_family ] );

	/** Basic Standard Shortcode Style */
	wp_enqueue_style( 'tqb-shortcode', tqb()->plugin_url( 'assets/css/frontend/tqb-shortcode.css' ) );

	$frontend_options = array(
		'is_editor_page'   => is_editor_page(),
		'page_events'      => array(),
		'is_single'        => 1,
		'ajaxurl'          => admin_url( 'admin-ajax.php' ),
		'social_fb_app_id' => function_exists( 'tve_get_social_fb_app_id' ) ? tve_get_social_fb_app_id() : '',
	);

	if ( ! wp_script_is( 'tve_frontend' ) ) {

		tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/thrive_content_builder_frontend' . $js_suffix, array( 'jquery' ), false, true );

		wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );
	}
}

/**
 * Generate an array of dates between $start_date and $end_date depending on the $interval
 *
 * @param $start_date
 * @param $end_date
 * @param string $interval - can be 'day', 'week', 'month'
 *
 * @return array $dates
 */
function tqb_generate_dates_interval( $start_date, $end_date, $interval = 'day' ) {
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
			return array();
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
 * return a formatted conversion rate based on $impressions and $conversions
 *
 * @param int $impressions
 * @param int $conversions
 * @param string $suffix
 * @param string $decimals
 *
 * @return string $rate the calculated conversion rate
 */
function tqb_conversion_rate( $impressions, $conversions, $suffix = '%', $decimals = '2' ) {
	if ( $conversions == 0 || $impressions == 0 ) {
		return 'N/A';
	}

	return round( 100 * ( $conversions / $impressions ), $decimals ) . $suffix;
}

/**
 * generate a random number between 0 and $total-1
 *
 * @param int $total
 * @param int $multiplier for smaller values, it's better to extend the interval by a number of times,
 * example: to choose between 0 and 1 -> we think it's better to have a random number between 0 and 10000 and split that into halves
 *
 * @return int
 */
function tqb_get_random_index( $total, $multiplier = 1000 ) {
	$_rand = function_exists( 'mt_rand' ) ? mt_rand( 0, $total * $multiplier - 1 ) : rand( 0, $total * $multiplier - 1 );

	return intval( floor( $_rand / $multiplier ) );
}


/**
 * Return data for the test chart
 *
 * @param $filter
 *
 * @return array
 */
function tqb_get_conversion_rate_test_data( $filter ) {

	global $tqbdb;
	$report_data = $tqbdb->get_report_data_count_event_type( $filter );
	$group_names = $filter['group_names'];

	//generate interval to fill empty dates.
	$dates = tqb_generate_dates_interval( $filter['start_date'], $filter['end_date'], $filter['interval'] );

	$chart_data_temp = array();
	foreach ( $report_data as $interval ) {
		//Group all report data by main_group_id
		if ( ! isset( $chart_data_temp[ $interval->data_group ] ) ) {
			$chart_data_temp[ $interval->data_group ]['id']                                  = intval( $interval->data_group );
			$chart_data_temp[ $interval->data_group ]['name']                                = $group_names[ intval( $interval->data_group ) ];
			$chart_data_temp[ $interval->data_group ][ Thrive_Quiz_Builder::TQB_CONVERSION ] = array();
			$chart_data_temp[ $interval->data_group ][ Thrive_Quiz_Builder::TQB_IMPRESSION ] = array();
			$chart_data_temp[ $interval->data_group ]['data']                                = array();
		}

		//store the date interval so we can add it as X Axis in the chart
		if ( $filter['interval'] == 'day' ) {
			$interval->date_interval = date( 'd M, Y', strtotime( $interval->date_interval ) );
		}

		$chart_data_temp[ $interval->data_group ][ intval( $interval->event_type ) ][ $interval->date_interval ] = intval( $interval->log_count );
	}

	$chart_data = array();
	foreach ( $group_names as $key => $name ) {

		if ( ! isset( $chart_data[ $key ] ) ) {
			$chart_data[ $key ]['id']               = $key;
			$chart_data[ $key ]['name']             = $name;
			$chart_data[ $key ]['data']             = array();
			$chart_data[ $key ]['impression_count'] = array();
			$chart_data[ $key ]['conversion_count'] = array();
		}
		//complete missing data with zero
		foreach ( $dates as $date ) {
			if ( ! isset( $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_IMPRESSION ][ $date ] )
			     ||
			     ! isset( $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_CONVERSION ][ $date ] )
			     || $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_IMPRESSION ][ $date ] == 0
			) {
				$chart_data[ $key ]['data'][] = 0;
			} else {
				$chart_data[ $key ]['data'][] = (float) tqb_conversion_rate( $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_IMPRESSION ][ $date ], $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_CONVERSION ][ $date ], '', 2 );
			}
			/**
			 * count impressions and conversions so we can use those values in the "cumulative" report shown on the test screen
			 */
			$chart_data[ $key ]['impression_count'][] = isset( $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_IMPRESSION ][ $date ] ) ? $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_IMPRESSION ][ $date ] : 0;
			$chart_data[ $key ]['conversion_count'][] = isset( $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_CONVERSION ][ $date ] ) ? $chart_data_temp[ $key ][ Thrive_Quiz_Builder::TQB_CONVERSION ][ $date ] : 0;
		}
	}

	$conversions = 0;
	$impressions = 0;
	foreach ( $chart_data_temp as $key ) {
		$conversions += array_sum( $key[ Thrive_Quiz_Builder::TQB_CONVERSION ] );
		$impressions += array_sum( $key[ Thrive_Quiz_Builder::TQB_IMPRESSION ] );
	}
	$average_rate = (float) tqb_conversion_rate( $impressions, $conversions, '', 2 );

	return array(
		'chart_title'  => __( 'Conversion rate over time', Thrive_Quiz_Builder::T ),
		'chart_data'   => $chart_data,
		'chart_x_axis' => $dates,
		'chart_y_axis' => __( 'Conversion Rate', Thrive_Quiz_Builder::T ) . ' (%)',
		'table_data'   => array(
			'count_table_data' => count( $dates ),
			'average_rate'     => $average_rate,
		),
	);
}

/**
 * Function that will generate a cumulative normal distribution and return the confidence level as a number between 0 and 1
 *
 * @param $x
 *
 * @return float
 */
function tqb_norm_dist( $x ) {
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
 * Function that will create frontend error message
 *
 * @param $text
 *
 * @return string
 */
function tqb_create_frontend_error_message( $text ) {
	$html = '';
	foreach ( $text as $error ) {
		$html .= '<div class="tqb-frontend-error-message-individual"><p class="tqb-error-message"><span>Error: </span> ' . $error . '</p></div>';
	}

	return $html;
}

/**
 * Add 'move forward' event on visual editor
 */
function tqb_event_actions( $actions, $scope, $post_id ) {

	$post       = get_post( $post_id );
	$post_types = array(
		Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE,
		Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
		Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
	);
	if ( ! empty( $post ) && in_array( $post->post_type, $post_types ) ) {
		require_once dirname( dirname( __FILE__ ) ) . '/tcb-bridge/class-tqb-next-step-event.php';
		$actions['thrive_quiz_next_step'] = array(
			'class' => 'TQB_Thrive_Next_Step',
			'order' => 90,
		);
	}

	return $actions;
}

/**
 * Gets default values
 *
 * @param string $option
 *
 * @return array
 */
function tqb_get_default_values( $option = '' ) {

	$has_quizzes = get_posts( array(
		'post_type' => Thrive_Quiz_Builder::SHORTCODE_NAME,
	) );

	switch ( $option ) {
		case Thrive_Quiz_Builder::PLUGIN_SETTINGS:
			return array(
				'tqb_promotion_badge' => ( empty( $has_quizzes[0] ) ) ? 1 : 0,
			);
			break;
		default:
			return array();
			break;
	}
}

/**
 * @param $option_name
 * @param array $default_values
 *
 * @return array|mixed
 */
function tqb_get_option( $option_name, $default_values = array() ) {

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
function tqb_update_option( $option_name, $value, $serialize = false ) {

	if ( empty( $option_name ) || empty( $value ) ) {
		return false;
	}

	$old_value = tqb_get_option( $option_name );

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
