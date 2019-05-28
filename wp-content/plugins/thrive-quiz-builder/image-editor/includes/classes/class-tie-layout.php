<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Layout {
	static private $instance;

	private function __construct() {
		$this->hooks();
	}

	private function hooks() {
	}

	public function canvas_content( $post ) {
		if ( ! is_numeric( $post ) && ! ( $post instanceof WP_Post ) ) {
			return;
		}

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		$image   = new TIE_Image( $post );
		$content = $image->get_content();
		sprintf( '%s', $content );
	}

	public static function init() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function load_editor() {
		return dirname( dirname( __FILE__ ) ) . '/layouts/editor.php';
	}
}

TIE_Layout::init();
