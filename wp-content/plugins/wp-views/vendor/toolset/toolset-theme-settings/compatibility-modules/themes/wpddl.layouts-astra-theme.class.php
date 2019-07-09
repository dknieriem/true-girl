<?php

/**
 * Compatibility class for Ocean WP theme
 * Class Toolset_Compatibility_Theme_oceanwp
 */
class Toolset_Compatibility_Theme_astra extends Toolset_Compatibility_Theme_Handler {

    public function add_register_styles( $styles ) {

        $styles['astra-overrides-css'] = new Toolset_Style( 'astra-overrides-css', TOOLSET_THEME_SETTINGS_URL . '/res/css/themes/astra-overrides.css', array(), TOOLSET_VERSION, 'screen' );

        return $styles;
    }

    public function frontend_enqueue() {
        do_action( 'toolset_enqueue_styles', array( 'astra-overrides-css' ) );
    }

    protected function run_hooks() {
	    add_action('init', array($this, 'load_scripts_and_styles'), 12);
	    add_action( 'get_header', array( $this, 'disable_featured_image' ) );
	    add_action( 'get_header', array( $this, 'disable_title' ) );
	    add_action( 'get_header', array( $this, 'disable_pagination' ) );
	    add_action( 'get_header', array( $this, 'disable_read_more_for_excerpt' ) );
    }

	public function load_scripts_and_styles(){
		add_filter( 'toolset_add_registered_styles', array( &$this, 'add_register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );
	}

    public function disable_pagination(){
	    $toolset_disable_pagination = apply_filters( 'toolset_theme_integration_get_setting', null, 'astra_pagination' );
	    if ( "1" === $toolset_disable_pagination ) {
		    remove_filter( 'astra_pagination', 'astra_number_pagination' );
	    }
    }

	/**
	 * Remove "Read more" link from excerpt when option is enabled
	 */
	public function disable_read_more_for_excerpt(){
		$toolset_disable_read_more = apply_filters( 'toolset_theme_integration_get_setting', null, 'toolset_disable_read_more_for_excerpt' );
		if ( "1" === $toolset_disable_read_more ) {
			add_filter( 'astra_post_link_enabled', '__return_false' );
		}
	}

	/**
	 * @deprecated
	 * Get value from theme integration settings filter and disable featured image for current page if option is enabled
	 * TODO: remove this one
	 */
	public function disable_featured_image() {

		$toolset_disable_featured_image = apply_filters( 'toolset_theme_integration_get_setting', null, 'astra_featured_image_enabled' );

		if ( "1" === $toolset_disable_featured_image ) {
			add_filter( 'astra_featured_image_enabled', '__return_false' );
		}
	}

	/**
	 * @deprecated
	 * Get value from theme integration settings filter and disable title for current page if option is enabled
	 * TODO: remove this one
	 */
	public function disable_title() {
		$toolset_disable_title = apply_filters( 'toolset_theme_integration_get_setting', null, 'astra_the_title_enabled' );

		if ( "1" == $toolset_disable_title ) {
			add_filter( 'astra_the_title_enabled', '__return_false' );
		}
	}
}