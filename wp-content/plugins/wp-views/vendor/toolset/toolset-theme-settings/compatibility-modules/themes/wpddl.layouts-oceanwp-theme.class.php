<?php

/**
 * Compatibility class for Ocean WP theme
 * Class Toolset_Compatibility_Theme_oceanwp
 * @since layouts 2.0.2
 */
class Toolset_Compatibility_Theme_oceanwp extends Toolset_Compatibility_Theme_Handler {

	protected function run_hooks() {

		add_action('init', array($this, 'load_scripts_and_styles'), 12);

		add_action( 'get_header', array( $this, 'disable_title' ) );
		add_action( 'get_header', array( $this, 'disable_featured_image' ) );
		add_action( 'get_header', array( $this, 'disable_meta' ) );
		add_action( 'get_header', array( $this, 'disable_archive_title' ) );
		add_action( 'get_header', array( $this, 'disable_pagination' ) );
		add_action( 'get_header', array( $this, 'disable_sidebar' ) );

		add_action( 'wp_get_attachment_image_src', array( $this, 'fix_attachment_page_layout' ), 100, 4 );
		add_filter('ddl_render_cell_content', array($this,'fix_attachment_output'), 10, 3 );
	}

	public function load_scripts_and_styles(){
		add_filter( 'toolset_add_registered_styles', array( $this, 'add_register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );
	}

	public function fix_attachment_page_layout( $image, $attachment_id, $size, $icon ) {
		if ( is_attachment() && ! is_admin() ) {
			return '';
		}
		return $image;
	}

	function fix_attachment_output( $content, $cell, $renderer ) {

		if ( is_attachment() ) {
			global $post;

			remove_filter('wp_get_attachment_image_src', array( $this, 'fix_attachment_page_layout' ), 100);
			$attach = wp_get_attachment_url($post->ID );
			// Do not render attachment post type posts' bodies automatically
			if ( WPDD_Utils::is_wp_post_object( $post ) && $post->post_type === 'attachment' ) {
				if ( $cell->get_cell_type() === "cell-post-content" && ! empty( $attach ) && is_array( getimagesize( $attach ) ) ) {
					return '<a href="'.$attach.'"><img src="'.$attach.'"></a>' . $content;
				}
			}
		}

		return $content;
	}

	private function get_toolset_theme_option( $option_name ) {
		$get_toolset_theme_option = apply_filters( 'toolset_theme_integration_get_setting', null, $option_name );
		return ( $get_toolset_theme_option == "1" ) ? true : false;
	}

    public function add_register_styles( $styles ) {

        $styles['oceanwp-overrides-css'] = new Toolset_Style( 'oceanwp-overrides-css', TOOLSET_THEME_SETTINGS_URL . '/res/css/themes/oceanwp-overrides.css', array(), TOOLSET_THEME_SETTINGS_VERSION, 'screen' );

        return $styles;
    }

    public function frontend_enqueue() {
        do_action( 'toolset_enqueue_styles', array( 'oceanwp-overrides-css' ) );
    }
	
	/**
	 * Get value from theme integration settings filter and disable title for current page if option is enabled
	 */

	public function disable_title() {

		if ( 
			$this->get_toolset_theme_option( 'toolset_disable_title' ) 
			&& ( is_single() || is_page() ) 
		) {
			add_filter( 'ocean_blog_single_elements_positioning', array( $this, 'remove_title_from_single' ) );
			return true;
		}
		return false;
	}



	public function remove_title_from_single( $sections ) {
		$sections = array_diff( $sections, array( 'title' ) );
		return $sections;
	}


	/**
	 * Get value from theme integration settings filter and disable featured image for current page if option is enabled
	 */
	public function disable_featured_image() {

		if ( $this->get_toolset_theme_option('toolset_disable_featured_image') ) {
			add_filter( 'ocean_blog_single_elements_positioning', array( $this, 'remove_featured_image_from_single' ) );
			return true;
		}
		return false;
	}
	
	public function remove_featured_image_from_single( $sections ) {
		$sections = array_diff( $sections, array( 'featured_image' ) );
		return $sections;
	}
	
	public function disable_meta() {
		if ( $this->get_toolset_theme_option('toolset_disable_meta') ) {
			add_filter( 'ocean_blog_single_elements_positioning', array( $this, 'remove_meta_from_single' ) );
			return true;
		}
		return false;
	}
	
	public function remove_meta_from_single( $sections ) {
		$sections = array_diff( $sections, array( 'meta' ) );
		return $sections;
	}

	/**
	 * Get value from theme integration settings filter and disable featured image for current page if option is enabled
	 */
	public function disable_pagination() {

		if ( $this->get_toolset_theme_option( 'toolset_disable_pagination' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'remove_pagination' ) );
			return true;
		}
		return false;
	}

	/**
	 * ToDo: replace this with filter later, right now it is not possible because of the theme
	 */
	public function remove_pagination(){
		$custom_css = ".oceanwp-pagination{display:none;}";
		wp_add_inline_style( 'oceanwp-overrides-css', $custom_css );
	}

	function is_custom_post_type( $post = null ) {
		$all_custom_post_types = get_post_types( array( '_builtin' => false ) );

		// there are no custom post types
		if ( empty ( $all_custom_post_types ) ) {
			return false;
		}

		$custom_types      = array_keys( $all_custom_post_types );
		$current_post_type = get_post_type( $post );

		// could not detect current type
		if ( ! $current_post_type ) {
			return false;
		}

		return in_array( $current_post_type, $custom_types );
	}

	/**
	 * Get value from theme integration settings filter and disable title for current page if option is enabled
	 */
	public function disable_archive_title(){

		if ( $this->get_toolset_theme_option( 'toolset_disable_archive_title' ) && is_archive() ) {
			add_filter( 'ocean_title', array( $this, 'remove_archive_title' ), 10 );
			add_filter( 'ocean_post_subheading', array( $this, 'remove_archive_title' ), 10 );
			add_action( 'wp_head', array( $this, 'remove_title_tag' ) );
			return true;
		}
		return false;

	}
	public function remove_archive_title(){
		return '';
	}
	public function remove_title_tag(){
		echo '<script type="text/javascript">jQuery(function() { jQuery(".page-header-inner .page-header-title").remove(); });</script>';
	}



	/**
	 * Get value from theme integration settings filter and disable sidebar for current page if option is enabled
	 */
	public function disable_sidebar(){

		if ( $this->get_toolset_theme_option('toolset_disable_sidebar') ) {
			add_filter( 'ocean_post_layout_class', array( $this, 'remove_sidebar' ) );
			if( defined('WC_VIEWS_VERSION' ) ) {
				remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
			}

			return true;
		}
		return false;
	}

	public function remove_sidebar(){
		return 'full-width';
	}


}