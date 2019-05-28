<?php
/**
 * global functions file
 */

/**
 * make sure the TL_product is displayed in thrive dashboard
 *
 * @param $items
 *
 * @return array
 */
function tho_add_to_dashboard( $items ) {
	require_once dirname( __FILE__ ) . '/classes/class-tho-product.php';
	$items[] = new THO_Product();

	return $items;
}

function tho_load_dash_version() {
	$tho_dash_path      = dirname( dirname( __FILE__ ) ) . '/thrive-dashboard';
	$tho_dash_file_path = $tho_dash_path . '/version.php';

	if ( is_file( $tho_dash_file_path ) ) {
		$version                                  = require_once( $tho_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tho_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-headline-optimizer',
			'from'   => 'plugins',
		);
	}
}

/**
 * Load plugin text domain @const THO_TRANSLATE_DOMAIN
 */
function tho_load_plugin_textdomain() {
	$domain = THO_TRANSLATE_DOMAIN;
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$path   = 'thrive-headline-optimizer/languages/';

	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . "-" . $locale . ".mo" );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 * Check if there is a valid activated license for the THT plugin
 *
 * @return bool
 */
function tho_check_license() {
	return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::THO_TAG );
}

/**
 * show a box with a warning message and a link to take the user to the license activation page
 * this will be called only when no valid / activated license has been found
 *
 * @return mixed
 */
function tho_license_warning() {
	return include THO_PATH . 'admin/views/license_inactive.php';
}

/**
 * check if there is a valid activated license for the THT plugin
 *
 * @return bool
 */
function tho_license_activated() {
	return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::THO_TAG );
}

/**
 * Register REST Routes
 */
function tho_create_initial_rest_routes() {

	$endpoints = array(
		'THO_REST_Tests_Controller',
		'THO_REST_Variation_Controller',
		'THO_REST_Settings_Controller',
		'THO_REST_Posts_Controller',
		'THO_REST_Logs_Controller',
	);

	foreach ( $endpoints as $e ) {
		$controller = new $e();
		$controller->register_routes();
	}
}

/**
 * Load header scripts
 */
function tho_enqueue_scripts() {

	if ( tho_license_activated() ) {
		/* header scripts */
		tho_enqueue_script( 'tho-header-js', THO_URL . 'frontend/js/header.min.js' );
		/* footer triggers */
		tho_enqueue_script( 'tho-footer-js', THO_URL . 'frontend/js/triggers.min.js', array( 'jquery', 'tho-header-js' ), false, true );

		$variations = tho_get_active_tests_variations();

		wp_localize_script( 'tho-header-js', 'THO_Head', array(
			'variations'  => $variations,
			'post_id'     => get_the_ID(),
			'element_tag' => THO_HEADLINE_TAG,
			'woo_tag'     => THO_WOO_TAG,
		) );

	}
}

function tho_print_footer_scripts() {
	/* first, the license check */
	if ( ! tho_license_activated() ) {
		return;
	}

	$is_single = is_singular();
	if ( $is_single ) {
		$post_id     = get_the_ID();
		$active_test = tho_get_running_test( array( 'post_id' => $post_id ) );
	} else {
		$post_id     = 0;
		$active_test = 0;
	}

	$tm          = THO_Trigger_Manager::instance();
	$triggers    = $tm->get_active_triggers( $post_id, $is_single );
	$engagements = THO_Trigger_Manager::get_trigger_engagement( $triggers );

	$THO_Front = array(
		'end_of_content_id' => THO_END_OF_CONTENT_ID,
		'is_single'         => $is_single,
		'log_url'           => tho_get_route_url( 'logs' ),
		'active_triggers'   => $triggers,
		'log_engagements'   => $engagements,
		'post_id'           => $post_id,
		'test_id'           => $active_test ? $active_test->id : 0,
		'const'             => array(
			'_e_click'    => THO_CLICK_ENGAGEMENT,
			'_e_scroll'   => THO_SCROLL_ENGAGEMENT,
			'_e_time'     => THO_TIME_ENGAGEMENT,
			'_impression' => THO_LOG_IMPRESSION,
			'_engagement' => THO_LOG_ENGAGEMENT,
		),
	);

	echo sprintf( '<script type="text/javascript">/*<![CDATA[*/var THO_Front = THO_Front || {}; THO_Front.data = %s/*]]> */</script>', json_encode( $THO_Front ) );
}

/**
 * Add frontend triggers before the title so we can log impressions and engagements
 *
 * @param $title
 * @param null $post_id
 *
 * @return mixed
 */
function tho_add_title_variations( $title, $post_id = null ) {

	$post_type = get_post_type( $post_id );
	if ( $post_type == 'nav_menu_item' ) {
		$post_id = get_post_meta( $post_id, '_menu_item_object_id', true );
	}

	if ( tho_are_we_in( 'wc_add_to_cart_message' ) ) {
		return tho_backend_change_title( $title, $post_id );
	}

	$title = THO_Trigger_Manager::instance()->title_trigger( $post_id, $title );

	return $title;
}

/**
 * Change the title for the cases where the user does not have cache and backend processing is possible
 *
 * @param $title
 * @param $post_id
 *
 * @return mixed
 */
function tho_backend_change_title( $title, $post_id ) {
	if ( empty( $_COOKIE['tho_post_titles'] ) ) {
		return $title;
	}

	$headlines = json_decode( stripslashes( $_COOKIE['tho_post_titles'] ), true );
	if ( empty( $headlines[ $post_id ] ) ) {
		return $title;
	}

	$variations = tho_get_active_tests_variations();

	if ( empty( $variations[ $post_id ]['variations'][ $headlines[ $post_id ] ] ) ) {
		return $title;
	} else {
		return $variations[ $post_id ]['variations'][ $headlines[ $post_id ] ];
	}
}

/**
 * Same trigger as for the posts, but this one is special for woocommerce
 *
 * @param $title
 * @param $product
 *
 * @return mixed
 */
function tho_add_woocommerce_title_variation( $title, $product ) {

	if ( empty( $product ) ) {
		return $title;
	}

	$id = empty( $product->id ) ? ( empty( $product->post->ID ) ? 0 : $product->post->ID ) : $product->id;

	$title = tho_backend_change_title( $title, $id );

	$title = THO_Trigger_Manager::instance()->title_trigger( $id, $title );

	return $title;
}

/**
 * Append the content with and element to be used in JS
 * Used for scrolling triggers
 *
 * @see triggers.js
 *
 * @param $content
 *
 * @return string
 */
function tho_filter_end_content( $content ) {
	if ( ! is_singular() ) {
		return $content;
	}

	$content .= '<span id="' . THO_END_OF_CONTENT_ID . '" style="display: block; visibility: hidden;"></span>';

	return $content;
}

/**
 * Update the cookie data with the new values.
 * One user, for one post, can log only one impression and engagement for each type.
 *
 * A user can log an engagement only if he previously logged an impression.
 * For click, an impression is logged when the user sees the post on index, for the rest, the impression is logged when the user enters the single post
 *
 * If the user goes on a single post without previously making a click impression, he won't be able to make any other click logs - no_click variable
 *
 * For a single post, a user will log an impression and engagements, only once, for the first test he sees.
 * For the rest of the tests on this post, he will not take part.
 *
 * @param $data
 */
function tho_update_user_cookie_data( $data ) {

	$cookie_key = 'tho_post_cookie_' . $data['post_id'];

	if ( empty( $_COOKIE[ $cookie_key ] ) ) {
		/* by default the user hasn't made any impression or engagement type */
		$old_data = array(
			'no_click'    => (int) empty( $data['is_single'] ),
			'impressions' => array(),
			'engagements' => array(),
		);
	} else {
		$old_data = json_decode( stripslashes( $_COOKIE[ $cookie_key ] ), true );
	}

	$cookie_data = array(
		'variation'   => (int) $data['variation'],
		'post_id'     => (int) $data['post_id'],
		'no_click'    => (int) $old_data['no_click'],
		'test_id'     => (int) $data['test_id'],
		'impressions' => $data['log_type'] == THO_LOG_IMPRESSION ? array_merge( $old_data['impressions'], array( (int) $data['engagement_type'] ) ) : $old_data['impressions'],
		'engagements' => $data['log_type'] == THO_LOG_ENGAGEMENT ? array_merge( $old_data['engagements'], array( (int) $data['engagement_type'] ) ) : $old_data['engagements'],
	);

	setcookie( $cookie_key, wp_json_encode( $cookie_data ), time() + ( 364 * 24 * 3600 ), '/' );
}

/**
 * Check if a test has the data to indicate an automate winner.
 * If we can decide a winner, we update the test as completed, set the winner variation and change de post title.
 *
 * @param $test_id
 */
function tho_check_test_auto_win( $test_id ) {

	if ( empty( $test_id ) ) {
		return;
	}

	$test = tho_get_running_test( array(
		'test_id' => $test_id,
		'status'  => THO_TEST_STATUS_ACTIVE,
	) );

	if ( empty( $test ) ) {
		return;
	}

	if ( empty( $test->enable_automatic_winner ) || $test->status != THO_TEST_STATUS_ACTIVE ) {
		return;
	}

	if ( ! empty( $test->minimum_duration ) ) {
		/* check if this amount of time has passed -> if not, no need for further processing */
		if ( time() < strtotime( $test->date_started . ' +' . $test->minimum_duration . 'days' ) ) {
			return;
		} /* The time interval has passed, we can check the other conditions */
	}

	if ( $test->engagements < $test->minimum_engagements ) {
		return;
	}

	/* @var Tho_Db */
	global $thodb;
	$test_items = tho_get_test_items( $test->id, false, true );

	/* find the control variation for later use */
	foreach ( $test_items as $item ) {
		if ( ! empty( $item->is_control ) ) {
			$control = $item;
			break;
		}
	}

	if ( empty( $control ) ) {
		return;
	}

	$control_engagement_rate = floatval( tho_engagement_rate( $control->views, $control->engagements, '' ) );
	/* check the number of conversions of each item, and the chance to beat original */
	$minimum_engagements      = intval( $test->minimum_engagements );
	$variation_win_array      = array();
	$variations_beat_original = 100.0 - (float) $test->chance_to_beat_original;

	foreach ( $test_items as $variation ) {
//		if ( $minimum_engagements > $variation->engagements ) {
//			continue;
//		}

		if ( $variation->is_control ) {
			$control_win = true;
			foreach ( $test_items as $var ) {
				if ( $var->is_control ) {
					continue;
				}

				$engagement_rate = tho_engagement_rate( $var->views, $var->engagements );
				$beat_original   = floatval( tho_test_item_beat_original( $engagement_rate, $var->views, $control_engagement_rate, $control->views, '' ) );
				if ( $variations_beat_original < $beat_original || empty( $beat_original ) ) {
					$control_win = false;
				}
			}

			if ( $control_win ) {
				tho_set_test_winner( $test, $variation );
				break;
			}

		} else {
			$engagement_rate = tho_engagement_rate( $variation->views, $variation->engagements );

			$beat_original = tho_test_item_beat_original( $engagement_rate, $variation->views, $control_engagement_rate, $control->views, '' );

			if ( (float) $beat_original > (float) $test->chance_to_beat_original ) {

				$obj                   = new stdClass();
				$obj->beat_original    = (float) $beat_original;
				$obj->variation        = $variation;
				$variation_win_array[] = $obj;
				//tho_set_test_winner( $test, $variation );
				//break;
			}
		}
	}


	if ( ! empty( $variation_win_array ) ) {
		$winner_variation     = $variation_win_array[0]->variation;
		$winner_beat_original = $variation_win_array[0]->beat_original;
		foreach ( $variation_win_array as $var_win_arr ) {
			if ( $winner_beat_original <= $var_win_arr->beat_original ) {
				$winner_beat_original = $var_win_arr->beat_original;
				$winner_variation     = $var_win_arr->variation;
			}
		}
		/*Set the winner to the highest beat original*/
		tho_set_test_winner( $test, $winner_variation );
	}
}

/**
 * Function that stops underperforming variations of a test
 *
 * @param $test_id
 */
function tho_stop_underperforming_variations( $test_id ) {
	if ( empty( $test_id ) ) {
		return;
	}

	$test = tho_get_running_test( array(
		'test_id' => $test_id,
		'status'  => THO_TEST_STATUS_ACTIVE,
	) );

	if ( empty( $test ) ) {
		return;
	}

	if ( empty( $test->enable_automatic_winner ) || $test->status != THO_TEST_STATUS_ACTIVE ) {
		return;
	}

	if ( ! empty( $test->minimum_duration ) ) {
		/* check if this amount of time has passed -> if not, no need for further processing */
		if ( time() < strtotime( $test->date_started . ' +' . $test->minimum_duration . 'days' ) ) {
			return;
		} /* The time interval has passed, we can check the other conditions */
	}

	if ( $test->engagements < $test->minimum_engagements ) {
		return;
	}

	/* @var Tho_Db */
	global $thodb;
	$test_items = tho_get_test_items( $test->id, false, true );

	foreach ( $test_items as $item ) {
		if ( ! empty( $item->is_control ) ) {
			$control = $item;
			break;
		}
	}

	/*Check if the control has engagements*/
	if ( empty( $control ) ) {
		return;
	}

	if ( empty( $control->engagements ) ) {
		return;
	}

	$control_engagement_rate  = floatval( tho_engagement_rate( $control->views, $control->engagements, '' ) );
	$variations_beat_original = 100.0 - (float) $test->chance_to_beat_original;

	foreach ( $test_items as $variation ) {
		if ( $variation->is_control ) {
			continue;
		}
		$engagement_rate = tho_engagement_rate( $variation->views, $variation->engagements );
		$beat_original   = tho_test_item_beat_original( $engagement_rate, $variation->views, $control_engagement_rate, $control->views, '' );

		if ( (float) $beat_original < $variations_beat_original ) {
			/* We stop the variation */
			$variation->active       = 0;
			$variation->stopped_date = date( 'Y-m-d H:i:s' );
			$thodb->save_test_item( $variation );
		}
	}
}

/**
 * A winner has been decided so we update the test as completed, set the winner variation and change de post title.
 *
 * @param $test object
 * @param $variation object
 */
function tho_set_test_winner( $test, $variation ) {

	/* @var Tho_Db */
	global $thodb;

	/* Update test status */
	$test->status         = THO_TEST_STATUS_COMPLETED;
	$test->date_completed = date( 'Y-m-d H:i:s' );
	$thodb->save_test( $test );

	/* Update test winner variation */
	$variation->is_winner = 1;
	$thodb->save_test_item( $variation );

	/* Update post title */
	$post = array(
		'ID'         => (int) $test->post_id,
		'post_title' => $variation->variation_title,
	);
	wp_update_post( $post );

	/*Hook Headline Optimizer Test Ends*/
	$test->trigger_source = 'tho';
	$test->url            = admin_url( 'admin.php?page=tho_admin_dashboard#test/' . $test->id );
	$variation->variation = array( 'post_title' => $variation->variation_title, 'key' => $variation->id );
	do_action( THO_ACTION_SET_TEST_ITEM_WINNER, $variation, $test );
}

/**
 * Wrap the breadcrumb items in our tag so we can find them and replace the title
 *
 * @param $defaults
 *
 * @return mixed
 */
function tho_change_woo_breadcrumb( $defaults ) {

	$defaults['before'] .= '<' . THO_WOO_TAG . '>';
	$defaults['after'] .= '</' . THO_WOO_TAG . '>';

	return $defaults;
}

/**
 * Use our breadcrumb template
 *
 * @param $template
 * @param $template_name
 * @param $template_path
 *
 * @return string
 */
function tho_woocommerce_locate_template( $template, $template_name, $template_path ) {

	global $woocommerce;

	$_template = $template;

	if ( ! $template_path ) {
		$template_path = $woocommerce->template_url;
	}

	$plugin_path = THO_PATH . '/frontend/woocommerce/';

	// Look within passed path within the theme - this is priority
	$template = locate_template( array( $template_path . $template_name, $template_name ) );

	// Modification: Get the template from this plugin, if it exists
	if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
		$template = $plugin_path . $template_name;
	}


	// Use default template
	if ( ! $template ) {
		$template = $_template;
	}

	return $template;

}

/**
 * Filter products title before displaying them in the cart
 *
 * @param $fragments
 *
 * @return mixed
 */
function tho_filter_product_title( $fragments ) {

	global $woocommerce;

	if ( empty( $woocommerce ) || empty( $woocommerce->cart ) || empty( $woocommerce->cart->cart_contents ) ) {
		return $fragments;
	}

	foreach ( $woocommerce->cart->cart_contents as $cart_item ) {
		$title = get_the_title( $cart_item['data']->post->ID );
		if ( ! empty( $title ) ) {
			$cart_item['data']->post->post_title = $title;
		}
	}

	return $fragments;
}

/**
 * Initialize the Update Checker
 */
function tho_update_checker() {
	new TVE_PluginUpdateChecker(
		THO_UPDATE_URL,
		THO_PLUGIN_FILE_PATH,
		'thrive-headline-optimizer',
		12,
		'',
		'thrive_headline_optimizer'
	);
}

/**
 * Gets the inconclusive tests
 */
function tho_get_inconclusive_tests() {
	$running_tests = tho_get_running_inconclusive_tests();

	if ( is_array( $running_tests ) && ! empty( $running_tests ) ) {
		tho_enqueue_script( 'tho-inconclusive-tests-js', THO_URL . 'admin/js/inconclusive_tests.js' );


		$thrive_head_special_routes = array(
			'nonce'  => wp_create_nonce( 'wp_rest' ),
			'routes' => array(
				'tests' => tho_get_route_url( 'tests' ),
			),
		);
		wp_localize_script( 'tho-inconclusive-tests-js', 'ThriveHeadInconclusive', $thrive_head_special_routes );

		add_action( 'admin_notices', 'tho_inconclusive_tests_notice' );
	}
}

/**
 * Hook into TD Notification Manager and push trigger types
 *
 * @param $trigger_types
 *
 * @return array
 */
function tho_filter_nm_trigger_types( $trigger_types ) {

	if ( ! in_array( 'split_test_ends', array_keys( $trigger_types ) ) ) {
		$trigger_types['split_test_ends'] = __( 'A/B Test Ends', THO_TRANSLATE_DOMAIN );
	}

	return $trigger_types;
}
