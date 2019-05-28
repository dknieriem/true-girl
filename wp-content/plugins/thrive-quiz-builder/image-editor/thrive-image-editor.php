<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}


final class Thrive_Image_Editor {

	const VERSION = '1.0.0';

	const EDITOR_FLAG = 'tie';

	/**
	 * Translation Domain
	 */
	const T = 'thrive_image_editor';
	/**
	 * @var $this
	 */
	private static $_instance;

	/**
	 * @var TIE_Query
	 */
	private $query;

	/**
	 * @var TIE_Editor
	 */
	public $editor;

	/**
	 * @var TIE_Template_Manager
	 */
	private $template_manager;

	/**
	 * Thrive_Image_Editor constructor.
	 */
	private function __construct() {
		$this->_includes();
		$this->init();
	}

	/**
	 * @return Thrive_Image_Editor
	 */
	public static function instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	private function _includes() {
		require_once( 'includes/classes/class-tie-post-types.php' );
		require_once( 'includes/tie-data-functions.php' );
		require_once( 'includes/tie-global-functions.php' );
		require_once( 'includes/classes/class-tie-ajax.php' );
		require_once( 'includes/classes/class-tie-ajax-controller.php' );
		require_once( 'includes/classes/class-tie-query.php' );
		require_once( 'includes/classes/class-tie-image.php' );
		require_once( 'includes/classes/class-tie-image-settings.php' );
		require_once( 'includes/classes/class-tie-layout.php' );
		require_once( 'includes/classes/class-tie-template-manager.php' );

		if ( $this->is_request( 'admin' ) ) {
			require_once( 'includes/classes/class-tie-admin-assets.php' );
		}

		$this->query            = new TIE_Query();
		$this->template_manager = new TIE_Template_Manager();
	}

	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	private function init() {
		add_action( 'template_redirect', array( $this, 'init_editor' ) );
	}

	public function init_editor() {

		if ( true !== (bool) $this->query->get_var( self::EDITOR_FLAG ) ) {
			return;
		}

		require_once( 'includes/classes/class-tie-editor.php' );
		$this->editor = TIE_Editor::instance();
	}

	/**
	 * Set a property with custom images built for
	 *
	 * @param WP_Post $post
	 */
	public function set_images( $post ) {

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$post->thrive_images = tie_get_images( $post );
	}

	public function delete_images( $post ) {

		if ( ! is_int( $post ) && ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		$images = tie_get_images( $post );

		foreach ( $images as $image ) {
			tie_delete_image( $image );
		}
	}

	public function url( $file = '' ) {
		return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
	}

	public function path( $file = '' ) {
		return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
	}

	public function template_manager() {
		return $this->template_manager;
	}
}

function tie() {
	return Thrive_Image_Editor::instance();
}

tie();
