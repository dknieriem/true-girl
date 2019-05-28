<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/4/2016
 * Time: 11:22 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Hooks {


	/**
	 * Actions
	 */
	const ACTION_TEMPLATE = 'tqb_tpl';
	const ACTION_STATE = 'tqb_state';
	const ACTION_SAVE_VARIATION_CONTENT = 'tqb_save_variation_content';

	/**
	 * Security nonce
	 */
	const SECURITY_NONCE = 'tqb-verify-track-sender-007';

	public static function init() {
		/**
		 * Filter that gets called when the following situation occurs:
		 * TCB is installed and enabled, but there is no active license activated
		 * in this case, we should only allow users to edit: tve_ult_campaign
		 */
		add_filter( 'tcb_skip_license_check', array( __CLASS__, 'license_override' ) );

		/**
		 * called when enqueuing scripts from the editor on editor page. it needs to check if TQB has a valid license
		 */
		add_filter( 'tcb_user_can_edit', array( __CLASS__, 'editor_check_license' ) );

		/**
		 * called when enqueuing scripts on editor pages. It checks if the separate TCB plugin has the required version
		 */
		add_filter( 'tcb_user_can_edit', array( __CLASS__, 'editor_check_tcb_version' ) );

		/**
		 * get the editing layout for variations
		 */
		add_filter( 'tcb_custom_post_layouts', array( __CLASS__, 'editor_layout' ), 10, 3 );

		/**
		 * hook for adding a "Choose Opt-in Template" button which is based on the current state of the edit page and on the actual content being edited
		 */
		add_action( 'tcb_custom_top_buttons', array( __CLASS__, 'add_template_button' ), 10, 3 );

		/**
		 * modify the localization parameters for the javascript on the editor page (in editing mode)
		 */
		add_filter( 'tcb_editor_javascript_params', array( __CLASS__, 'editor_javascript_params' ), 10, 3 );

		/**
		 * action hook that overrides the default tve_save_post action from the editor
		 * used to save the editor contents in custom post fields specific
		 */
		add_action( 'wp_ajax_' . self::ACTION_SAVE_VARIATION_CONTENT, array( __CLASS__, 'editor_save_content' ) );

		/**
		 * we need to modify the preview URL for tve_form_type post types
		 */
		add_filter( 'tcb_editor_preview_link_query_args', array( __CLASS__, 'editor_append_preview_link_args' ), 10, 2 );

		/**
		 * modify the edit url by inserting also the form variation key in the query vars
		 */
		add_filter( 'tcb_editor_edit_link_query_args', array( __CLASS__, 'editor_append_preview_link_args' ), 10, 2 );

		/**
		 * main entry point for template-related actions: choose new template, reset current template
		 */
		add_action( 'wp_ajax_' . self::ACTION_TEMPLATE, array( __CLASS__, 'template_action' ) );

		/**
		 * main entry point for state-related actions: add state, delete state
		 */
		add_action( 'wp_ajax_' . self::ACTION_STATE, array( __CLASS__, 'state_action' ) );

		/**
		 * custom element menu
		 * include the html needed for the general design settings editor control panel
		 */
		add_action( 'tcb_custom_menus_html', array( __CLASS__, 'output_custom_menu' ), 11 );

		/**
		 * TCB Advanced Elements
		 */
		add_action( 'tcb_advanced_elements_html', array( __CLASS__, 'add_advanced_elements' ), 11 );

		/**
		 * TQB - TCB Advanced Elements
		 */
		add_action( 'tcb_add_elements_wrapper', array( __CLASS__, 'add_tcb_dynamic_content_wrapper_element' ), 11 );
		add_action( 'tcb_add_elements_wrapper', array( __CLASS__, 'add_tcb_social_share_badge_wrapper_element' ), 11 );
		add_action( 'tcb_add_elements_wrapper', array( __CLASS__, 'add_tcb_quiz_shortcode_wrapper_element' ), 11 );

		/**
		 * Social networks visible hook
		 */
		add_filter( 'tcb_social_networks_visible', array( __CLASS__, 'social_networks_visible' ), 10, 2 );

		/**
		 * Add "go forward" event to TCB
		 */
		add_filter( 'tcb_event_actions', 'tqb_event_actions', 10, 3 );

		/**
		 * TCB main Cpanel Configuration
		 */
		add_filter( 'tcb_main_cpanel_config', array( __CLASS__, 'exclude_advanced_elements' ), 10, 1 );

		/**
		 * Disable the style families change TCB button
		 */
		add_filter( 'tcb_style_families', array( __CLASS__, 'disable_style_families_option' ), 10, 1 );

		/**
		 *  Add to TCB text element additional buttons
		 */
		add_action( 'tcb_text_element_extra_buttons', array( __CLASS__, 'tcb_text_element_extra_buttons' ), 10, 1 );

		/**
		 * TCB Autoresponder after submit options
		 */
		add_filter( 'tve_autoresponder_show_submit', array( __CLASS__, 'tqb_filter_autoresponder_submit_option' ) );

		/**
		 * TCB Autoresponder connection type
		 */
		add_filter( 'tve_autoresponder_connection_types', array( __CLASS__, 'tqb_filter_autoresponder_connection_type' ), 10, 1 );

		/**
		 * Adds additional data into the TCB main frame
		 */
		add_action( 'tcb_hook_template_redirect', array( __CLASS__, 'tqb_include_main_frame_data' ) );
	}

	/**
	 * called when there is no active license for TCB, but it is installed and enabled
	 * the function returns true only for pieces of content that "belong" to Thrive Ultimatum, so only the following:
	 *
	 * @param bool $override
	 *
	 * @return bool whether or not the current piece of content can be edited with TCB core functions
	 */
	public static function license_override( $override ) {
		/* this means that the license check should be skipped, possibly from thrive leads */
		if ( $override ) {
			return true;
		}

		$post_type = get_post_type();

		return self::is_editable( $post_type );
	}

	/**
	 * Checks if TQB license if valid (only if the user is trying to edit a design)
	 *
	 * @param bool $valid
	 *
	 * @return bool
	 */
	public static function editor_check_license( $valid ) {
		if ( empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $valid;
		}

		if ( ! tqb()->license_activated() ) {
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'tcb_license_warning' ) );


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
	public static function editor_check_tcb_version( $valid ) {
		if ( empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $valid;
		}

		if ( ! tqb()->check_tcb_version() ) {
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'tcb_version_warning' ) );

			return false;
		}

		return true;
	}

	/**
	 * show a box with a warning message notifying the user to update the TCB plugin to the latest version
	 * this will be shown only when the TCB version is lower than a minimum required version
	 */
	public static function tcb_version_warning() {
		return include tqb()->plugin_path( 'includes/admin/views/tcb_version_incompatible.phtml' );
	}

	public static function tcb_license_warning() {
		return include tqb()->plugin_path( '/includes/admin/views/license-inactive.phtml' );
	}

	/**
	 * Adds additional data into the TCB main frame
	 */
	public static function tqb_include_main_frame_data() {
		tqb_enqueue_style( 'tqb-main-frame-css', tqb()->plugin_url( 'tcb-bridge/assets/css/main-frame.css' ) );
	}

	/**
	 *  Add to TCB text element additional buttons
	 */
	public static function tcb_text_element_extra_buttons() {
		if ( ! empty( $_GET['post_type'] ) && $_GET['post_type'] === Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) {
			include tqb()->plugin_path( '/tcb-bridge/editor-layouts/element-menus/additional-text-element-buttons.php' );
		}
	}

	/**
	 * Exclude TU and TL Elements
	 *
	 * @param array $cpanel_config
	 *
	 * @return array
	 */
	public static function exclude_advanced_elements( $cpanel_config = array() ) {

		$post_type = get_post_type();
		if ( ! self::is_editable( $post_type ) || empty( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $cpanel_config;
		}

		$cpanel_config['disabled_controls']['tu_shortcodes']        = 1;
		$cpanel_config['disabled_controls']['leads_shortcodes']     = 1;
		$cpanel_config['disabled_controls']['to_advanced_elements'] = 1;

		return $cpanel_config;
	}

	/**
	 * Filter autoresponder actions after submit
	 *
	 * @param bool $show_submit
	 *
	 * @return bool
	 */
	public static function tqb_filter_autoresponder_submit_option( $show_submit ) {

		if ( empty( $_POST['post_id'] ) ) {
			return $show_submit;
		}
		$post = get_post( $_POST['post_id'] );
		if ( empty( $post ) ) {
			return $show_submit;
		}
		if ( $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN || $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ) {
			return false;
		}

		return $show_submit;
	}

	/**
	 * Filter autoresponder connection types
	 *
	 * @param array $connection_types
	 *
	 * @return array
	 */
	public static function tqb_filter_autoresponder_connection_type( $connection_types ) {

		$post = get_post( $_POST['post_id'] );
		if ( empty( $post ) ) {
			return $connection_types;
		}
		if ( $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN || $post->post_type == Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_SPLASH_PAGE ) {
			if ( isset( $connection_types['custom-html'] ) ) {
				unset( $connection_types['custom-html'] );
			}
		}

		return $connection_types;
	}

	/**
	 * Disable the style families option from TCB when editing a quiz page
	 *
	 * @param array $style_families
	 *
	 * @return array
	 */
	public static function disable_style_families_option( $style_families = array() ) {

		$post_type = get_post_type();;

		if ( ! self::is_editable( $post_type ) ) {
			return $style_families;
		}

		unset( $style_families['Classy'] );
		unset( $style_families['Minimal'] );

		return $style_families;
	}

	/**
	 * Social networks visible hook from TCB
	 *
	 * @param $networks
	 * @param $post_data
	 *
	 * @return mixed
	 */
	public static function social_networks_visible( $networks, $post_data ) {
		$is_social_share_badge = ( strpos( $post_data['element'], 'tqb-social-share-badge-container' ) !== false ) ? true : false;
		if ( $is_social_share_badge ) {
			$networks['in_share']   = false;
			$networks['pin_share']  = false;
			$networks['xing_share'] = false;
		}

		return $networks;
	}

	/**
	 * Hook from TCB, this loads the editor layout file
	 *
	 * @param $current_templates
	 * @param $post_id
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function editor_layout( $current_templates, $post_id, $post_type ) {
		global $variation;

		if ( $post_type == 'post' || $post_type == 'page' ) {
			/*For editing a post or page with TCB*/
			tqb_enqueue_script( 'tqb-external-editor', tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-external.min.js' ), array( 'tve_editor' ) );
			wp_localize_script( 'tqb-external-editor', 'tqb_page_data', array(
				'action'   => 'tcb-tqb-quiz-action',
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'tqb_frontend_ajax_request' ),
			) );
		}

		if ( ! self::is_editable( $post_type ) ) {
			return $current_templates;
		}

		if ( empty( $variation ) ) {
			$variation = tqb_get_variation( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
		}

		if ( empty( $variation ) ) {
			return $current_templates;
		}

		$current_templates['variation'] = tqb()->plugin_path( 'tcb-bridge/editor/page/' . TQB_Template_Manager::type( $variation['post_type'] ) . '.php' );
		$variation['style']             = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );

		$allow_tqb_advanced = ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) && TCB_Hooks::enable_tqb_advanced_menu( $variation['post_type'] ) ) ? true : false;

		/* flat is the default style for Thrive Quiz Builder designs */
		global $tve_style_family_classes;
		$tve_style_families = tve_get_style_families();
		$style_family       = 'Flat';
		$style_key          = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $style_family ] );
		/* Style family */
		wp_style_is( $style_key ) || tve_enqueue_style( $style_key, $tve_style_families[ $style_family ] );

		if ( is_editor_page() ) {

			tqb_enqueue_style( 'tqb-variation-editor', tqb()->plugin_url( 'tcb-bridge/assets/css/editor.css' ) );
			tqb_enqueue_script( 'tqb-editor', tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-editor.min.js' ), array( 'tve_editor' ) );
			// Lean Modal js & Velocity
			wp_enqueue_script( 'tqb-velocity', TVE_DASH_URL . '/js/dist/velocity.min.js' );
			wp_enqueue_script( 'tqb-lean-modal', TVE_DASH_URL . '/js/dist/leanmodal.min.js' );
			// Include resizable only for result page
			if ( $allow_tqb_advanced ) {
				tqb_enqueue_style( 'tqb-bootstrap-css', tqb()->plugin_url( 'tcb-bridge/assets/css/lib/bootstrap.min.css' ) );
				wp_enqueue_script( 'tqb-bootstrap-tooltip', tqb()->plugin_url( 'tcb-bridge/assets/js/lib/bootstrap.min.js' ), array( 'jquery' ), '', true );
			}

			$absolute_limits = tqb_compute_quiz_absolute_max_min_values( $variation['quiz_id'], $allow_tqb_advanced );
			$quiz_type       = TQB_Post_meta::get_quiz_type_meta( $variation['quiz_id'] );

			$page_data = array(
				'variation_id'         => $variation['id'],
				'variation_type'       => str_replace( 'tqb_', '', $variation['post_type'] ),
				'page_id'              => $variation['page_id'],
				'tpl_action'           => self::ACTION_TEMPLATE,
				'state_action'         => self::ACTION_STATE,
				'ajaxurl'              => admin_url( 'admin-ajax.php' ),
				'has_content'          => ! empty( $variation['content'] ),
				'allow_tqb_advanced'   => $allow_tqb_advanced,
				'is_personality_type'  => ( $quiz_type['type'] === Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY ) ? true : false,
				'quiz_config'          => array(
					'absolute_min_value'  => $absolute_limits['min'],
					'absolute_max_value'  => $absolute_limits['max'],
					'max_interval_number' => Thrive_Quiz_Builder::STATES_MAXIMUM_NUMBER_OF_INTERVALS,
				),
				'security'             => wp_create_nonce( self::SECURITY_NONCE ),
				'current_css'          => ! empty( $css_handle ) ? $css_handle : '',
				'kb_next_step_article' => Thrive_Quiz_Builder::KB_NEXT_STEP_ARTICLE,
				'L'                    => array(
					'alert_choose_tpl'                    => __( 'Please choose a template', Thrive_Quiz_Builder::T ),
					'tpl_name_required'                   => __( 'Please enter a template name, it will be easier to reload it after.', Thrive_Quiz_Builder::T ),
					'fetching_saved_templates'            => __( 'Fetching saved templates...', Thrive_Quiz_Builder::T ),
					'tpl_confirm_delete'                  => __( 'Are you sure you want to delete this saved template? This action cannot be undone', Thrive_Quiz_Builder::T ),
					'template_deleted'                    => __( 'Template deleted.', Thrive_Quiz_Builder::T ),
					'intervals_min_val_cannot_be_changed' => __( 'The minimum value cannot be changed!', Thrive_Quiz_Builder::T ),
					'intervals_max_val_cannot_be_changed' => __( 'The maximum value cannot be changed!', Thrive_Quiz_Builder::T ),
					'min_value_limit'                     => __( 'The minimum value cannot be less than ', Thrive_Quiz_Builder::T ),
					'max_value_limit'                     => __( 'The maximum value cannot be greater than ', Thrive_Quiz_Builder::T ),
					'missing_step_error'                  => __( 'Your page has no link to the next step in the quiz, so your visitors will be blocked on this step.', Thrive_Quiz_Builder::T ),
					'missing_step_error_link'             => __( 'Learn how to add links to the next step in the quiz.', Thrive_Quiz_Builder::T ),
					'missing_step_error_continue'         => __( 'Continue', Thrive_Quiz_Builder::T ),
					'missing_step_error_title'            => __( 'Links missing', Thrive_Quiz_Builder::T ),
				),
			);
			wp_localize_script( 'tqb-editor', 'tqb_page_data', $page_data );
		} else {
			//this is the preview page
			tqb_enqueue_default_scripts();

			// Include draggable only for result page
			if ( $allow_tqb_advanced ) {
				wp_enqueue_script( 'jquery-ui-draggable', false, array( 'jquery' ) );
				// enqueue the state-picker js
				tqb_enqueue_script( 'tqb-state-picker', tqb()->plugin_url( 'tcb-bridge/assets/js/tqb-tcb-state-picker.min.js' ) );
			}
		}


		add_action( 'wp_enqueue_scripts', 'tqb_enqueue_variation_scripts' );

		return $current_templates;
	}

	/**
	 * Add a button for displaying the opt-in templates the user can choose from for this post type
	 *
	 * called from TCB, in the first AJAX-call (after DOMReady)
	 */
	public static function add_template_button() {
		if ( empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return;
		}

		global $variation;

		if ( empty( $variation ) && ! ( $variation = tqb_get_variation( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) ) {
			echo '';

			return;
		}
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/side-menu/settings.php' );
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
	public static function editor_javascript_params( $js_params, $post_id, $post_type ) {
		if ( ! self::is_editable( $post_id ) || empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			return $js_params;
		}

		global $variation;
		if ( empty( $variation ) ) {
			//TODO: implement this.
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
			Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME => $variation['id'],
			'disabled_controls'                           => array(
				'page_events'   => 1,
				'text'          => array( 'more_link' ),
				'event_manager' => array(),
			),
		);

		$js_params['save_post_action'] = self::ACTION_SAVE_VARIATION_CONTENT;

		/** custom color mappings - general options */
		$custom_colors = include tqb()->plugin_path( '/tcb-bridge/custom-color-mapping.php' );
		if ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ) {
			$template_config = self::tqb_editor_get_template_config( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] );
			/* specific color mappings for each template */
			$custom_colors = array_merge_recursive( $custom_colors, isset( $template_config['custom_color_mappings'] ) ? $template_config['custom_color_mappings'] : array() );
		}
		$js_params['tve_colour_mapping'] = array_merge_recursive( $js_params['tve_colour_mapping'], $custom_colors );

		return $js_params;
	}

	/**
	 * Is Editable with TCB
	 *
	 * @param int|string $post_or_type
	 *
	 * @return bool
	 */
	public static function is_editable( $post_or_type ) {
		$post_or_type = is_numeric( $post_or_type ) ? get_post_type( $post_or_type ) : $post_or_type;

		return in_array( $post_or_type, array(
			TQB_Post_types::OPTIN_PAGE_POST_TYPE,
			TQB_Post_types::RESULTS_PAGE_POST_TYPE,
			TQB_Post_types::SPLASH_PAGE_POST_TYPE,
		) );
	}


	/**
	 * called via AJAX
	 * receives editor content and various fields needed throughout the editor
	 */
	public static function editor_save_content() {

		check_ajax_referer( 'tve-le-verify-sender-track129', 'security' );

		if ( empty( $_POST['post_id'] ) || ! current_user_can( 'edit_post', $_POST['post_id'] ) || empty( $_POST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			exit( '-1' );
		}

		if ( ob_get_contents() ) {
			ob_clean();
		}

		$variation = tqb_get_variation( $_POST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] );
		if ( empty( $variation ) ) {
			exit( __( 'Could not find the variation you are editing... Is it possible that someone deleted it from the admin panel?', Thrive_Quiz_Builder::T ) );
		}


		/*
		 * Prepare the child variation content
		 */
		if ( ! empty( $_POST['tqb_child_variation_id'] ) && is_numeric( $_POST['tqb_child_variation_id'] ) ) {
			$pattern = '#__TQB__dynamic_DELIMITER</div>(.+?)<div style=\\\"display:none\\\">__TQB__dynamic_DELIMITER#s';
			preg_match( $pattern, $_POST['tve_content'], $m );
			$dynamic_content = '';
			if ( ! empty( $m[1] ) ) { // . '<div class="tve_content_inner tqb-content-inner">'  . '</div>'
				$dynamic_content = Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN . $m[1] . Thrive_Quiz_Builder::STATES_DYNAMIC_CONTENT_PATTERN;
			}

			TQB_Variation_Manager::save_child_variation( array(
				'id'        => $_POST['tqb_child_variation_id'],
				'parent_id' => $variation['id'],
				'content'   => $dynamic_content,
			) );
		}
		/*
		 * END: Prepare the child variation content
		 */
		$variation[ Thrive_Quiz_Builder::FIELD_CONTENT ]            = $_POST['tve_content'];
		$variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ]         = trim( $_POST['inline_rules'] );
		$variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ]           = $_POST['tve_custom_css'];
		$variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ]       = self::tqb_get_custom_font_links( empty( $_POST['custom_font_classes'] ) ? array() : $_POST['custom_font_classes'] );
		$variation[ Thrive_Quiz_Builder::FIELD_TYPEFOCUS ]          = empty( $_POST['tve_has_typefocus'] ) ? 0 : 1;
		$variation[ Thrive_Quiz_Builder::FIELD_MASONRY ]            = empty( $_POST['tve_has_masonry'] ) ? 0 : 1;
		$variation[ Thrive_Quiz_Builder::FIELD_ICON_PACK ]          = empty( $_POST['has_icons'] ) ? 0 : 1;
		$variation[ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] = ( strpos( $_POST['tve_content'], '"tqb-social-share-badge-container' ) !== false ) ? 1 : 0;

		$variation_manager = new TQB_Variation_Manager( $variation['quiz_id'], $variation['page_id'] );

		//Save only the content and the tcb_fields. nothing else.
		$variation = $variation_manager->prepare_variation_for_tcb_save( $variation );

		$variation_manager->save_variation( $variation, false );

		exit( '1' );
	}

	/**
	 * Transform an array of font classes into links to the actual google font
	 *
	 * @param array $custom_font_classes the classes used for custom fonts
	 *
	 * @return array
	 */
	public static function tqb_get_custom_font_links( $custom_font_classes = array() ) {
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
	 * Append the variation id as a parameter for the preview link
	 * Link that is built for the "Preview" button in the editor
	 * This should always lead to the main (Default) state of the variation
	 *
	 * @param $current_args
	 * @param $post_id
	 *
	 * @return $current_args array
	 */
	public static function editor_append_preview_link_args( $current_args, $post_id ) {

		if ( self::is_editable( $post_id ) && ! empty( $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			$current_args [ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] = $_GET[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ];
		}

		return $current_args;
	}

	/**
	 * Handles template-related actions:
	 */
	public static function template_action() {
		add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
		add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );
		check_ajax_referer( self::SECURITY_NONCE, 'security' );

		if ( empty( $_POST['page_id'] ) || ! current_user_can( 'edit_post', $_POST['page_id'] ) || empty( $_POST['variation_id'] ) || ! is_numeric( $_POST['variation_id'] ) || empty( $_POST['custom'] ) ) {
			exit();
		}

		if ( ! ( $variation = tqb_get_variation( $_POST['variation_id'] ) ) ) {
			exit( '1' );
		}

		TQB_Template_Manager::get_instance( $variation )->api( $_POST['custom'] );
	}

	public static function state_action() {
		check_ajax_referer( self::SECURITY_NONCE, 'security' );
		add_filter( 'tcb_is_editor_page_ajax', '__return_true' );
		add_filter( 'tcb_is_editor_page_raw_ajax', '__return_true' );

		if ( empty( $_POST['page_id'] ) || ! current_user_can( 'edit_post', $_POST['page_id'] ) || empty( $_POST['variation_id'] ) || ! is_numeric( $_POST['variation_id'] ) || empty( $_POST['custom'] ) ) {
			exit();
		}

		if ( ! ( $variation = tqb_get_variation( $_POST['variation_id'] ) ) ) {
			exit( '1' );
		}

		TQB_State_Manager::get_instance( $variation )->api( $_POST['custom'] );

	}

	/**
	 * output the custom html needed for the design editor menu (control panel)
	 *
	 * @param string $menu_path full path to the tcb menu folder
	 *
	 * @return void
	 */
	public static function output_custom_menu( $menu_path ) {
		$fonts          = tve_get_all_custom_fonts( true );
		$web_safe_fonts = tve_dash_font_manager_get_safe_fonts();

		$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
		// for some reason, the above line does not work in some instances
		if ( is_ssl() ) {
			$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
		}

		$font_settings_url = $admin_base_url . 'admin.php?page=tve_dash_font_manager';

		if ( isset( $_REQUEST[ Thrive_Quiz_Builder::VARIATION_QUERY_KEY_NAME ] ) ) {
			// The following menus are included only for quiz-builder
			include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/page-settings.php' );
			include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/social-share-badge-settings.php' );
		}
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/quiz-shortcode-settings.php' );
	}

	/**
	 * Add the advanced menu element
	 *
	 * @param $cpanel_config
	 */
	public static function add_advanced_elements( $cpanel_config ) {
		if ( empty( $cpanel_config['disabled_controls']['tqb_advanced_elements'] ) ) {
			include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/advanced.php' );
			include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/advanced-quiz-shortcodes.php' );
		}
	}

	public static function add_tcb_dynamic_content_wrapper_element() {
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/dynamic-content-wrapper.php' );
	}

	public static function add_tcb_social_share_badge_wrapper_element() {
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/social-share-badge-wrapper.php' );
	}

	public static function add_tcb_quiz_shortcode_wrapper_element() {
		include tqb()->plugin_path( 'tcb-bridge/editor-layouts/element-menus/quiz-shortcode-wrapper.php' );
	}

	/**
	 * Gets the default variation content from a pre-defined template
	 *
	 * @param $variation    array
	 * @param $template_key string formatted like {variation_type}|{template_name}
	 *
	 * @return string for content
	 */
	public static function tqb_editor_get_template_content( & $variation, $template_key = null ) {
		if ( $template_key === null && ! empty( $variation ) && ! empty( $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ] ) ) {
			$template_key = $variation[ Thrive_Quiz_Builder::FIELD_TEMPLATE ];
		}

		if ( empty( $template_key ) ) {
			return '';
		}

		list( $type, $template ) = explode( '|', $template_key );

		$base = tqb()->plugin_path( 'tcb-bridge/editor-templates' );

		$templates = TQB_Template_Manager::get_templates( $type, $variation['quiz_id'] );

		if ( ! isset( $templates[ $template ] ) || ! is_file( $base . '/' . $type . '/' . $template . '.php' ) ) {
			return '';
		}

		$tie_image       = new TIE_Image( $variation['page_id'] );
		$quiz_style_meta = TQB_Post_meta::get_quiz_style_meta( $variation['quiz_id'] );
		$style_config    = tqb()->get_style_config( $quiz_style_meta );

		ob_start();
		$main_content_style = $style_config[ $type ]['config']['main-content-style'];
		$tie_image_url      = $tie_image->get_image_url();
		if ( empty( $tie_image_url ) ) {
			$tie_image_url = tqb()->plugin_url( 'tcb-bridge/assets/images/share-badge-default.png' );
		}
		include $base . '/' . $type . '/' . $template . '.php';
		$content = ob_get_contents();
		ob_end_clean();

		/** we need to make sure we don't have any left-over data from the previous template */
		$variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ]         = '';
		$variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ]           = '';
		$variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ]       = array();
		$variation[ Thrive_Quiz_Builder::FIELD_TYPEFOCUS ]          = '';
		$variation[ Thrive_Quiz_Builder::FIELD_MASONRY ]            = '';
		$variation[ Thrive_Quiz_Builder::FIELD_ICON_PACK ]          = '';
		$variation[ Thrive_Quiz_Builder::FIELD_SOCIAL_SHARE_BADGE ] = ( strpos( $content, '"tqb-social-share-badge-container' ) !== false ) ? 1 : 0;

		return $content;

	}

	/**
	 * This is the main controller for editor and preview page
	 *
	 * @param array $variation
	 * @param array $is_editor_or_preview true if we are on the editor / preview page
	 *
	 * @return string
	 */
	public static function tqb_editor_custom_content( $variation, $is_editor_or_preview = true ) {

		if ( empty( $variation ) ) {
			return __( 'Variation cannot be empty', Thrive_Quiz_Builder::T );
		}

		$tve_saved_content = $variation[ Thrive_Quiz_Builder::FIELD_CONTENT ];

		/**
		 * if in editor page or preview, replace the data-date attribute for the countdown timers with the current_date + 1 day (just for demo purposes)
		 */

		/* this will hold the html for the tinymce editor instantiation, only if we're on the editor page */
		$tinymce_editor = $page_loader = '';

		$is_editor_page = $is_editor_or_preview && tqb_is_editor_page();

		/**
		 * this means we are getting the content to output it on a targeted page => include also the custom CSS rules
		 */
		$custom_css = TCB_Hooks::tqb_editor_output_custom_css( $variation, true );

		$wrap = array(
			'start' => '<div id="tve_editor" class="tve_shortcode_editor">',
			'end'   => '</div>',
		);

		if ( $is_editor_page ) {

			add_action( 'wp_footer', 'tve_output_wysiwyg_editor' );

			$page_loader = '';

		} else {
			$tve_saved_content = tve_restore_script_tags( $tve_saved_content );
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
	 * Get the configuration array used in editor for a specific design template
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public static function tqb_editor_get_template_config( $key ) {

		if ( strpos( $key, '|' ) === false ) {
			return array();
		}

		list( $variation_type, $key ) = TQB_Template_Manager::tpl_type_key( $key );
		$config = require tqb()->plugin_path( 'tcb-bridge/editor-templates/config.php' );

		return isset( $config[ $variation_type ][ $key ] ) ? $config[ $variation_type ][ $key ] : array();
	}

	/**
	 * TCB Enqueues fonts and returns them for a specific design
	 *
	 * @param $variation array
	 *
	 * @return array
	 */
	public static function tqb_editor_enqueue_custom_fonts( $variation ) {
		if ( empty( $variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] ) ) {
			return array();
		}

		return tve_enqueue_fonts( $variation[ Thrive_Quiz_Builder::FIELD_CUSTOM_FONTS ] );
	}

	/**
	 * Outputs custom CSS for a design
	 *
	 * @param mixed $variation can be either a numeric value - for variation_key or an already loaded variation array
	 * @param bool $return whether to output the CSS or return it
	 *
	 * @return string the CSS, if $return was true
	 */
	public static function tqb_editor_output_custom_css( $variation, $return = false ) {

		if ( empty( $variation ) || ! is_array( $variation ) ) {
			return '';
		}

		$css = '';
		if ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] ) ) { /* inline style rules = custom colors */
			$css .= sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', $variation[ Thrive_Quiz_Builder::FIELD_INLINE_CSS ] );
		}

		/** user-defined Custom CSS rules for the form */
		$custom_css = '';

		if ( ! empty( $variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ] ) ) {
			$custom_css = $variation[ Thrive_Quiz_Builder::FIELD_USER_CSS ] . $custom_css;
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
	 * Enable the TQB advanced menu
	 *
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function enable_tqb_advanced_menu( $post_type ) {
		return in_array( $post_type, array(
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS,
			Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_OPTIN,
		) );
	}
}

return TCB_Hooks::init();

