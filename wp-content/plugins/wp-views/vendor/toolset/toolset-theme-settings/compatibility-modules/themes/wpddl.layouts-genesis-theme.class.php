<?php
/**
 * Compatibility class for Genesis theme
 * Class Toolset_Compatibility_Theme_genesis
 */
class Toolset_Compatibility_Theme_genesis extends Toolset_Compatibility_Theme_Handler {


	public function add_register_styles( $styles ) {

		$styles['genesis-overrides-css'] = new WPDDL_style( 'genesis-overrides-css', WPDDL_RES_RELPATH . '/css/themes/genesis-overrides.css', array(), WPDDL_VERSION, 'screen' );

		return $styles;
	}

	public function frontend_enqueue() {
		do_action( 'toolset_enqueue_styles', array( 'genesis-overrides-css' ) );
	}

	protected function run_hooks() {
		add_action( 'get_header', array( $this, 'disable_title' ) );
		add_action( 'get_header', array( $this, 'disable_meta_before_content' ) );
		add_action( 'get_header', array( $this, 'disable_meta_after_content' ) );
		add_action( 'get_header', array( $this, 'disable_pagination' ) );

		$layouts_active = new Toolset_Theme_Settings_Condition_Plugin_Layouts_Active();
		if( $layouts_active->is_met() ){
			add_filter( 'toolset_add_registered_styles', array( &$this, 'add_register_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );

		}
	}

	/**
	 * Get value from theme integration settings filter and disable title for current page if option is enabled
	 */
	public function disable_title() {
		$toolset_disable_title = apply_filters( 'toolset_theme_integration_get_setting', null, 'toolset_disable_title' );

		if (
			"1" == $toolset_disable_title
			&& ( is_single() || is_page() )
		) {
			remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
			return true;
		}
		return false;
	}

	/**
	 * Get value from theme integration settings filter and disable meta before content for current page if option is enabled
	 */
	public function disable_meta_before_content() {
		$toolset_disable_meta_before_content = apply_filters( 'toolset_theme_integration_get_setting', null, 'toolset_disable_meta_before_content' );

		if (
			"1" == $toolset_disable_meta_before_content
			&& ( is_single() || is_page() )
		) {
			remove_action( 'genesis_before_post_content', 'genesis_post_info' );
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
			return true;
		}
		return false;
	}

	/**
	 * Get value from theme integration settings filter and disable met after content for current page if option is enabled
	 */
	public function disable_meta_after_content() {
		$toolset_disable_meta_after_content = apply_filters( 'toolset_theme_integration_get_setting', null, 'toolset_disable_meta_after_content' );

		if (
			"1" == $toolset_disable_meta_after_content
			&& ( is_single() || is_page() )
		) {
			remove_action( 'genesis_after_post_content', 'genesis_post_meta' );
			remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
			return true;
		}
		return false;
	}

	/**
	 * Get value from theme integration settings filter and disable pagination for current page if option is enabled
	 */
	public function disable_pagination() {
		$toolset_disable_pagination = apply_filters( 'toolset_theme_integration_get_setting', null, 'toolset_disable_pagination' );

		if (
			"1" == $toolset_disable_pagination
			&& ( is_archive() || is_home() || is_search() )
		) {
			remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );
		}
	}
}