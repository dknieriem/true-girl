<?php

/**
 * Class MC4WP_Styles_Builder_Public
 *
 * @ignore
 */
class MC4WP_Styles_Builder_Public {

	/**
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// capture form preview requests
		add_action( 'wp', array( $this, 'maybe_load_preview' ), 99 );
		add_action( 'mc4wp_load_form_stylesheets', array( $this, 'load_stylesheets' ) );
	}

	/**
	 * Load Styles Builder stylesheets
	 *
	 * @param array $stylesheets
	 */
	public function load_stylesheets( $stylesheets ) {

		// only load bundle when stylesheets has `styles-builder` in it.
		if( ! in_array( 'styles-builder', $stylesheets ) ) {
			return;
		}

		// get stylesheet file
		$uploads = wp_upload_dir( null, false );
		$bundle_filename = '/mc4wp-stylesheets/bundle.css';
		$version = get_option( 'mc4wp_forms_styles_builder_version', 1 );

		// use protocol relative URL's
		$base_url = str_ireplace( array( 'http://', 'https://' ), '//', $uploads['baseurl'] );

		// check if bundle file exists, file system check is cheap, 404 in WordPress is not.
		if( file_exists( $uploads['basedir'] . $bundle_filename ) ) {

			// generate url of stylesheet
			$url = $base_url . $bundle_filename;
			wp_enqueue_style( 'mc4wp-form-styles-builder', $url, array(), $version );
			add_editor_style( $url );
		}

		// if this a preview, load single stylesheet (because styles may not be in bundle yet)
		if( defined( 'MC4WP_FORM_IS_PREVIEW' ) && MC4WP_FORM_IS_PREVIEW ) {
			$single_filename = '/mc4wp-stylesheets/form-' . intval( $_GET['form_id'] ) .'.css';
			$url = $base_url . $single_filename;
			wp_enqueue_style( 'mc4wp-form-styles-builder', $url, array(), $version );
		}

	}

	/**
	 * Maybe load form preview for Styles Builder
	 */
	public function maybe_load_preview() {

		// make sure form_id is set and current user has required capabilities
		if( ! isset( $_GET['_mc4wp_styles_builder_preview'] ) || empty( $_GET['form_id'] ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// disable all other stylesheets
		add_filter( 'mc4wp_form_stylesheets', '__return_empty_array' );


		require dirname( $this->plugin_file ) . '/views/form-preview.php';
		exit;
	}

}
