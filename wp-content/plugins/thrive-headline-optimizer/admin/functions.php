<?php
/**
 * Created by PhpStorm.
 * User: sala
 * Date: 26-Jan-16
 * Time: 13:16
 */

/**
 * @param array $menus
 *
 * @return array
 */
function tho_admin_menu( $menus = array() ) {
	$menus['tho'] = array(
		'parent_slug' => 'tve_dash_section',
		'page_title'  => __( 'Thrive Headline Optimizer', THO_TRANSLATE_DOMAIN ),
		'menu_title'  => __( 'Thrive Headline Optimizer', THO_TRANSLATE_DOMAIN ),
		'capability'  => 'manage_options',
		'menu_slug'   => 'tho_admin_dashboard',
		'function'    => 'tho_admin_dashboard',
	);

	return $menus;
}

/**
 * Display Thrive Headline Test Dashboard - the main plugin page
 */
function tho_admin_dashboard() {
	if ( ! tho_check_license() ) {
		return tho_license_warning();
	}

	include dirname( __FILE__ ) . '/views/dashboard.php';
}

/**
 * Enqueue admin scripts and styles
 */
function tho_admin_enqueue_scripts( $hook ) {

	if ( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'edit.php' ) {
		tho_enqueue_style( 'tho-admin-styles', THO_ADMIN_URL . '/css/post-styles.css' );
		tho_enqueue_script( 'tho-admin-velocity', THO_ADMIN_URL . '/js/libs/velocity.min.js' );
		tho_enqueue_script( 'tho-admin-global', THO_ADMIN_URL . '/js/global.js' );
		tho_enqueue_script( 'tho-admin-tooltip', THO_ADMIN_URL . '/js/libs/tooltip.min.js' );

		tho_enqueue_script( 'tho-edit-post', THO_ADMIN_URL . '/js/edit_post.js' );

		$active_test = tho_get_running_test( array( 'post_id' => get_the_ID() ) );

		wp_localize_script( 'tho-edit-post', 'ThriveHead', array(
			'hasActiveTest'       => ! empty( $active_test ),
			'addedVariationsText' => __( 'You have created some headline variations. In order to start the test, simply save the post', THO_TRANSLATE_DOMAIN )
		) );
	}

	$accepted_hooks = apply_filters( 'tho_accepted_admin_pages', array(
		'admin_page_tho_admin_dashboard',
		'thrive-dashboard_page_tho_admin_dashboard'
	) );

	if ( ! in_array( $hook, $accepted_hooks ) ) {
		return;
	}

	/* first, the license check */
	if ( ! tho_license_activated() ) {
		return;
	}

	tve_dash_enqueue();

	tho_enqueue_style( 'admin-styles', THO_ADMIN_URL . '/css/styles.css' );

	tho_enqueue_script( 'tho-highcharts', tho_plugin_url( '/admin/js/libs/highcharts.js' ) );
	tho_enqueue_script( 'tho-highcharts-more', tho_plugin_url( '/admin/js/libs/highcharts-more.js' ) );

	tho_enqueue_script( 'tve-dash-api-wistia-popover', '//fast.wistia.com/assets/external/popover-v1.js', array(), '', true );

	tho_enqueue_script( 'tho-admin-js', tho_plugin_url( '/admin/js/admin.min.js' ), array(
		'jquery',
		'backbone',
		'tve-dash-main-js'
	), false, true );

	$ThriveHead = array(
		'routes'       => array(
			'tests'     => tho_get_route_url( 'tests' ),
			'settings'  => tho_get_route_url( 'settings' ),
			'posts'     => tho_get_route_url( 'posts' ),
			'logs'      => tho_get_route_url( 'logs' ),
			'variation' => tho_get_route_url( 'variation' ),
		),
		'nonce'        => wp_create_nonce( 'wp_rest' ),
		'translations' => require THO_PATH . 'admin/i18n.php',
		'breadcrumbs'  => tho_get_default_breadcrumbs(),
		'const'        => array(
			'toast_timeout'         => THO_TOAST_TIMEOUT,
			'test_status_active'    => THO_TEST_STATUS_ACTIVE,
			'test_status_completed' => THO_TEST_STATUS_COMPLETED,
			'CHART_RED'             => '#F60000',
			'CHART_GREEN'           => '#006600',
			'CHART_GREY'            => '#C0C0C0',
			'CHART_COLORS'          => tho_get_chart_colors(),
			'date_intervals'        => array(
				'last_7_days'       => THO_LAST_7_DAYS,
				'last_30_days'      => THO_LAST_30_DAYS,
				'this_month'        => THO_THIS_MONTH,
				'last_month'        => THO_LAST_MONTH,
				'this_year'         => THO_THIS_YEAR,
				'last_year'         => THO_LAST_YEAR,
				'last_12_months'    => THO_LAST_12_MONTHS,
				'custom_date_range' => THO_CUSTOM_DATE_RANGE
			),
			'log_source'            => THO_SOURCE_REPORT_ALL,
			'log_type'              => THO_ENGAGEMENT_REPORT,
			'reports'               => array(
				'engagement_report'            => THO_ENGAGEMENT_REPORT,
				'engagement_rate_report'       => THO_ENGAGEMENT_RATE_REPORT,
				'cumulative_engagement_report' => THO_CUMULATIVE_ENGAGEMENT_REPORT,
			)
		),
	);
	wp_localize_script( 'tho-admin-js', 'ThriveHead', $ThriveHead );

	add_action( 'admin_print_footer_scripts', 'tho_backbone_templates' );
}

/**
 *
 */
function tho_backbone_templates() {
	$templates = tve_dash_get_backbone_templates( plugin_dir_path( __FILE__ ) . 'views/template', 'template' );
	tve_dash_output_backbone_templates( $templates );
}

function tho_plugin_init() {
	/* check database version and run any necessary update scripts */
	require_once THO_PATH . 'init/database/Tho_Database_Manager.php';
	Tho_Database_Manager::check();
}

/**
 * get list of post types
 * @return WP_REST_Response
 */
function tho_get_post_types() {
	$all_post_types = get_post_types( array(
		'public' => true
	) );
	$exceptionList  = array( 'attachment', 'focus_area', 'thrive_optin', 'tcb_lightbox', 'wysijap' );
	$post_types     = array_diff( $all_post_types, $exceptionList );

	$names = array();
	foreach ( $post_types as $key => $type ) {
		$labels        = get_post_type_object( $key );
		$names[ $key ] = $labels->labels->name;
	}

	return $names;
}

/**
 *  Displays an error notice at the top of the screen
 */
function tho_error_notice() {
	?>
	<div id="tho_error_notice" class="error notice hidden">
		<p class="tho_error_text"><?php echo __( 'You can not have identical variations!', THO_TRANSLATE_DOMAIN ); ?></p>
	</div>
	<?php
	$cache_plugin = tve_dash_detect_cache_plugin();
	if ( $cache_plugin ) {
		?>
		<div id="tho_error_cache_notice" class="error notice hidden">
			<div class="tvd-v-spacer"></div>
			<p class="tho_error_text"><?php echo __( "After saving these settings, the cache data from <strong>$cache_plugin</strong> plugin will be automatically cleared (this is required each time you make a change to these settings).", THO_TRANSLATE_DOMAIN ); ?></p>
		</div>
		<?php
	}
}

/**
 * includes the variations to the post view
 */
function tho_variation_template() {

	$license           = tho_check_license();
	$invalid_post_type = array( 'tcb_lightbox', 'focus_area', 'thrive_optin', 'thrive_ad_group' );
	$post_id           = get_the_ID();

	if ( ! $license || in_array( get_post_type( $post_id ), $invalid_post_type ) ) {
		return;
	}

	$runningTest = tho_get_running_test( array( 'post_id' => $post_id ) );

	$isTestRunning = ! empty( $runningTest );

	if ( $isTestRunning ) {
		$test_items = tho_get_test_items( $runningTest->id, true );

		$engagement_rate = array();
		$post_variations = array();
		foreach ( $test_items as $item ) {

			if ( $item->is_control ) {
				$control_id                   = $item->id;
				$engagement_rate[ $item->id ] = $item->engagement_rate;
			} else {
				$post_variations[ $item->id ] = $item->variation_title;
				$engagement_rate[ $item->id ] = $item->engagement_rate;
			}

		}

		/* recalculate the engagement rate for the progress bar */
		$min = is_numeric( min( $engagement_rate ) ) ? intval( min( $engagement_rate ) ) : 0;
		$max = is_numeric( max( $engagement_rate ) ) ? intval( max( $engagement_rate ) ) : 0;
		/* I'm trying to create some kind of range in order to display a wider range */
		$min = $min / 10;
		$max = $max + $min > 100 ? 100 : $max + $min;

		$new_percentage = array();
		if ( $max == $min ) {
			$new_percentage = $engagement_rate;
		} else {
			foreach ( $engagement_rate as $k => $v ) {
				$new_percentage[ $k ] = is_numeric( $v ) ? round( ( $v - $min ) * 100 / ( $max - $min ) ) : 0;
			}
		}
	} else {
		$post_variations = get_post_meta( $post_id, '_tho_draft_variation', true );
	}

	if ( $post_id !== intval( get_option( 'page_on_front' ) ) && $post_id !== intval( get_option( 'page_for_posts' ) ) ) {
		if ( $isTestRunning ) {
			include THO_ADMIN_PATH . '/views/edit_post/variation_template_running.php';
		} else {
			include THO_ADMIN_PATH . '/views/edit_post/variation_template.php';
		}

	}
}

/**
 * Adds post headline variation
 *
 * @param $post_ID
 * @param $post
 * @param $update
 */
function tho_save_post_variation( $post_ID, $post, $update ) {
	$post_status  = get_post_status( $post_ID );
	$running_test = tho_get_running_test(
		array( 'post_id' => get_the_ID() )
	);

	if ( isset( $_POST['tho_post_variation'] ) && ! empty( $_POST['tho_post_variation'][0] ) && empty( $running_test ) ) {
		$tho_post_variations_array = array_filter( $_POST['tho_post_variation'] );

		/* When the user saves the draft, we save the variations */
		add_post_meta( $post_ID, '_tho_draft_variation', $tho_post_variations_array, true ) or update_post_meta( $post_ID, '_tho_draft_variation', $tho_post_variations_array );
		if ( $post_status == "publish" ) {
			/* Delete it, because we don't need it */
			delete_post_meta( $post_ID, '_tho_draft_variation' );
			tho_save_test( $post_ID, $tho_post_variations_array, array(), array( 'enable_automatic_winner' => 0 ) ); /*Set automatic winner setting to off when creating a test from post view*/
		}

	}

}

/**
 * Adds the thrive headline test meta boxes
 */
function tho_add_headline_meta_box() {

	if ( ! tho_check_license() ) {
		return;
	}

	$post_types = apply_filters( 'tho_custom_post_type_meta_box_filter', array() );

	add_meta_box(
		'thrive_headline_optimizer_meta_box',
		__( "Thrive Headline Optimizer Test" ),
		'show_custom_meta_box',
		$post_types,
		'side',
		'high'
	);
}

/**
 * Add post types for where the meta box to appear
 *
 * @param $post_types
 *
 * @return array
 */
function tho_add_post_types_meta_filter( $post_types ) {

	$default = array( 'post', 'page', 'product', 'appr_lesson', 'appr_page' );

	$post_types = wp_parse_args( $post_types, $default );

	return $post_types;
}

/**
 * Add icon element for posts with running test
 *
 * @param $actions
 * @param $page_object
 *
 * @return mixed
 */
function tho_add_post_test_icon( $actions, $page_object ) {

	if ( ! tho_license_activated() ) {
		return $actions;
	}

	$post_id = $page_object->ID;
	$test    = tho_get_running_test( array( 'post_id' => $post_id ) );

	if ( empty( $test ) ) {
		return $actions;
	}

	echo '<span class="tho-has-test tho-icon-flask tvd-left"></span>';

	return $actions;
}

/**
 * Callback method for add_meta_box
 */
function show_custom_meta_box() {

	if ( ! tho_check_license() ) {
		return;
	}

	$post_id         = get_the_ID();
	$runningTest     = tho_get_running_test( array( 'post_id' => $post_id ) );
	$test_statistics = tho_get_test_statistics( $post_id );
	$isTestRunning   = ! empty( $runningTest );
	include THO_PATH . 'admin/views/edit_post/meta_box_template.php';
}

/**
 * Add META tags so we will prevent browser from caching our admin
 */
function tho_admin_cache_meta() {

	$page = get_current_screen();
	if ( $page && $page->id == 'thrive-dashboard_page_tho_admin_dashboard' ):
		?>
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Expires" content="0"/>
		<?php
	endif;
}

