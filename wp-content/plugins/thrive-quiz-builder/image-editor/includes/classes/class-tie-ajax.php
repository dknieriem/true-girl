<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Ajax {

	const AJAX_NONCE_NAME = 'tie_ajax_controller';

	public static function init() {
		self::add_ajax_events();
	}

	public static function add_ajax_events() {

		$ajax_events = array(
			'tie_admin_ajax_controller' => false,
			'tie_save_image_content'    => true,
		);

		foreach ( $ajax_events as $action => $nopriv ) {
			add_action( 'wp_ajax_' . $action, array( __CLASS__, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $action, array( __CLASS__, $action ) );
			}
		}
	}

	public static function tie_admin_ajax_controller() {
		$response = TIE_Ajax_Controller::instance()->handle();
		wp_send_json( $response );
	}

	public static function tie_save_image_content() {

		$image = new TIE_Image( $_REQUEST['post_id'] );

		if ( isset( $_REQUEST['html_canvas'] ) ) {
			$image->save_html_canvas( $_REQUEST['html_canvas'] );
		}

		if ( isset( $_REQUEST['content'] ) && $image->save_content( $_REQUEST['content'] ) ) {

			if ( ! empty( $_REQUEST['image'] ) ) {
				$image->save_file( $_REQUEST['image'] );
			}


			if ( isset( $_REQUEST['image_settings'] ) ) {
				$image->get_settings()->save( $_REQUEST['image_settings'] );
			}

			wp_send_json_success( array(
				'message' => __( 'All changes saved!', Thrive_Image_Editor::T ),
			) );
		}

		wp_send_json_error( array(
			'message' => __( 'Changes could not be saved!', Thrive_Image_Editor::T ),
		) );
	}
}

TIE_Ajax::init();
