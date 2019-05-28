<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TQB_Ajax {

	const AJAX_NONCE_NAME = 'tie_ajax_controller';

	public static function init() {
		self::add_ajax_events();
	}

	public static function add_ajax_events() {

		$ajax_events = array(
			'tqb_admin_ajax_controller'    => false,
			'tqb_frontend_ajax_controller' => true,
		);

		foreach ( $ajax_events as $action => $nopriv ) {
			add_action( 'wp_ajax_' . $action, array( __CLASS__, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $action, array( __CLASS__, $action ) );
			}
		}
	}

	public static function tqb_admin_ajax_controller() {
		require_once dirname( __FILE__ ) . '/admin/classes/class-tqb-admin-ajax-controller.php';

		$response = TQB_Admin_Ajax_Controller::instance()->handle();

		wp_send_json( $response );
	}

	public static function tqb_frontend_ajax_controller() {
		require_once dirname( __FILE__ ) . '/class-tqb-frontend-ajax-controller.php';

		$response = TQB_Frontend_Ajax_Controller::instance()->handle();

		wp_send_json( $response );
	}
}

TQB_Ajax::init();
