<?php
 
if ( ! class_exists( 'WPV_Shortcode_Generator' ) ) {
	
	/**
	 * Shortcodes generator class for Views.
	 *
	 * Inherits from Toolset_Shortcode_Generator which is the base class 
	 * used to register items in the backend Admin Bar item for Toolset shortcodes.
	 * Since 2.3.0 it is also used to generate the Fields and Views editor buttons 
	 * and the dialogs for inserting shortcodes using the Shortcodes GUI API.
	 *
	 * @since unknown
	 * @since 2.3.0 Used to generate the Fields and Views buttons and dialogs.
	 */
	
	class WPV_Shortcode_Generator extends Toolset_Shortcode_Generator {
		
		const API_VERSION = 260000;
		
		public $admin_bar_item_registered	= false;
		public $dialog_groups				= array();
		public $footer_dialogs				= '';
		public $footer_dialogs_needing		= array();
		public $footer_dialogs_existing		= array();
		public $footer_dialogs_added		= false;

		/**
		 * @note Runs at after_setup_theme::999
		 *
		 * @since unknown
		 */
	    public function initialize() {
			
			/**
			 * Canonical filter to get the Views shortcodes GUI API version number.
			 *
			 * @since m2m
			 */
			add_filter( 'wpv_filter_wpv_get_shortcodes_api_version', array( $this, 'get_api_version' ) );
			
			/**
			 * ---------------------
			 * Admin Bar
			 * ---------------------
			 */
			
			// Track whether the Admin Bar item has been registered
			$this->admin_bar_item_registered		= false;
			// Register the Fields and Views item in the backend Admin Bar
			add_filter( 'toolset_shortcode_generator_register_item',	array( $this, 'register_fields_and_views_shortcode_generator' ) );
			
			/**
			 * ---------------------
			 * Fields and Views button and dialogs
			 * ---------------------
			 */
			
			// Initialize dialog groups and the action to register them
			$this->dialog_groups					= array();
			add_action( 'wpv_action_collect_shortcode_groups', array( $this, 'register_top_builtin_groups' ), 1 );
			add_action( 'wpv_action_collect_shortcode_groups', array( $this, 'register_bottom_builtin_groups' ), 100 );
			add_action( 'wpv_action_register_shortcode_group', array( $this, 'register_shortcode_group' ), 10, 2 );
			add_action( 'wpv_action_wpv_register_dialog_group',	array( $this, 'register_shortcode_group' ), 10, 2 );
			
			add_filter( 'wpv_filter_wpv_get_shortcode_groups', array( $this, 'get_shortcode_groups' ) );
			
			// Fields and Views button in native editors plus on demand:
			// - From media_buttons actions, for posts, taxonomy or users depending on the current edit page
			// - From Views inner actions, for posts, taxonomy or users
			// - From Toolset arbitrary editor toolbars, for posts
			add_action( 'media_buttons',										array( $this, 'generate_fields_and_views_button' ) );
			add_action( 'wpv_action_wpv_generate_fields_and_views_button',		array( $this, 'generate_fields_and_views_button' ), 10, 2 );
			add_action( 'toolset_action_toolset_editor_toolbar_add_buttons',	array( $this, 'generate_fields_and_views_custom_button' ), 10, 2 );
			
			// External call to ensure that a dialog for a given target will be generated, used in Views edit pages
			add_action( 'wpv_action_wpv_require_shortcodes_dialog_target', array( $this, 'require_shortcodes_dialog_target' ) );
			// Generate the shortcodes dialog for a given target. Called from render_footer_dialogs.
			// In native editors, they target 'posts'; on demand they can also target 'taxonomy' or 'users'.
			add_action( 'wpv_action_wpv_generate_shortcodes_dialog',       array( $this, 'generate_shortcodes_dialog' ) );
			
			// Make sure at least the 'posts' dialog is added, even on pages without editors, if the settings state so, 
			// or if we are in a backend editor or frontend editor page, 
			// or if we want to force it with the wpv_filter_wpv_force_generate_fields_and_views_dialog filter
			add_action( 'wp_footer',				array( $this, 'force_fields_and_views_dialog_shortcode_generator' ), 1 );
			add_action( 'admin_footer',				array( $this, 'force_fields_and_views_dialog_shortcode_generator' ), 1 );
			
			// Track whether dialogs re needed and have been rendered in the footer
			$this->footer_dialogs					= '';
			$this->footer_dialogs_needing			= array();
			$this->footer_dialogs_existing			= array();
			$this->footer_dialogs_added				= false;
			
			// Generate and print the shortcodes dialogs in the footer,
			// both in frotend and backend, as long as there is anything to print.
			// Do it as late as possible because page builders tend to register their templates,
			// including native WP editors, hence shortcode buttons, in wp_footer:10.
			// Also, because this way we can extend the dialog groups for almost the whole page request.
			// Substract 10 so this runs slightly earlier than the Types version, 
			// so Types groups are collected by Views firts.
			add_action( 'wp_footer',				array( $this, 'render_footer_dialogs' ), ( PHP_INT_MAX - 10 ) );
			add_action( 'admin_footer',				array( $this, 'render_footer_dialogs' ), ( PHP_INT_MAX - 10 ) );
			
			/**
			 * ---------------------
			 * Assets
			 * ---------------------
			 */
			 
			// Register shortcodes dialogs assets
			add_action( 'init',											array( $this, 'register_assets' ) );
			add_action( 'wp_enqueue_scripts',							array( $this, 'frontend_enqueue_assets' ) );
			add_action( 'admin_enqueue_scripts',						array( $this, 'admin_enqueue_assets' ) );
			
			// Ensure that shortcodes dialogs assets re enqueued
			// both when using the Admin Bar item and when a Fields and Views button is on the page.
			add_action( 'wpv_action_wpv_enforce_shortcodes_assets', 	array( $this, 'enforce_shortcodes_assets' ) );
			
			/**
			 * ---------------------
			 * Compatibility
			 * ---------------------
			 */
			 
			add_filter( 'gform_noconflict_scripts',	array( $this, 'gform_noconflict_scripts' ) );
			add_filter( 'gform_noconflict_styles',	array( $this, 'gform_noconflict_styles' ) );
			
	    }
		
		/**
		 * Get the shortcodes API version.
		 *
		 * This version is updated on each groundbreaking chnage that can affect third parties hooking into it.
		 * Its value will always be the Views target version for the change, as in XYZAAA, where:
		 * XYZ matches the version numbr X.Y.Z
		 * AAA is an increentl integer that supports up to 100 changes per development cycle.
		 *
		 * @param int $version
		 *
		 * @return int
		 *
		 * @since m2m
		 */
		public function get_api_version( $version ) {
			return self::API_VERSION;
		}
		
		public function get_shortcode_groups( $shortcode_groups = array() )  {
			return $this->dialog_groups;
		}
		
		/**
		 * Register the Fields and Views shortcode generator in the Toolset shortcodes admin bar entry.
		 *
		 * Hooked into the toolset_shortcode_generator_register_item filter.
		 * 
		 * @since unknown
		 */
		
		public function register_fields_and_views_shortcode_generator( $registered_sections ) {
			$this->admin_bar_item_registered = true;
			do_action( 'wpv_action_wpv_enforce_shortcodes_assets' );
			$registered_sections[ 'fields_and_views' ] = array(
				'id'		=> 'fields-and-views',
				'title'		=> __( 'Fields and Views', 'wpv-views' ),
				'href'		=> '#fields_and_views_shortcodes',
				'parent'	=> 'toolset-shortcodes',
				'meta'		=> 'js-wpv-shortcode-generator-node'
			);
			return $registered_sections;
		}
		
		/**
		 * Register all the dedicated shortcodes assets:
		 * - Shortcodes GUI script.
		 *
		 * @todo Move the assets registration to here
		 *
		 * @since 2.3.0
		 * @todo Actually register th shortcodes asets from here, using the Toolset Assets Manager class.
		 */
		
		function register_assets() {
			// Register here the shortcodes GUI script and see which CSS is needed
		}
		
		/**
		 * Enforce some assets that need to be in the frontend header, like styles, 
		 * when we detect that we are on a page that needs them.
		 * Basically, this involves frontend page builders, detected by their own methods.
		 * Also enforces the generation of the Fields and Views dialog, just in case, in the footer.
		 *
		 * @uses is_frontend_editor_page which is a parent method.
		 *
		 * @since 2.3.0
		 */
		
		function frontend_enqueue_assets() {
			// Enqueue on the frontend pages that we know it is needed, maybe on users frontend editors only
			
			if ( $this->is_frontend_editor_page() ) {
				$this->enforce_shortcodes_assets();
				add_filter( 'wpv_filter_wpv_force_generate_fields_and_views_dialog', '__return_true' );
			}
			
		}
		
		/**
		 * Enforce some assets that need to be in the backend header, like styles, 
		 * when we detect that we are on a page that needs them.
		 * Also enforces the generation of the Fields and Views dialog, just in case, in the footer.
		 *
		 * Note that register_fields_and_views_shortcode_generator happens on admin_init:99 
		 * so by admin_enqueue_scripts:10 we already know whether the Admin Bar item is registered or not, 
		 * hence $this->admin_bar_item_registered is a valid flag.
		 *
		 * Note that we enforce the shortcode assets in all known admin editor pages.
		 *
		 * @uses is_admin_editor_page which is a parent method.
		 *
		 * @since 2.3.0
		 */
		
		function admin_enqueue_assets( $hook ) {
			if ( 
				$this->admin_bar_item_registered 
				|| $this->is_admin_editor_page()
			) {
				$this->enforce_shortcodes_assets();
				add_filter( 'wpv_filter_wpv_force_generate_fields_and_views_dialog', '__return_true' );
			} else {
				/**
				 * When the Admin Bar item is not registered, we still know that some 
				 * admin pages demand those assets.
				 */
			}
		}
		
		/**
		 * Enfoces the shortcodes assets when loaded at a late time.
		 * Note that there should be no problem with scripts, 
		 * although styles might not be correctly enqueued.
		 *
		 * @usage do_action( 'wpv_action_wpv_enforce_shortcodes_assets' );
		 *
		 * @since 2.3.0
		 * @todo Create proper Views, or Toolset, dialog styles.
		 */
		
		function enforce_shortcodes_assets() {
			
			wp_enqueue_script( 'views-shortcodes-gui-script' );
			wp_enqueue_style( 'views-admin-css' );
			do_action( 'toolset_enqueue_styles', array( 
				Toolset_Assets_Manager::STYLE_TOOLSET_COMMON, 
				Toolset_Assets_Manager::STYLE_TOOLSET_DIALOGS_OVERRIDES, 
				Toolset_Assets_Manager::STYLE_NOTIFICATIONS
			) );
			
			do_action( 'otg_action_otg_enforce_styles' );
			
		}
		
		/**
		 * Register the first shortcode groups in the API so they appear early in the dialog.
		 *
		 * @since m2m
		 */
		public function register_top_builtin_groups() {
			
			$this->register_post_data_group();
			$this->preregister_post_taxonomy_group();
			$this->register_taxonomy_group();
			$this->register_user_data_group();
			$this->register_basic_data_group();
			
		}
		
		/**
		 * Register the last shortcode groups in the API so they appear late in the dialog.
		 *
		 * @since m2m
		 */
		public function register_bottom_builtin_groups() {
			
			$this->register_wpv_theme_option_shortcode();
			$this->populate_post_taxonomy_group();
			$this->register_generic_post_fields_group();
			$this->register_password_management_group();
			$this->register_content_templates_group();
			$this->register_view_groups();
			
		}
		
		/**
		 * Register the wpv-post-xxx shortcodes in the API.
		 *
		 * @since m2m
		 */
		private function register_post_data_group() {
			$group_id	= 'post';
			$group_data	= array(
				'name'		=> __( 'Standard WordPress Fields', 'wpv-views' ),
				'target'	=> array( 'posts' ),
				'fields'	=> array(),
				'documentation' => array(
					'handle' => __( 'What are these fields?', 'wpv-views' ),
					'url' => 'https://toolset.com/documentation/user-guides/standard-wordpress-fields/'
				)
			);
			
			$post_shortcodes = array(
				'wpv-post-title'			=> __( 'Post title', 'wpv-views' ),
				'wpv-post-link'				=> __( 'Post title with a link', 'wpv-views' ),
				'wpv-post-url'				=> __( 'Post URL', 'wpv-views' ),
				'wpv-post-body'				=> __( 'Post body', 'wpv-views' ),
				'wpv-post-excerpt'			=> __( 'Post excerpt', 'wpv-views' ),
				'wpv-post-date'				=> __( 'Post date', 'wpv-views' ),
				'wpv-post-author'			=> __( 'Post author', 'wpv-views' ),
				'wpv-post-featured-image'	=> __( 'Post featured image', 'wpv-views' ),
				'wpv-post-id'				=> __( 'Post ID', 'wpv-views' ),
				'wpv-post-slug'				=> __( 'Post slug', 'wpv-views' ),
				'wpv-post-type'				=> __( 'Post type', 'wpv-views' ),
				'wpv-post-format'			=> __( 'Post format', 'wpv-views' ),
				'wpv-post-status'			=> __( 'Post status', 'wpv-views' ),
				'wpv-post-comments-number'	=> __( 'Post comments number', 'wpv-views' ),
				'wpv-post-class'			=> __( 'Post class', 'wpv-views' ),
				'wpv-post-edit-link'		=> __( 'Post edit link', 'wpv-views' ),
				'wpv-post-menu-order'		=> __( 'Post menu order', 'wpv-views' ),
				'wpv-post-field'			=> __( 'Post field', 'wpv-views' ),
				'wpv-for-each'				=> __( 'Post field iterator', 'wpv-views' ),
				'wpv-post-previous-link'	=> __( 'Post previous link', 'wpv-views' ),
				'wpv-post-next-link'		=> __( 'Post next link', 'wpv-views' ),
			);
			foreach ( $post_shortcodes as $post_shortcode_slug => $post_shortcode_title ) {
				$group_data['fields'][ $post_shortcode_slug ] = array(
					'name'		=> $post_shortcode_title,
					'handle'	=> $post_shortcode_slug,
					'shortcode'	=> '[' . $post_shortcode_slug . ']',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: '" . esc_js( $post_shortcode_slug ) . "', title: '" . esc_js( $post_shortcode_title ) . "' })"
				);
			}
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Preregister the wpv-post-taxonomy shortcodes so they get the right order.
		 *
		 * @since m2m
		 */
		private function preregister_post_taxonomy_group() {
			$group_id	= 'post-taxonomy';
			$group_data	= array(
				'name'		=> __( 'Taxonomy', 'wpv-views' ),
				'target'	=> array( 'posts' ),
				'fields'	=> array()
			);
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Register the wpv-taxonomy-xxx shortcodes in the API.
		 *
		 * @since m2m
		 */
		private function register_taxonomy_group() {
			$group_id	= 'taxonomy';
			$group_data	= array(
				'name'		=> __( 'Taxonomy data', 'wpv-views' ),
				'target'	=> array( 'taxonomy' ),
				'fields'	=> array()
			);
			
			$taxonomy_shortcodes = array(
				'wpv-taxonomy-title'		=> __( 'Taxonomy title', 'wpv-views' ),
				'wpv-taxonomy-link'			=> __( 'Taxonomy link', 'wpv-views' ),
				'wpv-taxonomy-url'			=> __( 'Taxonomy URL', 'wpv-views' ),
				'wpv-taxonomy-slug'			=> __( 'Taxonomy slug', 'wpv-views' ),
				'wpv-taxonomy-id'			=> __( 'Taxonomy ID', 'wpv-views' ),
				'wpv-taxonomy-description'	=> __( 'Taxonomy description', 'wpv-views' ),
				'wpv-taxonomy-post-count'	=> __( 'Taxonomy post count', 'wpv-views' ),
			);
			foreach ( $taxonomy_shortcodes as $taxonomy_shortcode_slug => $taxonomy_shortcode_title ) {
				$group_data['fields'][ $taxonomy_shortcode_slug ] = array(
					'name'		=> $taxonomy_shortcode_title,
					'handle'	=> $taxonomy_shortcode_slug,
					'shortcode'	=> '[' . $taxonomy_shortcode_slug . ']',
					'callback'	=> ''
				);
			}
			
			$group_data['fields']['wpv-taxonomy-field'] = array(
				'name'		=> __('Taxonomy field', 'wpv-views'),
				'handle'	=> 'wpv-taxonomy-field',
				'shortcode'	=> '[wpv-taxonomy-field]',
				'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-taxonomy-field', title: '" . esc_js( __('Taxonomy field', 'wpv-views') ). "' })"
			);
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Register the remaining basic shortcodes in the API.
		 *
		 * @since m2m
		 */
		private function register_basic_data_group() {
			$group_id	= 'basic';
			$group_data	= array(
				'name'		=> __( 'Basic data', 'wpv-views' ),
				'fields'	=> array()
			);
			
			$basic_shortcodes = array(
				'wpv-bloginfo'			=> __( 'Site information', 'wpv-views' ),
				'wpv-current-user'		=> __( 'Current user information', 'wpv-views' ),
				'wpv-archive-link'		=> __( 'Post type archive link', 'wpv-views' ),
				'wpv-search-term'		=> __( 'Search term', 'wpv-views' ),
			);
			
			foreach ( $basic_shortcodes as $basic_shortcode_slug => $basic_shortcode_title ) {
				$group_data['fields'][ $basic_shortcode_slug ] = array(
					'name'		=> $basic_shortcode_title,
					'handle'	=> $basic_shortcode_slug,
					'shortcode'	=> '[' . $basic_shortcode_slug . ']',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: '" . esc_js( $basic_shortcode_slug ) . "', title: '" . esc_js( $basic_shortcode_title ) . "' })"
				);
			}
			
			$group_data['fields']['wpv-archive-title'] = array(
				'name'		=> __('Archive title', 'wpv-views'),
				'handle'	=> 'wpv-archive-title',
				'shortcode'	=> '[wpv-archive-title]',
				'callback'	=> ''
			);
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Register the wpv-user shortcodes in the API.
		 *
		 * @since m2m
		 */
		private function register_user_data_group() {
			$shorcode_fields = array(
				'ID'				=> __( 'User ID', 'wpv-views' ),
				'user_email'		=> __( 'User Email', 'wpv-views' ),
				'user_login'		=> __( 'User Login', 'wpv-views' ),
				'user_firstname'	=> __( 'First Name', 'wpv-views' ),
				'user_lastname'		=> __( 'Last Name', 'wpv-views' ),
				'nickname'			=> __( 'Nickname', 'wpv-views' ),
				'display_name'		=> __( 'Display Name', 'wpv-views' ),
				'profile_picture'	=> __( 'Profile Picture', 'wpv-views' ),
				'user_nicename'		=> __( 'Nicename', 'wpv-views' ),
				'description'		=> __( 'Description', 'wpv-views' ),
				'yim'				=> __( 'Yahoo IM', 'wpv-views' ),
				'jabber'			=> __( 'Jabber', 'wpv-views' ),
				'aim'				=> __( 'AIM', 'wpv-views' ),
				'user_url'			=> __( 'User URL', 'wpv-views' ),
				'user_registered'	=> __( 'Registration Date', 'wpv-views' ),
				'user_status'		=> __( 'User Status', 'wpv-views' ),
				'spam'				=> __( 'User Spam Status', 'wpv-views' )
			);
			
			$group_id	= 'user';
			$group_data	= array(
				'name'		=> __( 'User data', 'wpv-views' ),
				'fields'	=> array()
			);
			
			foreach ( $shorcode_fields as $field => $field_title ) {
				$group_data['fields']['wpv-user-' . $field] = array(
					'name'		=> $field_title,
					'handle'	=> 'wpv-user',
					'shortcode'	=> '[wpv-user field="' . $field . '"]',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-user', title: '" . esc_js( $field_title ) . "', params: {attributes:{field:'" . esc_js( $field ) . "'}} })"
				);
			}
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Include the frameworks integration wpv-theme-option shortcode in the Basic data group.
		 *
		 * @since m2m
		 */
		private function register_wpv_theme_option_shortcode() {
			if (
				apply_filters( 'wpv_filter_framework_has_valid_framework', false ) 
				&& apply_filters( 'wpv_filter_framework_count_registered_keys', 0 ) > 0
			) {
				
				$group_id	= 'basic';
				$group_data	= array(
					'fields'	=> array(
						'wpv-theme-option' => array(
							'name'		=> __( 'Theme option', 'wpv-views' ),
							'handle'	=> 'wpv-theme-option',
							'shortcode'	=> '[wpv-theme-option]',
							'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-theme-option', title: '" . esc_js( __( 'Theme option', 'wpv-views' ) ) . "' })"
						)
					)
				);
				
				do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
				
			}
		}
		
		/**
		 * Populate the Taxonomy group with actual taxonomies.
		 *
		 * @since m2m
		 */
		private function populate_post_taxonomy_group() {
			$group_id	= 'post-taxonomy';
			$group_data	= array();
			
			$taxonomies = get_taxonomies('', 'objects');
			$exclude_tax_slugs = array();
			$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
			foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
				if ( 
					in_array( $taxonomy_slug, $exclude_tax_slugs ) 
					|| ! $taxonomy->show_ui
				) {
					continue;
				}
				$group_data['fields'][ $taxonomy_slug ] = array(
					'name'		=> $taxonomy->label,
					'handle'    => 'wpv-post-taxonomy',
					'shortcode'	=> '[wpv-post-taxonomy type="' . esc_attr( $taxonomy_slug ) . '"]',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'wpv-post-taxonomy', title: '" . esc_js( $taxonomy->label ) . "', params: {attributes:{type:'" . esc_js( $taxonomy_slug ) . "'}} })"
				);
			}
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Register the generic post fields group in the API.
		 * This group is populated on demand inside the dialog.
		 *
		 * @since m2m
		 */
		private function register_generic_post_fields_group() {
			$group_id	= 'non-types-post-fields';
			$group_data	= array(
				'name'		=> __( 'Post fields', 'wpv-views' ),
				'target'	=> array( 'posts' ),
				'fields'	=> array(
					'wpv-non-types-fields' => array(
						'name'		=> __( 'Load non-Types custom fields', 'wpv-views' ),
						'handle'	=> '',
						'shortcode'	=> '',
						'callback'	=> "WPViews.shortcodes_gui.load_post_field_section_on_demand( event, this )"
					)
				)
			);
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Register the Paswword management shortcodes group in the API.
		 *
		 * @since m2m
		 */
		private function register_password_management_group() {
			$group_id	= 'password-management';
			$group_data	= array(
				'name'		=> __( 'Password management', 'wpv-views' ),
				'fields'	=> array()
			);
			
			$password_shortcodes = array(
				'wpv-login-form'			=> __( 'Login form', 'wpv-views' ),
				'wpv-logout-link'			=> __( 'Logout link', 'wpv-views' ),
				'wpv-forgot-password-form'	=> __( 'Forgot password form', 'wpv-views' ),
				'wpv-reset-password-form'	=> __( 'Reset password form', 'wpv-views' ),
			);
			
			foreach ( $password_shortcodes as $password_shortcode_slug => $password_shortcode_title ) {
				$group_data['fields'][ $password_shortcode_slug ] = array(
					'name'		=> $password_shortcode_title,
					'handle'	=> $password_shortcode_slug,
					'shortcode'	=> '[' . $password_shortcode_slug . ']',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: '" . esc_js( $password_shortcode_slug ) . "', title: '" . esc_js( $password_shortcode_title ) . "' })"
				);
			}
			
			do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		}
		
		/**
		 * Get the list of IDs for Content Templates that should not be offered in the shortcode GUI.
		 *
		 * When editing a CT, we should not offer to insert a shortcode to render itself. Also, those 
		 * CTs used as loop wrappers for Views or WPAs shoudl not be used separatedly.
		 *
		 * @return array
		 *
		 * @since m2m
		 */
		private function get_content_templates_to_exclude() {
			global $pagenow;
			
			$values_to_exclude = array();
			
			// Exclude current post, for CTs on user editors that get fired on the native post edit page.
			if (
				in_array( $pagenow, array( 'post.php' ) )
				&& isset( $_GET["post"] )
				&& is_numeric( $_GET["post"] )
			) {
				$values_to_exclude[] = (int) $_GET["post"];
			}
			
			// Exclude current Content Template
			if ( 
				isset( $_GET["page"] ) 
				&& 'ct-editor' == $_GET["page"]
				&& isset( $_GET["ct_id"] )
				&& is_numeric( $_GET["ct_id"] )
			) {
				$values_to_exclude[] = (int) $_GET["ct_id"];
			}
			
			// Exclude all Loop Templates
			$exclude_loop_templates_ids = wpv_get_loop_content_template_ids();
			if ( count( $exclude_loop_templates_ids ) > 0 ) {
				$exclude_loop_templates_ids_sanitized = array_map( 'esc_attr', $exclude_loop_templates_ids );
				$exclude_loop_templates_ids_sanitized = array_map( 'trim', $exclude_loop_templates_ids_sanitized );
				// is_numeric + intval does sanitization
				$exclude_loop_templates_ids_sanitized = array_filter( $exclude_loop_templates_ids_sanitized, 'is_numeric' );
				$exclude_loop_templates_ids_sanitized = array_map( 'intval', $exclude_loop_templates_ids_sanitized );
				if ( count( $exclude_loop_templates_ids_sanitized ) > 0 ) {
					$values_to_exclude = array_merge( $values_to_exclude, $exclude_loop_templates_ids_sanitized );
				}
			}
			
			return $values_to_exclude;
			
		}
		
		/**
		 * Register the Content Templates group in the API.
		 *
		 * @since m2m
		 */
		private function register_content_templates_group() {
			$view_template_available = apply_filters( 'wpv_get_available_content_templates', array() );
			
			if ( 
				is_array( $view_template_available ) 
				&& count( $view_template_available ) > 0
			) {
				// Get the CT IDs that should not be offered in the GUI
				$values_to_exclude = $this->get_content_templates_to_exclude();
				
				$group_id	= 'content-template';
				$group_data	= array(
					'name'		=> __( 'Content Template', 'wpv-views' ),
					'fields'	=> array()
				);
				foreach ( $view_template_available as $view_template ) {
					if ( ! in_array( $view_template->ID, $values_to_exclude ) ) {
						$group_data['fields'][ $view_template->post_name ] = array(
							'name'		=> $view_template->post_title,
							'handle'	=> 'wpv-post-body',
							'shortcode'	=> '[wpv-post-body view_template="' . esc_js( $view_template->post_name ) . '"]',
							'callback'	=> ''
						);
					}
				}
				
				do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
			}
		}
		
		/**
		 * Get the list of IDs for Views that should not be offered in the shortcode GUI.
		 *
		 * When editing a View, we should not offer to insert a shortcode to render itself.
		 *
		 * @return array
		 *
		 * @since m2m
		 */
		private function get_views_to_exclude() {
			$values_to_exclude = array();
			if ( 
				isset( $_GET["page"] ) 
				&& 'views-editor' == $_GET["page"]
				&& isset( $_GET["view_id"] )
				&& is_numeric( $_GET["view_id"] )
			) {
				$values_to_exclude[] = (int) $_GET["view_id"];
			}
			if ( 
				isset( $_POST["action"] ) 
				&& (
					'wpv_loop_wizard_add_field' == $_POST["action"]
					|| 'wpv_loop_wizard_load_saved_fields' == $_POST["action"]
				)
				&& isset( $_POST["view_id"] )
				&& is_numeric( $_POST["view_id"] )
			) {
				$values_to_exclude[] = (int) $_POST["view_id"];
			}
			return $values_to_exclude;
		}
		
		/**
		 * Get the list of published post, term and user Views, to include in the GUI.
		 *
		 * Returns the composed groups with all the data needed to register each of the groups, 
		 * so we avoid looping over the same set of data on self::register_view_groups(), since we do 
		 * need to loop to generate the cache transient.
		 *
		 * @return array
		 *
		 * @since m2m
		 * @note Uses a transient for caching. It gets invalidated on View creation/edition/deletion.
		 */
		private function get_published_views_groups() {
			$values_to_exclude = $this->get_views_to_exclude();
			
			$view_groups = array(
				'posts-view'	=> array(
					'name'		=> __( 'Post View', 'wpv-views' ),
					'fields'	=> array()
				),
				'taxonomy-view'	=> array(
					'name'		=> __( 'Taxonomy View', 'wpv-views' ),
					'fields'	=> array()
				),
				'users-view'	=> array(
					'name'		=> __( 'User View', 'wpv-views' ),
					'fields'	=> array()
				)
			);
			
			$views_objects = apply_filters( 'wpv_get_available_views', array() );

			$view_query_types = array( 'posts', 'taxonomy', 'users' );
			foreach ( $view_query_types as $view_type ) {
				if ( 
					isset( $views_objects[ $view_type ] )
					&& is_array( $views_objects[ $view_type ] )
				) {
					foreach ( $views_objects[ $view_type ] as $view ) {
						if ( ! in_array( $view->ID, $values_to_exclude ) ) {
							$view_groups[ $view_type . '-view' ]['fields'][ $view->post_name ] = array(
								'name'		=> $view->post_title,
								'handle'	=> 'wpv-view',
								'shortcode'	=> '[wpv-view name="' . esc_html( $view->post_name ) . '"]',
								'callback'	=> 'WPViews.shortcodes_gui.wpv_insert_view_shortcode_dialog_open({view_id:\'' . esc_js( $view->ID ) . '\', view_title: \'' . esc_js( $view->post_title ) . '\', view_name:\'' . esc_js( $view->post_name ) . '\'})'
							);
						}
					}
				}
			}
			
			return $view_groups;
		}
		
		/**
		 * Register the Post Views, Term Views and User Views shortcodes in the API.
		 *
		 * @since m2m
		 */
		private function register_view_groups() {
			$view_groups = $this->get_published_views_groups();
			
			foreach ( $view_groups as $view_group_candidate_id => $view_group_candidate_data ) {
				if ( count( $view_group_candidate_data['fields'] ) > 0 ) {
					do_action( 'wpv_action_register_shortcode_group', $view_group_candidate_id, $view_group_candidate_data );
				}
			}
		}
		
		/**
		 * Register a Fields and Views dialog group with its fields.
		 *
		 * This can also be used to:
		 * - register groups with no fields, just to ensure the group order position in the registered groups, and fill it later.
		 * - extend an alredy registered group by just passing more fields to its group ID.
		 *
		 * @param $group_id		string 	The group unique ID.
		 * @param $group_data	array	The group data:
		 *     name		string	The group name that will be used over the group fields.
		 *     fields	array	Optional. The group fields. Leave blank or empty to just pre-register the group.
		 *         array(
		 *             field_key => array(
		 *                 shortcode	string	The shortcode that this item will insert.
		 *                 name			string	The button label for this item.
		 *                 callback		string	The JS callback to execute when this item is clicked.
		 *             )
		 *         )
		 *     target	string	Optional. Which target this group is aimed to: 'posts'|'taxonomy'|'users'. Defaults to all of them.
		 *     documentation array Optional. Handle and link to a documentation page.
		 *
		 * @usage do_action( 'wpv_action_wpv_register_dialog_group', $group_id, $group_data ); DEPRECATED
		 * @usage do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		 *
		 * @since 2.3.0
		 * @since m2m Rename the method and associated action
		 * @since m2m Added a documentation optional attribute for field groups
		 */
		
		function register_shortcode_group( $group_id = '', $group_data = array() ) {
			
			$group_id = sanitize_text_field( $group_id );
			
			if ( empty( $group_id ) ) {
				return;
			}
			
			$group_data['fields'] = ( isset( $group_data['fields'] ) && is_array( $group_data['fields'] ) ) ? $group_data['fields'] : array();
			
			$dialog_groups = $this->dialog_groups;
			
			if ( isset( $dialog_groups[ $group_id ] ) ) {
				
				// Extending an already registered group, which should have a name and a target already.
				if ( 
					! array_key_exists( 'name', $dialog_groups[ $group_id ] ) 
					|| ! array_key_exists( 'target', $dialog_groups[ $group_id ] ) 
				) {
					return;
				}
				foreach( $group_data['fields'] as $field_key => $field_data ) {
					$dialog_groups[ $group_id ]['fields'][ $field_key ] = $field_data;
				}
				
			} else {
				
				// Registering a new group, the group name is mandatory
				if ( ! array_key_exists( 'name', $group_data ) ) {
					return;
				}
				$dialog_groups[ $group_id ]['name']		= $group_data['name'];
				$dialog_groups[ $group_id ]['fields']	= $group_data['fields'];
				$dialog_groups[ $group_id ]['target']	= isset( $group_data['target'] ) ? $group_data['target'] : array( 'posts', 'taxonomy', 'users' );
				$dialog_groups[ $group_id ]['documentation'] = isset( $group_data['documentation'] ) ? $group_data['documentation'] : array( 'handle' => '', 'url' => '' );
			
			}
			$this->dialog_groups = $dialog_groups;
			
		}
		
		public function require_shortcodes_dialog_target( $target = 'posts' ) {
			
			$footer_dialogs_needing = $this->footer_dialogs_needing;
			if ( ! in_array( $target, $footer_dialogs_needing ) ) {
				$footer_dialogs_needing[] = $target;
			}
			$this->footer_dialogs_needing = $footer_dialogs_needing;
			
			return;
			
		}
		
		/**
		 * Generates a shortodes dialog for a given target, 
		 * by checking the registered dialog groups for that target.
		 * This usually happens when an editor button is generated for a given target, 
		 * or is forced in the footer when needed and no editor button was generated 
		 * (like when having an Admin Bar item on an admin page withotu editors).
		 *
		 * @param $target string Which target this group is aimed to: 'posts'|'taxonomy'|'users'. Defaults to 'post'.
		 *
		 * @usage do_action( 'wpv_action_wpv_generate_shortcodes_dialog', $target );
		 *
		 * @since 2.3.0
		 */
		
		function generate_shortcodes_dialog( $target = 'posts' ) {
			
			$existing_dialogs = $this->footer_dialogs_existing;
			if ( in_array( $target, $existing_dialogs ) ) {
				return '';
			}
			
			$dialog_links = array();
			$dialog_content = '';
			foreach ( $this->dialog_groups as $group_id => $group_data ) {
				
				if ( ! in_array( $target, $group_data['target'] ) ) {
					continue;
				}
				
				if ( empty( $group_data['fields'] ) ) {
					continue;
				}
				
				$dialog_links[] = '<li data-id="' . md5( $group_id ) . '" class="editor-addon-top-link" data-editor_addon_target="editor-addon-link-' . md5( $group_id ) . '">' . esc_html( $group_data['name'] ) . ' </li>';

				$post_field_section_classname = ( $group_data['name'] == __('Post field', 'wpv-views') ) ? ' js-wpv-shortcode-gui-group-list-post-field-section' : '';
				
				$fields_group_section_title = ( empty( $group_data['documentation']['handle'] ) ) 
					? esc_html( $group_data['name'] ) 
					: sprintf(
						_x( '%1$s &bull; %2$s%3$s %4$s%5$s', 'For RTL languages, just swap the groups around the bull sign', 'wpv-views' ),
						esc_html( $group_data['name'] ),
						'<a href="' . esc_url( $group_data['documentation']['url'] ) . '">',
						esc_html( $group_data['documentation']['handle'] ),
						'<i class="fa fa-external-link"></i>',
						'</a>'
					);

				$dialog_content .= '<div class="group">'
					. '<h4 data-id="' . md5( $group_id ) . '" class="group-title  editor-addon-link-' . md5( $group_id ) . '-target">' 
					. $fields_group_section_title
					. "</h4>";
				$dialog_content .= "\n";
				$dialog_content .= '<ul class="wpv-shortcode-gui-group-list js-wpv-shortcode-gui-group-list' . $post_field_section_classname . '">';
				$dialog_content .= "\n";
				foreach ( $group_data['fields'] as $group_data_field_key => $group_data_field_data ) {
					if (
						! isset( $group_data_field_data['callback'] ) 
						|| empty( $group_data_field_data['callback'] )
					) {
						$dialog_content .= sprintf(
							'<li class="item"><button class="button button-secondary button-small" onclick="WPViews.shortcodes_gui.insert_shortcode_with_no_attributes(\'%s\', \'%s\'); return false;">%s</button></li>',
							( isset( $group_data_field_data['handle'] ) ? esc_attr( $group_data_field_data['handle'] ) : $this->get_shortcode_handle( $group_data_field_data ) ),
							esc_attr( $group_data_field_data['shortcode'] ),
							esc_html( $group_data_field_data['name'] )
						);
					} else {
						$dialog_content .= sprintf(
							'<li class="item"><button class="button button-secondary button-small" onclick="%s; return false;">%s</button></li>', 
							$group_data_field_data['callback'], 
							esc_html( $group_data_field_data['name'] )
						);
					}
					$dialog_content .= "\n";
				}
				$dialog_content .= '</ul>';
				$dialog_content .= "\n";
				$dialog_content .= '</div>';
			}

			$direct_links = implode( '', $dialog_links );
			$dropdown_class = 'js-wpv-fields-and-views-dialog-for-' . $target;

			// add search box
			$searchbar = '<div class="searchbar">';
            $searchbar .=   '<label for="searchbar-input-for-' . esc_attr( $target ) . '">' . __( 'Search', 'wpv-views' ) . ': </label>';
            $searchbar .=   '<input id="searchbar-input-for-' . esc_attr( $target ) . '" type="text" class="search_field" onkeyup="wpv_on_search_filter(this)" />';
            $searchbar .= '</div>';

			// generate output content
			$out = '
			<div class="wpv-fields-and-views-dialog wpv-editor_addon_dropdown '. $dropdown_class .'" id="wpv-editor_addon_dropdown_' . rand() . '">'
				. "\n"
				. '<div class="wpv-editor_addon_dropdown_content editor_addon_dropdown_content js-wpv-fields-views-dialog-content">'
						. "\n"
						. $searchbar
						. "\n"
						. '<div class="direct-links-desc"><ul class="direct-links"><li class="direct-links-label">' . __( 'Jump to:', 'wpv-views' ) . '</li>' . $direct_links . '</ul></div>'
						. "\n"
						. $dialog_content
						. '
				</div>
			</div>';
			
			$existing_dialogs[] = $target;
			$this->footer_dialogs_existing = $existing_dialogs;
			$this->footer_dialogs .= $out;
			
		}
		
		/**
		 * Get the shortcode handle given its final string including square brackets
		 *
		 * Since the amount of data we pass to this API chnaged in m2m,
		 * some shortcodes might not be providing their handle,
		 * hence we need to calculate it manually.
		 *
		 * @param array $shortcode_data
		 *
		 * @return string
		 *
		 * @since m2m
		 */
		private function get_shortcode_handle( $shortcode_data ) {
			if ( isset( $shortcode_data['handle'] ) ) {
				return $shortcode_data['handle'];
			}
			$shortcode_pieces = explode( ' ', $shortcode_data['shortcode'] );
			$shortcode_handle = trim( $shortcode_pieces[0], ' []');
			return $shortcode_handle;
		}
		
		/**
		 * Generate the dialogs and add the HTML markup for the shortcode dialogs to both backend and frontend footers, 
		 * as late as possible, because page builders tend to register their templates, including native WP editors, 
		 * hence shortcode buttons, in wp_footer:10.
		 * Also, because this way we can extend the dialog groups for almost the whole page request.
		 *
		 * @since 2.3.0
		 */
		
		function render_footer_dialogs() {
			
			$footer_dialogs_needing = $this->footer_dialogs_needing;
			
			if ( empty( $footer_dialogs_needing ) ) {
				return;
			}
			
			do_action( 'wpv_action_collect_shortcode_groups' );
			
			foreach( $footer_dialogs_needing as $footer_dialogs_target_needing ) {
				do_action( 'wpv_action_wpv_generate_shortcodes_dialog', $footer_dialogs_target_needing );
			}
			
			$footer_dialogs = $this->footer_dialogs;
			if ( 
				'' != $footer_dialogs
				&& ! $this->footer_dialogs_added
			) {
				?>
				<div class="js-wpv-fields-views-footer-dialogs" style="display:none">
					<?php 
					echo $footer_dialogs; 
					$this->footer_dialogs_added = true;
					?>
				</div>
				<?php
				$this->render_dialog_templates();
			}
			
		}
		
		public function render_dialog_templates() {
			do_action( 'toolset_action_require_shortcodes_templates' );
		}
		
		/**
		 * Generates the Fields and Views button on native editors, using the media_buttons action, 
		 * and also on demand using a custom action.
		 *
		 * @param $editor		string
		 * @param $args			array
		 *     output	string	'span'|'button'. Defaults to 'span'.
		 *     target	string	'posts'|'taxonomy'|'users'. Defaults to 'posts'.
		 *
		 * @usage do_action( 'wpv_action_wpv_generate_fields_and_views_button', $editor_id, $args );
		 *
		 * @since 2.3.0
		 */
		
		function generate_fields_and_views_button( $editor, $args = array() ) {
			
			if ( 
				empty( $args ) 
				&& (
					! apply_filters( 'toolset_editor_add_form_buttons', true ) 
					/**
					 * wpv_filter_public_wpv_add_fields_and_views_button
					 *
					 * Public filter to disable the Fields and Views button on native WordPress editors.
					 *
					 * @since 2.3.0
					 */
					|| ! apply_filters( 'wpv_filter_public_wpv_add_fields_and_views_button', true )
				)
			) {
				// Disable the Fields and Views button just on native WP Editors
				return;
			}
			
			$defaults = array(
				'output'	=> 'span',
				'target'	=> $this->get_default_target(),
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			$button			= '';
			$button_label	= __( 'Fields and Views', 'wpv-views' );
			
			switch ( $args['output'] ) {
				case 'button':
					$button = '<button'
						. ' class="button-secondary js-wpv-fields-and-views-in-toolbar"'
						. ' data-editor="' . esc_attr( $editor ) . '">'
						. '<i class="icon-views-logo ont-icon-18"></i>'
						. '<span class="button-label">'. esc_html( $button_label ) . '</span>'
						. '</button>';
					break;
				case 'span':
				default:
					$button = '<span'
					. ' class="button js-wpv-fields-and-views-in-toolbar"'
					. ' data-editor="' . esc_attr( $editor ) . '">'
					. '<i class="icon-views-logo fa fa-wpv-custom ont-icon-18 ont-color-gray"></i>'
					. '<span class="button-label">' . esc_html( $button_label ) . '</span>'
					. '</span>';
					break;
			}
			
			do_action( 'wpv_action_wpv_enforce_shortcodes_assets' );
			
			$footer_dialogs_needing = $this->footer_dialogs_needing;
			if ( ! in_array( $args['target'], $footer_dialogs_needing ) ) {
				$footer_dialogs_needing[] = $args['target'];
			}
			$this->footer_dialogs_needing = $footer_dialogs_needing;
			
			echo apply_filters( 'wpv_add_media_buttons', $button );
			
		}
		
		/**
		 * Generate a Fields and Views button for custom editor toolbars, inside a <li></li> HTML tag.
		 *
		 * @param $editor	string	The editor ID.
		 * @param $source	string	The Toolset plugin originting the call.
		 *
		 * Hooked 9into the toolset_action_toolset_editor_toolbar_add_buttons action.
		 *
		 * @note Return early when the source is `views`, 
		 * since we need to manage Fields and Views buttons differently in our case 
		 * because we need to take care of the target of the button, etc etc.
		 *
		 * @since 2.3.0
		 */
		
		public function generate_fields_and_views_custom_button( $editor, $source = '' ) {
			
			if ( 'views' == $source ) {
				return;
			}
			
			$args = array(
				'output'	=> 'button',
				'target'	=> 'posts',
			);
			echo '<li class="wpv-vicon-codemirror-button">';
			$this->generate_fields_and_views_button( $editor, $args );
			echo '</li>';
			
		}
		
		/**
		 * Enforce at least the shortcode dialog for 'posts', 
		 * when the Admin Bar is registered but no editor triggered the dialog generation.
		 * Also, this can be enforced with the custom wpv_filter_wpv_force_generate_fields_and_views_dialog filter.
		 *
		 * @since unknown
		 */
		
		public function force_fields_and_views_dialog_shortcode_generator() {
			
			$target = $this->get_default_target();
			
			if ( $this->admin_bar_item_registered ) {
				// If we got to the footer without an editor that generates the Fields and Views dialog
				// It means we are on a page that might as well show all the Types shortcodes too
				// Since there is no active post to restrict to
				$footer_dialogs_needing = $this->footer_dialogs_needing;
				if ( ! in_array( $target, $footer_dialogs_needing ) ) {
					$footer_dialogs_needing[] = $target;
				}
				$this->footer_dialogs_needing = $footer_dialogs_needing;
			} else if ( 
				/**
				* wpv_filter_wpv_force_generate_fields_and_views_dialog
				*
				* Manually force the Fields and Views dialog content.
				*
				* Forces the Fields and Views dialog content on the admin or frontend footer,
				* in case it has not been rendered yet and the current page is not already loading it either.
				*
				* This is automatically enforced for pages that we decide are editor pages.
				*
				* @param bool false
				*
				* @since 2.3.0
				*/
				apply_filters( 'wpv_filter_wpv_force_generate_fields_and_views_dialog', false ) 
			) {
				do_action( 'wpv_action_wpv_enforce_shortcodes_assets' );
				$footer_dialogs_needing = $this->footer_dialogs_needing;
				if ( ! in_array( $target, $footer_dialogs_needing ) ) {
					$footer_dialogs_needing[] = $target;
				}
				$this->footer_dialogs_needing = $footer_dialogs_needing;
			}
			
		}
		
		/**
		 * Get the default shortcodes dialog target based on the current page characteristics.
		 *
		 * @since 2.3.0
		 */
		
		public function get_default_target() {
			
			global $pagenow;
			
			switch ( $pagenow ) {
				case 'term.php':
				case 'edit-tags.php':
					$target = 'taxonomy';
					break;
				case 'profile.php':
				case 'user-edit.php':
				case 'user-new.php':
					$target = 'users';
					break;
				default:
					$target = 'posts';
					break;
			}
			
			return $target;
			
		}
		
		/**
		 * Generate a dummy dialog for the shortcode generation response on the Admin Bar item.
		 *
		 * @since unknown
		 */
		
		public function display_shortcodes_target_dialog() {
			parent::display_shortcodes_target_dialog();
			if ( $this->admin_bar_item_registered ) {
				?>
				<div class="toolset-dialog-container">
					<div id="wpv-shortcode-generator-target-dialog" class="toolset-shortcode-gui-dialog-container js-wpv-shortcode-generator-target-dialog">
						<div class="wpv-dialog">
							<p>
								<?php echo __( 'This is the generated shortcode, based on the settings that you have selected:', 'wpv-views' ); ?>
							</p>
							<textarea id="wpv-shortcode-generator-target" readonly="readonly" style="width:100%;resize:none;box-sizing:border-box;font-family:monospace;display:block;padding:5px;background-color:#ededed;border: 1px solid #ccc !important;box-shadow: none !important;"></textarea>
							<p>
								<?php echo __( 'You can now copy and paste this shortcode anywhere you want.', 'wpv-views' ); ?>
							</p>
						</div>
					</div>
				</div>
				<?php
			}
		}
		
		/**
		 * ====================================
		 * Compatibility
		 * ====================================
		 */
		
		/**
		 * Gravity Forms compatibility.
		 *
		 * GF removes all assets from its admin pages, and offers a series of hooks to add your own to its whitelist.
		 * Those two callbacks are hooked to these filters.
		 *
		 * @param array $required_objects
		 *
		 * @return array
		 *
		 * @since 2.4.1
		 */
		function gform_noconflict_scripts( $required_objects ) {
			$required_objects[] = 'views-shortcodes-gui-script';
			return $required_objects;
		}
		function gform_noconflict_styles( $required_objects ) {
			$required_objects[] = 'views-admin-css';
			$required_objects[] = 'toolset-common';
			$required_objects[] = 'toolset-dialogs-overrides-css';
			$required_objects[] = 'onthego-admin-styles';
			return $required_objects;
		}
		
	}
	
}