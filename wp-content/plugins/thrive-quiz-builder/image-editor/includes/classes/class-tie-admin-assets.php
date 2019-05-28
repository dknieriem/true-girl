<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Admin_Assets {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	public function admin_scripts() {

		wp_enqueue_script( 'tiny_mce' );

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, apply_filters( 'tie_load_admin_scripts', array() ) ) ) {
			return;
		}

		$localize = array(
			'admin_ajax_controller' => 'tie_admin_ajax_controller',
			'nonce'                 => wp_create_nonce( TIE_Ajax::AJAX_NONCE_NAME ),
		);

		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		tie_enqueue_script( 'tie-admin', tie()->url( 'assets/js/dist/tie-admin' . $js_suffix ) );
		wp_localize_script( 'tie-admin', 'TIE', $localize );
	}
}

return new TIE_Admin_Assets();
