<?php

/**
 * Shortcode generator for Toolset Types
 *
 * @since m2m
 */
class Types_Shortcode_Generator extends Toolset_Shortcode_Generator {

	const SCRIPT_TYPES_SHORTCODE = 'types-shortcode';

	/**
	 * Admin bar shortcodes button priority.
	 *
	 * Set to 5 to follow an order for Toolset buttons:
	 * - 5 Types/Views
	 * - 6 Forms
	 * - 7 Access
	 */
	const ADMIN_BAR_BUTTON_PRIORITY = 5;

	/**
	 * Media toolbar shortcodes button priority. Note that the native button is loaded at 10.
	 *
	 * Set to 11 to follow an order for Toolset buttons:
	 * - 11 Types/Views
	 * - 12 Forms
	 * - 13 Access
	 */
	const MEDIA_TOOLBAR_BUTTON_PRIORITY = 11;

	/**
	 * MCE shortcodes button priority.
	 *
	 * Set to 5 to follow an order for Toolset buttons:
	 * - 5 Types/Views
	 * - 6 Forms
	 * - 7 Access
	 */
	const MCE_BUTTON_PRIORITY = 5;

	/**
	 * @var bool
	 */
	private $admin_bar_item_registered	= false;

	/**
	 * @var bool
	 */
	private $footer_dialog_needed		= false;

	/**
	 * @var array
	 */
	private $dialog_groups				= array();

	/**
	 * @var string
	 */
	private $footer_dialogs				= '';

	/**
	 * @var bool
	 */
	private $doing_ajax                 = false;

	/**
	 * @var Types_Field_Group_Repeatable_Service
	 */
	private $repeatable_group_service;

	/**
	 * @var bool
	 */
	private $views_available = false;

	/**
	 * Initialize the class.
	 *
	 * @since m2m
	 */
	public function initialize() {

		$this->doing_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$this->repeatable_group_service = new Types_Field_Group_Repeatable_Service();

		/*
		 * ---------------------
		 * Toolset fair play:
		 * When Views is installed, there is no Types shortcode generator button
		 * ---------------------
		 */
		$this->views_available = apply_filters( 'toolset_is_views_available', false );

		/*
		 * ---------------------
		 * Admin Bar
		 * ---------------------
		 */
		// Register the Fields and Views item in the backend Admin Bar
		$this->admin_bar_item_registered = false;
		add_filter( 'toolset_shortcode_generator_register_item', array( $this, 'register_shortcode_generator' ), self::ADMIN_BAR_BUTTON_PRIORITY );

		/*
		 * ---------------------
		 * Button and dialogs
		 * ---------------------
		 */
		// Register and collect fields groups
		$this->dialog_groups = array();
		add_action( 'types_action_register_shortcode_group', array( $this, 'register_shortcode_group' ), 10, 2 );
		add_action( 'types_action_collect_shortcode_groups', array( $this, 'register_builtin_groups' ), 1 );
		add_action( 'wpv_action_collect_shortcode_groups',   array( $this, 'register_builtin_groups' ), 10 );

		// Types in native editors plus on demand:
		// - From media_buttons actions
		// - From Toolset arbitrary editor toolbars
		add_action( 'media_buttons',                                     array( $this, 'generate_types_button' ), self::MEDIA_TOOLBAR_BUTTON_PRIORITY );
		add_action( 'toolset_action_toolset_editor_toolbar_add_buttons', array( $this, 'generate_types_custom_button' ), 10, 2 );

		// Shortcodes button in Gutenberg classic TinyMCE editor blocks
		add_filter( 'mce_external_plugins', array( $this, 'mce_button_scripts' ), self::MCE_BUTTON_PRIORITY );
		add_filter( 'mce_buttons', array( $this, 'mce_button' ), self::MCE_BUTTON_PRIORITY );

		// Unregister on known scenarios
		add_filter( 'types_filter_add_types_button',                     array( $this, 'unhook_types_button'), 10, 2 );

		// Track whether dialogs re needed and have been rendered in the footer
		$this->footer_dialogs = '';

		// Generate and print the shortcodes dialogs in the footer,
		// both in frotend and backend, as long as there is anything to print.
		// Do it as late as possible because page builders tend to register their templates,
		// including native WP editors, hence shortcode buttons, in wp_footer:10.
		// This way we can extend the dialog groups for almost the whole page request.
		// Note that we avoid this o AJAX requests.
		if ( ! $this->doing_ajax ) {
			add_action( 'wp_footer',    array( $this, 'render_footer_dialogs' ), PHP_INT_MAX );
			add_action( 'admin_footer', array( $this, 'render_footer_dialogs' ), PHP_INT_MAX );
		}

		/*
		 * ---------------------
		 * Assets
		 *
		 * Note that we avoid this on AJAX requests
		 * ---------------------
		 */
		// Register shortcodes dialogs assets
		if ( ! $this->doing_ajax ) {
			add_action( 'init',                  array( $this, 'register_assets' ) );
			add_action( 'wp_enqueue_scripts',    array( $this, 'frontend_enqueue_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
		}

		// Ensure that shortcodes dialogs assets are enqueued
		// both when using the Admin Bar item and when a Types button is on the page.
		if ( ! $this->doing_ajax ) {
			add_action( 'types_action_enforce_shortcode_assets', array( $this, 'enforce_shortcode_assets' ) );
		}

		/*
		 * ---------------------
		 * Compatibility
		 * ---------------------
		 */
		add_filter( 'gform_noconflict_scripts',	array( $this, 'gform_noconflict_scripts' ) );
		add_filter( 'gform_noconflict_styles',	array( $this, 'gform_noconflict_styles' ) );

	}

	/**
	 * Register the shortcode generator in the Toolset shortcodes admin bar entry.
	 *
	 * Hooked into the toolset_shortcode_generator_register_item filter.
	 *
	 * @since m2m
	 */
	public function register_shortcode_generator( $registered_sections ) {

		$this->footer_dialog_needed = true;
		$this->enforce_shortcode_assets();

		if ( $this->views_available ) {
			return $registered_sections;
		}

		$this->admin_bar_item_registered = true;
		$registered_sections[ 'types' ] = array(
			'id'		=> 'Types',
			'title'		=> __( 'Types fields', 'wpcf' ),
			'href'		=> '#types_shortcodes',
			'parent'	=> 'toolset-shortcodes',
			'meta'		=> 'js-types-shortcode-generator-node'
		);
		return $registered_sections;
	}

	/**
	 * Register all the dedicated shortcodes assets:
	 * - Shortcodes GUI script.
	 *
	 * @todo Move the assets registration to here
	 *
	 * @since m2m
	 */
	public function register_assets() {
		$toolset_assets_manager = Toolset_Assets_Manager::get_instance();

		$toolset_assets_manager->register_script(
			self::SCRIPT_TYPES_SHORTCODE,
			TYPES_RELPATH . '/public/js/types_shortcode.js',
			array( Toolset_Assets_Manager::SCRIPT_TOOLSET_SHORTCODE ),
			TYPES_VERSION,
			true
		);

		global $pagenow;
		$conditions = array(
			'toolsetViews' => new Toolset_Condition_Plugin_Views_Active()
		);
		$types_shortcode_i18n = array(
			'action'	   => array(
				'insert'   => __( 'Insert shortcode', 'wpcf' ),
				'create'   => __( 'Create shortcode', 'wpcf' ),
				'update'   => __( 'Update shortcode', 'wpcf' ),
				'close'    => __( 'Close', 'wpcf' ),
				'cancel'   => __( 'Cancel', 'wpcf' ),
				'back'     => __( 'Back', 'wpcf' ),
				'previous' => __( 'Previous step', 'wpcf' ),
				'next'     => __( 'Next step', 'wpcf' ),
				'save'     => __( 'Save settings', 'wpcf' ),
				'loading'  => __( 'Loading...', 'wpcf' ),
				'wizard'   => __( 'Show me how', 'wpcf' ),
				'got_it'   => __( 'Got it!', 'wpcf' ),
				'install'  => array(
					'toolsetViews' => array(
						'label' => __( 'Install Toolset Views', 'wpcf' ),
						'url' => admin_url( 'plugin-install.php?tab=commercial' )
					)
				)
			),
			'title' => array(
				'dialog'    => __( 'Types fields', 'wpcf' ),
				'generated' => __( 'Generated shortcode', 'wpcf' ),
				'button'    => __( 'Types', 'wpcf' ),
			),
			'validation' => array(
				'mandatory'		=> __( 'This option is mandatory ', 'wpcf' ),
				'number'		=> __( 'Please enter a valid number', 'wpcf' ),
				'numberlist'	=> __( 'Please enter a valid comma separated number list', 'wpcf' ),
				'url'			=> __( 'Please enter a valid URL', 'wpcf' ),

			),
			'conditions' => array(
				'plugins' => array(
					'toolsetViews' => $conditions['toolsetViews']->is_met()
				),
				'page' => array(
					'viewEditor' => ( 'admin.php' == $pagenow && 'views-editor' == toolset_getget( 'page' ) ),
					'ctEditor' => ( 'admin.php' == $pagenow && 'ct-editor' == toolset_getget( 'page' ) )
				)
			),
			'mce' => array(
				'types' => array(
					'button' => __( 'Types', 'wpcf' ),
				),
			),
			'ajaxurl'         => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' )  ),
			'pagenow'         => $pagenow,
			'attributes'      => $this->get_fields_expected_attributes(),
			'repeatingAttributes' => $this->get_repeating_fields_extra_attributes(),
			'selectorGroups'  => $this->get_selector_groups_attributes()
		);
		$toolset_assets_manager->localize_script(
			'types-shortcode',
			'types_shortcode_i18n',
			$types_shortcode_i18n
		);
	}

	/**
	 * Enforce some assets that need to be in the frontend header, like styles,
	 * when we detect that we are on a page that needs them.
	 * Basically, this involves frontend page builders, detected by their own methods.
	 * Also enforces the generation of the dialog, just in case, in the footer.
	 *
	 * @uses is_frontend_editor_page which is a parent method.
	 *
	 * @since m2m
	 */
	public function frontend_enqueue_assets() {
		// Enqueue on the frontend pages that we know it is needed, maybe on users frontend editors only

		if ( $this->is_frontend_editor_page() ) {
			$this->footer_dialog_needed = true;
			$this->enforce_shortcode_assets();

		}

	}

	/**
	 * Enforce some assets that need to be in the backend header, like styles,
	 * when we detect that we are on a page that needs them.
	 * Also enforces the generation of the dialog, just in case, in the footer.
	 *
	 * Note that we enforce the shortcode assets in all known admin editor pages.
	 *
	 * @uses is_admin_editor_page which is a parent method.
	 *
	 * @since m2m
	 */
	public function admin_enqueue_assets( $hook ) {
		if ( $this->is_admin_editor_page() ) {
			$this->footer_dialog_needed = true;
			$this->enforce_shortcode_assets();
		}
	}

	/**
	 * Enforce the shortcodes assets when loaded at a late time.
	 * Note that there should be no problem with scripts,
	 * although styles might not be correctly enqueued.
	 *
	 * @usage do_action( 'types_action_enforce_shortcode_assets' );
	 *
	 * @since m2m
	 */
	public function enforce_shortcode_assets() {

		do_action( 'toolset_enqueue_scripts', array( 'types-shortcode' ) );
		do_action( 'toolset_enqueue_styles', array(
			Toolset_Assets_Manager::STYLE_JQUERY_UI_DIALOG,
			Toolset_Assets_Manager::STYLE_TOOLSET_COMMON,
			Toolset_Assets_Manager::STYLE_TOOLSET_DIALOGS_OVERRIDES,
			Toolset_Assets_Manager::STYLE_SELECT2_CSS,
			Toolset_Assets_Manager::STYLE_NOTIFICATIONS
		) );
		do_action( 'otg_action_otg_enforce_styles' );

	}

	/**
	 * Unregister the Types button from editors on known problematic scenarios.
	 *
	 * @since m2m
	 */
	public function unhook_types_button( $status, $editor ) {

		// first determine what is the situation
		$is_elementor_page_builder = ( 'elementor' === toolset_getget( 'action' ) );

		// and after that, decide what to do
		if ( $is_elementor_page_builder ) {
			return false;
		}

		return $status;
	}

	/**
	 * Check whether the shortcodes generator button should not be included in editors.
	 *
	 * @param string $editor
	 * @return bool
	 * @since 3.2
	 */
	private function is_editor_button_disabled( $editor = '' ) {
		if ( ! apply_filters( 'toolset_editor_add_form_buttons', true ) ) {
			return true;
		}

		/**
		 * Public filter to disable the shortcodes button on selected editors.
		 *
		 * @param bool
		 * @param string $editor The ID of the editor.
		 * @return bool
		 * @since m2m
		 */
		if ( ! apply_filters( 'types_filter_add_types_button', true, $editor ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Generate the button on native editors, using the media_buttons action.
	 * and also on demand using a custom action.
	 *
	 * @param $editor		string
	 * @param $args			array
	 *     output	string	'span'|'button'. Defaults to 'span'.
	 *
	 * @since m2m
	 */
	public function generate_types_button( $editor, $args = array() ) {
		if (
			empty( $args )
			&& $this->is_editor_button_disabled()
		) {
			return;
		}

		$this->footer_dialog_needed = true;
		$this->enforce_shortcode_assets();

		if ( $this->views_available ) {
			return;
		}

		$defaults = array(
			'output'	=> 'span',
		);

		$args = wp_parse_args( $args, $defaults );

		$button			= '';
		$button_label	= __( 'Types', 'wpcf' );

		switch ( $args['output'] ) {
			case 'button':
				$button = '<button'
					. ' class="button button-secondary js-types-in-toolbar"'
					. ' data-editor="' . esc_attr( $editor ) . '">'
					. '<i class="icon-types-logo ont-icon-18"></i>'
					. '<span class="button-label">'. esc_html( $button_label ) . '</span>'
					. '</button>';
				break;
			case 'span':
			default:
				$button = '<span'
				. ' class="button js-types-in-toolbar"'
				. ' data-editor="' . esc_attr( $editor ) . '">'
				. '<i class="icon-types-logo fa fa-types-custom ont-icon-18 ont-color-gray"></i>'
				. '<span class="button-label">' . esc_html( $button_label ) . '</span>'
				. '</span>';
				break;
		}

		$this->enforce_shortcode_assets();

		echo $button;

	}

	/**
	 * Generate a button for custom editor toolbars, inside a <li></li> HTML tag.
	 *
	 * @param $editor	string	The editor ID.
	 * @param $source	string	The Toolset plugin originting the call.
	 *
	 * Hooked into the toolset_action_toolset_editor_toolbar_add_buttons action.
	 *
	 * @since m2m
	 */
	public function generate_types_custom_button( $editor, $source = '' ) {

		$this->footer_dialog_needed = true;
		$this->enforce_shortcode_assets();

		if ( $this->views_available ) {
			return;
		}

		$args = array(
			'output'	=> 'button',
		);
		echo '<li>';
		$this->generate_types_button( $editor, $args );
		echo '</li>';

	}

	/**
	 * Add a TinyMCE plugin script for the shortcodes generator button.
	 *
	 * Note that this only gets registered when editing a post with Gutenberg.
	 *
	 * @param array $plugin_array
	 * @return array
	 * @since 2.7
	 */
	public function mce_button_scripts( $plugin_array ) {
		if (
			! $this->is_blocks_editor_page()
			|| $this->is_editor_button_disabled()
			|| $this->views_available
		) {
			return $plugin_array;
		}
		$this->gutenberg_enqueue_assets();
		$plugin_array['toolset_add_types_shortcode_button'] = TYPES_RELPATH . '/public/js/compatibility/bundle.tinymce.js?ver=' . TYPES_VERSION;
		return $plugin_array;
	}

	/**
	 * Add a TinyMCE button for the shortcodes generator button.
	 *
	 * Note that this only gets registered when editing a post with Gutenberg.
	 *
	 * @param array $buttons
	 * @return array
	 * @since 2.7
	 */
	public function mce_button( $buttons ) {
		if (
			! $this->is_blocks_editor_page()
			|| $this->is_editor_button_disabled()
			|| $this->views_available
		) {
			return $buttons;
		}
		$this->gutenberg_enqueue_assets();
		array_push( $buttons, 'toolset_types_shortcodes' );
		$classic_editor_block_toolbar_icon_style = '.ont-icon-block-classic-toolbar::before {position:absolute;top:1px;left:2px;}';
		wp_add_inline_style(
			Toolset_Assets_Manager::STYLE_TOOLSET_COMMON,
			$classic_editor_block_toolbar_icon_style
		);
		return $buttons;
	}

	/**
	 * Enforce the shortcodes generator assets when using a Gutenberg editor.
	 *
	 * @since 2.7
	 */
	public function gutenberg_enqueue_assets() {
		$this->footer_dialog_needed = true;
		$this->enforce_shortcode_assets();
	}

	/**
	 * Register fields in some selected AJAX calbacks that require them.
	 *
	 * @since 3.2.5
	 */
	private function register_builtin_groups_in_ajax() {

		// Register the right fields in the Views loop wizard list of available items
		if ( 'wpv_loop_wizard_add_field' === toolset_getpost( 'action' ) ) {
			$domain = toolset_getpost( 'domain', 'posts' );

			// Note that the Views domains do not follow the Toolset_Field_Utils values
			switch ( $domain ) {
				case 'taxonomy':
					$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_TERMS );
					break;
				case 'users':
					$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_USERS );
					break;
				case 'posts':
				default:
					$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_POSTS );
					$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_USERS );
					break;
			}
		}
	}

	/**
	 * Register Types fields groups in the API.
	 *
	 * @since m2m
	 */
	public function register_builtin_groups() {
		global $pagenow;

		if ( 'admin-ajax.php' === $pagenow ) {
			$this->register_builtin_groups_in_ajax();
			return;
		}

		if (
			$pagenow == 'admin.php'
			&& in_array( toolset_getget( 'page' ), array(
				'views-editor',
				'ct-editor',
				'view-archives-editor',
				'dd_layouts_edit'
			) )
		) {
			// We are on a Views object edit page, so add all Types postmeta groups and usermeta groups
			// We can also be on a Layouts object edit page, so we add all postmeta and usermeta groups too
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_POSTS );
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_TERMS );
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_USERS );
			return;
		}

		if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			// We are on a post edit page, add the postmeta and usermeta groups
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_POSTS );
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_USERS );
			return;
		}

		if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ) ) ) {
			// We are on a term edit page, add the termmeta groups
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_TERMS );
			return;
		}

		if ( in_array( $pagenow, array( 'profile.php', 'user-new.php', 'user-edit.php' ) ) ) {
			// We are on an user edit page, add the usermeta groups
			$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_USERS );
			return;
		}

		// We are elsewhere, add the postmeta and usermeta groups
		$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_POSTS );
		$this->register_meta_dialog_groups( Toolset_Field_Utils::DOMAIN_USERS );

	}

	/**
	 * Get meta fields target per domain:
	 * - postmeta fields on posts
	 * - termmeta fields on taxonomy terms
	 * - usermeta fields on posts and users
	 *
	 * @param $domain string
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_target_by_domain( $domain ) {
		$target = array( 'posts' );
		switch ( $domain ) {
			case Toolset_Field_Utils::DOMAIN_POSTS:
				$target = array( 'posts' );
				break;
			case Toolset_Field_Utils::DOMAIN_TERMS:
				$target = array( 'taxonomy' );
				global $pagenow;
				if (
					$pagenow == 'admin.php'
					&& in_array( toolset_getget( 'page' ), array( 'view-archives-editor' ) )
				) {
					// On WPA edit pages, we can insret also termmeta fields even if the target is posts,
					// because [types] shortcodes do support termmeta fields output on term archives.
					$target = array( 'posts' );
				}
				break;
			case Toolset_Field_Utils::DOMAIN_USERS:
				$target = array( 'posts', 'users' );
				break;
		}
		return $target;
	}

	/**
	 * Get meta fields shortcode attribute per domain:
	 * - field on postmeta
	 * - termmeta on termmeta
	 * - usermeta on usermeta
	 *
	 * @param $domain string
	 *
	 * @return string
	 *
	 * @since m2m
	 */
	private function get_shortcode_meta_attribute_per_domain( $domain ) {
		$slug = 'field';
		switch ( $domain ) {
			case Toolset_Field_Utils::DOMAIN_POSTS:
				$slug = 'field';
				break;
			case Toolset_Field_Utils::DOMAIN_TERMS:
				$slug = 'termmeta';
				break;
			case Toolset_Field_Utils::DOMAIN_USERS:
				$slug = 'usermeta';
				break;
		}
		return $slug;
	}

	/**
	 * Get a field shortcode default set of data, including:
	 * - the main attribute as field|termmeta|usermeta key, and field slug value
	 * - the metaType as field type
	 * - the metaNature as single|multiple
	 * - the metaDomain as posts|terms|users
	 * - the metaOptions as { optionKey: { title: optionTitle } } for field types than can generate shortcodes per option
	 *
	 * @param $field Toolset_Field_Definition
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_shortcode_default_parameters( Toolset_Field_Definition $field ) {

		if ( ! in_array(
			$field->get_factory()->get_domain(),
			array(
				Toolset_Field_Utils::DOMAIN_POSTS,
				Toolset_Field_Utils::DOMAIN_TERMS,
				Toolset_Field_Utils::DOMAIN_USERS
			)
		) ) {
			return array();
		}


		$parameter_key = $this->get_shortcode_meta_attribute_per_domain( $field->get_factory()->get_domain() );

		$parameters = array(
			$parameter_key => $field->get_slug(),
			'metaType'     => $field->get_type()->get_slug(),
			'metaNature'   => ( $field->get_is_repetitive() ) ? 'multiple' : 'single',
			'metaDomain'   => $field->get_factory()->get_domain()
		);

		switch( $field->get_type()->get_slug() ) {
			case 'radio':
				$meta_options = array();
				foreach ( $field->get_field_options() as $option_key => $option ) {
					// Skip default value record
					if ( $option_key == 'default' ) {
						continue;
					}
					$meta_options[ $option_key ] = array(
						'title' => wpcf_translate(
							'field ' . $field->get_slug() . ' option ' . $option_key . ' title',
							$option->get_display_value()
						)
					);
				}
				$parameters['metaOptions'] = $meta_options;
				break;
			case 'checkboxes':
				$meta_options = array();
				foreach ( $field->get_field_options() as $option_key => $option ) {
					$meta_options[ $option_key ] = array(
						'title' => wpcf_translate(
							'field ' . $field->get_slug() . ' option ' . $option_key . ' title',
							$option->get_label()
						)
					);
				}
				$parameters['metaOptions'] = $meta_options;
				break;
		}

		return $parameters;
	}

	/**
	 *
	 * @param $repeatable_field_group Types_Field_Group_Repeatable
	 * @param $domain string
	 * * @since m2m
	 */
	private function get_repeatable_field_group_default_parameters( $repeatable_field_group, $domain ) {

		if ( ! in_array(
			$domain,
			array(
				Toolset_Field_Utils::DOMAIN_POSTS,
				Toolset_Field_Utils::DOMAIN_TERMS,
				Toolset_Field_Utils::DOMAIN_USERS
			)
		) ) {
			return array();
		}

		$parameter_key = $this->get_shortcode_meta_attribute_per_domain( $domain );
		$parameters = array(
			$parameter_key => $repeatable_field_group->get_slug(),
			'metaType'     => 'repeatable_field_group',
			'metaNature'   => 'multiple',
			'metaDomain'   => $domain
		);

		return $parameters;
	}

	/**
	 * Get the callback shortcode API JS function for a given field.
	 *
	 * @param $field      Toolset_Field_Definition
	 * @param $parameters array
	 *
	 * @reurn string
	 *
	 * @since m2m
	 */
	private function get_shortcode_callback( Toolset_Field_Definition $field, $parameters ) {
		if ( ! in_array(
			$field->get_factory()->get_domain(),
			array(
				Toolset_Field_Utils::DOMAIN_POSTS,
				Toolset_Field_Utils::DOMAIN_TERMS,
				Toolset_Field_Utils::DOMAIN_USERS
			)
		) ) {
			return array();
		}

		return "Toolset.Types.shortcodeGUI.shortcodeDialogOpen({
			shortcode: 'types',
			title: '" . esc_js( $field->get_name() ) . "',
			parameters: " . esc_js( json_encode( $parameters ) ) . "
		})";
	}

	/**
	 * Get the callback shortcode API JS function for a given field.
	 *
	 * @param $repeatable_field_group Types_Field_Group_Repeatable
	 * @param $parameters array
	 * @param $domain string
	 *
	 * @reurn string
	 *
	 * @since m2m
	 */
	private function get_repeatable_field_group_callback( $repeatable_field_group, $parameters, $domain ) {
		if ( ! in_array(
			$domain,
			array(
				Toolset_Field_Utils::DOMAIN_POSTS,
				Toolset_Field_Utils::DOMAIN_TERMS,
				Toolset_Field_Utils::DOMAIN_USERS
			)
		) ) {
			return array();
		}

		return "Toolset.Types.shortcodeGUI.shortcodeDialogOpen({
			shortcode: 'types',
			title: '" . esc_js( $repeatable_field_group->get_name() ) . "',
			parameters: " . esc_js( json_encode( $parameters ) ) . "
		})";
	}

	/**
	 * Get Types meta groups per domain.
	 *
	 * @param $domain string
	 *
	 * @return array
	 *
	 * @since m2m
	 * @note For the Toolset_Field_Utils::DOMAIN_POSTS domain we also return field groups for
	 *     intermediary post types in many-to-many relationships.
	 */
	private function get_meta_groups_by_domain( $domain ) {
		$meta_groups = array();

		$group_factory = Toolset_Field_Group_Factory::get_factory_by_domain( $domain );

		switch( $domain ) {
			case Toolset_Field_Utils::DOMAIN_POSTS:
				$meta_groups = $group_factory->query_groups();
				$meta_groups_for_intermediary = $group_factory->query_groups( array( 'purpose' => Toolset_Field_Group_Post::PURPOSE_FOR_INTERMEDIARY_POSTS ) );
				$meta_groups = array_merge( $meta_groups, $meta_groups_for_intermediary );
				break;
			case Toolset_Field_Utils::DOMAIN_TERMS:
				$meta_groups = $group_factory->query_groups();
				break;
			case Toolset_Field_Utils::DOMAIN_USERS:
				$meta_groups = $group_factory->query_groups();
				break;
		}

		return $meta_groups;
	}

	/**
	 * Get the fields definition factory per domain.
	 *
	 * @param $domain string
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_field_definition_factory( $domain ) {
		switch( $domain ) {
			case Toolset_Field_Utils::DOMAIN_POSTS:
				return Toolset_Field_Definition_Factory_Post::get_instance();
			case Toolset_Field_Utils::DOMAIN_TERMS:
				return Toolset_Field_Definition_Factory_Term::get_instance();
			case Toolset_Field_Utils::DOMAIN_USERS:
				return Toolset_Field_Definition_Factory_User::get_instance();
		}

		return null;
	}

	/**
	 * Get the fields in a given group and separate them by whether they are single or repeating fields groups items.
	 *
	 * @param $meta_group Toolset_Field_Group
	 * @param $domain string
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_fields_in_group_by_nature( Toolset_Field_Group $meta_group, $domain ) {
		$meta = array(
			'fields' => array(),
			'repeating_groups' => array()
		);

		$slugs = $meta_group->get_field_slugs();
		$factory = $this->get_field_definition_factory( $domain );

		foreach( $slugs as $slug ) {
			$field_definition = $factory->load_field_definition( $slug );
			if ( null != $field_definition && $field_definition->is_managed_by_types() ) {
				$meta['fields'][] = $field_definition;
			} else if ( $repeatable_group = $this->repeatable_group_service->get_object_from_prefixed_string( $slug ) ) {
				$repeatable_group_field_slugs = $repeatable_group->get_field_slugs();
				if ( ! empty( $repeatable_group_field_slugs ) ) {
					$meta['repeating_groups'][] = $repeatable_group;
				}
			}
		}

		return $meta;
	}

	/**
	 * Populate a registering group with its fields entries.
	 *
	 * @param $group_data array
	 * @param $meta_fields array
	 * @param $domain string
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function populate_dialog_group_with_fields( $group_data, $meta_fields, $domain ) {
		if ( ! empty( $meta_fields ) ) {
			foreach ( $meta_fields as $meta_field ) {
				$parameters = $this->get_shortcode_default_parameters( $meta_field );
				$group_data['fields'][ $meta_field->get_name() ] = array(
					'name'       => stripslashes( $meta_field->get_name() ),
					'handle'     => 'types',
					'shortcode'  => '[types '
									. $this->get_shortcode_meta_attribute_per_domain( $domain )
									. '="'
									. esc_js( $meta_field->get_slug() )
									. '"][/types]',
					'callback'   => $this->get_shortcode_callback( $meta_field, $parameters ),
					'parameters' => $parameters
				);
			}
		}

		return $group_data;
	}

	/**
	 * Populate a registering group with its repeating fields groups entries.
	 *
	 * @param $group_data array
	 * @param $meta_repeating_groups array
	 * @param $domain string
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function populate_dialog_group_with_repeating_group_fields( $group_data, $meta_repeating_groups, $domain ) {
		if ( ! empty( $meta_repeating_groups ) ) {
			foreach ( $meta_repeating_groups as $repeatable_field_group ) {
				$parameters = $this->get_repeatable_field_group_default_parameters( $repeatable_field_group, $domain );
				$group_data['fields'][ $repeatable_field_group->get_slug() ] = array(
					'name'       => stripslashes( $repeatable_field_group->get_name() ),
					'handle'     => 'types',
					'shortcode'  => '',
					'callback'   => $this->get_repeatable_field_group_callback( $repeatable_field_group, $parameters, $domain ),
					'parameters' => $parameters
				);
			}
		}

		return $group_data;
	}


	/**
	 * Register the meta fields groups given a valid domain.
	 *
	 * @param $domain string
	 *
	 * @since m2m
	 */
	private function register_meta_dialog_groups( $domain ) {
		$meta_groups = $this->get_meta_groups_by_domain( $domain );

		foreach( $meta_groups as $meta_group ) {
			$group_id = 'types-' . $domain . '-' . $meta_group->get_slug();
			$group_data = array(
				'name' => $meta_group->get_name(),
				'target' => $this->get_target_by_domain( $domain ),
				'fields' => array()
			);

			$meta = $this->get_fields_in_group_by_nature( $meta_group, $domain );

			$group_data = $this->populate_dialog_group_with_fields( $group_data, $meta['fields'], $domain );
			$group_data = $this->populate_dialog_group_with_repeating_group_fields( $group_data, $meta['repeating_groups'], $domain );

			if ( ! empty( $group_data['fields'] ) ) {
				do_action( 'types_action_register_shortcode_group', $group_id, $group_data );
				do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
				$this->maybe_register_repeating_meta_dialog_groups( $meta['repeating_groups'], $domain );
			}
		}
	}

	/**
	 * Register the repeating groups fields groups given a valid domain,
	 * only in Views, Content Templates and Layouts edit pages.
	 *
	 * @param $repeatable_field_groups array Repeatable field groups to register
	 * @param $domain string
	 *
	 * @since m2m
	 */
	private function maybe_register_repeating_meta_dialog_groups( $repeatable_field_groups, $domain ) {
		if ( empty( $repeatable_field_groups ) ) {
			return;
		}

		$is_toolset_supported_edit_page = $this->is_toolset_supported_edit_page();
		$is_views_loop_wizard_add_field_ajax = $this->is_views_loop_wizard_add_field_ajax();
		if (
			! $is_toolset_supported_edit_page
			&& ! $is_views_loop_wizard_add_field_ajax
		) {
			return;
		}

		foreach( $repeatable_field_groups as $meta_group ) {
			$group_id = 'types-' . $domain . '-' . $meta_group->get_slug();
			$group_data = array(
				'name' => sprintf(
					__( '%1$s (repeatable field group)', 'wpcf' ),
					$meta_group->get_name()
				),
				'target' => $this->get_target_by_domain( $domain ),
				'fields' => array()
			);

			$meta = $this->get_fields_in_group_by_nature( $meta_group, $domain );

			$group_data = $this->populate_dialog_group_with_fields( $group_data, $meta['fields'], $domain );
			$group_data = $this->populate_dialog_group_with_repeating_group_fields( $group_data, $meta['repeating_groups'], $domain );

			if ( ! empty( $group_data['fields'] ) ) {
				do_action( 'types_action_register_shortcode_group', $group_id, $group_data );
				do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
				$this->maybe_register_repeating_meta_dialog_groups( $meta['repeating_groups'], $domain );
			}
		}
	}

	/**
	 * Check whether we are in a supported page that Toolset recognizes as an editor for its objects.
	 *
	 * Toolset considers natively as an editor page those that let you edit a layout or a Content Template
	 * (be it with the proper CT editor or when it uses an user editor).
	 * Supported user editors: the native editor, whether Classic or Gutenberg, Divi, Beaver Builder.
	 *
	 * @return bool
	 */
	private function is_toolset_supported_edit_page() {
		global $pagenow;

		$is_toolset_edit_page = (
			'admin.php' === $pagenow
			&& in_array(
				toolset_getget( 'page' ),
				array(
					'views-editor',
					'ct-editor',
					'dd_layouts_edit',
				),
				true
			)
		);

		// Check if we are editing a CT in a native editor, by itself or by another user editor.
		$is_ct_native_edit_page = (
			in_array( $pagenow, array( 'post.php' ), true )
			&& isset( $_GET['post'] )
			&& 'view-template' === get_post_type( intval( toolset_getget( 'post' ) ) )
		);

		global $post;

		$is_ct_frontend_edit_page = (
			! is_admin()
			&& is_a( $post, 'WP_Post' )
			&& 'view-template' === $post->post_type
		);

		return ( $is_toolset_edit_page || $is_ct_native_edit_page || $is_ct_frontend_edit_page );
	}

	/**
	 * Check whether we are in an AJAX call triggered by the Views loop wizard mechanism to add a new field.
	 *
	 * @return bool
	 */
	private function is_views_loop_wizard_add_field_ajax() {
		$is_views_loop_wizard_add_field_ajax = (
			is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX
			&& 'wpv_loop_wizard_add_field' === toolset_getpost( 'action' )
		);
		return $is_views_loop_wizard_add_field_ajax;
	}

	/**
	 * Register a dialog group with its fields.
	 *
	 * @param string $group_id The group unique ID.
	 * @param array $group_data The group data:
	 *     name		string	The group name that will be used over the group fields.
	 *     fields	array	Optional. The group fields. Leave blank or empty to just pre-register the group.
	 *
	 * @usage do_action( 'types_action_register_shortcode_group', $group_id, $group_data );
	 *
	 * @since m2m
	 */
	public function register_shortcode_group( $group_id = '', $group_data = array() ) {

		$group_id = sanitize_text_field( $group_id );

		if ( empty( $group_id ) ) {
			return;
		}

		$group_data['fields'] = ( isset( $group_data['fields'] ) && is_array( $group_data['fields'] ) ) ? $group_data['fields'] : array();

		$dialog_groups = $this->dialog_groups;

		if ( isset( $dialog_groups[ $group_id ] ) ) {

			// Extending an already registered group, which should have a name already.
			if ( ! array_key_exists( 'name', $dialog_groups[ $group_id ] ) ) {
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

		}
		$this->dialog_groups = $dialog_groups;

	}

	/**
	 * Generate the main shortcode GUI dialog listing all the available relevant fields.
	 *
	 * @since m2m
	 * @todo Move this to a proper template
	 */
	public function generate_shortcodes_dialog() {

		$dialog_links = array();
		$dialog_content = '';

		foreach ( $this->dialog_groups as $group_id => $group_data ) {

			if ( empty( $group_data['fields'] ) ) {
				continue;
			}

			$dialog_links[] = '<li data-id="' . md5( $group_id ) . '" class="editor-addon-top-link" data-editor_addon_target="editor-addon-link-' . md5( $group_id ) . '">' . esc_html( $group_data['name'] ) . ' </li>';

			$dialog_content .= '<div class="toolset-shortcodes-gui-dialog-group"><h4 data-id="' . md5( $group_id ) . '" class="group-title  editor-addon-link-' . md5( $group_id ) . '-target">' . esc_html( $group_data['name'] ) . '</h4>';
			$dialog_content .= "\n";
			$dialog_content .= '<ul class="toolset-shortcode-gui-group-list types-shortcode-gui-group-list js-types-shortcode-gui-group-list">';
			$dialog_content .= "\n";
			foreach ( $group_data['fields'] as $group_data_field_key => $group_data_field_data ) {
				if (
					! isset( $group_data_field_data['callback'] )
					|| empty( $group_data_field_data['callback'] )
				) {
					$dialog_content .= sprintf(
						'<li class="item"><button class="button button-secondary button-small js-types-shortcode-gui-no-attributes" data-shortcode="%s" >%s</button></li>',
						'[' . esc_attr( $group_data_field_data['shortcode'] ) . ']',
						esc_html( $group_data_field_data['name'] )
					);
				} else {
					$dialog_content .= sprintf(
						'<li class="item"><button class="button button-secondary button-small js-types-shortcode-gui" onclick="%s; return false;">%s</button></li>',
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

		// add search box
		$searchbar = '<div class="types-shortcode-gui-dialog-searchbar toolset-shortcode-gui-dialog-searchbar">';
		$searchbar .=   '<label for="types-shortcode-gui-dialog-searchbar-input-for-types">' . __( 'Search', 'wpcf' ) . ': </label>';
		$searchbar .=   '<input id="types-shortcode-gui-dialog-searchbar-input-for-types" type="text" class="types-shortcode-gui-dialog-sarch-field js-types-shortcode-gui-dialog-sarch-field" onkeyup="wpv_on_search_filter(this)" />';
		$searchbar .= '</div>';

		// generate output content
		$out = '
		<div id="js-types-shortcode-gui-dialog-container-main" class="toolset-shortcode-gui-dialog types-shortcode-gui-dialog">'
			. "\n"
			. '<div class="types-shortcode-gui-dialog-content js-types-shortcode-gui-dialog-content">'
					. "\n"
					. $searchbar
					. "\n"
					//. '<div class="direct-links-desc"><ul class="direct-links"><li class="direct-links-label">' . __( 'Jump to:', 'wpcf' ) . '</li>' . $direct_links . '</ul></div>'
					//. "\n"
					. $dialog_content
					. '
			</div>
		</div>';

		$this->footer_dialogs .= $out;

	}

	/**
	 * Render the footer dialog, when needed.
	 *
	 * @since m2m
	 */
	public function render_footer_dialogs() {

		if ( ! $this->footer_dialog_needed ) {
			return;
		}

		// Generate foter dialogs even if Views is active, so we can
		// offer to append Types shortcodes directly
		do_action( 'types_action_collect_shortcode_groups' );
		$this->generate_shortcodes_dialog();
		// Make sure that Toolset Common shared templates are included,
		// and also the custom own ones.
		do_action( 'toolset_action_require_shortcodes_templates' );
		$this->generate_shortcode_templates();

		$footer_dialogs = $this->footer_dialogs;
		if ( '' != $footer_dialogs ) {
			?>
			<div class="js-types-footer-dialogs" style="display:none">
				<?php
				echo $footer_dialogs;
				?>
			</div>
			<?php
		}

	}

	/**
	 * Generate the repeating fields attribute for the index and separator.
	 * This will be automatically appended when the field is a repeating one.
	 *
	 * @since m2m
	 */
	private function get_repeating_fields_extra_attributes() {
		$attributes = array();

		$attributes['index'] = array(
			'label'        => __( 'Field index to display', 'wpcf' ),
			'type'         => 'text',
			'defaultValue' => '',
			'description'  => __( 'Zero-based index number of the field to be output.', 'wpcf' )
		);

		$attributes['separator'] = array(
			'label'        => __( 'Separator between multiple values', 'wpcf' ),
			'type'         => 'text',
			'defaultForceValue' => ', '
		);

		return $attributes;
	}

	/**
	 * Generate the item selector attribute for postmeta, termmeta and usermeta fields.
	 * This will be automatically appended to all fields and the matching template is shard in Toolset Common.
	 *
	 * @since m2m
	 */
	private function get_selector_groups_attributes() {
		$types_selector_groups_attributes = array(
			'typesUserSelector' => array(
				'header' => __( 'User selection', 'wpcf' ),
				'fields' => array(
					'item' => array(
						'label'             => __( 'Display the field for this user', 'wpcf' ),
						'type'              => 'typesUserSelector',
						'defaultForceValue' => 'author'
					)
				)
			),
			'typesViewsUserSelector' => array(
				'header' => __( 'User selection', 'wpcf' ),
				'fields' => array(
					'item' => array(
						'label'             => __( 'Display the field for this user', 'wpcf' ),
						'type'              => 'typesViewsUserSelector',
						'defaultForceValue' => 'viewloop'
					)
				)
			),
			'typesViewsTermSelector' => array(
				'header' => __( 'Term selection', 'wpcf' ),
				'fields' => array(
					'term_id' => array(
						'label'        => __( 'Display the field for this term', 'wpcf' ),
						'type'         => 'typesViewsTermSelector',
						'defaultValue' => 'viewloop'
					)
				)
			)
		);

		return $types_selector_groups_attributes;
	}

	/**
	 * List the expected attributed of each field type.
	 * Provide a fallback for generic fields not available in this API.
	 *
	 * Note that the 'displayOptions' group should be always included, even empty,
	 * since this is where repeating fields attributes are appended into.
	 *
	 * @since m2m
	 */
	private function get_fields_expected_attributes() {
		$attributes = array();

		$attributes['typesGenericType'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array()
			)
		);

		// Audio is OK
		$attributes['audio'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
				'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Audio player', 'wpcf' ),
							'raw'    => __( 'Raw audio file URL', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'preload' => array(
						'label'        => __( 'Preload', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'on'  => __( 'Begin the media download as soon as the page is loaded', 'wpcf' ),
							'off' => __( 'Hold the media download until the visitor plays it', 'wpcf' )
						),
						'defaultValue' => 'off'
					),
					'loop' => array(
						'label'        => __( 'Loop', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'on'  => __( 'Replay the audio field once it reaches the end', 'wpcf' ),
							'off' => __( 'Stop the audio play when it reaches the end', 'wpcf' )
						),
						'defaultValue' => 'off'
					),
					'autoplay' => array(
						'label'        => __( 'Autoplay', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'on'  => __( 'Begin the media play as soon as the page is loaded', 'wpcf' ),
							'off' => __( 'Hold the media play until the visitor starts it', 'wpcf' )
						),
						'defaultValue' => 'off'
					)
				)
			)
		);

		// Checkbox is OK
		// state='(un)checked' is not working
		$attributes['checkbox'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Normal', 'wpcf' ),
							'raw'    => __( 'Raw value', 'wpcf' ),
							'custom' => __( 'Custom values for selected and not selected states', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'outputCustomCombo' => array(
						'label'  => __( 'Custom values', 'wpcf' ),
						'type'   => 'group',
						'hidden' => true,
						'fields' => array(
							'selectedValue' => array(
								'pseudolabel' => __( 'When selected', 'wpcf' ),
								'type'        => 'text'
							),
							'unselectedValue' => array(
								'pseudolabel' => __( 'When not selected', 'wpcf' ),
								'type'        => 'text'
							)
						)
					),
					'show_name' => array(
						'label'        => __( 'Field name', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'true'  => __( 'Show the field name before its value', 'wpcf' ),
							'false' => __( 'Do not show the field name before its value', 'wpcf' )
						),
						'defaultValue' => 'false'
					)
				)
			)
		);

		// Checkboxes also support a "checked"/"unchecked" values for the state attribute
		// Checkboxes fields offer one custom output per value
		$attributes['checkboxes'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Normal', 'wpcf' ),
							'raw'    => __( 'Raw values', 'wpcf' ),
							'custom' => __( 'Custom values for selected and not selected states', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'separator' => array(
						'label'             => __( 'Separator between multiple values', 'wpcf' ),
						'type'              => 'text',
						'defaultForceValue' => ', '
					),
					'outputCustomCombo' => array(
						'label'  => __( 'Custom values', 'wpcf' ),
						'type'   => 'group',
						'hidden' => true,
						'fields' => array(
							'selectedValue' => array(
								'pseudolabel' => __( '%%OPTION%% selected', 'wpcf' ),
								'type'        => 'text'
							),
							'unselectedValue' => array(
								'pseudolabel' => __( '%%OPTION%% not selected', 'wpcf' ),
								'type'        => 'text'
							)
						)
					)
				)
			)
		);

		// Colorpicker is OK
		$attributes['colorpicker'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array()
			)
		);

		// Date is OK
		$info_about_escaping = __( 'Use % to escape characters, because Wordpress removes backslashes from shortcode attributes.', 'wpcf' );

		$date_site_settings = get_option( 'date_format' );
		$date_site_settings_has_escaped_chars = strpos( $date_site_settings, '\\' ) !== false;
		$date_site_settings_formatted = str_replace( '\\', '%', $date_site_settings );

		$date_site_settings_info = $date_site_settings_has_escaped_chars
			? '<br /><span class="description">' . $info_about_escaping . '</span>'
			: ''; // no escaped characters on site settings date, so no extra info needed here.

		$attributes['date'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
				'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Formatted date', 'wpcf' ),
							'raw'    => __( 'Raw timestamp', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'style' => array(
						'label'            => __( 'Output style', 'wpcf' ),
						'type'             => 'radio',
						'options'          => array(
							'calendar' => __( 'Calendar', 'wpcf' ),
							'text'     => __( 'Plain text', 'wpcf' )
						),
						'defaultForceValue' => 'text'
					),
					'format' => array(
						'label'        => __( 'Format', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							$date_site_settings_formatted => $date_site_settings_formatted . ' - ' . date_i18n( $date_site_settings ) . $date_site_settings_info,
							'F j, Y g:i a'  => 'F j, Y g:i a - ' . date_i18n( 'F j, Y g:i a' ),
							'F j, Y'        => 'F j, Y - ' . date_i18n( 'F j, Y' ),
							'd/m/y'         => 'd/m/y - ' . date_i18n( 'd/m/y' ),
							'toolsetCombo'  => __( 'Custom', 'wpcf' )
						),
						'defaultForceValue' => $date_site_settings_formatted
					),
					'toolsetCombo:format' => array(
						'type'        => 'text',
						'hidden'      => true,
						'placeholder' => 'l, F j, Y',
						'description' => $info_about_escaping
					)
				)
			)
		);

		// Email is OK
		// class attribute is not working
		$attributes['email'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Email link', 'wpcf' ),
							'raw'    => __( 'Raw email address', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'title' => array(
						'label'        => __( 'Email link label', 'wpcf' ),
						'type'         => 'text',
						'defaultValue' => '',
						'description'  => __( 'If empty, the same email address value will be used instead', 'wpcf' )
					),
					'attributesCombo' => array(
						'type'   => 'group',
						'fields' => array(
							'class' => array(
								'label' => __( 'Email link extra classes', 'wpcf' ),
								'type'        => 'text'
							),
							'style' => array(
								'label' => __( 'Email link inline style', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Include specific classnames in the email link, or add your own inline styles.', 'wpcf' )
					)
				)
			)
		);

		// Embed is OK
		$attributes['embed'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Embed player', 'wpcf' ),
							'raw'    => __( 'Raw field content', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'sizeCombo' => array(
						'label'  => __( 'Size', 'wpcf' ),
						'type'   => 'group',
						'fields' => array(
							'width' => array(
								'pseudolabel' => __( 'Width', 'wpcf' ),
								'type'        => 'text'
							),
							'height' => array(
								'pseudolabel' => __( 'Height', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Images will be resized before being sent to the client. width and height will be ignored if size is set. For Embedded media the width and height are maximum values and may be ignored if $content_width is set for the theme.', 'wpcf' )
					)
				)
			)
		);

		// File is OK
		$attributes['file'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'File link', 'wpcf' ),
							'raw'    => __( 'Raw file URL', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'title' => array(
						'label' => __( 'File link title', 'wpcf' ),
						'type'  => 'text'
					),
					'attributesCombo' => array(
						'type'   => 'group',
						'fields' => array(
							'class' => array(
								'label' => __( 'File link extra classes', 'wpcf' ),
								'type'        => 'text'
							),
							'style' => array(
								'label' => __( 'File link inline style', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Include specific classnames in the file link, or add your own inline styles.', 'wpcf' )
					)
				)
			)
		);

		// Image is OK
		// class attribute is not working
		$image_size_attributes = array(
			'full' => __( 'Original image', 'wpcf' ),
			'thumbnail' => sprintf( __( 'Thumbnail - %s', 'wpcf' ),
					get_option( 'thumbnail_size_w' ) . 'x' . get_option( 'thumbnail_size_h' ) ),
			'medium' => sprintf( __( 'Medium - %s', 'wpcf' ),
					get_option( 'medium_size_w' ) . 'x' . get_option( 'medium_size_h' ) ),
			'large' => sprintf( __( 'Large - %s', 'wpcf' ),
					get_option( 'large_size_w' ) . 'x' . get_option( 'large_size_h' ) ),
		);
		$wp_image_sizes = (array) get_intermediate_image_sizes();
		foreach ( $wp_image_sizes as $wp_size ) {
			if (
				$wp_size != 'post-thumbnail'
				&& ! array_key_exists( $wp_size, $image_size_attributes )
			) {
				$image_size_attributes[ $wp_size ] = $wp_size;
			}
		}
		$image_size_attributes['custom'] = __( 'Custom size...', 'wpcf' );
		$attributes['image'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Image tag', 'wpcf' ),
							'raw'    => __( 'Raw field value', 'wpcf' ),
							'url'    => __( 'URL of a resized version of the image', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'titleAltCombo' => array(
						'label'      => __( 'Title and alternative text', 'wpcf' ),
						'type'       => 'group',
						'fields'     => array(
							'title' => array(
								'pseudolabel'       => __( 'Title', 'wpcf' ),
								'type'              => 'text',
								'defaultForceValue' => '%%TITLE%%'
							),
							'alt'   => array(
								'pseudolabel'       => __( 'Alt', 'wpcf' ),
								'type'              => 'text',
								'defaultForceValue' => '%%ALT%%'
							)
						),
						'description' => __( 'For the alt and title attributes, you can also use placeholders to output the values of standard image fields added in WordPress: %%TITLE%%, %%ALT%%, %%CAPTION %%, and %%DESCRIPTION%%.', 'wpcf' )
					),
					'align' => array(
						'label'        => __( 'Align', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'none'   => __( 'None', 'wpcf' ),
							'left'   => __( 'Left', 'wpcf' ),
							'center' => __( 'Center', 'wpcf' ),
							'right'  => __( 'Right', 'wpcf' )
						),
						'defaultValue' => 'none'
					),
					'size' => array(
						'label'             => __( 'Image size', 'wpcf' ),
						'type'              => 'radio',
						'options'           => $image_size_attributes,
						'defaultForceValue' => 'full'
					),
					// with size='custom'
					'sizeCombo' => array(
						'label'  => __( 'Size', 'wpcf' ),
						'type'   => 'group',
						'fields' => array(
							'width' => array(
								'pseudolabel' => __( 'Width', 'wpcf' ),
								'type'        => 'text'
							),
							'height' => array(
								'pseudolabel' => __( 'Height', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Image will be resized before being sent to the client. width and height will be ignored if size is set. For Embedded media the width and height are maximum values and may be ignored if $content_width is set for the theme.', 'wpcf' )
					),
					// with size!='full'
					'proportional' => array(
						'label'        => __( 'Proportional', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'false' => __( 'Do not keep image proportional', 'wpcf' ),
							'true'  => __( 'Keep image proportional', 'wpcf' )
						),
						'defaultValue' => 'true',
						'description'  => __( 'Image will be cropped to specified height and width. Overridden if size is set', 'wpcf' )
					),
					// with size!='full'
					'resize' => array(
						'label'             => __( 'Image adjustment', 'wpcf' ),
						'type'              => 'radio',
						'options'           => array(
							'proportional' => __( 'Resize images to fit inside the new size. Width or height might be smaller than the specified dimensions', 'wpcf' ),
							'crop'         => __( 'Crop images, so that they fill the specified dimensions exactly', 'wpcf' ),
							'stretch'      => __( 'Stretch images', 'wpcf' ),
							'pad'          => __( 'Pad images, so that they fill the specified dimensions exactly', 'wpcf' )
						),
						'defaultForceValue' => 'proportional'
					),
					// with size!='full' and resize='pad'
					'padding_color' => array(
						'label'        => __( 'Padding color', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'transparent'  => __( 'Transparent', 'wpcf' ),
							'toolsetCombo' => __( 'Custom', 'wpcf' )
						),
						'defaultValue' => 'transparent',
					),
					// with size!='full' and resize='pad'
					'toolsetCombo:padding_color' => array(
						'type'        => 'text',
						'description' => __( 'Hex color', 'wpcf' )
					),
					'attributesCombo' => array(
						'type'   => 'group',
						'fields' => array(
							'class' => array(
								'label' => __( 'Image tag extra classes', 'wpcf' ),
								'type'        => 'text'
							),
							'style' => array(
								'label' => __( 'Image tag inline style', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Include specific classnames in the image tag, or add your own inline styles.', 'wpcf' )
					)
				)
			)
		);

		// Numeric is OK
		$attributes['numeric'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'As described in the following format', 'wpcf' ),
							'raw'    => __( 'Raw value from the database', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'format' => array(
						'label'       => __( 'Format', 'wpcf' ),
						'type'        => 'text',
						'required'    => true,
						'defaultForceValue' => 'FIELD_NAME: FIELD_VALUE',
						'description' => __( 'Use the placeholders FIELD_NAME and FIELD_VALUE in the format to ouput the name and value', 'wpcf' )
					)
				)
			)
		);

		// Phone is OK
		$attributes['phone'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array()
			)
		);

		// Post seems to be OK
		// This might not be needed at all, could be removed
		$attributes['post'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array()
			)
		);

		// Radio is OK
		// Radio fields offer one custom output per value
		$attributes['radio'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Display value of the selected option', 'wpcf' ),
							'raw'    => __( 'Raw database value of the selected option', 'wpcf' ),
							'custom' => __( 'Custom values for selected and not selected states', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'outputCustomCombo' => array(
						'label'  => __( 'Custom values', 'wpcf' ),
						'type'   => 'group',
						'hidden' => true,
						'fields' => array(
							'selectedValue' => array(
								'pseudolabel' => __( '%%OPTION%% selected', 'wpcf' ),
								'type'        => 'text'
							)
						)
					)
				)
			)
		);

		// Select is OK
		$attributes['select'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Display value of the selected option', 'wpcf' ),
							'raw'    => __( 'Raw database value of the selected option', 'wpcf' )
						),
						'defaultValue' => 'normal'
					)
				)
			)
		);

		// Skype is OK
		// class attribute is not working
		$attributes['skype'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Rich Skype button', 'wpcf' ),
							'raw'    => __( 'Raw Skype name', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'skype_button_style' => array(
						'label' => __( 'Button', 'wpcf' ),
						'type' => 'group',
						'fields' => array(
							'button' => array(
								'pseudolabel' => __( 'Style', 'wpcf' ),
								'type'  => 'select',
								'options' => array(
									'bubble' => __( 'Bubble (fixed position at the bottom right of the window)', 'wpcf'),
									'rounded' => __( 'Rounded (in place of the shortcode)', 'wpcf'),
									'rectangle' => __( 'Rectangle (in place of the shortcode)', 'wpcf'),
								)
							),
							'button-color' => array(
								'pseudolabel' => __( 'Color', 'wpcf' ),
								'type'  => 'text',
								'defaultValue' => '#00AFF0'
							),
						)
					),
					'skype_button_style_enhanced' => array(
						'pseudolabel' => __( 'Button Style', 'wpcf' ),
						'type' => 'group',
						'fields' => array(
							'button-icon'   => array(
								'pseudolabel' => __( 'Icon', 'wpcf' ),
								'type'  => 'select',
								'options' => array(
									'enabled' => __( 'Enabled', 'wpcf'),
									'disabled' => __( 'Disabled', 'wpcf'),
								),
								'defaultValue' => 'enabled'
							),
							'button-label'   => array(
								'pseudolabel' => __( 'Label', 'wpcf' ),
								'type'  => 'text',
								'placeholder' => 'Contact Us'
							),
						)
					),

					'skype_chat_style' => array(
						'label' => __( 'Chat Color', 'wpcf' ),
						'type' => 'group',
						'fields' => array(
							'chat-color' => array(
								'type'  => 'text',
								'defaultValue' => '#80DDFF'
							),
						)
					),

					'skype_preview' => array(
						'label' => __( 'Preview', 'wpcf' ),
						'type' => 'skype',
						'i18n' => array(
							'button' => __( 'Button', 'wpcf' ),
							'chat' => __( 'Chat', 'wpcf' ),
							'preview_title' => __( 'Preview', 'wpcf' ),
							'cdn_not_reachable' => __( 'The required files for the preview could not be loaded from the Skype servers. Continue without preview or try again later.', 'wpcf' )
						)
					),

					'receiver' => array(
						'label' => __( 'Treat field value as' ),
						'type' => 'select',
						'options' => array(
							'user' => __( 'User (Skype User ID)', 'wpcf'),
							'bot' => __( 'Bot (Microsoft App ID)', 'wpcf'),
						),
						'defaultValue' => 'user',
						'description' => sprintf( __( 'To learn more about Skype Bots see %s', 'wpcf' ),
							'<a href="https://dev.skype.com/bots" target="_blank">https://dev.skype.com/bots</a>' )
					),

					'class' => array(
						'label'       => __( 'Skype button extra classes', 'wpcf' ),
						'type'        => 'text',
						'description' => __( 'Include specific classnames in the Skype button.', 'wpcf' )
					)
				)
			)
		);

		// Textarea is OK
		$attributes['textarea'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Normal', 'wpcf' ),
							'raw'    => __( 'Raw', 'wpcf' )
						),
						'defaultValue' => 'normal'
					)
				)
			)
		);

		// Textfield is OK
		$attributes['textfield'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array()
			)
		);

		// URL is OK
		// class attribute is not working
		$attributes['url'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'URL link', 'wpcf' ),
							'raw'    => __( 'Raw URL', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'title' => array(
						'label'        => __( 'URL link label', 'wpcf' ),
						'type'         => 'text',
						'defaultValue' => '',
						'description'  => __( 'If empty, the same URL value will be used instead', 'wpcf' )
					),
					'attributesCombo' => array(
						'type'   => 'group',
						'fields' => array(
							'class' => array(
								'label' => __( 'URL link extra classes', 'wpcf' ),
								'type'        => 'text'
							),
							'style' => array(
								'label' => __( 'URL link inline style', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Include specific classnames in the URL link, or add your own inline styles.', 'wpcf' )
					),
					'target' => array(
						'label'        => __( 'Open link in:', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'_blank'       => __( 'New window or tab', 'wpcf' ),
							'_self'        => __( 'Same frame as clicked', 'wpcf' ),
							'_parent'      => __( 'Parent frame', 'wpcf' ),
							'_top'         => __( 'Full body of the window', 'wpcf' ),
							'toolsetCombo' => __( 'Named frame', 'wpcf' )
						),
						'defaultValue' => '_self'
					),
					'toolsetCombo:target' => array(
						'label'        => __( 'Name of the frame', 'wpcf' ),
						'type'        => 'text',
						'hidden'      => true
					)
				)
			)
		);

		// Video is OK
		$attributes['video'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Normal', 'wpcf' ),
							'raw'    => __( 'Raw', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'sizeCombo' => array(
						'label'  => __( 'Size', 'wpcf' ),
						'type'   => 'group',
						'fields' => array(
							'width' => array(
								'pseudolabel' => __( 'Width', 'wpcf' ),
								'type'        => 'text'
							),
							'height' => array(
								'pseudolabel' => __( 'Height', 'wpcf' ),
								'type'        => 'text'
							)
						),
						'description' => __( 'Image will be resized before being sent to the client. width and height will be ignored if size is set. For Embedded media the width and height are maximum values and may be ignored if $content_width is set for the theme.', 'wpcf' )
					),
					'poster' => array(
						'label'       => __( 'Poster cover image URL', 'wpcf' ),
						'type'        => 'text',
						'description' => __( 'The poster image will be displayed while the video is not playing. The image height will be set to match the height of the video container', 'wpcf' )
					),
					'preload' => array(
						'label'        => __( 'Preload', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'on'  => __( 'Begin the media download as soon as the page is loaded', 'wpcf' ),
							'off' => __( 'Hold the media download until the visitor plays it', 'wpcf' )
						),
						'defaultValue' => 'off'
					),
					'loop' => array(
						'label'        => __( 'Loop', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'on'  => __( 'Replay the audio field once it reaches the end', 'wpcf' ),
							'off' => __( 'Stop the audio play when it reaches the end', 'wpcf' )
						),
						'defaultValue' => 'off'
					),
					'autoplay' => array(
						'label'        => __( 'Autoplay', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'on'  => __( 'Begin the media play as soon as the page is loaded', 'wpcf' ),
							'off' => __( 'Hold the media play until the visitor starts it', 'wpcf' )
						),
						'defaultValue' => 'off'
					)
				)
			)
		);

		// WYSIWYG is OK
		$attributes['wysiwyg'] = array(
			'displayOptions' => array(
				'header' => __( 'Display options', 'wpcf' ),
				'fields' => array(
					'output' => array(
						'label'        => __( 'Output mode', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'normal' => __( 'Normal', 'wpcf' ),
							'raw'    => __( 'Raw', 'wpcf' )
						),
						'defaultValue' => 'normal'
					),
					'suppress_filters' => array(
						'label'        => __( 'Third party filters', 'wpcf' ),
						'type'         => 'radio',
						'options'      => array(
							'true'  => __( 'Do not apply third party filters to the output', 'wpcf' ),
							'false' => __( 'Apply third party filters to the output', 'wpcf' )
						),
						'defaultValue' => 'false'
					)
				)
			)
		);

		$attributes = apply_filters( 'types_extend_fields_expected_attributes', $attributes );

		return $attributes;
	}

	/**
	 * Generate the templates needed for Types shortcodes:
	 * - typesUserSelector for usermeta fields
	 * - typesViewsUserSelector for usermeta fields in scenarios where the View target is users
	 * - typesViewsTermSelector for termmeta fields in scenarios where the View target is terms
	 *
	 * @since m2m
	 */
	private function generate_shortcode_templates() {
		$toolset_ajax = Toolset_Ajax::get_instance();
		$template_repository = Types_Output_Template_Repository::get_instance();
		$renderer = Toolset_Renderer::get_instance();
		?>
		<script type="text/html" id="tmpl-toolset-shortcode-attribute-typesUserSelector">
			<ul id="{{{data.shortcode}}}-{{{data.attribute}}}">
				<li class="toolset-shortcode-gui-item-selector-option">
					<label for="toolset-shortcode-gui-item-selector-user-id-author">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-user-id-author" name="toolset_shortcode_gui_object_id" value="author" checked="checked" />
						<?php _e( 'Author of the current post', 'wpcf' ); ?>
					</label>
				</li>

				<li class="toolset-shortcode-gui-item-selector-option">
					<label for="toolset-shortcode-gui-item-selector-user-id-current">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-user-id-current" name="toolset_shortcode_gui_object_id" value="current" />
						<?php _e( 'The current logged in user', 'wpcf' ); ?>
					</label>
				</li>

				<li class="toolset-shortcode-gui-item-selector-option toolset-shortcode-gui-item-selector-has-related js-toolset-shortcode-gui-item-selector-has-related">
					<label for="toolset-shortcode-gui-item-selector-user-id">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-user-id" name="toolset_shortcode_gui_object_id" value="object_id" />
						<?php _e( 'A specific user', 'wpcf' ); ?>
					</label>
					<div class="toolset-advanced-settingtoolset-shortcode-gui-item-selector-is-related js-toolset-shortcode-gui-item-selector-is-related" style="display:none;padding-top:10px;">
						<select id="toolset-shortcode-gui-item-selector-user-id-object_id"
							class="js-toolset-shortcode-gui-item-selector_object_id js-toolset-shortcode-gui-field-ajax-select2"
							name="specific_object_id"
							data-action="<?php echo esc_attr( $toolset_ajax->get_action_js_name( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_USERS ) ); ?>"
							data-prefill="<?php echo esc_attr( $toolset_ajax->get_action_js_name( Toolset_Ajax::CALLBACK_GET_USER_BY_ID ) ); ?>"
							data-nonce="<?php echo wp_create_nonce( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_USERS ); ?>"
							data-prefill-nonce="<?php echo wp_create_nonce( Toolset_Ajax::CALLBACK_GET_USER_BY_ID ); ?>"
							data-placeholder="<?php echo esc_attr( __( 'Search for a user', 'wpcf' ) ); ?>">
						</select>
					</div>
				</li>
			</ul>
		</script>
		<script type="text/html" id="tmpl-toolset-shortcode-attribute-typesViewsUserSelector">
			<ul id="{{{data.shortcode}}}-{{{data.attribute}}}">
				<li class="toolset-shortcode-gui-item-selector-option">
					<label for="toolset-shortcode-gui-item-selector-user-id-viewloop">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-user-id-viewloop" name="toolset_shortcode_gui_object_id" value="viewloop" checked="checked" />
						<?php _e( 'The current user in the loop', 'wpcf' ); ?>
					</label>
				</li>

				<li class="toolset-shortcode-gui-item-selector-option">
					<label for="toolset-shortcode-gui-item-selector-user-id-current">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-user-id-current" name="toolset_shortcode_gui_object_id" value="current" />
						<?php _e( 'The current logged in user', 'wpcf' ); ?>
					</label>
				</li>

				<li class="toolset-shortcode-gui-item-selector-option toolset-shortcode-gui-item-selector-has-related js-toolset-shortcode-gui-item-selector-has-related">
					<label for="toolset-shortcode-gui-item-selector-user-id">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-user-id" name="toolset_shortcode_gui_object_id" value="object_id" />
						<?php _e( 'A specific user', 'wpcf' ); ?>
					</label>
					<div class="toolset-advanced-setting toolset-shortcode-gui-item-selector-is-related js-toolset-shortcode-gui-item-selector-is-related" style="display:none;padding-top:10px;">
						<select id="toolset-shortcode-gui-item-selector-user-id-object_id"
							class="js-toolset-shortcode-gui-item-selector_object_id js-toolset-shortcode-gui-field-ajax-select2"
							name="specific_object_id"
							data-action="<?php echo esc_attr( $toolset_ajax->get_action_js_name( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_USERS ) ); ?>"
							data-prefill="<?php echo esc_attr( $toolset_ajax->get_action_js_name( Toolset_Ajax::CALLBACK_GET_USER_BY_ID ) ); ?>"
							data-nonce="<?php echo wp_create_nonce( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_USERS ); ?>"
							data-prefill-nonce="<?php echo wp_create_nonce( Toolset_Ajax::CALLBACK_GET_USER_BY_ID ); ?>"
							data-placeholder="<?php echo esc_attr( __( 'Search for a user', 'wpcf' ) ); ?>">
						</select>
					</div>
				</li>
			</ul>
		</script>
		<script type="text/html" id="tmpl-toolset-shortcode-attribute-typesViewsTermSelector">
			<ul id="{{{data.shortcode}}}-{{{data.attribute}}}">
				<li class="toolset-shortcode-gui-item-selector-option">
					<label for="toolset-shortcode-gui-item-selector-term-id-viewloop">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-term-id-viewloop" name="toolset_shortcode_gui_object_id" value="viewloop" checked="checked" />
						<?php _e( 'The current term in the loop', 'wpcf' ); ?>
					</label>
				</li>

				<li class="toolset-shortcode-gui-item-selector-option toolset-shortcode-gui-item-selector-has-related js-toolset-shortcode-gui-item-selector-has-related">
					<label for="toolset-shortcode-gui-item-selector-term-id">
						<input type="radio" class="js-toolset-shortcode-gui-item-selector" id="toolset-shortcode-gui-item-selector-term-id" name="toolset_shortcode_gui_object_id" value="object_id" />
						<?php _e( 'A specific term', 'wpcf' ); ?>
					</label>
					<div class="toolset-advanced-setting toolset-shortcode-gui-item-selector-is-related js-toolset-shortcode-gui-item-selector-is-related" style="display:none;padding-top:10px;">
						<select id="toolset-shortcode-gui-item-selector-term-id-object_id"
							class="js-toolset-shortcode-gui-item-selector_object_id js-toolset-shortcode-gui-field-ajax-select2"
							name="specific_object_id"
							data-action="<?php echo esc_attr( $toolset_ajax->get_action_js_name( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_TERMS ) ); ?>"
							data-prefill="<?php echo esc_attr( $toolset_ajax->get_action_js_name( Toolset_Ajax::CALLBACK_GET_TERM_BY_ID ) ); ?>"
							data-nonce="<?php echo wp_create_nonce( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_TERMS ); ?>"
							data-prefill-nonce="<?php echo wp_create_nonce( Toolset_Ajax::CALLBACK_GET_TERM_BY_ID ); ?>"
							data-placeholder="<?php echo esc_attr( __( 'Search for a term', 'wpcf' ) ); ?>">
						</select>
					</div>
				</li>
			</ul>
		</script>
		<?php
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_POST_REFERENCE_FIELD_TEMPLATE ),
			null
		);
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_POST_REFERENCE_FIELD_WIZARD_FIRST_TEMPLATE ),
			null
		);
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_POST_REFERENCE_FIELD_WIZARD_SECOND_TEMPLATE ),
			null
		);
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_POST_REFERENCE_FIELD_WIZARD_THIRD_TEMPLATE ),
			null
		);

		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_REPEATING_FIELDS_GROUP_TEMPLATE ),
			null
		);
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_REPEATING_FIELDS_GROUP_WIZARD_FIRST_TEMPLATE ),
			null
		);
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_REPEATING_FIELDS_GROUP_WIZARD_SECOND_TEMPLATE ),
			null
		);
		$renderer->render(
			$template_repository->get( Types_Output_Template_Repository::INSERT_REPEATING_FIELDS_GROUP_WIZARD_THIRD_TEMPLATE ),
			null
		);
	}

	/*
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
	 * @since m2m
	 */

	public function gform_noconflict_scripts( $required_objects ) {
		$required_objects[] = 'types-shortcode';
		return $required_objects;
	}
	public function gform_noconflict_styles( $required_objects ) {
		$required_objects[] = 'toolset-common';
		$required_objects[] = 'toolset-dialogs-overrides-css';
		$required_objects[] = 'onthego-admin-styles';
		return $required_objects;
	}


}
