<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TQB_Shortcodes {

	public static function init() {
		add_shortcode( 'tqb_quiz', array( 'TQB_Shortcodes', 'render_quiz_shortcode' ) );
	}

	public static function render_quiz_shortcode( $attributes ) {

		add_action( 'wp_print_footer_scripts', array( 'TQB_Shortcodes', 'render_backbone_templates' ) );

		$quiz_id   = $attributes['id'];
		$unique_id = 'tqb-' . uniqid();

		tqb_enqueue_script( 'tqb-frontend', tqb()->plugin_url( 'assets/js/dist/tqb-frontend.min.js' ), array(
			'backbone',
		) );

		// Enqueue html2canvas script
		wp_enqueue_script( 'tqb-html2canvas', tie()->url( 'assets/js/html2canvas/html2canvas.js' ) );

		// For social sharing badge
		if ( ! wp_script_is( 'tve_js_sdk_fb' ) ) {
			wp_enqueue_script( 'tve_js_sdk_fb', tve_social_get_sdk_link( 'fb' ), array(), false );
		}

		tqb_enqueue_default_scripts();
		tve_enqueue_icon_pack(); // Include Thrive Icon pack

		wp_localize_script( 'tqb-frontend', 'TQB_Front', array(
			'nonce'      => wp_create_nonce( 'tqb_frontend_ajax_request' ),
			'ajax_url'   => admin_url( 'admin-ajax.php' ) . '?action=tqb_frontend_ajax_controller',
			'is_preview' => isset( $_REQUEST['tve'] ) && $_REQUEST['tve'] || isset( $_REQUEST['preview'] ) && $_REQUEST['preview'],
			'post_id'    => get_the_ID(),
			'settings'   => tqb_get_option( Thrive_Quiz_Builder::PLUGIN_SETTINGS, tqb_get_default_values( Thrive_Quiz_Builder::PLUGIN_SETTINGS ) ),
		) );
		$style = TQB_Post_meta::get_quiz_style_meta( $quiz_id );
		$html  = '<div class="tve_flt" id="tve_editor">
			<div class="tqb-shortcode-wrapper" id="tqb-shortcode-wrapper-' . $quiz_id . '-' . $unique_id . '" data-quiz-id="' . $quiz_id . '" data-unique="' . $unique_id . '" >
				<div class="tqb-loading-overlay tqb-template-overlay-style-' . $style . '"><div class="tqb-loading-bullets"></div></div><div class="tqb-frontend-error-message"></div>
				<div class="tqb-shortcode-old-content"></div>
				<div class="tqb-shortcode-new-content tqb-template-style-' . $style . '"></div>
			</div></div>';

		TQB_Quiz_Manager::run_shortcodes_on_quiz_content( $quiz_id );

		if ( is_editor_page() ) {
			$html = str_replace( array( 'id="tve_editor"' ), '', $html );
			$html = '<div class="thrive-shortcode-html"><div>' . $html . '</div><style>.tqb-shortcode-wrapper{pointer-events: none;}</style></div>';
		}

		return $html;
	}

	/**
	 * Render backbone templates
	 */
	public static function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( tqb()->plugin_path( 'includes/frontend/views/templates' ), 'templates' );
		tve_dash_output_backbone_templates( $templates );
	}
}

TQB_Shortcodes::init();

