<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( class_exists( 'TIE_Editor' ) ) {
	return;
}

class TIE_Editor {

	/**
	 * @var TIE_Editor
	 */
	private static $_instance = null;

	/**
	 * @var WP_Post
	 */
	private $_post = null;

	/**
	 * @var bool
	 */
	private $_can_edit_post = null;

	/**
	 * @var TIE_Image
	 */
	private $_image = null;

	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	final private function __construct() {
		if ( $this->_can_edit_post() ) {
			$this->_clear_scripts();
			$this->_init();
		}
	}

	private function _can_edit_post() {

		if ( isset( $this->_can_edit_post ) ) {
			return $this->_can_edit_post;
		}

		$this->_can_edit_post = false;
		$this->_can_edit_post = current_user_can( 'manage_options' );
		$post                 = $this->_can_edit_post ? get_post() : null;
		$this->_can_edit_post = $this->_can_edit_post && (bool) $post;
		$this->_can_edit_post = $this->_can_edit_post && $post->post_type === TIE_Post_Types::THRIVE_IMAGE;

		$this->_can_edit_post ? $this->_post = $post : null;

		return $this->_can_edit_post;
	}

	private function _clear_scripts() {

		//global $wp_filter;
		//print_r( $wp_filter['wp_footer'] );

		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_footer' );

		remove_all_actions( 'wp_enqueue_scripts' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_footer_scripts' );
		remove_all_actions( 'print_footer_scripts' );
		remove_all_actions( 'admin_bar_menu' );

		remove_all_filters( 'single_template' );
		remove_all_filters( 'template_include' );

		add_action( 'wp_head', 'wp_enqueue_scripts' );
		add_action( 'wp_head', 'wp_print_styles' );
		add_action( 'wp_head', 'wp_print_head_scripts' );

		add_action( 'wp_footer', '_wp_footer_scripts' );
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
		add_action( 'wp_footer', 'print_footer_scripts', 1000 );

		_wp_admin_bar_init();
	}

	private function _init() {

		/**
		 * Scripts
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

		/**
		 * Styles
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ), PHP_INT_MAX );

		/**
		 * Layout
		 */
		add_filter( 'single_template', array( $this, 'layout' ) );

		if ( ! class_exists( 'TIE_TinyMCE' ) ) {
			require_once( 'class-tie-tinymce.php' );
			TIE_TinyMCE::init( 'image_editor' );
		}

		$this->_image = new TIE_Image( $this->_post );
	}

	public function add_scripts() {
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		wp_enqueue_script( 'spectrum-script', tie()->url( 'assets/js/spectrum/spectrum.js' ), array( 'jquery' ), false, true );

		tie_enqueue_script( 'tie-editor-script', tie()->url( 'assets/js/dist/tie-editor' . $js_suffix ), array(
			'jquery',
			'underscore',
			'backbone',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-resizable',
		), false, true );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-resizable' );

		wp_enqueue_script( 'editor' );

		/** some themes have hooks defined here, which rely on functions defined only in the admin part - these will not be defined on frontend */
		remove_all_filters( 'media_view_settings' );
		wp_enqueue_media();

		tie_enqueue_script( 'tie-html2canvas', tie()->url( 'assets/js/html2canvas/html2canvas.js' ), array( 'tie-editor-script' ), false, true );

		$data = array(
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'quiz_dash_url'  => admin_url( 'admin.php?page=tqb_admin_dashboard#dashboard/quiz/' . $this->_post->post_parent ),
			'assets_url'     => tie()->url( 'assets' ),
			'post_id'        => $this->_post->ID,
			'image_settings' => $this->_image->get_settings()->get_data(),
			'templates'      => tie()->template_manager()->get_templates(),
			'fonts'          => $this->get_fonts(),
			'default_size'   => array(
				'width'  => TIE_Image_Settings::WIDTH,
				'height' => TIE_Image_Settings::HEIGHT,
			),
			't'              => array(
				'copy'                        => __( 'Copy', Thrive_Image_Editor::T ),
				'saving'                      => __( 'Saving...', Thrive_Image_Editor::T ),
				'changes_auto_saved'          => __( 'Your changes are auto-saved', Thrive_Image_Editor::T ),
				'replace_image'               => __( 'Replace Image', Thrive_Image_Editor::T ),
				'select_bg_image'             => __( 'Select Background Image', Thrive_Image_Editor::T ),
				'media'                       => array(
					'title' => __( 'Choose background image', Thrive_Image_Editor::T ),
				),
				'image_required_for_position' => __( 'Please choose an image.', Thrive_Image_Editor::T ),
				'no_image_to_remove'          => __( 'No image to remove, you have to to choose one before.', Thrive_Image_Editor::T ),
				'enter_your_text_here'        => __( 'Enter your text here...', Thrive_Image_Editor::T ),
				'changes_saved'               => __( 'Changes saved', Thrive_Image_Editor::T ),
				'invalid_width'               => __( 'Invalid width, accepted value is between 100 and 1200', Thrive_Image_Editor::T ),
				'invalid_height'              => __( 'Invalid height, accepted value is between 100 and 1200', Thrive_Image_Editor::T ),
				'select_template'             => __( 'Please select a template', Thrive_Image_Editor::T ),
			),
		);
		wp_localize_script( 'tie-editor-script', 'TIE_Editor', $data );
	}

	public function add_styles() {
		tie_enqueue_style( 'tie-editor-style', tie()->url( 'assets/css/tie-editor.css' ) );
		tie_enqueue_style( 'spectrum-style', tie()->url( 'assets/js/spectrum/spectrum.css' ) );
	}

	public function layout() {
		return TIE_Layout::init()->load_editor();
	}

	/**
	 * Get fonts from google and cache them using transient
	 *
	 * @return array|mixed with google fonts
	 */
	public function get_fonts() {

		if ( false !== $fonts = get_transient( 'tie_google_fonts' ) ) {
			//return $fonts;
		}

		$link     = 'https://www.googleapis.com/webfonts/v1/webfonts?key=';
		$key      = 'AIzaSyDJhU1bXm2YTz_c4VpWZrAyspOS37Nn-kI'; //original one
		$key      = 'AIzaSyBuT5A6rcDcbFcvLkTK0ZV8bfeXF1Gd6kE'; // dan's one
		$response = wp_remote_get( $link . $key, array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) || empty( $body['items'] ) ) {
			return array();
		}

		$fonts = $body['items'];
		set_transient( 'tie_google_fonts', $fonts, 12 * HOUR_IN_SECONDS );

		return $fonts;
	}
}
