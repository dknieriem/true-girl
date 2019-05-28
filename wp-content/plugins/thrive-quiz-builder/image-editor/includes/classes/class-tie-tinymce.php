<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TIE_TinyMCE
 *
 * This class should add TinyMCE in page at `wp_print_footer_scripts` with no other HTML output
 *
 * `tinyMCEPreInit` is outputted by WP
 * `tinymce.addI18n()` is outputted by WP
 * wp initializes a tinymce editor on html element with id="$editor_id"
 */
class TIE_TinyMCE {

	protected static $editor_id = null;

	public static function init( $editor_id ) {

		self::$editor_id = $editor_id;

		add_filter( 'tiny_mce_plugins', array( __CLASS__, 'plugins' ) );
		add_filter( 'the_editor', array( __CLASS__, 'editor_html' ) );

		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'tiny_mce_before_settings' ), 10, 2 );

		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'add_editor_to_page' ) );

		add_filter( 'user_can_richedit', array( __CLASS__, 'richedit' ) );
	}

	public static function richedit() {
		return true;
	}

	/**
	 * @param $settings
	 * @param $editor_id
	 *
	 * @return mixed
	 */
	public static function tiny_mce_before_settings( $settings, $editor_id ) {
		$settings['wp_skip_init'] = true;

		return $settings;
	}

	public static function add_editor_to_page() {

		if ( empty( self::$editor_id ) ) {
			return;
		}

		wp_editor( '', self::$editor_id, array(
			'quicktags'     => false,
			'media_buttons' => false,
		) );
	}

	public static function editor_html() {
		return '';
	}

	public static function plugins() {
		return array(
			'charmap',
			'colorpicker',
			'hr',
			'lists',
			'media',
			'paste',
			'tabfocus',
			'textcolor',
			'fullscreen',
			'wordpress',
			'wpautoresize',
			'wpeditimage',
			'wpemoji',
			'wpgallery',
			//'wplink',
			'wpdialogs',
			'wptextpattern',
			'wpview',
			'wpembed',
		);
	}
}
