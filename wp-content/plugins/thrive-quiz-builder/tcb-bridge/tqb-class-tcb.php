<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB {

	public static function init() {
		if ( ! defined( 'TVE_TCB_CORE_INCLUDED' ) ) {
			require_once dirname( plugin_dir_path( __FILE__ ) ) . '/tcb/plugin-core.php';
		}

		add_action( 'tcb_post_types', array( __CLASS__, 'editable_post_types' ) );
	}

	public static function editable_post_types( $post_types ) {
		$post_types['force_whitelist'] = isset( $post_types['force_whitelist'] ) ? $post_types['force_whitelist'] : array();
		$post_types['force_whitelist'] = array_merge( $post_types['force_whitelist'], array(
			TQB_Post_types::QUIZ_POST_TYPE,
			TQB_Post_types::OPTIN_PAGE_POST_TYPE,
			TQB_Post_types::QNA_PAGE_POST_TYPE,
			TQB_Post_types::RESULTS_PAGE_POST_TYPE,
			TQB_Post_types::SPLASH_PAGE_POST_TYPE,
		) );

		return $post_types;
	}
}

if ( ! function_exists( 'tve_editor_url' ) ) {

	function tve_editor_url() {
		return plugin_dir_url( dirname( __FILE__ ) ) . 'tcb';
	}
}

return TCB::init();
