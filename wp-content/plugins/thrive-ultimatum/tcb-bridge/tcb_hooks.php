<?php
/**
 * Used to group all the functionality of the TCB bridge into the existing TCB plugin (or the bundled tcb plugin)
 * This is included for both stand-alone TCB installations and for bundled tcb installations
 */

/**
 * Filter that gets called when the following situation occurs:
 * TCB is installed and enabled, but there is no active license activated
 * in this case, we should only allow users to edit: tve_ult_campaign
 */
add_filter( 'tcb_skip_license_check', 'tve_ult_tcb_license_override' );

/**
 * called when enqueuing scripts from the editor on editor page. it needs to check if TU has a valid license
 */
add_filter( 'tcb_user_can_edit', 'tve_ult_editor_check_license' );

/**
 * called when enqueuing scripts on editor pages. It checks if the separate TCB plugin has the required version
 */
add_filter( 'tcb_user_can_edit', 'tve_ult_editor_check_tcb_version' );

/**
 * get the editing layout for designs
 */
add_filter( 'tcb_custom_post_layouts', 'tve_ult_editor_layout', 10, 3 );

/**
 * hook for adding a "Choose Opt-in Template" button which is based on the current state of the edit page and on the actual content being edited
 */
add_action( 'tcb_custom_top_buttons', 'tve_ult_add_template_button', 10, 3 );

/**
 * modify the localization parameters for the javascript on the editor page (in editing mode)
 */
add_filter( 'tcb_editor_javascript_params', 'tve_ult_editor_javascript_params', 10, 3 );

/**
 * main entry point for template-related actions: choose new template, reset current template
 */
add_action( 'wp_ajax_' . TVE_Ult_Const::ACTION_TEMPLATE, 'tve_ult_template_action' );

/**
 * main entry point for state-related actions: add state, delete state, edit state name
 */
add_action( 'wp_ajax_' . TVE_Ult_Const::ACTION_STATE, 'tve_ult_state_action' );

/**
 * action hook that overrides the default tve_save_post action from the editor
 * used to save the editor contents in custom post fields specific
 */
add_action( 'wp_ajax_' . TVE_Ult_Const::ACTION_SAVE_DESIGN_CONTENT, 'tve_ult_editor_save_content' );

/**
 * we need to modify the preview URL for tve_form_type post types
 */
add_filter( 'tcb_editor_preview_link_query_args', 'tve_ult_editor_append_preview_link_args', 10, 2 );

/**
 * output any lightbox html that is needed on the design (TCB editor) page
 */
add_action( 'tcb_static_lightbox', 'tve_ult_editor_static_lightbox' );

/**
 * custom element menu
 * include the html needed for the general design settings editor control panel
 */
add_action( 'tcb_custom_menus_html', 'tve_ult_output_custom_menu' );

/* TCB Menu Elements */
add_action( 'tcb_custom_menus_html', 'tve_ult_add_tcb_menu_elements' );

/* TCB Advanced Elements */
add_action( 'tcb_advanced_elements_html', 'tve_ult_add_tcb_advanced_elements' );

/**
 * Main cpanel configuration
 */
add_filter( 'tcb_main_cpanel_config', 'tve_ult_main_cpanel_config' );

/**
 * called when there is no active license for TCB, but it is installed and enabled
 * the function returns true only for pieces of content that "belong" to Thrive Ultimatum, so only the following:
 *
 * @param bool $value
 *
 * @return bool whether or not the current piece of content can be edited with TCB core functions
 */
function tve_ult_tcb_license_override( $value ) {
	/* this means that the license check should be skipped, possibly from thrive leads */
	if ( $value ) {
		return true;
	}
	$post_type = get_post_type();

	return tve_ult_post_type_editable( $post_type );
}

/**
 * check if a post type from Thrive Ultimatum is editable with tcb.
 *
 * @param mixed $post_id_or_type if is_numeric -> consider it as ID
 *
 * @return bool
 */
function tve_ult_post_type_editable( $post_id_or_type ) {
	$post_type = is_numeric( $post_id_or_type ) ? get_post_type( $post_id_or_type ) : $post_id_or_type;

	return in_array( $post_type, array(
		TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN
	) );
}

/**
 * Checks if TU license if valid (only if the user is trying to edit a design)
 *
 * @param bool $valid
 *
 * @return bool
 */
function tve_ult_editor_check_license( $valid ) {
	if ( empty( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		return $valid;
	}

	if ( ! tve_ult_license_activated() ) {
		add_action( 'wp_print_footer_scripts', 'tve_leads_license_warning' );

		return false;
	}

	return true;
}

/**
 * Check if TCB version is valid
 *
 * @param bool $valid
 *
 * @return bool
 */
function tve_ult_editor_check_tcb_version( $valid ) {
	if ( empty( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		return $valid;
	}

	if ( ! $valid ) {
		return false;
	}

	if ( ! tve_ult_check_tcb_version() ) {
		add_action( 'wp_print_footer_scripts', 'tve_ult_tcb_version_warning' );

		return false;
	}

	return true;
}

/**
 * show a box with a warning message notifying the user to update the TCB plugin to the latest version
 * this will be shown only when the TCB version is lower than a minimum required version
 */
function tve_ult_tcb_version_warning() {
	return include TVE_Ult_Const::plugin_path( 'admin/views/tcb_version_incompatible.php' );
}

/**
 * show a box with a warning message and a link to take the user to the license activation page
 * this will be called only when no valid / activated license has been found
 *
 * @return mixed
 */
function tve_ult_license_warning() {
	return include TVE_Ult_Const::plugin_path( 'admin/views/license_inactive.php' );
}

/**
 * Callback for "tcb_custom_post_layouts" filter applied by TCB
 *
 * @param $current_templates
 * @param $post_id
 * @param $post_type
 *
 * @return array of layouts
 */
function tve_ult_editor_layout( $current_templates, $post_id, $post_type ) {

	global $design;

	if ( ! tve_ult_is_editable( $post_type ) ) {
		return $current_templates;
	}

	/* handles the following case: user refreshes the page when editing a child state - the child state should be directly opened after refresh */
	if ( is_editor_page() ) {
		$last_edited_state_key = get_post_meta( $post_id, TVE_Ult_Const::META_PREFIX_NAME_FOR_EDIT_STATE . $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ], true );
		if ( ! empty( $last_edited_state_key ) ) {
			$design = tve_ult_get_design( $last_edited_state_key );
		}
	}

	if ( empty( $design ) ) {
		$design = tve_ult_get_design( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] );
	}

	if ( empty( $design ) ) {
		return $current_templates;
	}

	$current_templates['campaign_design'] = TVE_Ult_Const::plugin_path() . 'editor/layouts/campaign/' . TU_Template_Manager::type( $design['post_type'] ) . '.php';

	if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {

		$config = tve_ult_editor_get_template_config( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

		/* custom fonts for the design */
		if ( ! empty( $config['fonts'] ) ) {
			foreach ( $config['fonts'] as $font ) {
				wp_enqueue_style( 'tve-ult-font-' . md5( $font ), $font );
			}
		}

		/* include also the CSS for each design template */
		if ( ! empty( $config['css'] ) ) {
			$css_handle = 'tve-ult-' . TU_Template_Manager::type( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) . '-' . str_replace( '.css', '', $config['css'] );
			tve_ult_enqueue_style( $css_handle, TVE_Ult_Const::plugin_url( 'editor-templates/css/' . TU_Template_Manager::type( $design['post_type'] ) . '/' . $config['css'] ) );
		}

		/**
		 * reset the design html to default
		 */
		if ( file_exists( TVE_Ult_Const::plugin_path( '.ui-develop' ) ) ) {
			$design[ TVE_Ult_Const::FIELD_CONTENT ] = tve_ult_editor_get_template_content( $design );
			tve_ult_save_design( $design );
		}
	}

	/* flat is the default style for Thrive Ultimatum designs */
	global $tve_style_family_classes;
	$tve_style_families = tve_get_style_families();
	$style_family       = 'Flat';
	$style_key          = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $style_family ] );
	/* Style family */
	wp_style_is( $style_key ) || tve_enqueue_style( $style_key, $tve_style_families[ $style_family ] );

	tve_ult_enqueue_style( 'tve-ult-design', TVE_Ult_Const::plugin_url( 'editor/layouts/css/editor.css' ) );

	if ( is_editor_page() ) {

		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';
		tve_ult_enqueue_script( 'tve-ult-editor', TVE_Ult_Const::plugin_url( 'js/dist/editor' . $js_suffix ), array( 'tve_editor' ) );

		$page_data = array(
			'design_id'    => $design['id'],
			'post_id'      => $design['post_parent'],
			'tpl_action'   => TVE_Ult_Const::ACTION_TEMPLATE,
			'state_action' => TVE_Ult_Const::ACTION_STATE,
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'has_content'  => ! empty( $design['content'] ),
			'security'     => wp_create_nonce( 'tve-ult-verify-track-sender-672' ),
			'current_css'  => ! empty( $css_handle ) ? $css_handle : '',
			'L'            => array(
				'alert_choose_tpl'         => __( 'Please choose a template', TVE_Ult_Const::T ),
				'confirm_tpl_reset'        => __( 'Are you sure you want to reset this Design to the default template? This action cannot be undone', TVE_Ult_Const::T ),
				'tpl_name_required'        => __( 'Please enter a template name, it will be easier to reload it after.', TVE_Ult_Const::T ),
				'tpl_confirm_delete'       => __( 'Are you sure you want to delete this saved template? This action cannot be undone', TVE_Ult_Const::T ),
				'only_one_subscribed'      => __( 'You can only have one Already Subscribed state for a design', TVE_Ult_Const::T ),
				'confirm_state_delete'     => __( 'Are you sure you want to delete this state?', TVE_Ult_Const::T ),
				'confirm_multi_step'       => __( 'If you choose a multi-step template, ALL CURRENT STATES WILL BE DELETED AND RE-CREATED. Do you want to continue?', TVE_Ult_Const::T ),
				'state_name_required'      => __( 'Please enter a name for the state so you can easily identify it', TVE_Ult_Const::T ),
				'template_deleted'         => __( 'Template deleted.', TVE_Ult_Const::T ),
				'fetching_saved_templates' => __( 'Fetching saved templates...', TVE_Ult_Const::T ),
			),
		);
		wp_localize_script( 'tve-ult-editor', 'tve_ult_page_data', $page_data );
	} else {
		//this is the preview page
		tve_ult_enqueue_default_scripts();
	}

	$globals = ! empty( $design[ TVE_Ult_Const::FIELD_GLOBALS ] ) ? $design[ TVE_Ult_Const::FIELD_GLOBALS ] : array();
	if ( ! empty( $globals['js_sdk'] ) ) {
		foreach ( $globals['js_sdk'] as $handle ) {
			$link                          = tve_social_get_sdk_link( $handle );
			$js[ 'tve_js_sdk_' . $handle ] = $link;

			wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
		}
	}

	add_action( 'wp_enqueue_scripts', 'tve_ult_enqueue_design_scripts' );

	return $current_templates;
}

/**
 * Check if a Thrive Ultimatum post is editable with TCB
 *
 * @param $post_or_type
 *      string  post type
 *      int     post it
 *
 * @return bool
 */
function tve_ult_is_editable( $post_or_type ) {
	$post_or_type = is_numeric( $post_or_type ) ? get_post_type( $post_or_type ) : $post_or_type;

	return in_array( $post_or_type, array( TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) );
}

/**
 * This is the main controller for editor and preview page
 *
 * @param array $design
 * @param array $is_editor_or_preview true if we are on the editor / preview page
 *
 * @return string
 */
function tve_ult_editor_custom_content( $design, $is_editor_or_preview = true ) {

	if ( empty( $design ) ) {
		return __( 'Design cannot be empty', TVE_Ult_Const::T );
	}

	$tve_saved_content = $design[ TVE_Ult_Const::FIELD_CONTENT ];

	/**
	 * if in editor page or preview, replace the data-date attribute for the countdown timers with the current_date + 1 day (just for demo purposes)
	 */
	if ( $is_editor_or_preview ) {
		$tomorrow          = tve_ult_current_time( 'timestamp' ) + DAY_IN_SECONDS;
		$tve_saved_content = preg_replace( '#data-dd="(\d+)"#', '', $tve_saved_content );
		$tve_saved_content = preg_replace( '#data-date="(\d+)-(\d+)-(\d+)"#', 'data-dd="2" data-date="' . date( 'Y-m-d', $tomorrow ) . '"', $tve_saved_content );
		$tve_saved_content = preg_replace( '#data-hour="(\d+)"#', 'data-hour="' . date( 'H', $tomorrow ) . '"', $tve_saved_content );
		$tve_saved_content = preg_replace( '#data-timezone="(.+?)"#', 'data-timezone="' . tve_ult_get_timezone_format() . '"', $tve_saved_content );
		$tve_saved_content = preg_replace( '#data-min="(.+?)"#', 'data-min="' . date( 'i' ) . '"', $tve_saved_content );
	}

	/* this will hold the html for the tinymce editor instantiation, only if we're on the editor page */
	$tinymce_editor = $page_loader = '';

	$is_editor_page = $is_editor_or_preview && tve_ult_is_editor_page();

	/**
	 * this means we are getting the content to output it on a targeted page => include also the custom CSS rules
	 */
	$custom_css = tve_ult_editor_output_custom_css( $design, true );

	/**
	 * style family class should always be Flat
	 */
	$style_family_class = 'tve_flt';

	$style_family_id = $is_editor_or_preview ? ' id="' . $style_family_class . '" ' : ' ';

	$wrap = array(
		'start' => '<div' . $style_family_id . 'class="' . $style_family_class . '">',
		'end'   => '</div>',
	);

	$wrap['start'] .= '<div id="tve_editor" class="tve_shortcode_editor">';
	$wrap['end'] .= '</div>';

	if ( $is_editor_page ) {

		add_action( 'wp_footer', 'tve_output_wysiwyg_editor' );

		$page_loader = '';

	} else {

		$tve_saved_content = tve_restore_script_tags( $tve_saved_content );

		/* prepare Events configuration */
		tve_parse_events( $tve_saved_content );
	}

	/**
	 * custom Thrive shortcodes
	 */
	$tve_saved_content = tve_thrive_shortcodes( $tve_saved_content, $is_editor_page );

	/* render the content added through WP Editor (element: "WordPress Content") */
	$tve_saved_content = tve_do_wp_shortcodes( $tve_saved_content, $is_editor_page );

	if ( ! $is_editor_page ) {
		$tve_saved_content = shortcode_unautop( $tve_saved_content );
		$tve_saved_content = do_shortcode( $tve_saved_content );
	}

	$tve_saved_content = preg_replace_callback( '/__CONFIG_lead_generation__(.+?)__CONFIG_lead_generation__/s', 'tcb_lg_err_inputs', $tve_saved_content );

	if ( ! $is_editor_page ) {
		$tve_saved_content = apply_filters( 'tcb_clean_frontend_content', $tve_saved_content );
	}

	/**
	 * append any needed custom CSS - only on regular pages, and not on editor / preview page
	 */
	return ( $is_editor_or_preview ? '' : '' . $custom_css ) . $wrap['start'] . $tve_saved_content . $wrap['end'] . $tinymce_editor . $page_loader;
}

/**
 * Add a button for displaying the opt-in templates the user can choose from for this post type
 *
 * called from TCB, in the first AJAX-call (after DOMReady)
 */
function tve_ult_add_template_button() {
	if ( empty( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		return;
	}

	global $design;

	if ( empty( $design ) && ! ( $design = tve_ult_get_design( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) ) {
		echo '';

		return;
	}
	$design_details = TVE_Ult_Const::design_details( $design['post_type'] );

	include TVE_Ult_Const::plugin_path( 'editor/layouts/element-menus/side-menu/settings.php' );
}

/**
 * Appends any required parameters to the global JS configuration array on the editor page
 *
 * @param $js_params
 * @param $post_id
 * @param $post_type
 *
 * @return mixed
 */
function tve_ult_editor_javascript_params( $js_params, $post_id, $post_type ) {

	if ( ! tve_ult_is_editable( $post_id ) || empty( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		return $js_params;
	}

	global $design;

	if ( empty( $design ) ) {
		$last_edited_state_key = get_post_meta( $post_id, TVE_Ult_Const::META_PREFIX_NAME_FOR_EDIT_STATE . $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ], true );

		if ( ! empty( $last_edited_state_key ) ) {
			if ( ! ( $design = tve_ult_get_design( $last_edited_state_key ) ) ) {
				return $js_params;
			}
		}
	}

	$_version = get_bloginfo( 'version' );

	/** clear out any data that's not necessary on the editor and add form variation custom data */
	$js_params['landing_page']          = '';
	$js_params['landing_page_config']   = array();
	$js_params['landing_pages']         = array();
	$js_params['page_events']           = array();
	$js_params['landing_page_lightbox'] = array();
	$js_params['style_families']        = array(
		'Flat' => tve_editor_css() . '/thrive_flat.css?ver=' . $_version,
	);
	$js_params['style_classes']         = array(
		'Flat' => 'tve_flt',
	);
	$js_params['custom_post_data']      = array(
		TVE_Ult_Const::DESIGN_QUERY_KEY_NAME => $design['id'],
		'disabled_controls'                  => array(
			'page_events'   => 1,
			'text'          => array( 'more_link' ),
			'event_manager' => array(),
		),
	);
	$js_params['save_post_action']      = TVE_Ult_Const::ACTION_SAVE_DESIGN_CONTENT;
	$js_params['tve_globals']           = isset( $design[ TVE_Ult_Const::FIELD_GLOBALS ] ) ? $design[ TVE_Ult_Const::FIELD_GLOBALS ] : array( 'e' => 1 );

	/** custom color mappings - general options */
	$custom_colors = include TVE_Ult_Const::plugin_path( '/tcb-bridge/custom_color_mappings.php' );

	if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
		$template_config = tve_ult_editor_get_template_config( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
		/* specific color mappings for each template */
		$custom_colors = array_merge_recursive( $custom_colors, isset( $template_config['custom_color_mappings'] ) ? $template_config['custom_color_mappings'] : array() );
	}
	$js_params['tve_colour_mapping'] = array_merge_recursive( $js_params['tve_colour_mapping'], $custom_colors );

	return $js_params;
}

/**
 * Handles template-related actions:
 */
function tve_ult_template_action() {
	add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
	add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );
	check_ajax_referer( 'tve-ult-verify-track-sender-672', 'security' );

	if ( empty( $_POST['post_id'] ) || ! current_user_can( 'edit_post', $_POST['post_id'] ) || empty( $_POST['design_id'] ) || empty( $_POST['custom'] ) ) {
		exit();
	}

	if ( ! ( $design = tve_ult_get_design( $_POST['design_id'] ) ) ) {
		exit( '1' );
	}

	TU_Template_Manager::getInstance( $design )->api( $_POST['custom'] );
}


/**
 * Handles state-related actions
 */
function tve_ult_state_action() {

	check_ajax_referer( 'tve-ult-verify-track-sender-672', 'security' );

	if ( empty( $_POST['post_id'] ) || ! current_user_can( 'edit_post', $_POST['post_id'] ) || empty( $_POST['design_id'] ) || empty( $_POST['custom'] ) ) {
		exit();
	}

	add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
	add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );

	$design = tve_ult_get_design( $_POST['design_id'] );
	if ( empty( $design ) ) {
		wp_die();
	}

	require_once TVE_Ult_Const::plugin_path( 'inc/classes/class-tu-state-manager.php' );

	TU_State_Manager::getInstance( $design )->api( $_POST['custom'] );
}


/**
 * Gets the default design content from a pre-defined template
 *
 * @param $design       array
 * @param $template_key string formatted like {design_type}|{template_name}
 *
 * @return string for content
 */
function tve_ult_editor_get_template_content( & $design, $template_key = null ) {
	if ( $template_key === null && ! empty( $design ) && ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
		$template_key = $design[ TVE_Ult_Const::FIELD_TEMPLATE ];
	}

	if ( empty( $template_key ) ) {
		return '';
	}

	list( $type, $template ) = explode( '|', $template_key );

	$base = TVE_Ult_Const::plugin_path() . 'editor-templates';

	$templates = TU_Template_Manager::get_templates( $type );

	if ( ! isset( $templates[ $template ] ) || ! is_file( $base . '/' . $type . '/' . $template . '.php' ) ) {
		return '';
	}

	ob_start();
	include $base . '/' . $type . '/' . $template . '.php';
	$content = ob_get_contents();
	ob_end_clean();

	/** we need to make sure we don't have any left-over data from the previous template */
	$design[ TVE_Ult_Const::FIELD_INLINE_CSS ]   = '';
	$design[ TVE_Ult_Const::FIELD_USER_CSS ]     = '';
	$design[ TVE_Ult_Const::FIELD_CUSTOM_FONTS ] = array();
	$design[ TVE_Ult_Const::FIELD_ICON_PACK ]    = '';
	$design[ TVE_Ult_Const::FIELD_MASONRY ]      = '';
	$design[ TVE_Ult_Const::FIELD_TYPEFOCUS ]    = '';

	/** also read in any other configuration values that might be required for this design */
	$config = tve_ult_editor_get_template_config( $template_key );
	if ( ! empty( $config[ TVE_Ult_Const::FIELD_GLOBALS ] ) ) {
		$design[ TVE_Ult_Const::FIELD_GLOBALS ] = $config[ TVE_Ult_Const::FIELD_GLOBALS ];
	} else {
		$design[ TVE_Ult_Const::FIELD_GLOBALS ] = array( 'e' => 1 );
	}

	return $content;
}

/**
 * Get the configuration array used in editor for a specific design template
 *
 * @param string $key
 *
 * @return array
 */
function tve_ult_editor_get_template_config( $key ) {

	if ( strpos( $key, '|' ) === false ) {
		return array();
	}

	list( $design_type, $key ) = TU_Template_Manager::tpl_type_key( $key );
	$config = require TVE_Ult_Const::plugin_path() . 'editor-templates/_config.php';

	return isset( $config[ $design_type ][ $key ] ) ? $config[ $design_type ][ $key ] : array();
}

/**
 * TCB Enqueues fonts and returns them for a specific design
 *
 * @param $design array
 *
 * @return array
 */
function tve_ult_editor_enqueue_custom_fonts( $design ) {
	if ( empty( $design[ TVE_Ult_Const::FIELD_CUSTOM_FONTS ] ) ) {
		return array();
	}

	return tve_enqueue_fonts( $design[ TVE_Ult_Const::FIELD_CUSTOM_FONTS ] );
}

/**
 * Outputs custom CSS for a design
 * The Custom CSS is only saved once in the default state (in the "parent" state)
 *
 * @param mixed $design can be either a numeric value - for variation_key or an already loaded variation array
 * @param bool $return whether to output the CSS or return it
 *
 * @return string the CSS, if $return was true
 */
function tve_ult_editor_output_custom_css( $design, $return = false ) {
	if ( is_numeric( $design ) ) {
		$design = tve_ult_get_design( $design );
	}
	if ( empty( $design ) || ! is_array( $design ) ) {
		return '';
	}

	$css = '';
	if ( ! empty( $design[ TVE_Ult_Const::FIELD_INLINE_CSS ] ) ) { /* inline style rules = custom colors */
		$css .= sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', $design[ TVE_Ult_Const::FIELD_INLINE_CSS ] );
	}

	/** user-defined Custom CSS rules for the form */
	$custom_css = '';
	/** first, check for a parent state */
	if ( ! empty( $design['parent_id'] ) ) {
		$parent_state = tve_ult_get_design( $design['parent_id'] );
		if ( ! empty( $parent_state ) && ! empty( $parent_state[ TVE_Ult_Const::FIELD_USER_CSS ] ) ) {
			$custom_css = $parent_state[ TVE_Ult_Const::FIELD_USER_CSS ] . $custom_css;
		}
	}

	/**
	 * fallback / backwards-compatibility: get the CustomCSS from the state itself
	 */
	if ( ! empty( $design[ TVE_Ult_Const::FIELD_USER_CSS ] ) ) {
		$custom_css = $design[ TVE_Ult_Const::FIELD_USER_CSS ] . $custom_css;
	}

	if ( ! empty( $custom_css ) ) {
		$css .= sprintf(
			'<style type="text/css"%s class="tve_user_custom_style">%s</style>',
			$return ? '' : ' id="tve_head_custom_css"', // if we return the CSS, do not append the id to the stylesheet
			$custom_css
		);
	}

	if ( $return === true ) {
		return $css;
	}

	echo $css;
}

/**
 * called via AJAX
 * receives editor content and various fields needed throughout the editor
 */
function tve_ult_editor_save_content() {
	check_ajax_referer( 'tve-le-verify-sender-track129', 'security' );

	if ( empty( $_POST['post_id'] ) || ! current_user_can( 'edit_post', $_POST['post_id'] ) || empty( $_POST[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		exit( '-1' );
	}
	if ( ob_get_contents() ) {
		ob_clean();
	}

	$design = tve_ult_get_design( $_POST[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] );
	if ( empty( $design ) ) {
		exit( __( 'Could not find the design you are editing... Is it possible that someone deleted it from the admin panel?', TVE_Ult_Const::T ) );
	}

	update_option( 'thrv_custom_colours', isset( $_POST['custom_colours'] ) ? $_POST['custom_colours'] : array() );

	$design[ TVE_Ult_Const::FIELD_CONTENT ]      = $_POST['tve_content'];
	$design[ TVE_Ult_Const::FIELD_INLINE_CSS ]   = trim( $_POST['inline_rules'] );
	$design[ TVE_Ult_Const::FIELD_USER_CSS ]     = $_POST['tve_custom_css'];
	$design[ TVE_Ult_Const::FIELD_GLOBALS ]      = ! empty( $_POST['tve_globals'] ) ? $_POST['tve_globals'] : array( 'e' => 1 );
	$design[ TVE_Ult_Const::FIELD_CUSTOM_FONTS ] = tve_ult_get_custom_font_links( empty( $_POST['custom_font_classes'] ) ? array() : $_POST['custom_font_classes'] );
	$design[ TVE_Ult_Const::FIELD_ICON_PACK ]    = empty( $_POST['has_icons'] ) ? 0 : 1;
	$design[ TVE_Ult_Const::FIELD_MASONRY ]      = empty( $_POST['tve_has_masonry'] ) ? 0 : 1;
	$design[ TVE_Ult_Const::FIELD_TYPEFOCUS ]    = empty( $_POST['tve_has_typefocus'] ) ? 0 : 1;

	if ( ! empty( $design['parent_id'] ) && ( $parent_state = tve_ult_get_design( $design['parent_id'] ) ) ) {
		$parent_state[ TVE_Ult_Const::FIELD_USER_CSS ] = $_POST['tve_custom_css'];
		$design[ TVE_Ult_Const::FIELD_USER_CSS ]       = '';

		tve_ult_save_design( $parent_state );
	}

	tve_ult_save_design( $design );

	exit( '1' );
}

/**
 * Transform an array of font classes into links to the actual google font
 *
 * @param array $custom_font_classes the classes used for custom fonts
 *
 * @return array
 */
function tve_ult_get_custom_font_links( $custom_font_classes = array() ) {
	$all_fonts = tve_get_all_custom_fonts();

	$post_fonts = array();
	foreach ( array_unique( $custom_font_classes ) as $cls ) {
		foreach ( $all_fonts as $font ) {
			if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font->font_name ) ) {
				$post_fonts[] = Tve_Dash_Font_Import_Manager::getCssFile();
			} elseif ( $font->font_class == $cls && ! tve_is_safe_font( $font ) ) {
				$post_fonts[] = tve_custom_font_get_link( $font );
				break;
			}
		}
	}

	return array_unique( $post_fonts );
}

/**
 * Append the design id as a parameter for the preview link
 * Link that is built for the "Preview" button in the editor
 * This should always lead to the main (Default) state of the design
 *
 * @param $current_args
 * @param $post_id
 *
 * @return $current_args array
 */
function tve_ult_editor_append_preview_link_args( $current_args, $post_id ) {

	if ( tve_ult_post_type_editable( $post_id ) && ! empty( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ) {
		$current_args [ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] = $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ];
	}

	return $current_args;
}

/**
 * append html for lightboxes needed when editing a Design
 */
function tve_ult_editor_static_lightbox() {

	include TVE_Ult_Const::plugin_path( 'editor/lightbox/state_name.php' );

}

/**
 * output the custom html needed for the design editor menu (control panel)
 *
 * @param string $menu_path full path to the tcb menu folder
 *
 * @return void
 */
function tve_ult_output_custom_menu( $menu_path ) {
	include TVE_Ult_Const::plugin_path( 'editor/layouts/element-menus/tve_ult_design.php' );
}

/**
 * Include Advanced elements
 *
 * @param $cpanel_config
 */
function tve_ult_add_tcb_advanced_elements( $cpanel_config ) {
	if ( empty( $cpanel_config['disabled_controls']['tu_shortcodes'] ) ) {
		$tu_campaigns = tve_ult_get_campaign_with_shortcodes();

		require_once dirname( dirname( __FILE__ ) ) . '/editor/elements/advanced.php';
	}
}

/**
 * Include TCB menu elements
 *
 * @param $menu_path
 */
function tve_ult_add_tcb_menu_elements( $menu_path ) {
	require_once dirname( dirname( __FILE__ ) ) . '/editor/elements/menus.php';
}

/**
 *
 * @param $config array
 *
 * @return array
 */
function tve_ult_main_cpanel_config( $config ) {

	$post_type = get_post_type();
	if ( $post_type != TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) {
		return $config;
	}

	$config['disabled_controls']                     = isset( $config['disabled_controls'] ) ? $config['disabled_controls'] : array();
	$config['disabled_controls']['tu_shortcodes']    = true;
	$config['disabled_controls']['leads_shortcodes'] = true;
	$config['disabled_controls']['page_events']      = 1;
	$config['disabled_controls']['text']             = array( 'more_link' );
	$config['disabled_controls']['event_manager']    = array();

	return $config;
}
