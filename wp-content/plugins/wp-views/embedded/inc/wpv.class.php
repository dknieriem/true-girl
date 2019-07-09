<?php

// @todo check whethwe we can move this out of here
require WPV_PATH_EMBEDDED . '/inc/listing/listing.php';


class WP_Views {

	function __construct() {
		
		$this->view_ids								= array();
		$this->current_view							= null;
		$this->CCK_types 							= array();// @deprecated maybe
		$this->widget_view_id						= 0;
		$this->view_depth							= 0;
		$this->view_count							= array();
		$this->set_view_counts						= array();
		$this->view_shortcode_attributes			= array();
		$this->view_used_ids						= array();
		$this->rendering_views_form_in_progress		= false;

		$this->post_query							= null;
		$this->post_query_stack						= array();// @deprecated maybe
		$this->top_current_page						= null;
		$this->current_page							= array();

		$this->taxonomy_data						= array();
		$this->parent_taxonomy						= 0;

		$this->users_data							= array();
		$this->parent_user							= 0;

		$this->variables							= array();

		$this->force_disable_dependant_parametric_search	= false;
		$this->returned_ids_for_parametric_search			= array();
		
		$this->wpv_editor_addon						= null;
		
		add_action( 'after_setup_theme',								array( $this, 'before_init' ), 999 );
		add_filter( 'wpv_filter_wpv_get_editor_addon',					array( $this, 'get_editor_addon' ) );
		
		add_action( 'init',												array( $this, 'init' ) );
		add_action( 'widgets_init',										array( $this, 'widgets_init' ) );
		
		add_action( 'init',												array( $this, 'wpv_register_assets' ) );
		
		// API
		add_filter( 'wpv_filter_wpv_get_current_view',					array( $this, 'wpv_get_current_view' ) );
		add_filter( 'wpv_filter_wpv_get_current_views_tree',			array( $this, 'wpv_get_current_views_tree' ) );
		add_action( 'wpv_action_wpv_set_current_view',					array( $this, 'wpv_set_current_view' ) );
		add_action( 'wpv_action_wpv_reset_current_view',				array( $this, 'wpv_reset_current_view' ) );
		
		// @todo move tis to a proper API file...
		add_filter( 'wpv_filter_wpv_get_object_unique_hash',			array( $this, 'wpv_get_object_unique_hash' ), 10, 2 );
		add_filter( 'wpv_filter_wpv_get_view_unique_hash',				array( $this, 'wpv_get_view_unique_hash' ) );
		
		// @todo move tis to a proper API file...
		add_filter( 'wpv_filter_wpv_get_object_settings',				array( $this, 'wpv_get_view_settings' ), 10, 3 );
		add_filter( 'wpv_filter_wpv_get_view_settings',					array( $this, 'wpv_get_view_settings' ), 10, 3 );
		add_filter( 'wpv_filter_wpv_get_object_layout_settings',		array( $this, 'wpv_get_view_layout_settings' ), 10, 2 );
		add_filter( 'wpv_filter_wpv_get_view_layout_settings',			array( $this, 'wpv_get_view_layout_settings' ), 10, 2 );
		
		add_filter( 'wpv_filter_wpv_get_query_type',					array( $this, 'wpv_get_query_type' ), 10, 2 );
		
		add_filter( 'wpv_filter_wpv_get_view_shortcodes_attributes',	array( $this, 'wpv_get_view_shortcodes_attributes' ) );
		add_action( 'wpv_action_wpv_set_view_shortcodes_attributes',	array( $this, 'set_view_shortcode_attributes' ) );
		add_action( 'wpv_action_wpv_reset_view_shortcodes_attributes',	array( $this, 'reset_view_shortcode_attributes' ) );
		
		add_filter( 'wpv_filter_wpv_get_max_pages',						array( $this, 'wpv_get_max_pages' ) );
		add_filter( 'wpv_filter_wpv_get_current_page_number',			array( $this, 'wpv_get_current_page_number' ) );
		
		add_filter( 'wpv_filter_wpv_get_top_current_post',				array( $this, 'wpv_get_top_current_post' ) );
		add_action( 'wpv_action_wpv_set_top_current_post',				array( $this, 'wpv_set_top_current_post' ) );
			
		/**
		 * Get the top current page for using it on the id="$current_page" shortcode attribute.
		 *
		 * @since 2.3.0
		 */
		add_filter( 'toolset_filter_get_top_current_post',				array( $this, 'wpv_get_top_current_post' ) );
		
		add_filter( 'wpv_filter_wpv_get_current_post',					array( $this, 'wpv_get_current_post' ) );
		add_action( 'wpv_action_wpv_set_current_post',					array( $this, 'wpv_set_current_post' ) );
		
		add_filter( 'wpv_filter_wpv_get_parent_view_taxonomy',			array( $this, 'wpv_get_parent_view_taxonomy' ) );
		add_action( 'wpv_action_wpv_set_parent_view_taxonomy',			array( $this, 'wpv_set_parent_view_taxonomy' ) );
		
		add_filter( 'wpv_filter_wpv_get_parent_view_user',				array( $this, 'wpv_get_parent_view_user' ) );
		add_action( 'wpv_action_wpv_set_parent_view_user',				array( $this, 'wpv_set_parent_view_user' ) );
		
		add_filter( 'wpv_filter_wpv_get_widget_view_id',				array( $this, 'wpv_get_widget_view_id' ) );
		add_action( 'wpv_action_wpv_set_widget_view_id',				array( $this, 'wpv_set_widget_view_id' ) );
		
		add_filter( 'wpv_filter_wpv_get_force_disable_dps',				array( $this, 'wpv_get_force_disable_dps' ) );
		add_action( 'wpv_action_wpv_force_disable_dps',					array( $this, 'wpv_force_disable_dps' ) );
		
		add_filter( 'wpv_filter_wpv_get_postmeta_keys',					array( $this, 'wpv_get_postmeta_keys' ), 10, 2 );
		add_filter( 'wpv_filter_wpv_get_termmeta_keys',					array( $this, 'wpv_get_termmeta_keys' ), 10, 2 );
		add_filter( 'wpv_filter_wpv_get_usermeta_keys',					array( $this, 'wpv_get_usermeta_keys' ), 10, 2 );
		
		add_filter( 'wpv_filter_wpv_get_rendered_views_ids',			array( $this, 'wpv_get_rendered_views_ids' ) );
		
		add_filter( 'wpv_filter_wpv_get_post_query',					array( $this, 'wpv_get_post_query' ) );
		add_filter( 'wpv_filter_wpv_get_taxonomy_query',				array( $this, 'wpv_get_taxonomy_query' ) );
		add_filter( 'wpv_filter_wpv_get_user_query',					array( $this, 'wpv_get_user_query' ) );
		
		add_filter( 'wpv_filter_wpv_get_taxonomy_found_count',			array( $this, 'wpv_get_taxonomy_found_count' ) );
		add_filter( 'wpv_filter_wpv_get_users_found_count',				array( $this, 'wpv_get_users_found_count' ) );
		
		add_filter( 'wpv_filter_wpv_is_rendering_form_view',			array( $this, 'wpv_is_rendering_form_view' ) );
		
		// PUBLIC API
		add_filter( 'wpv_filter_public_wpv_get_view_shortcodes_attributes',		array( $this, 'wpv_get_view_shortcodes_attributes' ) );
        
	}


	function __destruct() { }
	
	// This happens on after_setup_theme:999
	function before_init() {
		
		$toolset_common_bootstrap = Toolset_Common_Bootstrap::getInstance();
		$toolset_common_sections = array( 'toolset_visual_editor', 'toolset_user_editor', 'toolset_blocks' );
		$toolset_common_bootstrap->load_sections( $toolset_common_sections );
		
		$this->wpv_editor_addon	= new WPV_Editor_addon(
			'wpv-views',
			__( 'Insert Views Shortcodes', 'wpv-views' ),
			WPV_URL . '/res/js/views_editor_plugin.js',
			'',
			true,
			'icon-views-logo fa fa-wpv-custom ont-icon-18 ont-color-gray' 
		);
		/**
		 * Exclude filters.
		 * They need to be in place as soon as posible, since they are used by the Fields and Views dialog generation.
		 */
		// Exclude some taxonomies from different pieces of the GUI
		add_filter( 'wpv_admin_exclude_tax_slugs', 'wpv_admin_exclude_tax_slugs' );
		// Exclude some post types from different pieces of the GUI
		add_filter( 'wpv_admin_exclude_post_type_slugs', 'wpv_admin_exclude_post_type_slugs' );
        // Include some post types from different pieces of the GUI
        add_filter( 'wpv_admin_include_post_type_slugs', 'wpv_admin_include_post_type_slugs' );
	}
	
	/**
	 * get_editor_addon
	 *
	 * @usage apply_filters( 'wpv_filter_wpv_get_editor_addon', null );
	 *
	 * @since 2.3.0 Deprecated, but still used in the Loop Wizard :-/
	 */
	
	function get_editor_addon( $editor_addon = null ) {
		return $this->wpv_editor_addon;
	}

	function init() {

		

		$this->wpv_register_type_view();
		
		add_filter( 'toolset_filter_register_menu_pages',				array( $this, 'register_views_pages_in_menu' ), 40 );
		add_filter( 'toolset_filter_register_export_import_section',	array( $this, 'register_export_import_section' ), 20 );
		
		/*
		* ----------------------------
		* Assets
		* ----------------------------
		*/
		add_action( 'admin_enqueue_scripts', array( $this,'wpv_admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wpv_frontend_enqueue_scripts' ) );
		
		/*
		* ----------------------------
		* AJAX calls for date filters
		* @todo this might be better in the filter file
		* ----------------------------
		*/
		add_action('wp_ajax_wpv_format_date', array( $this, 'wpv_format_date' ) );
		add_action('wp_ajax_nopriv_wpv_format_date', array( $this, 'wpv_format_date' ) );
		
		/*
		* ----------------------------
		* Basic fallback values for get_view_settings and get_view_layout_settings
		* ----------------------------
		*/
		add_filter( 'wpv_view_settings', array( $this, 'wpv_view_settings_set_fallbacks' ), 5, 2 );
		add_filter( 'wpv_view_layout_settings', array( $this, 'wpv_view_layout_settings_set_fallbacks' ), 5, 2 );
		
		/*
		*
		* Extra loop wpv-item index management
		*
		*/
		add_filter( 'wpv_filter_wpv_item_loop_selected_index', array( $this, 'wpv_filter_wpv_item_loop_selected_index' ), 10, 2 );
		
		/*
		* ----------------------------
		* Workflows actions
		* ----------------------------
		*/
		
		// List the default spinners available for pagination and parametric search
		add_filter( 'wpv_admin_available_spinners', 'wpv_admin_available_spinners', 5 );
		
		// Delete the current user data after using it on a View listing users loop
		add_action( 'wpv-after-display-user', array( $this, 'clean_current_loop_user' ), 99 );
		
		// Manage the _toolset_edit_last postmeta on Views objects
		add_action( 'wpv_action_wpv_save_item', array( $this, 'after_save_item' ) );
		add_action( 'wpv_action_wpv_import_item', array( $this, 'after_import_item' ) );
		
		// Edit post links
		add_filter( 'edit_post_link', array( $this, 'edit_post_link' ), 10, 2 );
		
		// Set priority lower than 20, so we load the CSS before the footer scripts and avoid the bottleneck
		add_action( 'wp_footer', array( $this, 'wpv_meta_html_extra_css' ), 5 );
		// Set priority higher than 20, when all the footer scripts are loaded
		add_action( 'wp_footer', array( $this, 'wpv_meta_html_extra_js' ), 25 );
		// Set priority higher than 20, when all footer scripts are loaded, but before 25, when custom javascript is added
		add_action( 'wp_footer', array( $this, 'wpv_additional_js_files' ), 21 );
		
		/*
		* ----------------------------
		* Compatibility
		* ----------------------------
		*/
		
		// WooCommerce
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'wpv_woocommerce_product_add_to_cart_url' ), 10, 2 );
		// Gravity Forms
		add_filter( 'gform_form_tag', array( $this, 'wpv_gravityforms_fix_form_action_on_ajax' ), 10, 2 );

		/*
		* ----------------------------
		* Shortcodes
		* ----------------------------
		*/
		add_shortcode( 'wpv-view', array( $this, 'short_tag_wpv_view' ) );
		add_shortcode( 'wpv-form-view', array( $this, 'short_tag_wpv_view_form' ) );
		
		add_filter( 'wpv_filter_wpv_view_shortcode_output', array( $this, 'remove_html_comments_from_shortcode_output' ) );
		
		// Clear the WPV_Settings nstance when switching to another blog
		add_action( 'switch_blog', array( $this, 'wpv_clear_settings_instance' ) );
        
	}


	/**
	 * Register the post type of View.
	 *
	 * @since unknown
	 */
	function wpv_register_type_view() {
        $labels = array(
            'name' => _x( 'Views', 'post type general name' ),
            'singular_name' => _x( 'View', 'post type singular name' ),
            'add_new' => _x( 'Add New View', 'book' ),
            'add_new_item' => __( 'Add New View', 'wpv-views' ),
            'edit_item' => __( 'Edit View', 'wpv-views' ),
            'new_item' => __( 'New View', 'wpv-views' ),
            'view_item' => __( 'View Views', 'wpv-views' ),
            'search_items' => __( 'Search Views', 'wpv-views' ),
            'not_found' =>  __( 'No views found', 'wpv-views' ),
            'not_found_in_trash' => __( 'No views found in Trash', 'wpv-views' ),
            'parent_item_colon' => '',
            'menu_name' => 'Views'
        );
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'query_var' => false,
            'rewrite' => false,
            'can_export' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array( 'title', 'editor', 'author' )
        );
        register_post_type( 'view', $args );
	}

	// Add WPML sync options.
	function language_options() {
		// not needed for theme version.
	}
	
	/*
	* ----------------------------
	* Clear the WPV_Settings instance when switching to another blog
	* ----------------------------
	*/
	
	function wpv_clear_settings_instance() {
		WPV_Settings::clear_instance();
	}
	
	/*
	* ----------------------------
	* Compatibility
	* ----------------------------
	*/
	
	/**
	* WooCommerce
	*
	* Fix malformed add to cart URL in Views AJAX pagination and automatic results in a parametric search.
	*
	* @see https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/186738278/comments
	*/
	function wpv_woocommerce_product_add_to_cart_url( $add_to_cart_url, $wc_prod_object ) {
		if ( 
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			&& isset( $_REQUEST['action'] )
			&& (
				$_REQUEST['action'] == 'wpv_get_view_query_results' 
				|| $_REQUEST['action'] == 'wpv_get_archive_query_results'
			)
		) {
			$parsed = array();
			$parsed = parse_url( $add_to_cart_url );
			if ( isset( $parsed['query'] ) ) {
				wp_parse_str( $parsed['query'], $parsed_query );

				// Only alter the URL if it already contains a numeric add-to-cart parameter
				if ( isset( $parsed_query['add-to-cart'] ) && is_numeric( $parsed_query['add-to-cart'] ) ) {

					// If the product is a variation, we need to handle the variation_id parameter too
					if ( isset( $wc_prod_object->product_type )
						&& $wc_prod_object->product_type == 'variation'
						&& isset( $wc_prod_object->variation_id )
						&& isset( $wc_prod_object->variation_data )
					) {
						$query_args_to_add = array_merge( array( 'variation_id' => $wc_prod_object->variation_id ), $wc_prod_object->variation_data );
					} else {
						$query_args_to_add = array();
					}

					// Build the base URL, it should have a referrer being the actual current page
					if ( wp_get_referer() ) {
						$base_url = wp_get_referer();
					} else {
						$base_url = get_home_url();
					}

					// Modify the URL
					$query_args_to_add['add-to-cart'] = $parsed_query['add-to-cart'];
					$add_to_cart_url = esc_url( remove_query_arg( 'added-to-cart', add_query_arg( $query_args_to_add, $base_url ) ) );
				}
			}
		}
		return $add_to_cart_url;
	}
	
	/**
	* Gravity Forms
	*
	* wpv_gravityforms_fix_form_action_on_ajax
	*
	* Fix the action attribute for Gravity Forms loaded on Views AJAX calls
	*
	* @since 1.10
	*/
	
	function wpv_gravityforms_fix_form_action_on_ajax( $form_tag, $form ) {
		if ( preg_match( "|action='(.*?)'|", $form_tag, $matches ) ) {
			$form_action = $matches[1];
			if ( 
				defined( 'DOING_AJAX' )
				&& DOING_AJAX
				&& isset( $_REQUEST['action'] )
				&& (
					$_REQUEST['action'] == 'wpv_get_view_query_results' 
					|| $_REQUEST['action'] == 'wpv_get_archive_query_results'
				)
			) {
				$base_url = "action='" . esc_url( wp_get_referer() ) . "'";
				$form_tag = preg_replace( "|action='(.*?)'|", $base_url, $form_tag );
			}
		}
		return $form_tag;
	}

	/**
	 * Register Views widgets
	 *
	 * @since unknown
	 */
	function widgets_init() {
		register_widget( 'WPV_Widget' );
		register_widget( 'WPV_Widget_filter' );
	}


	function register_CCK_type( $type ) {
		$this->CCK_types[] = $type;
	}


	function can_include_type($type) {
		return !in_array( $type, $this->CCK_types );
	}
	
	function register_views_pages_in_menu( $pages ) {
		if ( $this->is_embedded() ) {
			$page = wpv_getget( 'page' );
			$pages[] = array(
				'slug'			=> 'embedded-views',
				'menu_title'	=> __( 'Views', 'wpv-views' ),
				'page_title'	=> __( 'Views', 'wpv-views' ),
				'callback'		=> 'wpv_admin_menu_embedded_views_listing_page'
			);
			if ( 'views-embedded' == $page ) {
				add_filter( 'screen_options_show_screen', '__return_false', 99 );
				$pages[] = array(
					'slug'			=> 'views-embedded',
					'menu_title'	=> __( 'Embedded View', 'wpv-views' ),
					'page_title'	=> __( 'Embedded View', 'wpv-views' ),
					'callback'		=> 'views_embedded_html'
				);
			}
			$pages[] = array(
				'slug'			=> 'embedded-views-templates',
				'menu_title'	=> __( 'Content Templates', 'wpv-views' ),
				'page_title'	=> __( 'Content Templates', 'wpv-views' ),
				'callback'		=> 'wpv_admin_menu_embedded_views_templates_listing_page'
			);
			if ( 'view-templates-embedded' == $page ) {
				add_filter( 'screen_options_show_screen', '__return_false', 99 );
				$pages[] = array(
					'slug'			=> 'view-templates-embedded',
					'menu_title'	=> __( 'Embedded Content Template', 'wpv-views' ),
					'page_title'	=> __( 'Embedded Content Template', 'wpv-views' ),
					'callback'		=> 'content_templates_embedded_html'
				);
			}
			$pages[] = array(
				'slug'			=> 'embedded-views-archives',
				'menu_title'	=> __( 'WordPress Archives', 'wpv-views' ),
				'page_title'	=> __( 'WordPress Archives', 'wpv-views' ),
				'callback'		=> 'wpv_admin_menu_embedded_views_archives_listing_page'
			);
			if ( 'view-archives-embedded' == $page ) {
				add_filter( 'screen_options_show_screen', '__return_false', 99 );
				$pages[] = array(
					'slug'			=> 'view-archives-embedded',
					'menu_title'	=> __( 'Embedded WordPress Archive', 'wpv-views' ),
					'page_title'	=> __( 'Embedded WordPress Archive', 'wpv-views' ),
					'callback'		=> 'view_archives_embedded_html'
				);
			}
		}
		return $pages;
	}
	
	function register_export_import_section( $sections ) {
		$promo_link_args = array(
			'query'		=> array(
				'utm_source'	=> 'viewsplugin',
				'utm_campaign'	=> 'views',
				'utm_medium'	=> 'embedded-view-export-import-page',
				'utm_term'		=> 'Get Views'
				
			),
			'anchor'	=> 'views'
		);
		$promo_link = WPV_Admin_Messages::get_documentation_promotional_link( $promo_link_args, 'https://toolset.com/home/toolset-components/' );
		$sections['wpv-views'] = array(
			'slug'		=> 'wpv-views',
			'title'		=> __( 'Views', 'wpv-views' ),
			'icon'		=> '<i class="icon-views-logo ont-icon-16"></i>',
			'items'		=> array(
				'mixed'	=> array(
								'title'		=> __( 'Export and Import Views data', 'wpv-views' ),
								'content'	=> '<p>' 
											. __( 'You need the full Toolset Views plugin to export and import data.', 'wpv-views' ) 
											. WPV_MESSAGE_SPACE_CHAR
											. '<a href="' . $promo_link . '" title="" class="button button-primary-toolset" target="_blank">'
											. __( 'Get Views', 'wpv-views' )
											. '</a>'
											. '</p>'
							)
			)
		);
		return $sections;
	}

	// @deprecate
	// enqueue this correctly
	function settings_box_load() {
		global $pagenow;
		if ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpv-import-theme' ) {
			$this->include_admin_css();
		}
	}


	function include_admin_css() {
		printf(
				'<link rel="stylesheet" href="%s" type="text/css" media="all" />',
				esc_url( add_query_arg( array( 'v' => WPV_VERSION ), WPV_URL . '/res/css/wpv-views.css' ) ) );
	}
	
	/*
	* after_save_item
	* after_import_item
	*
	* Manage the _toolset_edit_last postmeta on Views objects
	*/
	
	function after_save_item( $item_id ) {
		// do nothing in the embedded version
	}
	
	function after_import_item( $item_id ) {
		if (
			! is_numeric( $item_id )
			|| intval( $item_id ) < 1
		) {
			return;
		}
        delete_post_meta( $item_id, '_toolset_edit_last' );
	}

    /**
     * Return View ID given the slug or the name
     * @param type $atts shortcode attributes
     * @return type int View ID
     */
	function get_view_id( $atts ) {
		global $wpdb;
		extract( 
			shortcode_atts(
				array(
					'id'	=> false,
					'name'  => false 
				),
				$atts 
			) 
		);

		if ( 
			empty( $id ) 
			&& ! empty( $name ) 
		) {
			// lookup by post title first
			$id = $wpdb->get_var( 
				$wpdb->prepare( 
					"SELECT ID FROM {$wpdb->posts} 
					WHERE post_type = 'view' 
					AND post_title = %s 
					LIMIT 1",
					$name 
				) 
			);
			if ( ! $id ) {
				// try the post name
				$id = $wpdb->get_var( 
					$wpdb->prepare( 
						"SELECT ID FROM {$wpdb->posts} 
						WHERE post_type = 'view' 
						AND post_name = %s 
						LIMIT 1",
						$name 
					) 
				);
			}
		}
        
		return $id;
	}


	/**
 	 * Process the View shortcode.
	 *
	 * eg. [wpv-view name='my-view']
	 */
	function short_tag_wpv_view( $atts ){

		global $WPVDebug;
		toolset_wplog( $atts, null, __FILE__, 'short_tag_wpv_view', 541 );

		apply_filters( 'wpv_shortcode_debug', 'wpv-view', json_encode($atts), '' , 'Output shown in the Nested elements section' );

		$id = $this->get_view_id( $atts );

		if( empty( $id ) ) {
			return sprintf( '<!- %s ->', __( 'View not found', 'wpv-views' ) );
		}

		$this->view_used_ids[] = $id;
		
        array_push( $this->view_shortcode_attributes, $atts );

        // Shall we look up in the cache?
        $is_cacheable = $this->is_cacheable( $id );
        $cache_id = $this->get_cache_id( $id );
        if ( $is_cacheable ) {
            
            // Is it cached?
            $cached_output_index = get_option( 'wpv_transient_view_index', array() );
            $is_cached = isset( $cached_output_index[$cache_id] ) && $cached_output_index[$cache_id];
            
            if( $is_cached ) {
                
                $trasient = 'wpv_transient_view_'.$cache_id; // strlen() <= 45
                $cached_output = get_transient( $trasient );
                if( $cached_output !== false ) {
                    array_pop( $this->view_shortcode_attributes );
					return apply_filters( 'wpv_filter_wpv_view_shortcode_output', $cached_output, $id );
                }
                // We should not reach this line if everything is working fine
                // But if not, let's continue
            }
            
        }
        
		$out = $this->render_view_ex( $id, md5( serialize( $atts ) ) );
		
		array_pop( $this->view_shortcode_attributes );
        
        // Update Views cache if applicable
        if ( $is_cacheable ) {
            
            // Update Views Loop Content Cache
            $trasient = 'wpv_transient_view_'.$cache_id;
            $is_stored = set_transient( $trasient, $out, DAY_IN_SECONDS );
            
            // Update Views Loop Index
            if( $is_stored === true ) {
                $cached_output_index = get_option( 'wpv_transient_view_index', array() );
                $cached_output_index[$cache_id] = true;
                update_option( 'wpv_transient_view_index', $cached_output_index );
            }
        }
		
		$out = apply_filters( 'wpv_filter_wpv_view_shortcode_output', $out, $id );
        
		return $out;

	}


	/**
	* Process the View form shortcode.
	*
	* eg. [wpv-form-view name='my-view' target_id='xx']
	*
	* @todo Switch .js-wpv-filter-data-for-this-form into a form data attribute that we can init on document.ready and refresh when neeeded
	*/
	function short_tag_wpv_view_form( $atts ) {
		global $sitepress;

		toolset_wplog( $atts, null, __FILE__, 'short_tag_wpv_view_form', 610 );

		apply_filters( 'wpv_shortcode_debug', 'wpv-form-view', json_encode($atts), '', 'Output shown in the Nested elements section' );

		extract( shortcode_atts(
			array(
					'id' => false,
					'name' => false,
					'target_id' => 'self'
			),
			$atts ) 
		);

		$id = $this->get_view_id( $atts );

		if( empty( $id ) ) {
			return sprintf( '<!- %s ->', __( 'View not found', 'wpv-views' ) );
		}

		if ( 
			empty( $target_id ) 
			|| $target_id == 'self' 
		) {
			$target_id = 'self';
			$url = $_SERVER['REQUEST_URI'];
			// @todo review this, we might do the same than on wpv-filter-embedded.php:
			/*
			* $view_settings			= apply_filters( 'wpv_filter_wpv_get_view_settings', array() );
			* $pagination_permalinks	= apply_filters( 'wpv_filter_wpv_get_pagination_permalinks', array(), $view_settings, $view_id );
			* $url						= $pagination_permalinks['first'];
			*/
			if ( 
				defined('DOING_AJAX')
				&& DOING_AJAX
				&& isset( $_REQUEST['action'] )
				&& (
					$_REQUEST['action'] == 'wpv_get_view_query_results' 
					|| $_REQUEST['action'] == 'wpv_get_archive_query_results'
				)
			) {
				if ( wp_get_referer() ) {
					$url = wp_get_referer();
				}
			}
			if (
				isset( $_GET['wpv_aux_current_post_id'] )
				&& is_numeric( $_GET['wpv_aux_current_post_id'] )
			) {
				$url = get_permalink( intval( $_GET['wpv_aux_current_post_id'] ) );
			}
		} else {
			if ( is_numeric( $target_id ) ) {
				// Adjust for WPML support
				$target_id = apply_filters( 'translate_object_id', $target_id, 'page', true, null );
				$url = get_permalink( $target_id );
			} else {
				return sprintf( '<!- %s ->', __( 'target_id not valid', 'wpv-views' ) );
			}
		}

		$this->view_used_ids[] = $id;
		
        array_push( $this->view_shortcode_attributes, $atts );
        
        // Shall we look up in the cache? Is this a Parametric Search View?
        $is_cacheable = $this->is_cacheable( $id ) && $this->does_view_have_form_controls( $id );
        $cache_id = $this->get_cache_id( $id );
        $target_id = isset( $atts['target_id'] ) ? $atts['target_id'] : 'self';
        if( $is_cacheable ) {
            
            // Is it cached?
            $cached_filter_index = get_option( 'wpv_transient_viewform_index', array() );
            $is_cached = isset( $cached_filter_index[$cache_id] ) && $cached_filter_index[$cache_id];
            
            if ( $is_cached ) {
                
                $trasient = 'wpv_transient_viewform_'.$cache_id.'_'.$target_id; // strlen() <= 45
                $cached_filter = get_transient( $trasient );
                if( $cached_filter !== false ) {
                    array_pop( $this->view_shortcode_attributes );
                    return $cached_filter;
                }
                // We should not reach this line if everything is working fine
                // But if not, let's continue
            }
            
        }
        
		$this->rendering_views_form_in_progress = true;

		$out = '';
        
		$view_settings = $this->get_view_settings( $id );
		if ( isset( $view_settings['filter_meta_html'] ) ) {
            
            $this->view_depth++;
			array_push( $this->view_ids, $this->current_view );
			$this->current_view = $id;

			// increment the view count.
			if ( !isset( $this->view_count[ $this->view_depth ] ) ) {
				$this->view_count[ $this->view_depth ] = 0;
			}
			$this->view_count[ $this->view_depth ]++;

			$form_class = array( 'js-wpv-form-only' );

			// Dependant stuff
			$dps_enabled = false;
			$counters_enabled = false;
			if ( !isset( $view_settings['dps'] ) || !is_array( $view_settings['dps'] ) ) {
				$view_settings['dps'] = array();
			}
			if ( isset( $view_settings['dps']['enable_dependency'] ) && $view_settings['dps']['enable_dependency'] == 'enable' ) {
				$dps_enabled = true;
				$controls_per_kind = wpv_count_filter_controls( $view_settings );
				$controls_count = 0;
				$no_intersection = array();
				if ( !isset( $controls_per_kind['error'] ) ) {
					// $controls_count = array_sum( $controls_per_kind );
					$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'] + $controls_per_kind['search'];
					if ( $controls_per_kind['cf'] > 1 && ( !isset( $view_settings['custom_fields_relationship'] ) || $view_settings['custom_fields_relationship'] != 'AND' ) ) {
						$no_intersection[] = __( 'custom field', 'wpv-views' );
					}
					if ( $controls_per_kind['tax'] > 1 && ( !isset( $view_settings['taxonomy_relationship'] ) || $view_settings['taxonomy_relationship'] != 'AND' ) ) {
						$no_intersection[] = __( 'taxonomy', 'wpv-views' );
					}
				} else {
					$dps_enabled = false;
				}
				if ( $controls_count > 0 ) {
					if ( count( $no_intersection ) > 0 ) {
						$dps_enabled = false;
					}
				} else {
					$dps_enabled = false;
				}
			}
			if ( !isset( $view_settings['filter_meta_html'] ) ) {
				$view_settings['filter_meta_html'] = '';
			}
			if ( strpos( $view_settings['filter_meta_html'], '%%COUNT%%' ) !== false ) {
				$counters_enabled = true;
			}
			if ( $dps_enabled || $counters_enabled ) {
				// TODO review this, makes little sense
				if ( $dps_enabled ) {
					$form_class[] = 'js-wpv-dps-enabled';
				}
				do_action( 'wpv_action_extend_query_for_parametric_and_counters', array(), $view_settings, $id );
			} else {
				// Set the force value
				$this->set_force_disable_dependant_parametric_search( true );
			}

			if ( ! isset( $view_settings['dps']['ajax_results'] ) ) {
				$view_settings['dps']['ajax_results'] = 'disable';
			}
			if ( ! isset( $view_settings['dps']['ajax_results_submit'] ) ) {
				$view_settings['dps']['ajax_results_submit'] = 'reload';
			}
			$ajax = $view_settings['dps']['ajax_results'];
			$ajax_submit = $view_settings['dps']['ajax_results_submit'];
			// Disable AJAX results when the target page is set and is not the current one, since there should be no results here whatsoever
			// (and if there are, they belong to a page that should not be targeted by this form)
			$current_page = $this->get_top_current_page();
			if (
				$target_id != 'self'
				&& (
					! $current_page
					|| $current_page->ID != $target_id
				)
			) {
				$ajax = 'disable';
				$ajax_submit = 'reload';
			}

			if ( $ajax == 'enable' ) {
				$form_class[] = 'js-wpv-ajax-results-enabled';
			} else if ( $ajax == 'disable' && $ajax_submit == 'ajaxed' ) {
				$form_class[] = 'js-wpv-ajax-results-submit-enabled';
			}

			$page = 1;

			$effect = 'fade';
			$ajax_pre_before = '';
			if ( isset( $view_settings['dps']['ajax_results_pre_before'] ) ) {
				$ajax_pre_before = esc_attr( $view_settings['dps']['ajax_results_pre_before'] );
			}
			$ajax_before = '';
			if ( isset( $view_settings['dps']['ajax_results_before'] ) ) {
				$ajax_before = esc_attr( $view_settings['dps']['ajax_results_before'] );
			}
			$ajax_after = '';
			if ( isset( $view_settings['dps']['ajax_results_after'] ) ) {
				$ajax_after = esc_attr( $view_settings['dps']['ajax_results_after'] );
			}

			//$url = get_permalink($target_id);
			if( isset( $sitepress ) ) {
				// Dirty hack to be able to use the wpml_content_fix_links_to_translated_content() function
				// It will take a string, parse its links based on <a> tag and return the translated link
				
				// @todo this is not needed anymore, we already translate the $url above
				// on the only case it is a permalink to a given post ID
				$url = '<a href="' . esc_url( $url ) . '"></a>';
				$url = wpml_content_fix_links_to_translated_content( $url );
				$url = substr( $url, 9, -6 );
			}

            $view_url_data			= get_view_allowed_url_parameters( $id );
            $query_args_remove		= wp_list_pluck( $view_url_data, 'attribute' );

            $url = remove_query_arg(
                $query_args_remove,
                $url
            );
			
			$view_attrs = $atts;
			
			$sort_orderby		= '';
			$sort_order			= '';
			$sort_orderby_as	= '';
			$sort_orderby_second	= '';
			$sort_order_second		= '';
			
			if (
				isset( $_GET['wpv_view_count'] ) 
				&& esc_attr( $_GET['wpv_view_count'] ) == $this->get_view_count()
			) {
				if (
					isset( $_GET['wpv_sort_orderby'] ) 
					&& esc_attr( $_GET['wpv_sort_orderby'] ) != '' 
				) {
					$sort_orderby = esc_attr( $_GET['wpv_sort_orderby'] );
				}
				if (
					isset( $_GET['wpv_sort_order'] ) 
					&& esc_attr( $_GET['wpv_sort_order'] ) != '' 
				) {
					$sort_order = esc_attr( $_GET['wpv_sort_order'] );
				}
				if (
					isset( $_GET['wpv_sort_orderby_as'] ) 
					&& esc_attr( $_GET['wpv_sort_orderby_as'] ) != '' 
				) {
					$sort_orderby_as = esc_attr( $_GET['wpv_sort_orderby_as'] );
				}
				// Secondary sorting
				if (
					isset( $_GET['wpv_sort_order_second'] ) 
					&& in_array( strtoupper( esc_attr( $_GET['wpv_sort_order_second'] ) ), array( 'ASC', 'DESC' ) )
				) {
					$sort_order_second = strtoupper( esc_attr( $_GET['wpv_sort_order_second'] ) );
				}
				if (
					isset( $_GET['wpv_sort_orderby_second'] ) 
					&& esc_attr( $_GET['wpv_sort_orderby_second'] ) != 'undefined' 
					&& esc_attr( $_GET['wpv_sort_orderby_second'] ) != '' 
					&& in_array( $_GET['wpv_sort_orderby_second'], array( 'post_date', 'post_title', 'ID', 'modified', 'menu_order', 'rand' ) )
				) {
					$sort_orderby_second = esc_attr( $_GET['wpv_sort_orderby_second'] );
				}
			}
			
			// @todo Switch .js-wpv-filter-data-for-this-form into a form data attribute that we can init on document.ready and refresh when neeeded
			// @note Mind that this could be served from cache, so we better have a caching cleaering mechanism that runs only once...
			
			$parametric_data = array(
				'query'				=> 'normal',
				'id'				=> $id,
				'view_id'			=> $id,
				'widget_id'			=> $this->get_widget_view_id(),
				'view_hash'			=> $this->get_view_count(),
				'action'			=> esc_url( $url ),
				'sort'				=> array(
									'orderby'		=> $sort_orderby,
									'order'			=> $sort_order,
									'orderby_as'	=> $sort_orderby_as,
									'orderby_second'	=> $sort_orderby_second,
									'order_second'		=> $sort_order_second,
									),
				'orderby'			=> $sort_orderby,
				'order'				=> $sort_order,
				'orderby_as'		=> $sort_orderby_as,
				'orderby_second'	=> $sort_orderby_second,
				'order_second'		=> $sort_order_second,
				'ajax_form'			=> '',// 'disabled'|'enabled'
				'ajax_results'		=> '',// 'disabled'|'onsubmit'|'enabled'
				'effect'			=> 'fade',
				'prebefore'			=> $ajax_pre_before,
				'before'			=> $ajax_before,
				'after'				=> $ajax_after
			);
			
			$view_attrs_to_keep = $view_attrs;
			if ( isset( $view_attrs_to_keep['name'] ) ) {
				unset( $view_attrs_to_keep['name'] );
			}
			if ( isset( $view_attrs_to_keep['target_id'] ) ) {
				unset( $view_attrs_to_keep['target_id'] );
			}
			
			$parametric_data['attributes'] = $view_attrs_to_keep;
			
			$view_auxiliar_requires = array(
				'current_post_id'	=> 0,
				'parent_post_id'	=> 0,
				'parent_term_id'	=> 0,
				'parent_user_id'	=> 0
			);
			
			/**
			* wpv_filter_requires_current_page
			*
			* Whether the current View requires the current page for any filter
			*
			* @param $requires_current_page boolean
			* @param $view_settings
			*
			* @since unknown
			*/
			$requires_current_page = apply_filters('wpv_filter_requires_current_page', false, $view_settings);
			if ( 
				$requires_current_page 
				|| $target_id == 'self'
			) {
				$current_post = $this->get_top_current_page();
				if (
					$current_post 
					&& isset( $current_post->ID ) 
				) {
					$view_auxiliar_requires['current_post_id'] = $current_post->ID;
				}
			}
			
			/**
			* wpv_filter_requires_parent_post
			*
			* Whether the current View is nested and requires the parent term for any filter
			*
			* @param $requires_parent_term boolean
			* @param $view_settings
			*
			* @since unknown
			*/
			$requires_parent_post = apply_filters( 'wpv_filter_requires_parent_post', false, $view_settings );
			if ( $requires_parent_post ) {
				$current_post = $this->get_current_page();
				if (
					$current_post 
					&& isset( $current_post->ID ) 
				) {
					$view_auxiliar_requires['parent_post_id'] = $current_post->ID;
				}
			}
			
			/**
			* wpv_filter_requires_parent_term
			*
			* Whether the current View is nested and requires the parent term for any filter
			*
			* @param $requires_parent_term boolean
			* @param $view_settings
			*
			* @since unknown
			*/
			$requires_parent_term = apply_filters( 'wpv_filter_requires_parent_term', false, $view_settings );
			if ( $requires_parent_term ) {
				$parent_term_id = $this->get_parent_view_taxonomy();
				if ( $parent_term_id ) {
					$view_auxiliar_requires['parent_term_id'] = $parent_term_id;
				}
			}
			
			/**
			* wpv_filter_requires_parent_user
			*
			* Whether the current View is nested and requires the parent user for any filter
			*
			* @param $requires_parent_user boolean
			* @param $view_settings
			*
			* @since unknown
			*/
			$requires_parent_user = apply_filters( 'wpv_filter_requires_parent_user', false, $view_settings );
			if ( $requires_parent_user ) {
				$parent_user_id = $this->get_parent_view_user();
				if ( $parent_user_id ) {
					$view_auxiliar_requires['parent_user_id'] = $parent_user_id;
				}
			}
			
			$archive_environment = apply_filters( 'wpv_filter_wpv_get_current_archive_loop', array() );
			$view_auxiliar_requires['archive'] = array(
				'type'	=> $archive_environment['type'],
				'name'	=> $archive_environment['name'],
				'data'	=> $archive_environment['data'],
			);
			
			$parametric_data['environment'] = $view_auxiliar_requires;
			
			$parametric_data = apply_filters( 'wpv_filter_wpv_get_parametric_settings', $parametric_data, $view_settings );

			$out .= '<form'
				. ' autocomplete="off"'
				. ' action="' . esc_url( $url ) . '"'
				. ' method="get"'
				. ' class="wpv-filter-form js-wpv-filter-form js-wpv-filter-form-' . $this->get_view_count() . ' ' . implode( ' ', $form_class ) . '"'
				. ' data-viewnumber="' . $this->get_view_count() . '"'
				. ' data-targetid="' . $target_id . '"'
				. ' data-viewid="' . $id . '"'
				. ' data-viewhash="' . base64_encode( json_encode( $view_attrs ) ) . '"'
				. ' data-viewwidgetid="' . intval( $this->get_widget_view_id() ) . '"'
				. ' data-orderby="' . $sort_orderby . '"'
				. ' data-order="' . $sort_order . '"'
				. ' data-orderbyas="' . $sort_orderby_as . '"'
				. ' data-orderbysecond="' . $sort_orderby_second . '"'
				. ' data-ordersecond="' . $sort_order_second . '"'
				. ' data-parametric="' . esc_js( wp_json_encode( $parametric_data ) ) . '"'
				. ' data-attributes="' . esc_js( wp_json_encode( $view_attrs_to_keep ) ) . '"'
				. ' data-environment="' . esc_js( wp_json_encode( $view_auxiliar_requires ) ) . '"'
				. '>';

			$out .= '<input'
				. ' type="hidden"'
				. ' class="js-wpv-dps-filter-data js-wpv-filter-data-for-this-form"'
				. ' data-action="' . esc_url( $url ) . '"'
				. ' data-page="' . $page . '"'
				. ' data-ajax="disable"'
				. ' data-effect="' . $effect . '"'
				. ' data-ajaxprebefore="' . $ajax_pre_before . '"'
				. ' data-ajaxbefore="' . $ajax_before . '"'
				. ' data-ajaxafter="' . $ajax_after . '"'
				. ' />';

			// add hidden inputs for any url parameters.
			// We need these for when the form is submitted.
			$url_query = parse_url( $url, PHP_URL_QUERY );
			if ( $url_query != '' ) {
				$url_query_args = wp_parse_args( $url_query );
				foreach ( $url_query_args as $url_key => $url_value ) {
					if ( strpos( $url_key, 'wpv_' ) !== 0 ) {
						if ( is_array( $url_value ) ) {
							foreach ( $url_value as $url_value_counter => $url_value_item ) {
								$out .= '<input id="wpv_param_' . esc_attr( $url_key . '_' . $url_value_counter ) . '" type="hidden" name="' . esc_attr( $url_key ) . '[]" value="' . esc_attr( $url_value_item ) . '" />';
							}
						} else if ( is_string( $url_value ) ) {
							$out .= '<input id="wpv_param_' . esc_attr( $url_key ) . '" type="hidden" name="' . esc_attr( $url_key ) . '" value="' . esc_attr( $url_value ) . '" />';
						}
					}
				}
			}

			$view_id = $id;
			$is_required = true;

			/**
			 * Filter wpv_filter_start_filter_form
			 *
			 * @param $out the default form opening tag followed by the required hidden input tags needed for pagination and table sorting
			 * @param $view_settings the current View settings
			 * @param $view_id the ID of the View being displayed
			 * @param $is_required [true|false] whether this View requires a form to be displayed (has a parametric search OR uses table sorting OR uses pagination)
			 *
			 * This can be useful to create additional inputs for the current form without needing to add them to the Filter HTML textarea
			 * Also, can help users having formatting issues
			 *
			 * @return $out
			 *
			 * Since 2.3.0
			 *
			 */

			$out = apply_filters( 'wpv_filter_start_filter_form', $out, $view_settings, $view_id, $is_required );

			$meta_html = $view_settings['filter_meta_html'];
			$fixmatches = '';

			if(	preg_match( '#\\[wpv-filter-start.*?\](.*?)\\[\wpv-filter-end\\]#is', $meta_html, $matches ) ) {

				$fixmatches = str_replace( ' hide="true"', '', $matches[1] );

			} else if( preg_match( '#\\[wpv-filter-controls\\](.*?)\\[\/wpv-filter-controls\\]#is', $meta_html, $matches ) ) {

				$fixmatches = str_replace( ' hide="true"', '', $matches[0] );

			} elseif( preg_match( '#\\[wpv-control.*?\\]#is', $meta_html ) || preg_match( '#\\[wpv-filter-search-box.*?\]#is', $meta_html ) ) {

				if(	preg_match( '#\\[wpv-filter-start.*?\](.*?)\\[\wpv-filter-end\\]#is', $meta_html, $matches ) ) {
					$fixmatches = str_replace( ' hide="true"', '', $matches[1] );
				}
			}

			$out .= wpv_do_shortcode( $fixmatches );

			$form_closure = '</form>';

			/**
			 * Filter wpv_filter_end_filter_form
			 *
			 * @param $out the default form closing tag
			 * @param $view_settings the current View settings
			 * @param $view_id the ID of the View being displayed
			 * @param $is_required [true|false] whether this View requires a form to be displayed (has a parametric search OR uses table sorting OR uses pagination)
			 *
			 * This can be useful to create additional inputs for the current form without needing to add them to the Filter HTML textarea
			 *
			 * @return $out
			 *
			 * Since 2.3.0
			 *
			 */

			$form_closure = apply_filters( 'wpv_filter_end_filter_form', $form_closure, $view_settings, $view_id, $is_required );

			
			$out .= $form_closure;

			$this->current_view = array_pop( $this->view_ids );
			$this->view_depth--;

            // Update Views cache if applicable
            if( $is_cacheable ) {

                // Update Views Filter Content Cache
                $trasient = 'wpv_transient_viewform_' . $cache_id;
                $is_stored = set_transient( $trasient, $out, DAY_IN_SECONDS );

                // Update Views Filter Index
                if ( $is_stored === true ) {
                    $cached_filter_index = get_option( 'wpv_transient_viewform_index', array() );
                    $cached_filter_index = $cached_filter_index === false ? array() : $cached_filter_index;
                    $cached_filter_index[$cache_id] = true;
                    update_option( 'wpv_transient_viewform_index', $cached_filter_index );
                }
            }
            
		}

		array_pop( $this->view_shortcode_attributes );

		$this->rendering_views_form_in_progress = false;

		return $out;
	}
	
	function remove_html_comments_from_shortcode_output( $out ) {
		$out = str_replace('<!-- wpv-loop-start -->', '', $out);
		$out = str_replace('<!-- wpv-loop-end -->', '', $out);
		return $out;
	}
   
    /**
     * Can we use cache for this View?
     * @param int $view_id View ID
     * @return boolean
     */
    function is_cacheable( $view_id ) {
        
        global $WPV_settings, $WPVDebug, $sitepress;
        
        // For the sake of performance, we should check lightest issues first
        
        /* Rule 0: Only the default first page can be cached, and only when it is not modified by core URL parameters */
		$wpv_core_url_parameter_modifiers = array(
			// Table sorting: do not cache if manually modified column orderby or order
			'wpv_column_sort_id', 'wpv_column_sort_dir', 
			// Frontend sorting controls: do not cache if orderby or order are enforced
			'wpv_sort_orderby', 'wpv_sort_order', 'wpv_sort_orderby_as', 'wpv_sort_orderby_second', 'wpv_sort_order_second',
			// Paged and frontend search parameters
			'wpv_paged', 'wpv_view_count', 'wpv_post_search', 'wpv_taxonomy_search' 
		);
		foreach ( $wpv_core_url_parameter_modifiers as $modifier ) {
		    if ( isset( $_GET[ $modifier ] ) ) {
				return false;
		    }
        }

        /* Rule 1: Cache cannot be used while the user is debugging Views */
        $is_debug_mode_on = isset( $WPV_settings->wpv_debug_mode ) && ! empty( $WPV_settings->wpv_debug_mode );
        $current_user_can_debug = $WPVDebug->user_can_debug();       
        if( $is_debug_mode_on && $current_user_can_debug ) {
            return false;
        }

        /* Rule 2: Only "name" and/or "id" and/or "cached" attributes are allowed (exception: "target_id" in some Parametric Search Views) */
        $view_attributes = $this->get_view_shortcodes_attributes();
		$accepted_attributes = array( 'name', 'id', 'cached' );
        if( $this->does_view_have_form_controls( $view_id ) ) {
            $accepted_attributes[] = 'target_id';
        }
        // If target_id is not numeric and nor do be bigger than zero, does not qualify for caching
        if( isset( $view_attributes['target_id'] ) && ( $view_attributes['target_id'] === 'self' || intval( $view_attributes['target_id'] ) == 0 ) ) {
            return false;
        }

        // If "cached" is set and it is set to "off", then the View output should be generated again.
	    if( isset( $view_attributes['cached'] ) && ( $view_attributes['cached'] === 'off' ) ) {
            return false;
	    }

        // Remove accepted attributes
		foreach ( $accepted_attributes as $attribute_name ) {
			if ( isset( $view_attributes[ $attribute_name ] ) ) {
				unset( $view_attributes[ $attribute_name ] );
			}
		}
        // If there are still attributes, something is wrong
		if ( ! empty( $view_attributes ) ) {
			return false;
		}
        
        $view_settings = $this->get_view_settings( $view_id );
        
        /* Rule 3: Random-sorted Views cannot be cached, obviously */
        if( isset( $view_settings['orderby'] ) && 'rand' === $view_settings['orderby'] ) {
            return false;
        }
		if( isset( $view_settings['orderby_second'] ) && 'rand' === $view_settings['orderby_second'] ) {
            return false;
        }
        
        /* Rule 4: Environment-bound Views cannot be cached (at this time) */
        $requirement_list = array(
            'wpv_filter_requires_current_page', // Depends on the current page (bound by Page ID)?
            'wpv_filter_requires_current_archive', // Depends on the current archive page?
            'wpv_filter_requires_current_user', // Depends on the current author?
            'wpv_filter_requires_parent_user', // Is it nested? Depends on the current author?
            'wpv_filter_requires_parent_term', // Is it nested? Depends on the current term?
            'wpv_filter_requires_parent_post' // Is it nested? Depends on the current post?
            );

        foreach($requirement_list as $requirement) {
            $requirement_result = apply_filters( $requirement, false, $view_settings );
            if( $requirement_result ) {
                return false;
            }
        }
        
        /* Rule 5: Themes that modify the query disallow caching */ 
        //$view_settings_no_override = $this->get_view_settings( $view_id, null, array( 'override_view_settings' => false ) );
	    $view_settings_no_override = apply_filters( 'wpv_filter_wpv_get_view_settings', array(), $view_id, array( 'override_view_settings' => false )  );
        $requirement_result = apply_filters( 'wpv_filter_requires_framework_values', false, $view_settings_no_override );
        if( $requirement_result ) {
            return false;
        }
        
        /* Rule 6: Manually-disabled caching. View explicitly demanded not to be cached */
        if( isset( $view_settings['disable_caching'] ) && true === $view_settings['disable_caching'] ) {
            return false;
        }
        
        /* Rule 7: Filter-dependent caching. Apply filter on $view_id, settings($view_id), attributes($view_id) */
        $view_attributes = $this->get_view_shortcodes_attributes();
        /**
		* wpv_filter_disable_caching
		*
		* Disable caching if certains conditions are met
		*
		* @param $view_id
		* @param $view_settings
		* @param $view_attributes
		*
		* @since unknown
		*/
        $requirement_result = apply_filters( 'wpv_filter_disable_caching', false, $view_id, $view_settings, $view_attributes );
        if( $requirement_result ) {
            return false;
        }
        
        /* Rule 8: WPML is active */
        if( is_object( $sitepress ) ) {
            return false;
        }
        
        /* Rule 9: Views that retrieve values through URL parameters (Non-Parametric Search Views) */
	    // TODO: Define methods
	    $attributes = get_view_allowed_url_parameters( $view_id );
	    foreach ( $attributes as $attribute ) {
		    if ( isset( $_GET[ $attribute['attribute'] ] ) ) {
				return false;
		    }
        }

        /* Rule 10: View contains a CT or another View which might have custom CSS or JS */
		$view_layout_settings = $this->get_view_layout_settings( $view_id );
		$view_layout_html = isset( $view_layout_settings[ 'layout_meta_html' ] ) ? $view_layout_settings[ 'layout_meta_html' ] : '';
		if (
			strpos( $view_layout_html, '[wpv-view' ) !== false
			|| strpos( $view_layout_html, '[wpv-post-body' ) !== false
		) {
			return false;
		}
        
        /* Rule N: Guess caching can be allow at this point */
        return true;
    }
    
    /**
     * Generates an unique ID for the cache
     * @param type $id
     * @return string | strlen(string) <= 20
     */
    function get_cache_id( $id ) {
        // FIXME: Instead of using $id, we could use a hash function 
        // and take into account parameters and other environmental variables
        
        return $id;
    }
	
	function wpv_is_rendering_form_view( $false ) {
		return $this->rendering_views_form();
	}
    
	function rendering_views_form() {
		return $this->rendering_views_form_in_progress;
	}


	function get_current_page() {
		$aux_array = $this->current_page;
		return end( $aux_array );
	}
	
	function wpv_get_current_post( $current_post = null ) {
		$maybe_current_post = $this->get_current_page();
		if ( $maybe_current_post ) {
			$current_post = $maybe_current_post;
		}
		return $current_post;
	}
	
	function wpv_set_current_post( $current_post ) {
		$post_exists = ( isset( $current_post ) && $current_post instanceof WP_Post );
		if (
			isset( $current_post ) 
			&& $current_post instanceof WP_Post
		) {
			
		}
		array_push( $this->current_page, clone $current_post );
	}

	function wpv_get_view_shortcodes_attributes( $attributes = false ) {
		$attributes = $this->get_view_shortcodes_attributes();
		if ( ! is_array( $attributes ) ) {
			$attributes = array();
		}
		return $attributes;
	}

	function get_view_shortcodes_attributes() {
		$aux_array = $this->view_shortcode_attributes;
		return end( $aux_array );
	}
	
	/**
	* set_view_shortcode_attributes
	*
	* Sets the shortcode attributess for the current View.
	*
	* Note that calling this twice on the same View will have undesired effects. Use with caution.
	* - Shortcode attributes are pushed into an array when a View is about to be rendered.
	* - Shortcode attributes are poped from that array after the View has been rendered.
	* - This way, several nested Views can have their attributes stored and in sync with the currently rendered one.
	* - Pushing twice for the same View mwans that only the last set is used, and the sync for nested Views is broken.
	*
	* This method is useful when doing AJAX cals, where you know that no View is being loaded, but you need to fake the View shortcode attributes to calculate its hash.
	*/
	
	function set_view_shortcode_attributes( $attributes ) {
		array_push( $this->view_shortcode_attributes, $attributes );
	}
	
	/**
	 * Helper method/API to reset the shortcode attributes for current View, 
	 * for API functions that need to get View results based on their ID.
	 *
	 * @since 2.3.0
	 */
	function reset_view_shortcode_attributes() {
		array_pop( $this->view_shortcode_attributes );
	}


	function get_top_current_page() {
		if ( isset( $_GET['wpv_aux_current_post_id'] ) ) { 
			// In AJAX pagination is_single() and is_page() do not work as expected, but it seems they return TRUE here anyway
			$top_post_id = esc_attr( $_GET['wpv_aux_current_post_id'] );
			$top_post = get_post( $top_post_id );
			$this->top_current_page = $top_post;
			return $this->top_current_page;
		} else if ( is_single() || is_page() ) {
			// In this case, check directly the current page - needed to make the post_type_dont_include_current_page setting work in AJAX pagination
			global $wp_query;
			if ( isset( $wp_query->posts[0] ) ) {
				$current_post = $wp_query->posts[0];
				return $current_post;
			} else {
				return $this->top_current_page;
			}
		} else {
			return $this->top_current_page;
		}
	}
	
	function wpv_get_top_current_post( $top_current_post = null ) {
		$top_current_post = $this->get_top_current_page();
		return $top_current_post;
	}
	
	function wpv_set_top_current_post( $top_current_post ) {
		$this->top_current_page = $top_current_post;
	}

	function wpv_get_current_view( $current_view = null ) {
		$current_view = $this->get_current_view();
		return $current_view;
	}
	
	/**
	 * Get the tree of currently nested Views displayed on the page.
	 *
	 * Returns an array of currently looped Views, in order of appearance, 
	 * or an empty array otherwise.
	 *
	 * @dince 2.3.0
	 */
	
	function wpv_get_current_views_tree( $current_views_tree = array() ) {
		$current_views_tree = $this->view_ids;
		return $current_views_tree;
	}

	/**
	* Get the current view we are processing.
	*/
	function get_current_view() {
		return $this->current_view;
	}
	
	/**
	 * Helper method/API to set the current View, for API functions that need to get View results based on their ID.
	 *
	 * @param $view_id integer The View ID.
	 *
	 * @since 2.3.0
	 */
	function wpv_set_current_view( $view_id ) {
		array_push( $this->view_ids, $this->current_view );
		$this->current_view = $view_id;
	}
	
	/**
	 * Helper method/API to reset the current View, for API functions that need to get View results based on their ID.
	 *
	 * @param $view_id_fallback integer The View ID to set as fallback, for backwards consistency.
	 *
	 * @since 2.3.0
	 */
	function wpv_reset_current_view( $view_id_fallback = null ) {
		$this->current_view = array_pop( $this->view_ids );
		if ( $this->current_view == null ) {
			$this->current_view = $view_id_fallback;
		}
	}
	
	function wpv_get_object_unique_hash( $hash = '', $view_settings = array() ) {
		$view_settings['view-query-mode'] = isset( $view_settings['view-query-mode'] ) ? $view_settings['view-query-mode']  : 'normal';
		switch ( $view_settings['view-query-mode'] ) {
			case 'archive':
			case 'layouts-loop':
				$hash = apply_filters( 'wpv_filter_wpv_get_archive_unique_hash', '' );
				break;
			case 'normal':
			default:
				$hash = apply_filters( 'wpv_filter_wpv_get_view_unique_hash', '' );
				break;
		}
		return $hash;
	}
	

	function wpv_get_view_unique_hash( $view_unique_hash = '' ) {
		$view_unique_hash = $this->get_view_count();
		return $view_unique_hash;
	}

	/**
	* Get the current view count.
	*/
	function get_view_count() {
		$attr_attr = '';
		$attr = $this->get_view_shortcodes_attributes();
		$ignore = array(
			'name',
			'id',
			'target_id',
			'view_display',
			'limit',
			'offset',
			'orderby',
			'order',
			'orderby_as',
			'orderby_second',
			'order_second',
			'cached'
		);
		foreach ( $ignore as $ig_key ) {
			if ( isset( $attr[ $ig_key ] ) ) {
				unset( $attr[ $ig_key ] );
			}
		}
		if ( ! empty( $attr ) ) {
			// Let's assume that shortcode attributes must be strings
			$attr = array_map( 'strval', $attr );
			ksort( $attr );
			$attr_attr = 'CATTR' . md5( serialize( $attr ) );
		}
		
		$view_settings = $this->get_view_settings();
		$attr_top_current_post = '';
		$requires_top_current_post = false;
		/**
		* wpv_filter_requires_current_page
		*
		* Whether the current View requires the current page for any filter
		*
		* @param $requires_top_current_post boolean
		* @param $view_settings
		*
		* @since unknown
		*/
        $requires_top_current_post = apply_filters('wpv_filter_requires_current_page', $requires_top_current_post, $view_settings);
			if ( $requires_top_current_post ) {
			$top_current_post = $this->get_top_current_page();
			if (
				$top_current_post 
				&& isset( $top_current_post->ID )
			) {
				$attr_top_current_post = 'TCPID' . intval( $top_current_post->ID );
			}
		}
		$attr_current_post = '';
		$requires_current_post = false;
		/**
		* wpv_filter_requires_parent_post
		*
		* Whether the current View is nested and requires the parent post for any filter
		*
		* @param $requires_current_post boolean
		* @param $view_settings
		*
		* @since unknown
		*/
		$requires_current_post = apply_filters( 'wpv_filter_requires_parent_post', $requires_current_post, $view_settings );
		if ( $requires_current_post ) {
			$current_post = $this->get_current_page();
			if (
				$current_post 
				&& isset( $current_post->ID )
			) {
				$attr_current_post = 'CPID' . intval( $current_post->ID );
			}
		}
		$attr_term = '';
		$requires_parent_term = false;
		/**
		* wpv_filter_requires_parent_term
		*
		* Whether the current View is nested and requires the parent term for any filter
		*
		* @param $requires_parent_term boolean
		* @param $view_settings
		*
		* @since unknown
		*/
		$requires_parent_term = apply_filters( 'wpv_filter_requires_parent_term', $requires_parent_term, $view_settings );
			if ( $requires_parent_term ) {
			if ( $this->get_parent_view_taxonomy() ) {
				$attr_term = 'CTID' . intval( $this->get_parent_view_taxonomy() );
			}
		}
		$attr_user = '';
		$requires_parent_user = false;
		/**
		* wpv_filter_requires_parent_user
		*
		* Whether the current View is nested and requires the parent user for any filter
		*
		* @param $requires_parent_user boolean
		* @param $view_settings
		*
		* @since unknown
		*/
		$requires_parent_user = apply_filters( 'wpv_filter_requires_parent_user', $requires_parent_user, $view_settings );
		if ( $requires_parent_user ) {
			if ( $this->get_parent_view_user() ) {
				$attr_user = 'CUID' . intval( $this->get_parent_view_user() );
			}
		}
		
		$attr_suffix = $attr_attr . $attr_top_current_post . $attr_current_post . $attr_term . $attr_user;
		if ( ! empty( $attr_suffix ) ) {
			$attr_suffix = '-' . $attr_suffix;
		}
		
		$return = $this->current_view . $attr_suffix;

		return (string) $return;
	}


	function set_view_count( $count, $view_id ) {
		if ( $view_id ) {
			$this->set_view_counts[ $view_id ] = $count;
		} else {
			$this->view_count[ $this->view_depth ] = $count;
		}
	}

	function wpv_get_view_settings( $view_settings = array(), $view_id = null, $options_array = array( 'override_view_settings' => true, 'extend_view_settings' => true, 'public_view_settings' => true, 'original_view_settings' => null ) ) {
		$view_settings = $this->get_view_settings( $view_id, null, $options_array );
		return $view_settings;
	}

	/**
	 * Get the view settings for a given or the current View.
	 *
	 * @param integer $view_id View post ID.
	 * @param array|null $post_meta If not null, this value will be used instead of querying the '_wpv_settings'
	 *	 postmeta of given View. Please refer to wpv_prepare_view_listing_query() to understand why it is necessary
	 *	- usually because we already got the _wpv_settings postmeta for the View and just want to normalize  and filter the output
	 * @param array $options_array (array) Unserialized array with options
     * 
	 * @return array View's settings.
	 *
	 * @since unknown
	 * @singe 2.3.0 Added the $options_array argument to extend the arguments list of the filter. It contains the 'override_view_settings'
	 * boolean (former '$disable_override' boolean argument), the 'extend_view_settings' boolean which decides if the resulting $view_settings
	 * array will be extended and the 'original_view_settings' array which contains the original metadata before the filters are applied.
	 */
	 
	function get_view_settings( $view_id = null, $post_meta = null, $options_array = array( 'override_view_settings' => true, 'extend_view_settings' => true, 'public_view_settings' => true, 'original_view_settings' => null ) ) {
		if ( is_null( $view_id ) ) {
			$view_id = $this->get_current_view();
		}
		// Normalize _wpv_settings postmeta if we got that earlier
		if ( null == $post_meta ) {
			$post_meta = (array) get_post_meta( $view_id, '_wpv_settings', true );
			$options_array[ 'original_view_settings' ] = $post_meta;
		}

        $options_array_defaults = array(
            'override_view_settings' => true,
            'extend_view_settings' => true,
            'public_view_settings' => true,
            'original_view_settings' => null
        );
        $options_array = wp_parse_args( $options_array, $options_array_defaults );
		
		/**
		* wpv_view_settings
		*
		* Internal filter to set some View settings that will overwrite the ones existing in the _wpv_settings postmeta
		* Only used to set default values that need to be there on the returned array, but may not be there for legacy reasons
		* Use wpv_filter_override_view_settings to override View settings - like on the Theme Frameworks integration
		*
		* @param $post_meta (array) Unserialized array of the _wpv_settings postmeta
		* @param $view_id (integer) The View ID
		* @param $options_array (array) Unserialized array with options
		*
		* @return $view_settings (array) The View settings
		*
		* @since unknown
		* @singe 2.3.0 Added the $options_array argument to extend the arguments list of the filter. It contains the 'override_view_settings'
		* boolean (former '$disable_override' boolean argument), the 'extend_view_settings' boolean which decides if the resulting $view_settings
		* array will be extended and the 'original_view_settings' array which contains the original metadata before the filters are applied.
		*/
		$view_settings = apply_filters( 'wpv_view_settings', $post_meta, $view_id, $options_array );

		// @todo Move this functionality to a better place :-)
		if( $options_array['extend_view_settings'] ) {

			$current_view = get_post( $view_id );

			$view_settings['view_id'] = $view_id;
			$view_settings['view_slug'] = isset( $current_view ) ? $current_view->post_name : "";
			$view_settings['view_description'] = get_post_meta( $view_id, '_wpv_description', true );
		}

        if( $options_array['override_view_settings'] ) {

			/**
			 * wpv_filter_override_view_settings
			 *
			 * Public filter to set some View settings that will overwrite the ones existing in the _wpv_settings postmeta
			 * For example, on the Theme Frameworks integration
			 *
			 * @param $view_settings (array) The View settings
			 * @param $view_id (integer) The View ID
			 * @param $options_array (array) Unserialized array with options
			 *
			 * @return $view_settings (array) The View settings
			 *
			 * @since 1.8.0
			 * @singe 2.3.0 Added the $options_array argument to extend the arguments list of the filter. It contains the 'override_view_settings'
			 * boolean (former '$disable_override' boolean argument), the 'extend_view_settings' boolean which decides if the resulting $view_settings
			 * array will be extended and the 'original_view_settings' array which contains the original metadata before the filters are applied.
			 */
            $view_settings = apply_filters( 'wpv_filter_override_view_settings', $view_settings, $view_id, $options_array );
        }

		if( $options_array['public_view_settings'] ) {
			
			/**
			 * wpv_filter_public_wpv_view_settings
			 *
			 * Public filter to set some View settings that will overwrite the ones existing in the _wpv_settings postmeta
			 *
			 * @param $view_settings (array) The View settings
			 * @param $view_id (integer) The View ID
			 * @param $options_array (array) Unserialized array with options
			 *
			 * @return $view_settings (array) The View settings
			 *
			 * @singe 2.3.0
			 */
			$view_settings = apply_filters( 'wpv_filter_public_wpv_view_settings', $view_settings, $view_id, $options_array );
		}

		return $view_settings;
	}


	/**
	 * Callback hooked into the wpv_view_settings filter to set default values
	 * that should be in the _wpv_settings postmeta but might be missing somehow
	 *
	 * @param $view_settings (array)
	 * @param $view_id (integer)
	 * @return array $view_settings (array)
	 *
	 * @since 1.8.0
	 */
	function wpv_view_settings_set_fallbacks( $view_settings, $view_id ) {
		if ( ! is_array( $view_settings ) ) {
			$view_settings = array();
		}
		// Query mode
		if ( ! isset( $view_settings['view-query-mode'] ) ) {
			$view_settings['view-query-mode'] = 'normal';
		}
		return $view_settings;
	}

	function wpv_get_view_layout_settings( $view_layout_settings = array(), $view_id = null ) {
		$view_layout_settings = $this->get_view_layout_settings( $view_id );
		return $view_layout_settings;
	}

	/**
	 * Get the view layout settings for a given or the current View.
	 *
	 * @param integer $view_id View post ID.
	 * @param array|null $post_meta If not null, this value will be used instead of querying the '_wpv_layout_settings'
	 *	 postmeta of given View. Please refer to wpv_prepare_view_listing_query() to understand why it is necessary
	 *	- usually because we already got the _wpv_layout_settings postmeta for the View and just want to normalize  and filter the output
	 *
	 * @return array View's settings.
	 *
	 * @since unknown
	 */
	 
	function get_view_layout_settings( $view_id = null, $post_meta = null ) {
		if ( is_null( $view_id ) ) {
			$view_id = $this->get_current_view();
		}
		// Normalize _wpv_layout_settings postmeta if we got that earlier
		if ( null == $post_meta ) {
			$post_meta = (array) get_post_meta( $view_id, '_wpv_layout_settings', true );
		}
		
		/**
		* wpv_view_layout_settings
		*
		* Internal filter to set some View layout settings that will overwrite the ones existing in the _wpv_layout_settings postmeta
		* Only used to set default values that need to be there on the returned array, but may not be there for legacy reasons
		* Use wpv_filter_override_view_layout_settings to override View layout settings
		*
		* @param $post_meta (array) Unserialized array of the _wpv_layout_settings postmeta
		* @param $view_id (integer) The View ID
		*
		* @return $view_layout_settings (array) The View layout settings
		*
		* @since 1.8.0
		*/
		
		$view_layout_settings = apply_filters( 'wpv_view_layout_settings', $post_meta, $view_id );
		
		/**
		* wpv_filter_override_view_layout_settings
		*
		* Public filter to set some View layout settings that will overwrite the ones existing in the _wpv_layout_settings postmeta
		*
		* @param $view_layout_settings (array) The View layout settings
		* @param $view_id (integer) The View ID
		*
		* @return $view_layout_settings (array) The View layout settings
		*
		* @since 1.8.0
		*/
		
		$view_layout_settings = apply_filters( 'wpv_filter_override_view_layout_settings', $view_layout_settings, $view_id );
		
		return $view_layout_settings;
	}
	
	/**
	* wpv_view_layout_settings_set_fallbacks
	*
	* Callback hooked into the wpv_view_settings filter to set default values
	* that should be in the _wpv_settings postmeta but might be missing somehow
	*
	* @param $view_settings (array)
	* @param $view_id (integer)
	*
	* @return $view_settings (array)
	*
	* @since 1.8.1
	*/
	
	function wpv_view_layout_settings_set_fallbacks( $view_layout_settings, $view_id ) {
		if ( ! is_array( $view_layout_settings ) ) {
			$view_layout_settings = array();
		}
		return $view_layout_settings;
	}
	
	/**
	* clean_current_loop_user
	*
	* Clean the global data for the current user on a loop for a View listing users right after rendering it.
	*
	* This is useful and needed to avoid data leaking caused for persistance of this global values, related to the last rendered user.
	* Without this, the wpv-user shortcode used on a View listing users but ourside the loop, or after the View has been rendered
	* will reurn values related to the last rendered user, instead to the current user as default.
	*
	* @since 1.10
	*/

	function clean_current_loop_user() {
		$this->users_data['term'] = null;
	}

	/**
	 * Keep track of the current view and render the view.
	 */
	function render_view_ex( $id, $hash ){

		global $post, $WPVDebug;

		$this->view_depth++;
		$WPVDebug->wpv_debug_start( $id, $this->view_shortcode_attributes );

        $post_exists = ( isset( $post ) && $post instanceof WP_Post );

		if ( $this->top_current_page == null ) {
			$this->top_current_page = ( $post_exists ? clone $post : null );
		}

		array_push( $this->current_page, $post_exists ? clone $post : null );

		array_push( $this->view_ids, $this->current_view );
		
		// Adjust for WPML support
		// Although Views are not translatable anymore, keep for backwards compatibility
		$id = apply_filters( 'translate_object_id', $id, 'view', true, null );

		$this->current_view = $id;

		array_push( $this->post_query_stack, $this->post_query );

		// save original taxonomy term if any
		$tmp_parent_taxonomy = $this->parent_taxonomy;
		if ( isset( $this->taxonomy_data['term'] ) ) {
			$this->parent_taxonomy = $this->taxonomy_data['term']->term_id;
		} else {
			if (
				$this->parent_taxonomy 
				&& isset( $_GET['wpv_aux_parent_term_id'] ) 
				&& is_numeric( $_GET['wpv_aux_parent_term_id'] ) 
				&& $_GET['wpv_aux_parent_term_id'] == $this->parent_taxonomy
			) {
				$this->parent_taxonomy = intval( $_GET['wpv_aux_parent_term_id'] );
			} else {
				$this->parent_taxonomy = 0;
			}
		}
		$tmp_taxonomy_data = $this->taxonomy_data;

		// save original users if any
		$tmp_parent_user = $this->parent_user;
		if ( isset( $this->users_data['term'] ) ) {
			$this->parent_user = $this->users_data['term']->ID;
		} else {
			if (
				$this->parent_user 
				&& isset( $_GET['wpv_aux_parent_user_id'] ) 
				&& is_numeric( $_GET['wpv_aux_parent_user_id'] )
				&& $_GET['wpv_aux_parent_user_id'] == $this->parent_user
			) {
				$this->parent_user = intval( $_GET['wpv_aux_parent_user_id'] );
			} else {
				$this->parent_user = 0;
			}
		}
		$tmp_users_data = $this->users_data;

		$out =  $this->render_view( $id, $hash );

		if (
			$post_exists
			&& $this->is_archive_view( $id )
		) {
			/**
			* On WPAs, the global $post is valid inside the <wpv-loop></wpv-loop> loop, since each post sets its global,
			* but outside that loop, the global $post was defaulting to the first post in the global $wp_query.
			*
			* It caused that Views used outside the loop with "Don't include current page in query result" turned on
			* were not including the first result, when they should.
			*
			* So we need to temporarily unset the global $post when expanding shortcodes outside the loop on WPA.
			* To avoid problems with date-based archive pages and the [wpv-archive-title] shortcode, we must keep the object and its date,
			* hence we just modify the global $post properties to use the WPA ones instead.
			*
			* @since 1.10
			* @updated 1.11
			* @since 2.0		Do not use 'view' as post_type since Views rendered outside the wpv-loop and using a CT loop will fail to render it:
			* 					The wpv-post-body shortcode stops rendering when the curent post has a post_type of 'view' or 'view-template'
			* 					Let's use a dummy value here, as this will only affect this little piece and we should be more or less covered.
			*/
			
			$registered_post_types = get_post_types( array(), 'names' );
			$dummy_post_type_counter = 0;
			$dummy_post_type_base = 'view-dummy';
			$dummy_post_type = 'view-dummy';
			
			while ( in_array( $dummy_post_type, $registered_post_types ) ) {
				$dummy_post_type_counter = $dummy_post_type_counter + 1;
				$dummy_post_type = $dummy_post_type_base . '-' . $dummy_post_type_counter;
			}
			
			$temp_post = $post;
			$post->ID = $id;
			$post->post_type = $dummy_post_type;
			$post->post_parent = 0;
		}
		
		
		$out = wpv_do_shortcode( $out );
		
		if (
			$post_exists
			&& $this->is_archive_view( $id )
		) {
			/**
			* Restore back the current global $post.
			*
			* Not sure this is needed at all, but better keep it just in case.
			*
			* @since 1.10
			*/
			$post = $temp_post;
			$temp_post = null;
		}

		$this->taxonomy_data = $tmp_taxonomy_data;
		$this->parent_taxonomy = $tmp_parent_taxonomy;

		$this->users_data = $tmp_users_data;
		$this->parent_user = $tmp_parent_user;

		$this->current_view = array_pop( $this->view_ids );

		array_pop( $this->current_page );

		$this->post_query = array_pop( $this->post_query_stack );

		$this->view_depth--;
		$WPVDebug->wpv_debug_end();

		return $out;
	}


	/**
	 * Render the view and loops through the found posts
	 */
	function render_view( $view_id, $hash ){

		global $post, $WPVDebug;

		static $processed_views = array();

		// increment the view count.
		// TODO this code is duplicated, maybe create function for it?
		if ( !isset( $this->view_count[ $this->view_depth ] ) ) {
			$this->view_count[ $this->view_depth ] = 0;
		}
		$this->view_count[ $this->view_depth ]++;

		$view = get_post( $view_id );
		$this->view_used_ids[] = $view_id;

		$out = '';

		$view_caller_id = ( isset( $post ) && isset( $post->ID ) ) ? get_the_ID() : 0; // post or widget

		if( !isset( $processed_views[ $view_caller_id ][ $hash ] ) || 0 === $view_caller_id ) {
			//$processed_views[$view_caller_id][$hash] = true; // mark view as processed for this post

            $status = get_post_status( $view_id );

            // Views should be 'publish'ed to be allowed to produce an output
            // FIXME: Check also that user has permissions to render this view
			if( !empty( $view ) && $status == 'publish' ) {

				$post_content = $view->post_content;

				// apply the layout meta html if we have some.
				$view_layout_settings = $this->get_view_layout_settings();

				if ( isset( $view_layout_settings['layout_meta_html'] ) ) {
					$post_content = str_replace('[wpv-layout-meta-html]', $view_layout_settings['layout_meta_html'], $post_content );
				}

				$view_settings = $this->get_view_settings();

				// find the loop
				if( preg_match( '#\<wpv-loop(.*?)\>(.*)</wpv-loop>#is', $post_content, $matches ) ) {
					// get the loop arguments.
					$args = $matches[1];
					$exp = array_map( 'trim', explode( ' ', $args ) );
					$args = array();
					foreach( $exp as $e ){
						$kv = explode( '=', $e );
						if ( sizeof( $kv ) == 2 ) {
							$args[ $kv[0] ] = trim( $kv[1] ,'\'"');
						}
					}
					if ( isset( $args[ 'wrap' ] ) ) {
						$args['wrap'] = intval( $args['wrap'] );
					}
					if ( isset( $args['pad'] ) ) {
						$args['pad'] = $args['pad'] == 'true';
					} else {
						$args['pad'] = false;
					}

					// Get templates for items (differentiated by their indices, see [wpv-item] documentation).
					$tmpl = $matches[2];
					$item_indexes = $this->_get_item_indexes( $tmpl );
					
					$query_type = apply_filters( 'wpv_filter_wpv_get_query_type', 'posts', $view_id );

					if ( $query_type == 'posts' ) {
						// get the posts using the query settings for this view.

						$archive_query = null;
						if ( $view_settings['view-query-mode'] == 'archive' ) {

							// check for an archive loop
							global $WPV_view_archive_loop;
							if ( isset( $WPV_view_archive_loop ) ) {
								$archive_query = $WPV_view_archive_loop->get_archive_loop_query();
							}

						} else if( $view_settings['view-query-mode'] == 'layouts-loop' ) {
							global $wp_query;
							$archive_query = ( isset( $wp_query ) && $wp_query instanceof WP_Query ) ? clone $wp_query : null;
						}

						if ( $archive_query ) {
							$this->post_query = $archive_query;
							$WPVDebug->add_log( 'mysql_query', $archive_query->request , 'posts', '', true );
							$WPVDebug->add_log( 'info', print_r( $archive_query, true ), 'query_results', '', true );
						} else {
							$this->post_query = wpv_filter_get_posts( $view_id );
						}
						$items = $this->post_query->posts;

						toolset_wplog( 'Found '. count( $items ) . ' posts', null, __FILE__, 'WP_Views::render_view', 1686 );

					}

					// save original post
					global $post, $authordata, $id;
					$tmp_post = ( isset( $post ) && $post instanceof WP_Post ) ? clone $post : null;
					$tmp_authordata = ( isset( $authordata ) && is_object( $authordata ) ) ? clone $authordata : null;
					$tmp_id = $id;

					if ( $query_type == 'taxonomy') {
						$items = $this->taxonomy_query( $view_settings );
						toolset_wplog( $items, 'debug', __FILE__, 'WP_Views::render_view', 1709 );
					} else if ( $query_type == 'users') {
						$items = $this->users_query( $view_settings );
						toolset_wplog( $items, 'debug', __FILE__, 'WP_Views::render_view', 1714 );
					}

                    $items_count = count( $items );

                    global $WPV_settings;
					if ( isset( $WPV_settings->wpv_debug_mode ) && !empty( $WPV_settings->wpv_debug_mode ) ) {
						$WPVDebug->add_log( 'items_count', $items_count );
					}

					// The actual loop - render all items
					$loop = '';
					for( $i = 0; $i < $items_count; $i++) {
						$WPVDebug->set_index();
						$index = $i;

						if ( isset( $args['wrap'] ) ) {
							$index %= $args['wrap'];
						}

						// [wpv-item index=xx] uses base 1
						$index++;
						$index = strval( $index );
						
						/**
						 * Execute an action before each of the View loop items is being rendered.
						 *
						 * @param object $items[$i]  The object about to be displated, can be a WP_Post, a WP_Term, or a WP_User
						 * @param string $query_type The type of query for the current loop: 'posts', 'taxonomy', or 'users'
						 * @param int    $view_id    The ID of the View being looped
						 *
						 * @since 2.4.0
						 */
						do_action( 'wpv_action_wpv_loop_before_display_item', $items[ $i ], $query_type, $view_id );
						
						switch ( $query_type ) {
							case 'posts':
								$post = clone $items[ $i ];
								$authordata = new WP_User( $post->post_author );
								$id = $post->ID;
								$temp_variables = $this->variables;
								$this->variables = array();
								do_action( 'wpv-before-display-post', $post, $view_id );
								break;
							case 'taxonomy':
								$this->taxonomy_data['term'] = $items[ $i ];
								do_action( 'wpv-before-display-taxonomy', $items[ $i ], $view_id );
								break;
							case 'users':
								$user_id = $items[ $i ]->ID;
								$user_meta = get_user_meta( $user_id );
								$items[ $i ]->meta = $user_meta;
								$this->users_data['term'] = $items[ $i ];
								do_action( 'wpv-before-display-user', $items[ $i ], $view_id );
								break;
						}
						
						$WPVDebug->add_log( $query_type , $items[ $i ] );

						// first output the "all" index.
						$shortcodes_output = wpv_do_shortcode( $item_indexes['all'] );
						$loop .= $shortcodes_output;
						$WPVDebug->add_log_item( 'shortcodes', $item_indexes['all'] );
						$WPVDebug->add_log_item( 'output', $shortcodes_output );

						/* Select a template for this item based on it's index.
						 * Note: It is possible that we won't be rendering this item's content if the index 'other'
						 * isn't set and there is no other match. */
						$selected_index = null;
						if ( isset( $item_indexes[ $index ] ) ) {
							// First, set numeric templates
							$selected_index = $index;
						} else {
							// Else, set specific templates based on cases
							$index_data = array(
								'loop_index'	=> $i,
								'item_index'	=> $index,
								'avail_indexes'	=> $item_indexes,
								'view_id'		=> $view_id,
								'items_count'	=> $items_count
							);
							$selected_index = apply_filters( 'wpv_filter_wpv_item_loop_selected_index', $selected_index, $index_data );
						}
						
						// Finally there is an index 'other' and we did not set a valid template before, apply it
						if (
							null == $selected_index 
							&& isset( $item_indexes['other'] )
						) {
							$selected_index = 'other';
						}

						// Output the item with appropriate template (if we found one)
						if( null !== $selected_index ) {
							$shortcodes_output = wpv_do_shortcode( $item_indexes[ $selected_index ] );
							$loop .= $shortcodes_output;
							$WPVDebug->add_log_item( 'shortcodes', $item_indexes[ $selected_index ] );
							$WPVDebug->add_log_item( 'output', $shortcodes_output );
						}
						
						/**
						 * Execute an action after each of the View loop items is rendered.
						 *
						 * @param object $items[$i]  The object just displated, can be a WP_Post, a WP_Term, or a WP_User
						 * @param string $query_type The type of query for the current loop: 'posts', 'taxonomy', or 'users'
						 * @param int    $view_id    The ID of the View being looped
						 *
						 * @since 2.4.0
						 */
						do_action( 'wpv_action_wpv_loop_after_display_item', $items[ $i ], $query_type, $view_id );

						switch ( $query_type ) {
							case 'posts':
								do_action( 'wpv-after-display-post', $post, $view_id );
								$this->variables = $temp_variables;
								break;
							case 'taxonomy':
								do_action( 'wpv-after-display-taxonomy', $items[ $i ], $view_id );
								break;
							case 'users':
								do_action( 'wpv-after-display-user', $items[ $i ], $view_id );
								break;
						}

					}

					// see if we should pad the remaining items.
					if ( isset( $args['wrap'] ) && isset( $args['pad'] ) ) {
						while ( ( $i % $args['wrap'] ) && $args['pad'] ) {
							$index = $i;
							$index %= $args['wrap'];

							if ( $index == $args['wrap'] - 1 ) {
								if ( isset( $item_indexes['pad-last'] ) ) {
									$loop .= wpv_do_shortcode( $item_indexes['pad-last'] );
								}
							} else {
								if ( isset( $item_indexes['pad'] ) ) {
									$loop .= wpv_do_shortcode( $item_indexes['pad'] );
								}
							}

							$i++;
						}
					}

					$WPVDebug->clean_index();
					
					$pagination_data = apply_filters( 'wpv_filter_wpv_get_pagination_settings', array(), $view_settings );
					if ( $pagination_data['effect'] == 'infinite' ) {
						$loop = '<!-- WPV_Infinite_Scroll --><!-- WPV_Infinite_Scroll_Insert -->' . $loop . '<!-- WPV_Infinite_Scroll -->';
					}

					$out .= str_replace( $matches[0], $loop, $post_content );

					// restore original $post
					$post = ( isset( $tmp_post ) && ( $tmp_post instanceof WP_Post ) ) ? clone $tmp_post : null;
					$authordata = ( isset( $tmp_authordata ) && is_object( $tmp_authordata ) ) ? clone $tmp_authordata : null;
					$id = $tmp_id;

				}

			} else {
				$out .= sprintf( '<!- %s ->', __( 'View not found', 'wpv-views' ) );
			}

		} else {

			if( $processed_views[ $view_caller_id ][ $hash ] !== true ) {
				// use output from cache
				$out .= $processed_views[ $view_caller_id ][ $hash ];
			}

		}

		return $out;
	}


	/**
	 * Get the html for each of the wpv-item index.
	 *
	 * <wpv-loop wrap=8 pad=true>
	 * Output for all items
	 * [wpv-item index=1]
	 * Output for item 1
	 * [wpv-item index=4]
	 * Output for item 4
	 * [wpv-item index=8]
	 * Output for item 8
	 * [wpv-item index=odd]
	 * Output for odd items (if they have no output defined by their order)
	 * [wpv-item index=even]
	 * Output for even items (if they have no output defined by their order)
	 * [wpv-item index=others]
	 * Output for other items
	 * [wpv-item index=pad]
	 * Output for when padding is required
	 * [wpv-item index=pad-last]
	 * Output for the last item when padding is required
	 * </wpv-loop>
	 *
	 * Will return an array with the output for each index.
	 *
	 * e.g. array('all' => 'Output for all items',
	 *		  '1' => 'Output for item 1',
	 *		  '4' => 'Output for item 4',
	 *		  '8' => 'Output for item 8',
	 *		  'other' => 'Output for other items',
	 *		  )
	 *
	 */
	function _get_item_indexes( $template ) {
		$indexes = array();
		$indexes['all'] = '';
		$indexes['pad'] = '';
		$indexes['pad-last'] = '';

		// search for the [wpv-item index=xx] shortcode
		$found = false;
		$last_index = -1;

		while( preg_match( '#\\[wpv-item index=([^\[]+)\]#is', $template, $matches ) ) {

			$pos = strpos( $template, $matches[0] );

			if ( !$found ) {
				// found the first one.
				// use all the stuff before for the all index.
				$indexes['all'] = substr( $template, 0, $pos );
				$found = true;
			} else if ( $last_index != -1 ) {
				// All the stuff before belongs to the previous index
				$indexes[ $last_index ] = substr( $template, 0, $pos );
			}

			$template = substr( $template, $pos + strlen( $matches[0] ) );

			$last_index = $matches[1];

		}

		if ( !$found ) {
			$indexes['all'] = $template;
		} else {
			$indexes[ $last_index ] = $template;
		}

		return $indexes;
	}


    /**
     * Determine whether an item in wpv-loop should be targeted by a split* index.
     *
     * @param int $split_factor Count of partitions of items inside wpv-loop. For example, value of 3 means that
     *     we want to split items in three partitions, thus two items - last item in the first third and last in
     *     the second third - will be targeted.
     * @param int $items_count Total count of items inside wpv-loop.
     * @param int $loop_index A zero-based index of current item inside wpv-loop.
     *
     * @return bool True if the item should be targeted.
     *
     * @since 1.11
     */
    private function is_split_index_match( $split_factor, $items_count, $loop_index ) {
        for( $i = 1; $i < $split_factor; ++$i ) {
            if( $loop_index + 1 == floor( $i * $items_count / $split_factor ) ) {
                return true;
            }
        }
        return false;
    }


    /**
     * Select (or re-select) index of wpv-item during processing wpv-loop, if it matches any
     * conditions split or odd/even indices.
     *
     * @param string $selected_index Currently selected index.
     * @param array $index_data
     *
     * @return string New selected index.
     *
     * @since unknown
     */
	function wpv_filter_wpv_item_loop_selected_index( $selected_index, $index_data ) {
		$loop_index	= $index_data['loop_index'];
		$item_index	= $index_data['item_index'];
		$avail_indexes = $index_data['avail_indexes'];
		$items_count = $index_data['items_count'];

        // Check indexes split2 to split5. If there's a wpv-item for an index, check if current item should be targeted.
        for( $split_factor = 2; $split_factor <= 5; ++$split_factor ) {
            $index = 'split' . $split_factor;
            if ( isset( $avail_indexes[ $index ] ) && $this->is_split_index_match( $split_factor, $items_count, $loop_index ) ) {
                return $index;
            }
        }

        // No split* index was selected, check the rest.
		if (
			isset( $avail_indexes['odd'] ) 
			&& ( $item_index % 2 == 1 ) 
		) {
			$selected_index = 'odd';
		} elseif ( 
			isset( $avail_indexes['even'] ) 
			&& ( $item_index % 2 == 0 ) 
		) {
			$selected_index = 'even';
		}
		
		return $selected_index;
	}


	/**
	 * Get the current post query.
	 */
	function get_query() {
		return $this->post_query;
	}


	/**
	 * Get all the views that have been created.
	 */
	function get_views() {
		$views = get_posts( array(
				'post_type' => 'view',
				'post_status' => 'publish',
				'numberposts' => -1 ) );
		return $views;
	}


	/**
	 * New method to get Content templates for module manager.
	 */
	function get_view_templates() {
		$view_templates = get_posts( array(
				'post_type' => 'view-template',
				'post_status' => 'publish',
				'numberposts' => -1 ) );
		return $view_templates;
	}

	// @deprecated - to delete - not used anywhere in Views
	function get_view_titles() {
		global $wpdb;
		static $views_available = null;
		if ( $views_available === null ) {
			$views_available = array();
			$views = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='view'" );
			foreach ( $views as $view ) {
				$views_available[ $view->ID ] = $view->post_title;
			}
		}
		return $views_available;
	}
	
	function wpv_get_postmeta_keys( $keys, $limit = 512 ) {
		$keys = $this->get_meta_keys( $limit );
		return $keys;
	}

    /**
	* Get visible custom field keys and hidden custom field keys declared as such
	*
	* @param int $cf_keys_limit maximum number of keys retrievable from database. Greater than 0.
	*
	* @since unknown
	*/
    function get_meta_keys( $cf_keys_limit = 512 ) {
        
        return $this->_get_meta_keys_internal( true, $cf_keys_limit );
        
    }
    
    /**
	* Get hidden custom field keys from database and Types
	*
	* @param int $cf_keys_limit maximum number of keys retrievable from database. Greater than 0.
	*
	* @since 1.10
	*/
    function get_hidden_meta_keys( $cf_keys_limit = 512 ) {
        
        return $this->_get_meta_keys_internal( false, $cf_keys_limit );
        
    }
    
    /**
	* Is this custom field visible?
	*
	* @param string $custom_field_key
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.10
	*/
    private function custom_field_is_visible( $custom_field_key ) {
        
        static $cf_hidden_declared_visible = array();
        
        if( empty( $cf_hidden_declared_visible ) ) {
         
            global $WPV_settings;
            if( isset( $WPV_settings->wpv_show_hidden_fields ) && is_string( $WPV_settings->wpv_show_hidden_fields ) ) {
                $cf_hidden_declared_visible = explode( ',', $WPV_settings->wpv_show_hidden_fields );
            }
        }
        
        return substr( $custom_field_key, 0, 1 ) != '_' || in_array( $custom_field_key, $cf_hidden_declared_visible );
    }
    
    /**
	* Is this custom field hidden?
	*
	* @param string $custom_field_key name of the custom field.
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.10
	*/
    private function custom_field_is_hidden( $custom_field_key ) {
        return substr( $custom_field_key, 0, 1 ) == '_';
    }
    
    /**
	* Retrieve custom fields.
	*
	* @param bool $is_visible
	*
	* @param int $cf_keys_limit limit database results
	*
	* @return array custom field keys
	*
	* @since 1.10
	*/
    private function _get_meta_keys_internal( $is_visible = true, $cf_keys_limit = 512 ) {
        
        if ( $is_visible ) {
            $predicate_function_name = 'custom_field_is_visible';
            $wpv_filter_keys_limit = 'wpv_filter_wpv_get_postmeta_keys_limit';
            $wpv_filter_keys_result = 'wpv_filter_wpv_get_postmeta_keys_result';
        } else {
            $predicate_function_name = 'custom_field_is_hidden';
            $wpv_filter_keys_limit = 'wpv_filter_wpv_get_hidden_postmeta_keys_limit';
            $wpv_filter_keys_result = 'wpv_filter_wpv_get_hidden_postmeta_keys_result';
        }
        
        $cf_keys = array();
        
        // Filter limit. Allow 3rd parties increase or decrease the limit.
        $cf_keys_limit = apply_filters( $wpv_filter_keys_limit, $cf_keys_limit );
        
        // Verify it is still a number or revert to default
        if( ! is_int( $cf_keys_limit ) || $cf_keys_limit <= 0 ) {
            $cf_keys_limit = 512;
        }
        
        // Cache var
        // f(request_signature:string):array = request:array
        static $cf_keys_request_cache = array();
        $cf_request_signature = ( $is_visible ? 'visible' : 'hidden' ) . $cf_keys_limit;
		

		// We hard-cache default limit for visible and hidden fields
		if ( $cf_keys_limit == 512 ) {
			$wpv_transient_meta_keys = get_transient( 'wpv_transient_meta_keys_' . $cf_request_signature );
			if ( $wpv_transient_meta_keys !== false ) {
				$cf_keys_request_cache[$cf_request_signature] = $wpv_transient_meta_keys;
			}
		}

        // Retrieve from db if keys request cache is empty or contains zero elements
        if ( 
			empty( $cf_keys_request_cache ) 
			|| ! isset( $cf_keys_request_cache[$cf_request_signature] ) 
			|| count( $cf_keys_request_cache[$cf_request_signature] ) == 0 
		) {
            
            // Retrieve keys from postmeta (unsorted)
            // If meta_key starts with underscore, it is a hidden field
            // It's limited because DISTINCT queries are slow            
            global $wpdb, $WPV_settings;
            $cf_keys_request = $wpdb->get_col( 
                    $is_visible 
                    ?
                            /* visible keys and hidden ones declared as such */
                            $wpdb->prepare(
                                "SELECT DISTINCT meta_key
                                FROM {$wpdb->postmeta}
                                WHERE "
                                .(isset( $WPV_settings->wpv_show_hidden_fields ) 
                                        && is_string( $WPV_settings->wpv_show_hidden_fields ) 
                                        && strlen( $WPV_settings->wpv_show_hidden_fields ) > 0
                                        ? "meta_key IN ('" . implode( "','", explode( ',', $WPV_settings->wpv_show_hidden_fields ) ) . "') OR " : "" ).
                                " /* hidden declared as visible */
                                LEFT(meta_key, 1) <> '_' /* visible */
                                LIMIT %d",
                                $cf_keys_limit 
                            )
                                
                    :
                            /* hidden keys only */
                            $wpdb->prepare(
                                "SELECT DISTINCT meta_key
                                FROM {$wpdb->postmeta}
                                WHERE LEFT(meta_key, 1) = '_' /* hidden */
                                LIMIT %d",
                                $cf_keys_limit 
                            )
			);

            // Retrieve keys from Types (unsorted)
            if ( function_exists( 'wpcf_get_post_meta_field_names' ) ) {
                
				$types_fields = wpcf_get_post_meta_field_names();
                $types_fields_filtered = array_filter( $types_fields, array( $this, $predicate_function_name ) );
                $cf_keys_from_types = array_unique( $types_fields_filtered );
                $cf_keys_request = array_merge( $cf_keys_request, $cf_keys_from_types );
                
            }
            
            // Exclude there keys
            $cf_keys_exceptions = array(
                '_edit_last', '_edit_lock', '_wp_page_template', '_wp_attachment_metadata', '_icl_translator_note', '_alp_processed',
                '_icl_translation', '_thumbnail_id', '_views_template', '_wpml_media_duplicate', '_wpml_media_featured',
                '_top_nav_excluded', '_cms_nav_minihome',
                'wpml_media_duplicate_of', 'wpml_media_lang', 'wpml_media_processed',
                '_wpv_settings', '_wpv_layout_settings', '_wpv_view_sync',
                '_wpv_view_template_fields', // DEPRECATED
				'_wpv_view_template_mode',
                'dd_layouts_settings' );
			$cf_keys_request = array_diff( $cf_keys_request, $cf_keys_exceptions );
            
            // Update cache
			if ( $cf_keys_limit == 512 ) {
				set_transient( 'wpv_transient_meta_keys_' . $cf_request_signature, $cf_keys_request, WEEK_IN_SECONDS );
			}
            $cf_keys_request_cache[$cf_request_signature] = $cf_keys_request;
            
        } else {
            
            $cf_keys_request = $cf_keys_request_cache[$cf_request_signature];
            
        }
        
        // Filter result. Allow third-party developers add or remove elements.
        $cf_keys = apply_filters( $wpv_filter_keys_result, $cf_keys_request );
        
        // Remove duplicates and sort result naturally.
        $cf_keys = array_unique( $cf_keys );
        // FIXME: Why is sorting done inside the method? (Legacy)
        if ( $cf_keys && is_array( $cf_keys ) ) {
            natcasesort( $cf_keys );
        }
        
        return $cf_keys;
        
    }
	
	function wpv_get_usermeta_keys( $keys, $limit = 512 ) {
		$keys = $this->get_usermeta_keys( $limit );
		return $keys;
	}
	
	/**
	* Get visible usermeta field keys
	*
	* @param int $usermeta_keys_limit maximum number of keys retrievable from database. Greater than 0.
	*
	* @since unknown
	*/
    function get_usermeta_keys( $usermeta_keys_limit = 512 ) {
        
        return $this->_get_usermeta_keys_internal( true, $usermeta_keys_limit );
        
    }
    
    /**
	* Get hidden usermeta field keys
	*
	* @param int $usermeta_keys_limit maximum number of keys retrievable from database. Greater than 0.
	*
	* @since 1.10
	*/
    function get_hidden_usermeta_keys( $usermeta_keys_limit = 512 ) {
        
        return $this->_get_usermeta_keys_internal( false, $usermeta_keys_limit );
        
    }
	
	/**
	* Is this custom field visible?
	*
	* @param string $usermeta_field_key
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.10
	*/
    private function usermeta_field_is_visible( $usermeta_field_key ) {
        return substr( $usermeta_field_key, 0, 1 ) != '_';
    }
    
    /**
	* Is this custom field hidden?
	*
	* @param string $usermeta_field_key name of the custom field.
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.10
	*/
    private function usermeta_field_is_hidden( $usermeta_field_key ) {
        return substr( $usermeta_field_key, 0, 1 ) == '_';
    }
	
	/**
	* Is this custom field hidden?
	*
	* @param string $usermeta_field_key name of the custom field.
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.10
	*/
    private function usermeta_field_is_skipped( $usermeta_field_key ) {
		$return = true;
		// Exclude these keys
		$hidden_usermeta = array(
			'first_name', 'last_name', 'name', 'nickname', 'description', 'yim', 'jabber', 'aim',
			'rich_editing', 'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front',
			'capabilities', 'user_level', 'user-settings',
			'dismissed_wp_pointers','show_welcome_panel',
			'dashboard_quick_press_last_post_id', 'managenav-menuscolumnshidden',
			'primary_blog', 'source_domain',
			'closedpostboxes', 'metaboxhidden', 'meta-box-order_dashboard', 'meta-box-order', 'nav_menu_recently_edited',
			'new_date', 'show_highlight', 'language_pairs',
			'module-manager',
			'screen_layout', 'session_tokens',
			'hide_wpcf_welcome_panel'
		);
		if ( in_array( $usermeta_field_key, $hidden_usermeta ) ) {
			$return = false;
		}
		
        return $return;
    }
	
	/**
	* Retrieve custom fields.
	*
	* @param bool $is_visible
	*
	* @param int $usermeta_keys_limit limit database results
	*
	* @return array custom field keys
	*
	* @since 1.10
	*/
    private function _get_usermeta_keys_internal( $is_visible = true, $usermeta_keys_limit = 512 ) {
        
        if ( $is_visible ) {
            $predicate_function_name = 'usermeta_field_is_visible';
            $wpv_filter_keys_limit = 'wpv_filter_wpv_get_usermeta_keys_limit';
            $wpv_filter_keys_result = 'wpv_filter_wpv_get_usermeta_keys_result';
        } else {
            $predicate_function_name = 'usermeta_field_is_hidden';
            $wpv_filter_keys_limit = 'wpv_filter_wpv_get_hidden_usermeta_keys_limit';
            $wpv_filter_keys_result = 'wpv_filter_wpv_get_hidden_usermeta_keys_result';
        }
        
        $cf_keys = array();
        
        // Filter limit. Allow 3rd parties increase or decrease the limit.
        $usermeta_keys_limit = apply_filters( $wpv_filter_keys_limit, $usermeta_keys_limit );
        
        // Verify it is still a number or revert to default
        if( ! is_int( $usermeta_keys_limit ) || $usermeta_keys_limit <= 0 ) {
            $usermeta_keys_limit = 512;
        }
        
        // Cache var
        // f(request_signature:string):array = request:array
        static $usermeta_keys_request_cache = array();
        $usermeta_request_signature = ( $is_visible ? 'visible' : 'hidden' ) . $usermeta_keys_limit;
		

		// We hard-cache default limit for visible and hidden fields
		if ( $usermeta_keys_limit == 512 ) {
			$wpv_transient_meta_keys = get_transient( 'wpv_transient_usermeta_keys_' . $usermeta_request_signature );
			if ( $wpv_transient_meta_keys !== false ) {
				$usermeta_keys_request_cache[$usermeta_request_signature] = $wpv_transient_meta_keys;
			}
		}

        // Retrieve from db if keys request cache is empty or contains zero elements
        if ( 
			empty( $usermeta_keys_request_cache ) 
			|| ! isset( $usermeta_keys_request_cache[$usermeta_request_signature] ) 
			|| count( $usermeta_keys_request_cache[$usermeta_request_signature] ) == 0 
		) {
            
            // Retrieve keys from usermeta (unsorted)
            // If meta_key starts with underscore, it is a hidden field
            // It's limited because DISTINCT queries are slow            
            global $wpdb;
			$values_to_prepare = array();
			$umf_mulsitise_string = "";
			if ( is_multisite() ) {
				global $blog_id;
				$umf_mulsitise_string = " AND ( meta_key NOT REGEXP '^{$wpdb->base_prefix}[0-9]_' OR meta_key REGEXP '^{$wpdb->base_prefix}%d_' ) ";
				$values_to_prepare[] = $blog_id;
			}
			$values_to_prepare[] = $usermeta_keys_limit;
			$usermeta_keys = $wpdb->get_col( 
					$is_visible 
                    ?
						$wpdb->prepare(
							"SELECT DISTINCT meta_key 
							FROM {$wpdb->usermeta} 
							WHERE LEFT(meta_key, 1) <> '_' /* visible */
							{$umf_mulsitise_string}
							LIMIT %d",
							$values_to_prepare
						)
					:
						$wpdb->prepare(
							"SELECT DISTINCT meta_key FROM {$wpdb->usermeta} 
							WHERE LEFT(meta_key, 1) == '_' /* hidden */
							{$umf_mulsitise_string}
							LIMIT %d",
							$values_to_prepare
						)
			);

            // Retrieve keys from Types (unsorted) @todo
			/*
            if ( function_exists( 'wpcf_get_post_meta_field_names' ) ) {
                
				$types_fields = wpcf_get_post_meta_field_names();
                $types_fields_filtered = array_filter( $types_fields, array( $this, $predicate_function_name ) );
                $cf_keys_from_types = array_unique( $types_fields_filtered );
                $cf_keys_request = array_merge( $cf_keys_request, $cf_keys_from_types );
                
            }
			*/
			
			$usermeta_keys = array_filter( $usermeta_keys, array( $this, 'usermeta_field_is_skipped' ) );
            
            // Update cache
			if ( $usermeta_keys_limit == 512 ) {
				set_transient( 'wpv_transient_usermeta_keys_' . $usermeta_request_signature, $usermeta_keys, WEEK_IN_SECONDS );
			}
            $usermeta_keys_request_cache[$usermeta_request_signature] = $usermeta_keys;
            
        } else {
            
            $usermeta_keys = $usermeta_keys_request_cache[$usermeta_request_signature];
            
        }
        
        // Filter result. Allow third-party developers add or remove elements.
        $um_keys = apply_filters( $wpv_filter_keys_result, $usermeta_keys );
        
        // Remove duplicates and sort result naturally.
        $um_keys = array_unique( $um_keys );
        // FIXME: Why is sorting done inside the method? (Legacy)
        if ( $um_keys && is_array( $um_keys ) ) {
            natcasesort( $um_keys );
        }
        
        return $um_keys;
        
    }
	
	function wpv_get_termmeta_keys( $keys, $limit = 512 ) {
		$keys = $this->get_termmeta_keys( $limit );
		return $keys;
	}
	
	/**
	* Get visible termmeta field keys and hidden termmeta field keys declared as such
	*
	* @param int $cf_keys_limit maximum number of keys retrievable from database. Greater than 0.
	*
	* @since 1.12
	*/
    function get_termmeta_keys( $termmeta_keys_limit = 512 ) {
        
        return $this->_get_termmeta_keys_internal( true, $termmeta_keys_limit );
        
    }
    
    /**
	* Get hidden termmeta field keys from database and Types
	*
	* @param int $cf_keys_limit maximum number of keys retrievable from database. Greater than 0.
	*
	* @since 1.12
	*/
    function get_hidden_termmeta_keys( $termmeta_keys_limit = 512 ) {
        
        return $this->_get_termmeta_keys_internal( false, $termmeta_keys_limit );
        
    }
    
    /**
	* Is this termmeta field visible?
	*
	* @param string $termmeta_field_key
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.12
	*/
    private function termmeta_field_is_visible( $termmeta_field_key ) {
        
        static $termmeta_hidden_declared_visible = array();
        
        return substr( $termmeta_field_key, 0, 1 ) != '_' || in_array( $termmeta_field_key, $termmeta_hidden_declared_visible );
    }
    
    /**
	* Is this termmeta field hidden?
	*
	* @param string $termmeta_field_key name of the termmeta field.
	*
	* @return bool hidden fields declared as visible return true.
	*
	* @since 1.12
	*/
    private function termmeta_field_is_hidden( $termmeta_field_key ) {
        return substr( $termmeta_field_key, 0, 1 ) == '_';
    }
    
    /**
	* Retrieve termmeta fields.
	*
	* @param bool $is_visible
	*
	* @param int $termmeta_keys_limit limit database results
	*
	* @return array termmeta field keys
	*
	* @since 1.12
	*/
    private function _get_termmeta_keys_internal( $is_visible = true, $termmeta_keys_limit = 512 ) {
		
		global $wp_version;
		if ( version_compare( $wp_version, '4.4' ) < 0 ) {
			return array();
		}
        
        if ( $is_visible ) {
            $predicate_function_name = 'termmeta_field_is_visible';
            $wpv_filter_keys_limit = 'wpv_filter_wpv_get_termmeta_keys_limit';
            $wpv_filter_keys_result = 'wpv_filter_wpv_get_termmeta_keys_result';
        } else {
            $predicate_function_name = 'termmeta_field_is_hidden';
            $wpv_filter_keys_limit = 'wpv_filter_wpv_get_hidden_termmeta_keys_limit';
            $wpv_filter_keys_result = 'wpv_filter_wpv_get_hidden_termmeta_keys_result';
        }
        
        $termmeta_keys = array();
        
        // Filter limit. Allow 3rd parties increase or decrease the limit.
        $termmeta_keys_limit = apply_filters( $wpv_filter_keys_limit, $termmeta_keys_limit );
        
        // Verify it is still a number or revert to default
        if( ! is_int( $termmeta_keys_limit ) || $termmeta_keys_limit <= 0 ) {
            $termmeta_keys_limit = 512;
        }
        
        // Cache var
        // f(request_signature:string):array = request:array
        static $termmeta_keys_request_cache = array();
        $termmeta_request_signature = ( $is_visible ? 'visible' : 'hidden' ) . $termmeta_keys_limit;
		

		// We hard-cache default limit for visible and hidden fields
		if ( $termmeta_keys_limit == 512 ) {
			$wpv_transient_termmeta_keys = get_transient( 'wpv_transient_termmeta_keys_' . $termmeta_request_signature );
			if ( $wpv_transient_termmeta_keys !== false ) {
				$termmeta_keys_request_cache[$termmeta_request_signature] = $wpv_transient_termmeta_keys;
			}
		}

        // Retrieve from db if keys request cache is empty or contains zero elements
        if ( 
			empty( $termmeta_keys_request_cache ) 
			|| ! isset( $termmeta_keys_request_cache[$termmeta_request_signature] ) 
			|| count( $termmeta_keys_request_cache[$termmeta_request_signature] ) == 0 
		) {
            
            // Retrieve keys from postmeta (unsorted)
            // If meta_key starts with underscore, it is a hidden field
            // It's limited because DISTINCT queries are slow            
            global $wpdb;
			$values_to_prepare = array();
			$tmf_mulsitise_string = "";
			if ( is_multisite() ) {
				global $blog_id;
				$tmf_mulsitise_string = " AND ( meta_key NOT REGEXP '^{$wpdb->base_prefix}[0-9]_' OR meta_key REGEXP '^{$wpdb->base_prefix}%d_' ) ";
				$values_to_prepare[] = $blog_id;
			}
			$values_to_prepare[] = $termmeta_keys_limit;
            $termmeta_keys_request = array();
			
			$termmeta_keys_request = $wpdb->get_col( 
					$is_visible 
                    ?
						$wpdb->prepare(
							"SELECT DISTINCT meta_key 
							FROM {$wpdb->termmeta} 
							WHERE LEFT(meta_key, 1) <> '_' 
							{$tmf_mulsitise_string}
							LIMIT %d",
							$values_to_prepare
						)
					:
						$wpdb->prepare(
							"SELECT DISTINCT meta_key FROM {$wpdb->termmeta} 
							WHERE LEFT(meta_key, 1) == '_' 
							{$tmf_mulsitise_string}
							LIMIT %d",
							$values_to_prepare
						)
			);

            // Retrieve keys from Types (unsorted)
			/*
            if ( function_exists( 'wpcf_get_post_meta_field_names' ) ) {
                
				$types_fields = wpcf_get_post_meta_field_names();
                $types_fields_filtered = array_filter( $types_fields, array( $this, $predicate_function_name ) );
                $termmeta_keys_from_types = array_unique( $types_fields_filtered );
                $termmeta_keys_request = array_merge( $termmeta_keys_request, $termmeta_keys_from_types );
                
            }
			*/
            
            // Exclude there keys
			/*
            $termmeta_keys_exceptions = array(
                '_edit_last', '_edit_lock', '_wp_page_template', '_wp_attachment_metadata', '_icl_translator_note', '_alp_processed',
                '_icl_translation', '_thumbnail_id', '_views_template', '_wpml_media_duplicate', '_wpml_media_featured',
                '_top_nav_excluded', '_cms_nav_minihome',
                'wpml_media_duplicate_of', 'wpml_media_lang', 'wpml_media_processed',
                '_wpv_settings', '_wpv_layout_settings', '_wpv_view_sync',
                '_wpv_view_template_fields', // DEPRECATED
				'_wpv_view_template_mode',
                'dd_layouts_settings' );
			$termmeta_keys_request = array_diff( $termmeta_keys_request, $termmeta_keys_exceptions );
			*/
            
            // Update cache
			if ( $termmeta_keys_limit == 512 ) {
				set_transient( 'wpv_transient_termmeta_keys_' . $termmeta_request_signature, $termmeta_keys_request, WEEK_IN_SECONDS );
			}
            $termmeta_keys_request_cache[$termmeta_request_signature] = $termmeta_keys_request;
            
        } else {
            
            $termmeta_keys_request = $termmeta_keys_request_cache[$termmeta_request_signature];
            
        }
        
        // Filter result. Allow third-party developers add or remove elements.
        $termmeta_keys = apply_filters( $wpv_filter_keys_result, $termmeta_keys_request );
        
        // Remove duplicates and sort result naturally.
        $termmeta_keys = array_unique( $termmeta_keys );
        // FIXME: Why is sorting done inside the method? (Legacy)
        if ( $termmeta_keys && is_array( $termmeta_keys ) ) {
            natcasesort( $termmeta_keys );
        }
        
        return $termmeta_keys;
        
    }
    
	/**
	 * If the post has a view, add an view edit link to post.
	 */
	function edit_post_link( $link, $post_id ) {
		// do nothing for theme version.
		return $link;
	}


    /**
     * Retrieve $WPV_Settings_Screen (array-like)
     * @deprecated since version 1.8
     * @return \WPV_Settings_Screen $WPV_settings
     */
	function get_options() {
        global $WPV_settings;
        return $WPV_settings;
    }

    /**
     * Bulk set settings and save
     * @deprecated since version 1.8
     * @param array $options
     */
    function save_options( $options ) {
        global $WPV_settings;
        if ( is_array( $options ) ) {
            $WPV_settings->set( $options );
        }
        $WPV_settings->save();
    }

	function is_embedded() {
		return true;
	}

	function get_current_taxonomy_term() {
		if ( isset( $this->taxonomy_data['term'] ) ) {
			return $this->taxonomy_data['term'];
		} else {
			return null;
		}
	}


	function taxonomy_query( $view_settings ) {
		$items = get_taxonomy_query( $view_settings );
		
		$this->taxonomy_data['item_count'] = sizeof( $items );
		
		if ( $view_settings['pagination']['type'] == 'disabled' ) {
			$this->taxonomy_data['max_num_pages'] = 1;
			$this->taxonomy_data['item_count_this_page'] = $this->taxonomy_data['item_count'];
		} else {
			$posts_per_page = $view_settings['pagination']['posts_per_page'];
			$this->taxonomy_data['max_num_pages'] = ceil( $this->taxonomy_data['item_count'] / $posts_per_page );
			if ( $this->taxonomy_data['item_count'] > $posts_per_page ) {
				$page = 1;
				if (
					isset( $_GET['wpv_paged'] ) 
					&& isset( $_GET['wpv_view_count'] ) 
					&& esc_attr( $_GET['wpv_view_count'] ) == apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings )
				) {
					// @todo check this against the View hash too!
					$page = (int) $_GET['wpv_paged'];
				}
				$this->taxonomy_data['page_number'] = $page;
				$items = array_slice( $items, ($page - 1) * $posts_per_page, $posts_per_page );
			}
		}
		
		$this->taxonomy_data['item_count_this_page'] = sizeof( $items );
		return $items;
	}


	/**
	 * Get Users query,
	 */
	function users_query( $view_settings ) {
		$items = get_users_query( $view_settings );
		
		$this->users_data['item_count'] = sizeof( $items );
		
		if ( $view_settings['pagination']['type'] == 'disabled' ) {
			$this->users_data['item_count_this_page'] = $this->users_data['item_count'];
			$this->users_data['max_num_pages'] = 1;
		} else {
			$posts_per_page = $view_settings['pagination']['posts_per_page'];
			$this->users_data['max_num_pages'] = ceil( $this->users_data['item_count'] / $posts_per_page );
			if ( $this->users_data['item_count'] > $posts_per_page ) {
				$page = 1;
				if (
					isset( $_GET['wpv_paged'] ) 
					&& isset( $_GET['wpv_view_count'] ) 
					&& esc_attr( $_GET['wpv_view_count'] ) == apply_filters( 'wpv_filter_wpv_get_object_unique_hash', '', $view_settings )
				) {
					$page = (int) $_GET['wpv_paged'];
				}
				$this->users_data['page_number'] = $page;
				$items = array_slice( $items, ($page - 1) * $posts_per_page, $posts_per_page );
			}
		}
		
		$this->users_data['item_count_this_page'] = sizeof( $items );
		return $items;
	}

	function wpv_get_query_type( $query_type = 'posts', $view_id = null ) {
		$query_type = $this->get_query_type( $view_id );
		return $query_type;
	}

	/**
	 * Get query type for given or current View/WPA.
	 *
	 * @param null|int $view_id ID of existing View/WPA or null to use the current one.
	 *
	 * @return string Query type, which means 'posts', 'taxonomy' or 'users'.
	 *
	 * @since 1.11
	 * @since 2.4.0 Return an empty string when the passed View ID does not match a valid View.
	 */
	function get_query_type( $view_id = null ) {
		if ( is_null( $view_id ) ) {
			$view_id = $this->get_current_view();
		}

		$view = WPV_View_Base::get_instance( $view_id );
		if ( is_null( $view ) ) {
			return '';
		}
		return $view->query_type;
	}
	
	
	function wpv_get_current_page_number( $page = 1 ) {
		$page = $this->get_current_page_number();
		return $page;
	}


	function get_current_page_number() {
		$query_type = $this->get_query_type();
		if (
			$query_type == 'taxonomy'
			&& isset( $this->taxonomy_data ) 
			&& isset( $this->taxonomy_data['page_number'] )
		) {
			return $this->taxonomy_data['page_number'];
		} else if (
			$query_type == 'users'
			&& isset( $this->users_data ) 
			&& isset( $this->users_data['page_number'] )
		) {
			return $this->users_data['page_number'];
		} else if (
			$query_type == 'posts'
			&& $this->post_query
		) {
			return ( ! empty( $this->post_query->query_vars['paged'] ) ) ? (int) $this->post_query->query_vars['paged'] : 1;
		} else {
			return 1;
		}
		return 1;
	}

	function wpv_get_max_pages( $max_pages = 1 ) {
		$max_pages = $this->get_max_pages();
		return $max_pages;
	}

	function get_max_pages() {
		$query_type = $this->get_query_type();
		if (
			$query_type == 'taxonomy'
			&& isset( $this->taxonomy_data ) 
			&& isset( $this->taxonomy_data['max_num_pages'] )
		) {
			return $this->taxonomy_data['max_num_pages'];
		} else if (
			$query_type == 'users'
			&& isset( $this->users_data ) 
			&& isset( $this->users_data['max_num_pages'] )
		) {
			return $this->users_data['max_num_pages'];
		} else if (
			$query_type == 'posts'
			&& $this->post_query
		) {
			return $this->post_query->max_num_pages;
		} else {
			return 1;
		}
		return 1;
	}

	function wpv_get_taxonomy_found_count( $count = 0 ) {
		$count = $this->get_taxonomy_found_count();
		return $count;
	}

	function get_taxonomy_found_count() {
		if ( isset( $this->taxonomy_data['item_count'] ) ) {
			return $this->taxonomy_data['item_count'];
		} else {
			return 0;
		}
	}
	
	function wpv_get_users_found_count( $count = 0 ) {
		$count = $this->get_users_found_count();
		return $count;
	}


	function get_users_found_count() {
		if ( isset( $this->users_data['item_count'] ) ) {
			return $this->users_data['item_count'];
		} else {
			return 0;
		}
	}


	function get_parent_view_taxonomy() {
		return $this->parent_taxonomy;
	}
	
	function wpv_get_parent_view_taxonomy( $parent_taxonomy = null ) {
		$maybe_parent_taxonomy = $this->get_parent_view_taxonomy();
		if ( $maybe_parent_taxonomy ) {
			$parent_taxonomy = $maybe_parent_taxonomy;
		}
		return $parent_taxonomy;
	}
	
	function wpv_set_parent_view_taxonomy( $parent_taxonomy ) {
		$this->parent_taxonomy = $parent_taxonomy;
	}

	function get_parent_view_user() {
		return $this->parent_user;
	}
	
	function wpv_get_parent_view_user( $parent_user = null ) {
		$maybe_parent_user = $this->get_parent_view_user();
		if ( $maybe_parent_user ) {
			$parent_user = $maybe_parent_user;
		}
		return $parent_user;
	}
	
	function wpv_set_parent_view_user( $parent_user ) {
		$this->parent_user = $parent_user;
	}

	

	function wpv_get_widget_view_id( $widget_view_id = 0 ) {
		$widget_view_id = $this->get_widget_view_id();
		return $widget_view_id;
	}
	
	function wpv_set_widget_view_id( $widget_view_id ) {
		$this->widget_view_id = $widget_view_id;
	}

	function get_widget_view_id() {
		return $this->widget_view_id;
	}
	
	function set_widget_view_id( $widget_view_id ) {
		$this->widget_view_id = $widget_view_id;
	}


	function set_variable( $name, $value ) {
		$this->variables[ $name ] = $value;
	}


	function get_variable( $name ) {
		if ( strpos( $name, '$' ) === 0 ) {
			$name = substr( $name, 1 );

			if ( isset( $this->variables[ $name ] ) ) {
				return $this->variables[ $name ];
			}
		}
		return null;
	}

	/**
	* This might be deprecated, but does not hurt
	* Maybe add a _doing_it_wrong call_user_func
	*/
	function get_view_shortcode_params( $view_id ) {
		$settings = $this->get_view_settings( $view_id );

		$params = wpv_get_custom_field_view_params( $settings );
		$params = array_merge( $params, wpv_get_taxonomy_view_params( $settings ) );

		return $params;
	}

	/**
	 * See if a view has any enabled from controls.
	 */
	function does_view_have_form_controls( $view_id ) {
		$view_settings = $this->get_view_settings( $view_id );

		/*
		// @todo this seems broken - when deleting all parametric search items, we still have this available :-O
		// So... trust just actual filter shortcodes
		if ( isset( $view_settings['filter_controls_enable'] ) && is_array( $view_settings['filter_controls_enable'] ) ) {
			foreach( $view_settings['filter_controls_enable'] as $enable ) {
				if ( $enable ) {
					return true;
				}
			}
		}
		*/

		// Sometimes, the above check is not enough because the filters have been deleted => search for the actual controls shortcodes
		if ( isset( $view_settings['filter_meta_html'] ) ) {
			if ( strpos( $view_settings['filter_meta_html'], "[wpv-control" )
				|| strpos( $view_settings['filter_meta_html'], "[wpv-filter-search-box" )
				|| strpos( $view_settings['filter_meta_html'], "[wpv-filter-submit" ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	* does_view_have_form_control_with_submit
	*
	* See if a view has any enabled from controls and packs a submit button
	*
	* @param $view_id integer
	*
	* @return boolean
	*
	* @since 1.7.0
	*/

	function does_view_have_form_control_with_submit( $view_id ) {
		$view_settings = $this->get_view_settings( $view_id );

		if ( isset( $view_settings['filter_meta_html'] ) ) {
			if (
				(
					strpos( $view_settings['filter_meta_html'], "[wpv-control" )
					|| strpos( $view_settings['filter_meta_html'], "[wpv-filter-search-box" )
					|| strpos( $view_settings['filter_meta_html'], "[wpv-filter-submit" )
				)
				&& strpos( $view_settings['filter_meta_html'], '[wpv-filter-submit' )
			) {
				return true;
			}
		}

		return false;
	}


	/**
	 *	See if a view is used for an archive.
	 */
	function is_archive_view( $view_id ) {
		$view_settings = $this->get_view_settings( $view_id );
		if ( ! isset( $view_settings['view-query-mode'] ) ) {
			$view_settings['view-query-mode'] = 'normal';
		}
		$archive_query_modes = array( 'archive', 'layouts-loop' );
		
		/**
		* wpv_filter_allowed_archive_query_modes
		*
		* Filter the array of valid WPA view-query-mode values
		*
		* @param (array) The array of valid values
		*
		* @since 1.7
		*/
		
		$archive_query_modes = apply_filters( 'wpv_filter_allowed_archive_query_modes', $archive_query_modes );
		return ( in_array( $view_settings['view-query-mode'], $archive_query_modes ) );
	}


	function wpv_format_date() {
		$date_format = $_POST['date-format'];
		if ( $date_format == '' ) {
			$date_format = get_option( 'date_format' );
		}
		// this is needed to escape characters in the date_i18n function
		$date_format = str_replace( '\\\\', '\\', $date_format );
		$date = $_POST['date'];
		// We can not be sure that the adodb_xxx functions are available, so we do different things whether they exist or not
		if ( defined( 'ADODB_DATE_VERSION' ) ) {
			$date = adodb_mktime( 0, 0, 0, substr( $date, 2, 2 ), substr( $date, 0, 2 ), substr( $date, 4, 4 ) );
			echo json_encode( array(
					'display' => adodb_date( $date_format, $date ),
					'timestamp' => $date ) );
		} else {
			$date = mktime( 0, 0, 0, substr( $date, 2, 2 ), substr( $date, 0, 2 ), substr( $date, 4, 4 ) );
			echo json_encode( array(
					'display' => date_i18n( $date_format, intval( $date ) ),
					'timestamp' => $date ) );
		}

		die();
	}


	function wpv_meta_html_extra_css() {
		$view_ids = array_unique( $this->view_used_ids );
		$cssout = '';
		foreach ( $view_ids as $view_id ) {
			$meta = $this->get_view_settings( $view_id );
			$is_wpa = $this->is_archive_view( $view_id );
			$cssout_item = '';
			if ( 
				isset( $meta['filter_meta_html_css'] ) 
				&& '' != $meta['filter_meta_html_css']
			) {
				$cssout_item .= $meta["filter_meta_html_css"] . "\n";
			}
			if ( 
				isset( $meta['layout_meta_html_css'] ) 
				&& '' != $meta['layout_meta_html_css']
			) {
				$cssout_item .= $meta["layout_meta_html_css"] . "\n";
			}
			if ( '' != $cssout_item ) {
				$cssout_item_title = get_the_title( $view_id );
				$cssout .= "/* ----------------------------------------- */\n";
				if ( $is_wpa ) {
					$cssout .= "/* " . sprintf( __( 'WordPress Archive: %s - start', 'wpv-views' ), $cssout_item_title ) . " */\n";
				} else {
					$cssout .= "/* " . sprintf( __( 'View: %s - start', 'wpv-views' ), $cssout_item_title ) . " */\n";
				}
				$cssout .= "/* ----------------------------------------- */\n";
				$cssout .= $cssout_item;
				$cssout .= "/* ----------------------------------------- */\n";
				if ( $is_wpa ) {
					$cssout .= "/* " . sprintf( __( 'WordPress Archive: %s - end', 'wpv-views' ), $cssout_item_title ) . " */\n";
				} else {
					$cssout .= "/* " . sprintf( __( 'View: %s - end', 'wpv-views' ), $cssout_item_title ) . " */\n";
				}
				$cssout .= "/* ----------------------------------------- */\n";
			}
		}
		if ( '' != $cssout ) {
            echo "\n<div id=\"views-extra-css\" style=\"display:none;\" aria-hidden=\"true\">\n" . $cssout . "</div>\n";
		}

		$cssout_compat = "<!--[if IE 7]><style>\n"
				. ".wpv-pagination { *zoom: 1; }\n"
				. "</style><![endif]-->\n";

        echo "\n<div id=\"views-extra-css-ie7\" style=\"display:none;\" aria-hidden=\"true\">\n" . $cssout_compat . "</div>\n";

        $js_for_css_out = "jQuery( document ).ready( function( $ ) {\n"
            . "\tvar extra_css = $( \"#views-extra-css\" ) ? $( \"#views-extra-css\" ).text() : null;"
            . "\tif( extra_css ) {"
            . "\t\t$( 'head' ).append( '<style>' + extra_css + '</style>' );\n"
            . "\t\t$( \"#views-extra-css\" ).remove();"
            . "\t}"
            . "\n"
            . "\t$( 'head' ).append( $( \"#views-extra-css-ie7\" ).html() );\n"
            . "\t$( \"#views-extra-css-ie7\" ).remove();"
            . "});\n";

        echo "\n<script type=\"text/javascript\">\n" . $js_for_css_out . "</script>\n";
	}

	function wpv_meta_html_extra_js() {
		$view_ids = array_unique( $this->view_used_ids );
		$jsout = '';
		foreach ( $view_ids as $view_id ) {
			$meta = $this->get_view_settings( $view_id );
			$is_wpa = $this->is_archive_view( $view_id );
			$jsout_item = '';
			if ( 
				isset( $meta['filter_meta_html_js'] ) 
				&& '' != $meta['filter_meta_html_js']
			) {
				$jsout_item .= $meta["filter_meta_html_js"] . "\n";
			}
			if ( 
				isset( $meta['layout_meta_html_js'] ) 
				&& '' != $meta['layout_meta_html_js']
			) {
				$jsout_item .= $meta["layout_meta_html_js"] . "\n";
			}
			if ( '' != $jsout_item ) {
				$jsout_item_title = get_the_title( $view_id );
				$jsout .= "//-----------------------------------------\n";
				if ( $is_wpa ) {
					$jsout .= "// " . sprintf( __( 'WordPress Archive: %s - start', 'wpv-views' ), $jsout_item_title ) . "\n";
				} else {
					$jsout .= "// " . sprintf( __( 'View: %s - start', 'wpv-views' ), $jsout_item_title ) . "\n";
				}
				$jsout .= "//-----------------------------------------\n";
				$jsout .= $jsout_item;
				$jsout .= "//-----------------------------------------\n";
				if ( $is_wpa ) {
					$jsout .= "// " . sprintf( __( 'WordPress Archive: %s - end', 'wpv-views' ), $jsout_item_title ) . "\n";
				} else {
					$jsout .= "// " . sprintf( __( 'View: %s - end', 'wpv-views' ), $jsout_item_title ) . "\n";
				}
				$jsout .= "//-----------------------------------------\n";
			}
		}
		if ( '' != $jsout ) {
			echo "\n<script type=\"text/javascript\">\n" . $jsout . "</script>\n";
		}
	}
	
	/**
	* wpv_additional_js_files
	*
	* Add custom script URLs from the View layout settings into the wp_footer action
	*
	* @since 1.8.0
	*/
	
	function wpv_additional_js_files() {
		$view_ids = array_unique( $this->view_used_ids );
		foreach ( $view_ids as $view_id ) {
			$meta = $this->get_view_layout_settings( $view_id );
			if (
				isset( $meta['additional_js'] ) 
				&& ! empty( $meta['additional_js'] )
			) {
				$scripts = explode( ',', $meta['additional_js'] );
				foreach ( $scripts as $script ) {
					if ( strpos( $script, '[theme]' ) === 0 ) {
						$script = str_replace( '[theme]', get_stylesheet_directory_uri(), $script );
					}
					echo "\n";
					?>
					<script type="text/javascript" src="<?php echo esc_url( $script ); ?>"></script>
					<?php
					echo "\n";
				}
			}
		}
	}
	
	function wpv_register_assets() {
		
		$views_global_settings = WPV_Settings::get_instance();

		/* ---------------------------- /*
		/* BACKEND SCRIPTS
		/* ---------------------------- */
		
        // URI.js
        // @todo move to common
        if( ! wp_script_is( 'toolset-uri-js', 'registered' ) ) {
            wp_register_script( 'toolset-uri-js', WPV_URL_EMBEDDED . '/res/js/uri-js/URI.min.js', array(), WPV_VERSION );
        }
        if( ! wp_script_is( 'toolset-uri-js-jquery-plugin', 'registered' ) ) {
            wp_register_script( 'toolset-uri-js-jquery-plugin', WPV_URL_EMBEDDED . '/res/js/uri-js/jquery.URI.min.js', array( 'jquery', 'toolset-uri-js' ), WPV_VERSION );
        }

		// CodeMirror
		wp_register_script( 
			'views-codemirror-conf-script', 
			WPV_URL_EMBEDDED . '/res/js/views_codemirror_conf.js', 
			array( 
				'jquery', 
				'toolset-event-manager',
				'toolset-codemirror-script', 
				'toolset-meta-html-codemirror-overlay-script', 
				'toolset-meta-html-codemirror-xml-script', 
				'toolset-meta-html-codemirror-css-script', 
				'toolset-meta-html-codemirror-js-script', 
				'toolset-meta-html-codemirror-utils-search-cursor', 
				'toolset-meta-html-codemirror-utils-panel' 
			), 
			WPV_VERSION, 
			false
		);
		
		// DEPRECATED
		// Keep views-select2-script because the installed version of other plugin might be using it - just register, never enqueue
		// TO DEPRECATE
		wp_register_script( 'views-select2-script', TOOLSET_COMMON_PATH . '/res/lib/select2/select2.min.js', array( 'jquery' ), WPV_VERSION );
			
		// Views utils script
		wp_register_script( 'views-utils-script', WPV_URL_EMBEDDED . '/res/js/lib/utils.js', array( 'jquery', 'toolset_select2', 'toolset-utils' ), WPV_VERSION );
		$help_box_translations = array(
				'wpv_dont_show_it_again' => __( "Got it! Don't show this message again", 'wpv-views'),
				'wpv_close' => __( 'Close', 'wpv-views') );
		wp_localize_script( 'views-utils-script', 'wpv_help_box_texts', $help_box_translations );

		// Shortcodes GUI script
		global $pagenow, $post;
		wp_register_script( 
			'views-shortcodes-gui-script', 
			WPV_URL_EMBEDDED . '/res/js/views_shortcodes_gui.js', 
			array( 'views-utils-script', 'quicktags', 'toolset-shortcode' ), 
			WPV_VERSION 
		);
		$shortcodes_gui_translations = array(
			'wpv_insert_shortcode'						=> __( 'Insert shortcode', 'wpv-views'),
			'wpv_create_shortcode'						=> __( 'Create shortcode', 'wpv-views' ),
			'wpv_update_shortcode'						=> __( 'Update shortcode', 'wpv-views' ),
			'wpv_save_settings'							=> __( 'Save settings', 'wpv-views' ),
			'wpv_close'									=> __( 'Close', 'wpv-views'),
			'wpv_cancel'								=> __( 'Cancel', 'wpv-views' ),
			'wpv_back'									=> __( 'Back', 'wpv-views' ),
			'wpv_fields_and_views_title'				=> __( 'Fields and Views shortcodes', 'wpv-views' ),
			'wpv_fields_and_views_button_title'			=> __( 'Fields and Views', 'wpv-views' ),
			'wpv_shortcode_generated'					=> __( 'Generated shortcode', 'wpv-views' ),
			'wpv_previous'								=> __( 'Previous', 'wpv-views' ),
			'wpv_next'									=> __( 'Next', 'wpv-views' ),
			'loading_options'							=> __( 'Loading...', 'wpv-views' ),
			'nonce_error'								=> __( 'Security verification failed, please reload the page and try again', 'wpv-views' ),
			'attr_number_invalid'						=> __( 'Please enter a valid number', 'wpv-views' ),
			'attr_numberlist_invalid'					=> __( 'Please enter a valid comma separated number list', 'wpv-views' ),
			'attr_year_invalid'							=> __( 'Please enter a valid four-digits year, like 2015', 'wpv-views' ),
			'attr_month_invalid'						=> __( 'Please enter a valid month number (1-12)', 'wpv-views' ),
			'attr_week_invalid'							=> __( 'Please enter a valid week number (1-53)', 'wpv-views' ),
			'attr_day_invalid'							=> __( 'Please enter a valid day number (1-31)', 'wpv-views' ),
			'attr_hour_invalid'							=> __( 'Please enter a valid hour (0-23)', 'wpv-views' ),
			'attr_minute_invalid'						=> __( 'Please enter a valid minute (0-59)', 'wpv-views' ),
			'attr_second_invalid'						=> __( 'Please enter a valid second (0-59)', 'wpv-views' ),
			'attr_dayofyear_invalid'					=> __( 'Please enter a valid day of the year (1-366)', 'wpv-views' ),
			'attr_dayofweek_invalid'					=> __( 'Please enter a valid day of the week (1-7)', 'wpv-views' ),
			'attr_url_invalid'							=> __( 'Please enter a valid URL', 'wpv-views' ),
			'attr_empty'								=> __( 'This option is mandatory ', 'wpv-views' ),
            'wpv_conditional_button'					=> __( 'conditional output', 'wpv-views' ),
			'conditional_enter_conditions_manually'		=> __( 'Edit conditions manually', 'wpv-views' ),
			'conditional_enter_conditions_gui'			=> __( 'Edit conditions using the GUI', 'wpv-views' ),
			'conditional_switch_alert'					=> __( 'Your custom conditions will be lost if you switch back to GUI editing.', 'wpv-views' ),
			'integrated_inputs'							=> array(),
            'wpv_editor_callback_nonce'        			=> wp_create_nonce('wpv_editor_callback'),
			'ajaxurl'									=> wpv_get_views_ajaxurl(),
			'pagenow'									=> $pagenow,
			'get_page' => toolset_getget( 'page' )
		);
		
		$views_shortcodes_gui_data = apply_filters( 'wpv_filter_wpv_shortcodes_gui_data', array() );
		$shortcodes_gui_translations['shortcodes_with_gui'] = array_keys( $views_shortcodes_gui_data );
		
		$shortcodes_gui_translations['post_id'] = 0;
		if ( 
			in_array( $pagenow, array( 'post.php' ) ) 
			&& isset( $_GET["post"] ) 
		) {
			$shortcodes_gui_translations['post_id'] = (int) $_GET["post"];
		} else {
			if (
				isset( $post ) 
				&& is_object( $post )
				&& isset( $post->ID ) 
			) {
				$shortcodes_gui_translations['post_id'] = $post->ID;
			}
		}
		
		/**
		 * Filter the i18n data for the views-shortcodes-gui-script script.
		 *
		 * @since 2.3.0
		 */
		
        $shortcodes_gui_translations = apply_filters( 'wpv_filter_wpv_shortcodes_gui_localize_script', $shortcodes_gui_translations );
		
		wp_localize_script( 'views-shortcodes-gui-script', 'wpv_shortcodes_gui_texts', $shortcodes_gui_translations );
			
		// Views widget script
		wp_register_script( 'views-widgets-gui-script', WPV_URL_EMBEDDED . '/res/js/views_widgets_gui.js', array( 'jquery', 'suggest' ), WPV_VERSION );
		
		$widgets_gui_translations = array(
			'ajaxurl'									=> wpv_get_views_ajaxurl()
		);
		
		wp_localize_script( 'views-widgets-gui-script', 'wpv_widgets_gui_texts', $widgets_gui_translations );

		// Views embedded script
		wp_register_script( 'views-embedded-listing-pages-script', WPV_URL_EMBEDDED . '/res/js/listing_pages.js', array( 'jquery' ), WPV_VERSION, true );
		wp_register_script( 'views-embedded-script', WPV_URL_EMBEDDED . '/res/js/views_embedded.js', array( 'jquery', 'wp-pointer', 'views-codemirror-conf-script' ), WPV_VERSION, true );
			
		/* ---------------------------- /*
		/* BACKEND STYLES
		/* ---------------------------- */
			
		// Dialogs styles
		// @todo maybe move to common too
		// Depends on:
		// 		- wp-jquery-ui-dialog
		wp_register_style( 'views-admin-dialogs-css', WPV_URL_EMBEDDED . '/res/css/dialogs.css', array( 'wp-jquery-ui-dialog', 'toolset-dialogs-overrides-css' ), WPV_VERSION );

		// General Views admin style
		// Depends on:
		// 		- wp-pointer
		// 		- font-awesome
		// 		- toolset-colorbox
		// 		- views-admin-dialogs-css
		// @todo make this also dependant of the common 'editor_addon_menu' and 'editor_addon_menu_scroll'
		wp_register_style( 'views-admin-css', WPV_URL_EMBEDDED . '/res/css/views-admin.css', array( 
			'wp-pointer', 'font-awesome', 
			'toolset-colorbox', 'toolset-select2-css', 'toolset-select2-overrides-css', 
			Toolset_Assets_Manager::STYLE_NOTIFICATIONS,
			'views-admin-dialogs-css' 
		), WPV_VERSION );
			
		/* ---------------------------- /*
		/* FRONTEND SCRIPTS
		/* ---------------------------- */
		
		/**
		 * Datepicker localization
		 * Depends on:
		 * 		- jquery
		 * 		- jquery-ui-core
		 * 		- jquery-ui-datepicker
		 * 
		 * @note Since WordPress 4.6.0 the jQuery datepicker localization is added automatically 
		 *     as an inline script for jquery-ui-datepicker.
		 */
		global $wp_version;
		if ( version_compare( $wp_version, '4.6' ) < 0 ) {
			
			$lang = get_locale();
			$lang = str_replace( '_', '-', $lang );
			if ( file_exists( WPV_PATH_EMBEDDED . '/res/js/i18n/jquery.ui.datepicker-' . $lang . '.js' ) ) {
				wp_register_script( 'jquery-ui-datepicker-local', WPV_URL_EMBEDDED_FRONTEND . '/res/js/i18n/jquery.ui.datepicker-' . $lang . '.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), WPV_VERSION, true );
			} else {
				$lang = substr( $lang, 0, 2 );
				if ( file_exists( WPV_PATH_EMBEDDED . '/res/js/i18n/jquery.ui.datepicker-' . $lang . '.js' ) ) {
					wp_register_script( 'jquery-ui-datepicker-local', WPV_URL_EMBEDDED_FRONTEND . '/res/js/i18n/jquery.ui.datepicker-' . $lang . '.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), WPV_VERSION, true );
				}
			}
			
		}
		
		// Pagination script and style
		// Depends on:
		// 		- jquery
		// 		- jquery-ui-datepicker
		// 		- wp-mediaelement
		// 		- wp-playlist
		wp_register_script( 'views-pagination-script', WPV_URL_EMBEDDED_FRONTEND . '/res/js/wpv-pagination-embedded.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-mediaelement', 'wp-playlist', 'underscore' ), WPV_VERSION, true );
		
		$calendar_image = WPV_URL_EMBEDDED_FRONTEND . '/res/img/calendar.gif';
		$calendar_image = apply_filters( 'wpv_filter_wpv_calendar_image', $calendar_image );
		$calendar_image = apply_filters( 'wptoolset_filter_wptoolset_calendar_image', $calendar_image );
		/**
		 * Set minimum and maximum selectable date for the datepicker rendered by Views in front-end.
		 *
		 * Please note that using this will influenece all datepicker elements in the frontend page.
		 *
		 * @since 1.7
		 *
		 * @param mixed $minDate Minimum date value which will be passed to datepicker constructor. Following types are supported:
		 *	 - number: A number of days from today.
		 *	 - string: A string in the format of 'ddmmyy' or a relative date.
		 *	 - null: Default value. No minimum date is defined.
		 *
		 * @see http://api.jqueryui.com/datepicker/#option-minDate
		 * @see http://api.jqueryui.com/datepicker/#option-maxDate
		 */
		$datepicker_min_date = apply_filters( 'wpv_filter_wpv_datepicker_min_date', null );
		$datepicker_max_date = apply_filters( 'wpv_filter_wpv_datepicker_max_date', null );
		$resize_debounce_tolerance = apply_filters( 'wpv_filter_wpv_resize_debounce_tolerance', 100 );
		$wpv_pagination_localization = array(
			'front_ajaxurl'				=> admin_url( 'admin-ajax.php', null ),
			'calendar_image'			=> $calendar_image,
			'calendar_text'				=> esc_js( __( 'Select date', 'wpv-views') ),
			'datepicker_min_date'		=> $datepicker_min_date,
			'datepicker_max_date'		=> $datepicker_max_date,
			'resize_debounce_tolerance'	=> $resize_debounce_tolerance,
			'datepicker_style_url'		=> TOOLSET_COMMON_FRONTEND_URL . '/toolset-forms/css/wpt-jquery-ui/jquery-ui-1.11.4.custom.css'
		);
		wp_localize_script( 'views-pagination-script', 'wpv_pagination_local', $wpv_pagination_localization );
		
		// Map script
		// Depends on google-maps
		// For compatibility reasons, we are not registering this unless the Views legacy Maps plugin is enabled.
		if ( 
			isset( $views_global_settings->wpv_map_plugin ) 
			&& true == (bool) $views_global_settings->wpv_map_plugin 
		) {
			if ( ! wp_script_is( 'google-maps', 'registered' ) ) {
				if ( is_ssl() ) {
					$protocol = 'https';
				} else {
					$protocol = 'http';
				}
				wp_register_script( 'google-maps', $protocol . '://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&ver=3.5.2', array(), null, true );
			}
			wp_register_script( 'views-map-script', WPV_URL_EMBEDDED_FRONTEND . '/res/js/jquery.wpvmap.js', array( 'google-maps', 'jquery' ), WPV_VERSION, true );
		}
			
		/* ---------------------------- /*
		/* FRONTEND STYLES
		/* ---------------------------- */
			
		// Pagination styles - includes table styles
		// Depends on:
		// 		- mediaelement
		// 		- wp-mediaelement
		wp_register_style( 'views-pagination-style', WPV_URL_EMBEDDED_FRONTEND . '/res/css/wpv-pagination.css', array( 'mediaelement', 'wp-mediaelement' ), WPV_VERSION );
			
		// We need to add the WordPress playlist templates on the frontend
		// Just in case there is a playlist on responses of AJAXed related events
		add_action( 'wp_footer', 'wp_underscore_playlist_templates', 0 );
	}
	
	/**
	 * Add the frontend styles and scripts.
	 */
	function wpv_frontend_enqueue_scripts() {
		
		$views_global_settings = WPV_Settings::get_instance();

		// Pagination
		// Note that both jquery-ui-datepicker-local and views-pagination-script have jquery-ui-datepicker as dependency
		// Note that since WP 4.6.0 we do not register this locale script anymore
		if ( wp_script_is( 'jquery-ui-datepicker-local', 'registered' ) ) {
			wp_enqueue_script( 'jquery-ui-datepicker-local' );
		}
		if ( ! wp_script_is( 'views-pagination-script' ) ) {
			wp_enqueue_script( 'views-pagination-script' );
		}
		if ( ! wp_style_is( 'views-pagination-style' ) ) {
			wp_enqueue_style( 'views-pagination-style' );
			$style_options_for_list_controls = apply_filters( 'wpv_filter_wpv_get_styles_for_list_controls', array() );
			$css_options_for_list_controls = '';
			foreach ( $style_options_for_list_controls as $style_option_slug => $style_option_data ) {
				$css_options_for_list_controls .= ''
					. '.wpv-sort-list-dropdown.wpv-sort-list-dropdown-style-' . esc_attr( $style_option_slug ) . ' > span.wpv-sort-list,'
					. '.wpv-sort-list-dropdown.wpv-sort-list-dropdown-style-' . esc_attr( $style_option_slug ) . ' .wpv-sort-list-item {'
					. ( isset( $style_option_data['border-color'] ) ? ( 'border-color: ' . $style_option_data['border-color'] . ';' ) : '' )
					. '}'
					. '.wpv-sort-list-dropdown.wpv-sort-list-dropdown-style-' . esc_attr( $style_option_slug ) . ' .wpv-sort-list-item a {'
					. ( isset( $style_option_data['color'] ) ? ( 'color: ' . $style_option_data['color'] . ';' ) : '' )
					. ( isset( $style_option_data['background-color'] ) ? ( 'background-color: ' . $style_option_data['background-color'] . ';' ) : '' )
					. '}'
					. '.wpv-sort-list-dropdown.wpv-sort-list-dropdown-style-' . esc_attr( $style_option_slug ) . ' a:hover,'
					. '.wpv-sort-list-dropdown.wpv-sort-list-dropdown-style-' . esc_attr( $style_option_slug ) . ' a:focus {'
					. ( isset( $style_option_data['color-hover'] ) ? ( 'color: ' . $style_option_data['color-hover'] . ';' ) : '' )
					. ( isset( $style_option_data['background-color-hover'] ) ? ( 'background-color: ' . $style_option_data['background-color-hover'] . ';' ) : '' )
					. '}'
					. '.wpv-sort-list-dropdown.wpv-sort-list-dropdown-style-' . esc_attr( $style_option_slug ) . ' .wpv-sort-list-item.wpv-sort-list-current a {'
					. ( isset( $style_option_data['color-current'] ) ? ( 'color: ' . $style_option_data['color-current'] . ';' ) : '' )
					. ( isset( $style_option_data['background-color-current'] ) ? ( 'background-color: ' . $style_option_data['background-color-current']  . ';' ) : '' )
					. '}'
					. '';
					
			}
			wp_add_inline_style( 'views-pagination-style', $css_options_for_list_controls );
		}

		// Maps
		if ( 
			isset( $views_global_settings->wpv_map_plugin ) 
			&& true == (bool) $views_global_settings->wpv_map_plugin 
		) {
			wp_enqueue_script( 'views-map-script' );
		}
	}

	function wpv_admin_enqueue_scripts( $hook ) {
		
		$page = wpv_getget( 'page' );
		
		// Assets for the shortcodes GUI
		$force_load_shortcodes_gui_assets = array( 'dd_layouts_edit' );
		$force_load_shortcodes_gui_assets = apply_filters( 'wpv_filter_wpv_force_load_shortcodes_gui_assets', $force_load_shortcodes_gui_assets );
		if ( 
			$hook == 'post.php' 
			|| $hook == 'post-new.php' 
			|| in_array( $page, $force_load_shortcodes_gui_assets )
		) {
			if ( ! wp_script_is( 'views-shortcodes-gui-script' ) ) {
				wp_enqueue_script( 'views-shortcodes-gui-script' );
			}
            if ( ! wp_script_is( 'jquery-ui-resizable' ) ) {
				wp_enqueue_script('jquery-ui-resizable');
			}
			if ( ! wp_style_is( 'views-admin-css' ) ) {
				wp_enqueue_style( 'views-admin-css' );
			}
		}
		
		if ( $page == 'dd_layouts_edit' ) {
			if ( ! wp_script_is( 'views-codemirror-conf-script' ) ) {
				wp_enqueue_script( 'views-codemirror-conf-script' );
			}
			if ( ! wp_style_is( 'toolset-meta-html-codemirror-css' ) ) {
				wp_enqueue_style( 'toolset-meta-html-codemirror-css' );
			}
		}

        // Assets for embedded listing pages
        if( in_array( $page, array( 'embedded-views', 'embedded-views-templates', 'embedded-views-archives' ) ) ) {
            if ( ! wp_script_is( 'views-embedded-listing-pages-script' ) ) {
				wp_enqueue_script( 'views-embedded-listing-pages-script' );
			}
			if ( ! wp_style_is( 'views-admin-css' ) ) {
				wp_enqueue_style( 'views-admin-css' );
			}
		}

		// Assets for embedded edit pages
        if ( in_array( $page, array( 'views-embedded', 'view-templates-embedded', 'view-archives-embedded', 'ModuleManager_Modules' ) ) ) {
			if ( ! wp_script_is( 'views-codemirror-conf-script' ) ) {
				wp_enqueue_script( 'views-codemirror-conf-script' );
			}
			if ( ! wp_style_is( 'toolset-meta-html-codemirror-css' ) ) {
				wp_enqueue_style( 'toolset-meta-html-codemirror-css' );
			}
			if ( ! wp_script_is( 'views-embedded-script' ) ) {
				wp_enqueue_script( 'views-embedded-script' );
			}
			if ( ! wp_script_is( 'views-utils-script' ) ) {
				wp_enqueue_script( 'views-utils-script' );
			}
			if ( ! wp_style_is( 'views-admin-css' ) ) {
				wp_enqueue_style( 'views-admin-css' );
			}
		}
		
		// Assets for the Widgets page
		if ( $hook == 'widgets.php' ) {
			if ( ! wp_script_is( 'views-widgets-gui-script' ) ) {
				wp_enqueue_script( 'views-widgets-gui-script' );
			}
			if ( ! wp_style_is( 'views-admin-css' ) ) {
				wp_enqueue_style( 'views-admin-css' );
			}
		}

	}
	
	function wpv_get_force_disable_dps( $status = false ) {
		return $this->get_force_disable_dependant_parametric_search();
	}


	function get_force_disable_dependant_parametric_search() {
		return $this->force_disable_dependant_parametric_search;
	}

	function check_force_disable_dependant_parametric_search() {
		$force_disable = false;
		$view_settings = $this->get_view_settings();
		if ( isset( $view_settings['dps'] )
			&& isset( $view_settings['dps']['enable_dependency'] )
			&& $view_settings['dps']['enable_dependency'] == 'enable' )
		{
			$controls_per_kind = wpv_count_filter_controls( $view_settings );
			$controls_count = 0;
			$no_intersection = array();

			if ( !isset( $controls_per_kind['error'] ) ) {
				// $controls_count = array_sum( $controls_per_kind );
				$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'] + $controls_per_kind['search'];

				if ( $controls_per_kind['cf'] > 1
					&& ( !isset( $view_settings['custom_fields_relationship'] ) || $view_settings['custom_fields_relationship'] != 'AND' ) )
				{
					$no_intersection[] = __( 'custom field', 'wpv-views' );
				}

				if ( $controls_per_kind['tax'] > 1
					&& ( !isset( $view_settings['taxonomy_relationship'] ) || $view_settings['taxonomy_relationship'] != 'AND' ) )
				{
					$no_intersection[] = __( 'taxonomy', 'wpv-views' );
				}
			} else {
				$force_disable = true;
			}

			if ( $controls_count > 0 ) {
				if ( count( $no_intersection ) > 0 ) {
					$force_disable = true;
				}
			} else {
				$force_disable = true;
			}
		}
		$this->set_force_disable_dependant_parametric_search( $force_disable );
		return $force_disable;
	}
	
	function wpv_force_disable_dps( $state = false ) {
		$this->set_force_disable_dependant_parametric_search( $state );
	}

	function set_force_disable_dependant_parametric_search( $bool = false ) {
		$this->force_disable_dependant_parametric_search = $bool;
	}

	/**
	 * wpv_get_view_url_params TODO
	 *
	 */
	function wpv_get_view_url_params( $id = null ) {
		$view_settings = $this->get_view_settings( $view_id );

	}
	
	function wpv_get_rendered_views_ids( $used_ids = array() ) {
		return $this->view_used_ids;
	}
	
	function wpv_get_post_query( $query = null ) {
		return $this->post_query;
	}
	
	function wpv_get_taxonomy_query( $query = array() ) {
		return $this->taxonomy_data;
	}
	
	function wpv_get_user_query( $query = array() ) {
		return $this->users_data;
	}

}

/**
* @deprecated in 2.0
*/

function wpv_views_plugin_action_links( $links, $plugin_file ) {
	$this_plugin = basename( WPV_PATH ) . '/wp-views.php';
	if ( $plugin_file == $this_plugin ) {
		$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( 
					add_query_arg(
						array( 'page' => basename( WPV_PATH ) . '/menu/help.php' ),
						admin_url( 'admin.php' ) 
					) 
				),
				__( 'Getting started', 'wpv-views' ) 
			);
	}
	return $links;
}

function wpv_views_plugin_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	$this_plugin = basename( WPV_PATH ) . '/wp-views.php';
	if ( $plugin_file == $this_plugin ) {
		$plugin_meta[] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://toolset.com/version/views-2-6/?utm_source=viewsplugin&utm_campaign=views&utm_medium=release-notes-plugin-row&utm_term=Views 2.6 release notes',
				__( 'Views 2.6 release notes', 'wpv-views' )
			);
	}
	return $plugin_meta;
}


/**
 * WPML translate call.
 *
 * @param type $name
 * @param type $string
 * @return type
 *
 * @todo maybe move to the WPML file
 */
function wpv_translate( $name, $string, $register = false, $context = 'plugin Views' ) {
	if ( !function_exists( 'icl_t' ) ) {
		return $string;
	}

	if ( $register ) {
		icl_register_string( $context, $name, $string );
	}

	return icl_t( $context, $name, stripslashes( $string ) );
}


/**
* wpv_admin_exclude_tax_slugs
*
* Applied in the filter wpv_admin_exclude_tax_slugs, returns an array of taxonomy slugs that are left out in Views taxonomy-related View loops admin GUIs.
*
* We take out taxonomies with show_ui set to false by default, but some custom taxonomies declared for internal use
* by some plugins do not use it. If that is the case and no custom labels are provided, the custom taxonomy hijacks
* Categories or Post Tags in some Views taxonomy-related View loops admin GUIs that rely on the labels.
* This filter takes those internal taxonomies out of our loops.
*
* @param $exclude_tax_slugs (array) The slugs to be excluded.
*
* @return $exclude_tax_slugs
*
* @since unknown
*/

function wpv_admin_exclude_tax_slugs( $exclude_tax_slugs ) {

	// first we exclude the three built-in taxonomies that we want to leave out_items
	if ( ! in_array( 'post_format', $exclude_tax_slugs ) ) {
		$exclude_tax_slugs[] = 'post_format';
	}
	if ( ! in_array( 'link_category', $exclude_tax_slugs ) ) {
		$exclude_tax_slugs[] = 'link_category';
	}
	if ( ! in_array( 'nav_menu', $exclude_tax_slugs ) ) {
		$exclude_tax_slugs[] = 'nav_menu';
	}

	// WP RSS Aggregator issue: https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/171941369/comments
	// Filtering out an internal custom taxonomy with slug wp_log_type

	if ( ! in_array( 'wp_log_type', $exclude_tax_slugs ) ) {
		$exclude_tax_slugs[] = 'wp_log_type';
	}

	return $exclude_tax_slugs;
}

/**
* wpv_admin_exclude_post_type_slugs
*
* Applied in the filter wpv_admin_exclude_post_type_slugs, returns an array of post type slugs that are left out in some database calls.
*
* We are using this, for example, in the target suggest script for parametric search, as we do not want to offer some post types as available targets.
*
* @param $exclude_post_type_slugs (array) The slugs to be excluded.
*
* @return $exclude_post_type_slugs
*
* @since 1.7
*/

function wpv_admin_exclude_post_type_slugs( $exclude_post_type_slugs ) {
	// Exclude al non-public post types
	$exclude_args = array(
	   'public'   => false
	);
	$exclude_output = 'names';
	$exclude_post_types = get_post_types( $exclude_args, $exclude_output );
	foreach ( $exclude_post_types as $exclude_p_t ) {
		if ( ! in_array( $exclude_p_t, $exclude_post_type_slugs ) ) {
			$exclude_post_type_slugs[] = $exclude_p_t;
		}
	}
	// Leave out all the Toolset post types - the above one takes out the Types field groups ones
	if ( ! in_array( 'view', $exclude_post_type_slugs ) ) {
		$exclude_post_type_slugs[] = 'view';
	}
	if ( ! in_array( 'view-template', $exclude_post_type_slugs ) ) {
		$exclude_post_type_slugs[] = 'view-template';
	}
	if ( ! in_array( 'cred-form', $exclude_post_type_slugs ) ) {
		$exclude_post_type_slugs[] = 'cred-form';
	}
	if ( ! in_array( 'dd_layouts', $exclude_post_type_slugs ) ) {
		$exclude_post_type_slugs[] = 'dd_layouts';
	}
	// Also leave out revisions
	if ( ! in_array( 'revision', $exclude_post_type_slugs ) ) {
		$exclude_post_type_slugs[] = 'revision';
	}
	return $exclude_post_type_slugs;
}

/**
 * wpv_admin_include_post_type_slugs
 *
 * Applied in the filter wpv_admin_include_post_type_slugs, returns an array of post type slugs that are included in some database calls.
 *
 * @param $include_post_type_slugs (array) The slugs to be included.
 *
 * @return array $include_post_type_slugs
 *
 * @since 2.3.1
 */

function wpv_admin_include_post_type_slugs( $include_post_type_slugs ) {
    // Include al non-public post types
    $include_args = array(
        'public'   => true
    );
    $include_output = 'names';
    $include_post_types = get_post_types( $include_args, $include_output );
    foreach ( $include_post_types as $include_p_t ) {
        if ( ! in_array( $include_p_t, $include_post_type_slugs ) ) {
            $include_post_type_slugs[] = $include_p_t;
        }
    }

    return $include_post_type_slugs;
}

/**
 * wpv_admin_available_spinners
 *
 * Applied in the filter wpv_admin_available_spinners, returns an array of default available spinners used in pagination and parametric search.
 *
 * Note that this filter is hooked at priority 5 and sets the basic existing spinners, so further spinners should be added at a later priority.
 *
 * @param $available_spinners (array) The spinners to be offered.
 *
 * @return array $available_spinners
 *
 * @since 1.7
 */
function wpv_admin_available_spinners( $available_spinners ) {
	$available_spinners = array(
		array(
			'title'	=> __( 'Spinner #1', 'wpv-views' ),
			'url'	=> WPV_URL_EMBEDDED . '/res/img/ajax-loader.gif'
		),
		array(
			'title'	=> __( 'Spinner #2', 'wpv-views' ),
			'url'	=> WPV_URL_EMBEDDED . '/res/img/ajax-loader2.gif'
		),
		array(
			'title'	=> __( 'Spinner #3', 'wpv-views' ),
			'url'	=> WPV_URL_EMBEDDED . '/res/img/ajax-loader3.gif'
		),
		array(
			'title'	=> __( 'Spinner #4', 'wpv-views' ),
			'url'	=> WPV_URL_EMBEDDED . '/res/img/ajax-loader4.gif'
		),
		array(
			'title'	=> __( 'Spinner #5', 'wpv-views' ),
			'url'	=> WPV_URL_EMBEDDED . '/res/img/ajax-loader-overlay.gif'
		)
	);
	return $available_spinners;
}


/**
 * Return array of possible attributes for view shortcode
 *
 * @param $view_id The ID of the relevant View.
 *
 * @return Numeric array of possible attributes for $view_id.
 *
 * Output example:
 * 			'query_type' => posts|taxonomy|users
 * 			'filter_type' => filter that this attribute is used on (post_id, post_author, etc..)
 * 			'value' => filter from where attribute getting data
 * 			'attribute' => the actual shortcode attribute
 * 			'expected' => input data type integer|string|numeric
 *
 * Usage example:  <?php print_r( get_view_allowed_attributes( 80 ) ); ?>
 *
 * @todo review the 'value' entry
 */
function get_view_allowed_attributes( $view_id ) {
	$attributes = array();
	if ( empty( $view_id ) ) {
		return $attributes;
	}
	global $WP_Views;
	$view_settings = $WP_Views->get_view_settings( $view_id );
	if ( 
		is_array( $view_settings ) 
		&& isset( $view_settings['view-query-mode'] )
		&& $view_settings['view-query-mode'] == 'normal'
		&& isset( $view_settings['query_type'][0] )
	) {
		$query_type = $view_settings['query_type'][0];
		$attributes = apply_filters( 'wpv_filter_register_shortcode_attributes_for_' . $query_type, $attributes, $view_settings );
		// Post View
		if ( $view_settings['query_type'][0] == 'posts' ) {
			foreach ( $view_settings as $key => $value ) {
				// Taxonomy
				if ( 
					preg_match( "/tax_(.*)_relationship/", $key, $res ) 
					&& $value == 'FROM ATTRIBUTE' 
				) {
					$taxonomy = $res[1];
					if ( taxonomy_exists( $taxonomy ) ) {
						$attributes[] = array(
							'query_type'	=> $view_settings['query_type'][0],
							'filter_type'	=> 'post_taxonomy_' . $taxonomy,
							'filter_label'	=> sprintf( __( 'Post taxonomy - %s', 'wpv-views' ), $taxonomy ),
							'value'			=> $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url-format' ][0],
							'attribute'		=> $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url' ],
							'expected'		=> 'string',
							'placeholder'	=> ( $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url-format' ][0] == 'slug' ) ? 'cat1' : 'Cat 1',
							'description'	=> ( $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url-format' ][0] == 'slug' ) ? __( 'Please type a comma separated list of term slugs', 'wpv-views' ) : __( 'Please type a comma separated list of term names', 'wpv-views' )
						);
					}
				}
				// Custom fields
				if ( 
					preg_match( "/custom-field-(.*)_value/", $key, $res )
					&& preg_match( "/VIEW_PARAM\(([^\)]+)\)/", $value, $shortcode ) 
				) {
					$expected_input_data_type = in_array( $view_settings[ 'custom-field-' . $res[1] . '_type' ], array( 'NUMERIC', 'DATE', 'DATETIME', 'TIME' ) )
							? 'integer'
							: ( ( $view_settings[ 'custom-field-' . $res[1] . '_type' ] == 'DECIMAL' ) ? 'decimal' : 'string' );
					$attributes[] = array(
						'query_type'	=> $view_settings['query_type'][0],
						'filter_type'	=> 'post_custom_field_'. $res[1],
						'filter_label'	=> sprintf( __( 'Custom field - %s', 'wpv-views' ), $res[1] ),
						'value'			=> 'custom_field_value',
						'attribute'		=> $shortcode[1],
						'expected'		=> $expected_input_data_type,
						'placeholder'	=> 'value',
						'description'	=> __( 'Please type a custom field value', 'wpv-views' )
					);
				}
			}
		}
		
		// User View
		if ( $view_settings['query_type'][0] == 'taxonomy' ) {
			foreach ( $view_settings as $key => $value ) {
				// Termmeta fields
				if ( 
					preg_match( "/termmeta-field-(.*)_value/", $key, $res )
					&& preg_match( "/VIEW_PARAM\(([^\)]+)\)/", $value, $shortcode ) 
				) {
					$expected_input_data_type = in_array( $view_settings[ 'termmeta-field-' . $res[1] . '_type' ], array('NUMERIC','DATE','DATETIME','TIME') )
							? 'integer'
							: ( ( $view_settings[ 'termmeta-field-' . $res[1] . '_type' ] == 'DECIMAL' ) ? 'decimal' : 'string' );
					$attributes[] = array(
						'query_type'	=> $view_settings['query_type'][0],
						'filter_type'	=> 'taxonomy_termmeta_field_'. $res[1],
						'filter_label'	=> sprintf( __( 'Termmeta field - %s', 'wpv-views' ), $res[1] ),
						'value'			=> 'termmeta_field_value',
						'attribute'		=> $shortcode[1],
						'expected'		=> $expected_input_data_type,
						'placeholder'	=> 'value',
						'description'	=> __( 'Please type a termmeta field value', 'wpv-views' )
					);
				}
			}
		}

		// User View
		if ( $view_settings['query_type'][0] == 'users' ) {
			foreach ( $view_settings as $key => $value ) {
				// Usermeta fields
				if ( 
					preg_match( "/usermeta-field-(.*)_value/", $key, $res )
					&& preg_match( "/VIEW_PARAM\(([^\)]+)\)/", $value, $shortcode ) 
				) {
					$expected_input_data_type = in_array( $view_settings[ 'usermeta-field-' . $res[1] . '_type' ], array('NUMERIC','DATE','DATETIME','TIME') )
							? 'integer'
							: ( ( $view_settings[ 'usermeta-field-' . $res[1] . '_type' ] == 'DECIMAL' ) ? 'decimal' : 'string' );
					$attributes[] = array(
						'query_type'	=> $view_settings['query_type'][0],
						'filter_type'	=> 'user_usermeta_field_'. $res[1],
						'filter_label'	=> sprintf( __( 'Usermeta field - %s', 'wpv-views' ), $res[1] ),
						'value'			=> 'usermeta_field_value',
						'attribute'		=> $shortcode[1],
						'expected'		=> $expected_input_data_type,
						'placeholder'	=> 'value',
						'description'	=> __( 'Please type an username field value', 'wpv-views' )
					);
				}
			}
		}
	}

	return $attributes;
}

/**
 * Return array of possible attributes for View URL parameters
 *
 * @param $view_id The ID of the relevant View.
 *
 * @return Numeric array of possible URL parameters for $view_id.
 *
 * Output example:
 * 			'query_type' => posts|taxonomy|users
 * 			'filter_type' => filter that this attribute is used on (post_id, post_author, etc..)
 * 			'value' => filter from where attribute getting data
 * 			'attribute' => the actual url parameter
 * 			'expected' => input data type integer|string|numeric
 *
 * Usage example:  <?php print_r( get_view_allowed_url_parameters( 80 ) ); ?>
 *
 * @todo review the 'value' entry
 */
function get_view_allowed_url_parameters( $view_id ) {
	$attributes = array();
	if ( empty( $view_id ) ) {
		return $attributes;
	}
	global $WP_Views;
	$view_settings = $WP_Views->get_view_settings( $view_id );
	$query_type = '';
	
	if (
		is_array( $view_settings ) 
		&& isset( $view_settings['view-query-mode'] )
	) {
		switch ( $view_settings['view-query-mode'] ) {
			case 'normal':
				if ( isset( $view_settings['query_type'][0] ) ) {
					$query_type = $view_settings['query_type'][0];
				}
				break;
			default:
				$query_type = 'posts';
				break;
		}
	}
	
	$attributes = apply_filters( 'wpv_filter_register_url_parameters_for_' . $query_type, $attributes, $view_settings );
	$meta_value_pattern = '/URL_PARAM\(([^(]*?)\)/siU';
	
	switch ( $query_type ) {
		case 'posts':
			foreach ( $view_settings as $key => $value ) {
				// Taxonomy
				if ( 
					preg_match( "/tax_(.*)_relationship/", $key, $res ) 
					&& $value == 'FROM URL' 
				) {
					$taxonomy = $res[1];
					if ( taxonomy_exists( $taxonomy ) ) {
						$attributes[] = array(
							'query_type'	=> $view_settings['query_type'][0],
							'filter_type'	=> 'post_taxonomy_' . $taxonomy,
							'filter_label'	=> sprintf( __( 'Post taxonomy - %s', 'wpv-views' ), $taxonomy ),
							'value'			=> $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url-format' ][0],
							'attribute'		=> $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url' ],
							'expected'		=> 'string',
							'placeholder'	=> ( $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url-format' ][0] == 'slug' ) ? 'cat1' : 'Cat 1',
							'description'	=> ( $view_settings[ 'taxonomy-' . $taxonomy . '-attribute-url-format' ][0] == 'slug' ) ? __( 'Please type a comma separated list of term slugs', 'wpv-views' ) : __( 'Please type a comma separated list of term names', 'wpv-views' )
						);
					}
				}
				// Custom fields
				if ( 
					preg_match( "/custom-field-(.*)_value/", $key, $res )
					&& preg_match_all( $meta_value_pattern, $value, $matches_postmeta, PREG_SET_ORDER )
				) {
					$expected_input_data_type = in_array( $view_settings[ 'custom-field-' . $res[1] . '_type' ], array( 'NUMERIC', 'DATE', 'DATETIME', 'TIME' ) )
							? 'integer'
							: ( ( $view_settings[ 'custom-field-' . $res[1] . '_type' ] == 'DECIMAL' ) ? 'decimal' : 'string' );
					foreach( $matches_postmeta as $index => $match ) {
						$attributes[] = array(
							'query_type'	=> $view_settings['query_type'][0],
							'filter_type'	=> 'post_custom_field_'. $res[1] . '_' . $index,
							'filter_label'	=> sprintf( __( 'Custom field - %s', 'wpv-views' ), $res[1] ),
							'value'			=> 'custom_field_value',
							'attribute'		=> $match[1],
							'expected'		=> $expected_input_data_type,
							'placeholder'	=> 'value',
							'description'	=> __( 'Please type a custom field value', 'wpv-views' )
						);
					}
				}
			}
			break;
		case 'taxonomy':
			foreach ( $view_settings as $key => $value ) {
				// Termmeta fields
				if ( 
					preg_match( "/termmeta-field-(.*)_value/", $key, $res )
					&& preg_match_all( $meta_value_pattern, $value, $matches_termmeta, PREG_SET_ORDER )
				) {
					$expected_input_data_type = in_array( $view_settings[ 'termmeta-field-' . $res[1] . '_type' ], array('NUMERIC','DATE','DATETIME','TIME') )
							? 'integer'
							: ( ( $view_settings[ 'termmeta-field-' . $res[1] . '_type' ] == 'DECIMAL' ) ? 'decimal' : 'string' );
					foreach( $matches_termmeta as $index => $match ) {
						$attributes[] = array(
							'query_type'	=> $view_settings['query_type'][0],
							'filter_type'	=> 'taxonomy_termmeta_field_'. $res[1] . '_' . $index,
							'filter_label'	=> sprintf( __( 'Termmeta field - %s', 'wpv-views' ), $res[1] ),
							'value'			=> 'termmeta_field_value',
							'attribute'		=> $match[1],
							'expected'		=> $expected_input_data_type,
							'placeholder'	=> 'value',
							'description'	=> __( 'Please type a termmeta field value', 'wpv-views' )
						);
					}
				}
			}
			break;
		case 'users':
			foreach ( $view_settings as $key => $value ) {
				// Usermeta fields
				if ( 
					preg_match( "/usermeta-field-(.*)_value/", $key, $res )
					&& preg_match_all( $meta_value_pattern, $value, $matches_usermeta, PREG_SET_ORDER )
				) {
					$expected_input_data_type = in_array( $view_settings[ 'usermeta-field-' . $res[1] . '_type' ], array('NUMERIC','DATE','DATETIME','TIME') )
							? 'integer'
							: ( ( $view_settings[ 'usermeta-field-' . $res[1] . '_type' ] == 'DECIMAL' ) ? 'decimal' : 'string' );
					foreach( $matches_usermeta as $index => $match ) {
						$attributes[] = array(
							'query_type'	=> $view_settings['query_type'][0],
							'filter_type'	=> 'user_usermeta_field_'. $res[1] . '_' . $index,
							'filter_label'	=> sprintf( __( 'Usermeta field - %s', 'wpv-views' ), $res[1] ),
							'value'			=> 'usermeta_field_value',
							'attribute'		=> $match[1],
							'expected'		=> $expected_input_data_type,
							'placeholder'	=> 'value',
							'description'	=> __( 'Please type an username field value', 'wpv-views' )
						);
					}
				}
			}
			break;
	}

	return $attributes;
}
