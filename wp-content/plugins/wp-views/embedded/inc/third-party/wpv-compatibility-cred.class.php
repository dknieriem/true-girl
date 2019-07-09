<?php

/**
 * Miscellanneous Views-specific adjustments for compatibility with Toolset Forms.
 *
 * This class is a singleton that gets initialized on the plugin bootstrap.
 * It contains two shortcodes to generate edit links, which upon click should force some resources 
 * (Content Template or layout) that contains an edit form.
 * It contains a shortcode to generate a conditional message upon redirect 
 * after a form is submitted, by listening to an URL attribute.
 *
 * @since 2.4.0
 */
class WPV_Compatibility_CRED {

	private static $instance;
	
	private $is_cred_installed		= false;
	private $is_layouts_installed	= false;
	
	private $current_content_template;
	private $content_templates_to_forms;
	private $layouts_to_forms;
	
	const EDIT_LINK_DOCUMENTATION	= 'https://toolset.com/documentation/user-guides/displaying-cred-editing-forms/';


	/**
	 * Activate the compatibility adjustments.
	 *
	 * @since 2.4.0
	 *
	 * @note There is purposefully no get_instance because there should be no need for accessing this class from other code.
	 */
	public static function initialize() {
		if( null == self::$instance ) {
			self::$instance = new self();
		}
	}

	private function __construct() {
		
		add_action( 'plugins_loaded',	array( $this, 'plugins_loaded' ), 99 );
		
		add_action( 'wpv_action_collect_shortcode_groups', array( $this, 'register_shortcodes_in_dialogs' ), 5 );
		
		add_shortcode( 'toolset-edit-post-link',	array( $this, 'edit_post_link_shortcode' ) );
		add_filter( 'wpv_filter_wpv_shortcodes_gui_data', array( $this, 'register_toolset_edit_post_link_data' ) );
		
		add_shortcode( 'toolset-edit-user-link',	array( $this, 'edit_user_link_shortcode' ) );
		add_filter( 'wpv_filter_wpv_shortcodes_gui_data', array( $this, 'register_toolset_edit_user_link_data' ) );
		
		add_shortcode( 'cred-form-message',			array( $this, 'form_message_shortcode' ) );
		add_filter( 'wpv_filter_wpv_shortcodes_gui_data', array( $this, 'register_cred_form_message_data' ) );
		add_filter( 'the_content',					array( $this, 'pre_process_form_message_shortcode' ), 5 );
		add_filter( 'wpv_filter_wpv_the_content_suppressed',	array( $this, 'pre_process_form_message_shortcode' ), 5 );
		add_filter( 'wpv-pre-do-shortcode',			array( $this, 'pre_process_form_message_shortcode' ), 5 );
		
	}
	
	/**
	 * Initialize the flags on whether the right plugins are available.
	 *
	 * @since 2.4.0
	 */
	public function plugins_loaded() {
		
		$this->is_cred_installed	= defined( 'CRED_FE_VERSION' );
		$this->is_layouts_installed	= defined( 'WPDDL_VERSION' );
		
		$this->current_content_template = 0;
		$this->content_templates_to_forms = array();
		$this->layouts_to_forms = array();
		
	}
	
	/**
	 * Register the Forms-related shortcodes in the Fields and Views dialog.
	 *
	 * @since 2.4.0
	 */
	public function register_shortcodes_in_dialogs() {
		if ( ! $this->is_cred_installed ) {
			return;
		}
	
		$group_id	= 'toolset-edit-links';
		$group_data	= array(
			'name'		=> __( 'Forms Editing', 'wpv-views' ),
			'fields'	=> array(
				'toolset-edit-post-link' => array(
					'name'		=> __( 'Forms edit-post link', 'wpv-views' ),
					'handle'    => 'toolset-edit-post-link',
					'shortcode'	=> '[toolset-edit-post-link]',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'toolset-edit-post-link', title: '" . esc_js( __( 'Forms edit-post link', 'wpv-views' ) ) . "' })"
				),
				'toolset-edit-user-link' => array(
					'name'		=> __( 'Forms edit-user link', 'wpv-views' ),
					'handle'    => 'toolset-edit-user-link',
					'shortcode'	=> '[toolset-edit-user-link]',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'toolset-edit-user-link', title: '" . esc_js( __( 'Forms edit-user link', 'wpv-views' ) ) . "' })"
				),
				'cred-form-message' => array(
					'name'		=> __( 'Forms message', 'wpv-views' ),
					'handle'    => 'cred-form-message',
					'shortcode'	=> '[cred-form-message]',
					'callback'	=> "WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: 'cred-form-message', title: '" . esc_js( __( 'Forms message', 'wpv-views' ) ) . "' })"
				)
			)
		);
		
		do_action( 'wpv_action_register_shortcode_group', $group_id, $group_data );
		
	}
	
	/**
	 * Callback for the toolset-edit-post-link shortcode.
	 *
	 * Generates a link to edit the current global, or a given, post, using the post edit form 
	 * located in one resource (Content Template or layout), that will be forcedly displayed 
	 * when clicking that link.
	 *
	 * @param array  $atts    List of attributes passed to the shortcode
	 *     content_template_slug string     The slug of the Content Template to force
	 *     layout_slug           string     The slug of the layout to force
	 *     target                string     The target of the link: 'self'|'top'|'blank'
	 *     style                 string     Inline styles to be passed to the link
	 *     class                 string     Custom classes to be passed to the link
	 *     id                    string|int The post to edit with this link, can be a post ID or a placeholder expected by WPV_wpcf_switch_post_from_attr_id
	 * @param string $content The content of the shortcode
	 *
	 * @return string
	 *
	 * @note One of the content_template_slug and layout_slug two attributes must be passed and valid.
	 *
	 * @since 2.4.0
	 */
	public function edit_post_link_shortcode( $atts, $content ) {
		
		if ( ! $this->is_cred_installed ) {
			return;
		}
		
		$atts = shortcode_atts(
			array(
				'content_template_slug'	=> '',
				'layout_slug'			=> '',
				'target'	=> 'self',
				'style'		=> '',
				'class'		=> '',
				'id' => '',
				'item' => ''
			),
			$atts
		);
		
		if (
			empty( $atts['content_template_slug'] ) 
			&& empty( $atts['layout_slug'] ) 
		) {
			return;
		}

		$relationship_service = new Toolset_Relationship_Service();
		$attr_item_chain = new Toolset_Shortcode_Attr_Item_M2M(
			new Toolset_Shortcode_Attr_Item_Legacy(
				new Toolset_Shortcode_Attr_Item_Id(),
				$relationship_service
			),
			$relationship_service
		);
		
		if ( ! $item_id = $attr_item_chain->get( $atts ) ) {
			// no valid item
			return;
		}
		
		$item_post = get_post( $item_id );
		
		$out = '';
		$form_id = 0;
		$link_attributes = array();
		$translate_name = 'toolset-edit-post-link';
		
		if ( ! empty( $atts['layout_slug'] ) ) {
			$layout_id = apply_filters( 'ddl-get_layout_id_by_slug', 0, $atts['layout_slug'] );
			$translate_name .= '_' . $atts['layout_slug'];
			if ( $layout_id ) {
				$link_attributes['layout_id'] = $layout_id;
				$form_id = $this->get_form_in_layout( $layout_id, 'post' );
			} else {
				return;
			}
		} else if ( ! empty( $atts['content_template_slug'] ) ) {
			$ct_id = WPV_Content_Template_Embedded::get_template_id_by_name( $atts['content_template_slug'] );
			$translate_name .= '_' . $atts['content_template_slug'];
			if ( $ct_id ) {
				$link_attributes['content-template-id'] = $ct_id;
				$form_id = $this->get_form_in_content_template( $ct_id, 'post' );
			} else {
				return;
			}
		}
		
		$translate_name .= '_' . substr( md5( $content ), 0, 12 );
		
		global $post, $current_user;
		
		if (
			! empty( $post ) 
			&& ! empty( $form_id )
		) {
			
			$form_settings = (array) get_post_meta( $form_id, '_cred_form_settings', true );
			if (
				! is_array( $form_settings ) 
				|| empty( $form_settings ) 
				|| ! array_key_exists( 'form', $form_settings ) 
				|| ! array_key_exists( 'type', $form_settings['form'] ) 
				|| ! array_key_exists( 'post', $form_settings ) 
				|| ! array_key_exists( 'post_type', $form_settings['post'] )
			) {
				return;
			}
			
			$post_orig_id = $item_post->ID;
			
			// Adjust for WPML support
			// If WPML is enabled, $post_id should contain the right ID for the current post in the current language
			// However, if using the id attribute, we might need to adjust it to the translated post for the given ID
			$post_id = apply_filters( 'translate_object_id', $post_orig_id, $item_post->post_type, true, null );
			
			$post_type = ( $post_orig_id == $post_id ) ? $item_post->post_type : get_post_type( $post_id );
			
			if ( $post_type != $form_settings['post']['post_type'] ) {
				return $out;
			}
			
			$post_author = ( $post_orig_id == $post_id ) ? $item_post->post_author : get_post_field( 'post_author', $post_id );
			
			if ( 
				! current_user_can( 'edit_own_posts_with_cred_' . $form_id ) 
				&& $current_user->ID == $post_author 
			) {
				return $out;
			}
			if ( 
				! current_user_can( 'edit_other_posts_with_cred_' . $form_id ) 
				&& $current_user->ID != $post_author 
			) {
				return $out;
			}
			
			$post_status = ( $post_orig_id == $post_id ) ? $item_post->post_status : get_post_status( $post_id );
			$supported_extra_post_statuses = array( 'future', 'draft', 'pending', 'private' );
			/**
			 * Filter the array of allowed post statuses to be supported by the Toolst edit post links.
			 *
			 * By default, we display edit links for published posts, as well as for posts that are not published but:
			 * - belong to any of those supported statuses, and
			 * - are editable by the current user.
			 *
			 * @param array $supported_extra_post_statuses List of supported statuses
			 * @param int   $form_id                       ID of the form that this link is supposed to use
			 *
			 * @since 2.5
			 */
			$supported_extra_post_statuses = apply_filters( 'toolset_filter_edit_post_link_extra_statuses_allowed', $supported_extra_post_statuses, $form_id );
			$link = false;
			
			$rfg_post_type_query_factory = new Toolset_Post_Type_Query_Factory();
			$rfg_post_type_query = $rfg_post_type_query_factory->create(
				array(
					Toolset_Post_Type_Query::IS_REPEATING_FIELD_GROUP => true,
					Toolset_Post_Type_Query::RETURN_TYPE => 'slug'
				)
			);
			
			$rfg_post_types = $rfg_post_type_query->get_results();
			if ( in_array( $post_type, $rfg_post_types ) ) {
				if ( 
					! apply_filters( 'toolset_is_m2m_enabled', false ) 
					|| 'publish' != $post_status
				) {
					return $out;
				}
				do_action( 'toolset_do_m2m_full_init' );
				
				$association_query = new Toolset_Association_Query_V2();
				$associations = $association_query
					->limit( 1 )
					->add( $association_query->element_id_and_domain( $post_id, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() ) )
					->return_element_ids( new Toolset_Relationship_Role_Parent() )
					->get_results();
					
				if ( 
					is_array( $associations ) 
					&& count( $associations ) 
				) {
					$post_belongs_id = reset( $associations );
					$post_belongs_status = get_post_status( $post_belongs_id );
					
					if ( 'publish' == $post_belongs_status ) {
						$link = get_permalink( $post_belongs_id );
					} else if ( 
						in_array( $post_belongs_status, $supported_extra_post_statuses ) 
						&& current_user_can( 'edit_post', $post_belongs_id ) 
						&& function_exists( 'get_preview_post_link' )
						
					) {
						$link = get_preview_post_link( $post_belongs_id );
					}
					
					$link_attributes['cred_action'] = 'edit_rfg';
					$link_attributes['cred_rfg_id'] = $post_id;
				}
			} else if ( 'publish' == $post_status ) {
				$link = get_permalink( $post_id );
			} else if ( 
				in_array( $post_status, $supported_extra_post_statuses ) 
				&& current_user_can( 'edit_post', $post_id ) 
				&& function_exists( 'get_preview_post_link' )
				
			) {
				$link = get_preview_post_link( $post_id );
			}

			if ( ! $link ) {
				return $out;
			}
			
			$link = add_query_arg( $link_attributes, $link );
			
			$target = in_array( $atts['target'], array( 'top', 'blank' ) ) ? ( '_' . $atts['target'] ) : '';
			
			$content = wpv_translate( $translate_name, $content, true, 'Toolset Shortcodes' );
			
			$content = str_replace( '%%POST_TITLE%%', $item_post->post_title, $content );
			$content = str_replace( '%%POST_ID%%', $item_post->ID, $content );
			
			$out .= '<a'
				. ' href="' . esc_url( $link ) . '"' 
				. ( ! empty( $atts['class'] ) ? ( ' class="' . esc_attr( $atts['class'] ) . '"' ) : '' ) 
				. ( ! empty( $atts['style'] ) ? ( ' style="' . esc_attr( $atts['style'] ) . '"' ) : '' ) 
				. ( ! empty( $target ) ? ( ' target="' . esc_attr( $target ) . '"' ) : '' )
				. ' >'
				. $content
				. '</a>';

		}
		
		return $out;
		
	}
	
	/**
	 * Register the toolset-edit-post-link data for the Views shortcodes GUI API.
	 *
	 * @param array $view_shortcodes
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	function register_toolset_edit_post_link_data( $views_shortcodes ) {
		if ( ! $this->is_cred_installed ) {
			return $views_shortcodes;
		}
		$views_shortcodes['toolset-edit-post-link'] = array(
			'callback' => array( $this, 'edit_post_link_data' )
		);
		return $views_shortcodes;
	}
	
	/**
	 * Data provider for the toolset-edit-post-link shortcode GUI.
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	function edit_post_link_data() {
		
		$data = array(
			'name' => __( 'Forms edit-post link', 'wpv-views' ),
			'label' => __( 'Forms edit-post link', 'wpv-views' ),
			'post-selection' => true,
		);
		
		$attributes = array(
			'display-options' => array(
				'label' => __( 'Display options', 'wpv-views' ),
				'header' => __( 'Display options', 'wpv-views' ),
				'fields' => $this->get_edit_link_shortcodes_gui_basic_fields(),
				'content' => array(
					'label' => __( 'Link text', 'wpv-views' ),
					'description' => __( 'You can use %%POST_TITLE%% and %%POST_ID%% as placeholders.', 'wpv-views' ),
					'default' => sprintf( __( 'Edit %s', 'wpv-views' ), '%%POST_TITLE%%' )
				)
			),
		);
		
		$data = $this->adjust_edit_link_shortcodes_gui_fields( $data, $attributes );
		
		return $data;
		
	}
	
	/**
	 * Callback for the toolset-edit-user-link shortcode.
	 *
	 * Generates a link to edit the current, or a given, user, using the user edit form 
	 * located in one resource (Content Template or layout), that will be forcedly displayed 
	 * when clicking that link.
	 *
	 * @param array  $atts    List of attributes passed to the shortcode
	 *     content_template_slug string The slug of the Content Template to force
	 *     layout_slug           string The slug of the layout to force
	 *     target                string The target of the link: 'self'|'top'|'blank'
	 *     style                 string Inline styles to be passed to the link
	 *     class                 string Custom classes to be passed to the link
	 *     id                    string The ID of the user to be edited, will default to the current user in a View loop or the current user otherwise
	 * @param string $content The content of the shortcode
	 *
	 * @return string
	 *
	 * @note One of the content_template_slug and layout_slug two attributes must be passed and valid.
	 *
	 * @since 2.4.0
	 */
	public function edit_user_link_shortcode( $atts, $content ) {
		
		if ( ! $this->is_cred_installed ) {
			return;
		}
		
		$atts = shortcode_atts(
			array(
				'content_template_slug'	=> '',
				'layout_slug'			=> '',
				'target'	=> 'self',
				'style'		=> '',
                'class'		=> '',
				'id'		=> ''
			),
			$atts
		);
		
		if (
			empty( $atts['content_template_slug'] ) 
			&& empty( $atts['layout_slug'] ) 
		) {
			return;
		}
		
		$out = '';
		$form_id = 0;
		$link_attributes = array();
		$translate_name = 'toolset-edit-user-link';
		
		if ( ! empty( $atts['layout_slug'] ) ) {
			$layout_id = apply_filters( 'ddl-get_layout_id_by_slug', 0, $atts['layout_slug'] );
			$translate_name .= '_' . $atts['layout_slug'];
			if ( $layout_id ) {
				$link_attributes['layout_id'] = $layout_id;
				$form_id = $this->get_form_in_layout( $layout_id, 'user' );
			} else {
				return;
			}
		} else if ( ! empty( $atts['content_template_slug'] ) ) {
			$ct_id = WPV_Content_Template_Embedded::get_template_id_by_name( $atts['content_template_slug'] );
			$translate_name .= '_' . $atts['content_template_slug'];
			if ( $ct_id ) {
				$link_attributes['content-template-id'] = $ct_id;
				$form_id = $this->get_form_in_content_template( $ct_id, 'user' );
			} else {
				return;
			}
		}
		
		$translate_name .= '_' . substr( md5( $content ), 0, 12 );
		
		if ( 
			isset( $atts['id'] ) 
			&& ! empty( $atts['id'] )
		) {
			if ( is_numeric( $atts['id'] ) ) {
				$data = get_user_by( 'id', $atts['id'] );
				if ( $data ) {
					$user_id = $atts['id'];
					if ( isset( $data->data ) ) {
						$data = $data->data;
						$meta = get_user_meta( $atts['id'] );
					} else {
						return;
					}
				} else {
					return;
				}
			} else {
				return;
			}
		} else {
			global $WP_Views;
			if ( 
				isset( $WP_Views->users_data['term']->ID ) 
				&& ! empty( $WP_Views->users_data['term']->ID ) 
			) {
				$user_id = $WP_Views->users_data['term']->ID;
				$data = $WP_Views->users_data['term']->data;
				$meta = $WP_Views->users_data['term']->meta;
			} else {
				global $current_user;
				if ( $current_user->ID > 0 ) {
					$user_id = $current_user->ID;
					$data = new WP_User( $user_id );
					if ( isset( $data->data ) ) {
						$data = $data->data;
						$meta = get_user_meta( $user_id );
					} else {
						return;
					}
				} else {
					return;
				}
			}
		}
		
		$link_attributes['user_id'] = $user_id;
		
		global $post, $current_user;
		
		if (
			! empty( $post ) 
			&& ! empty( $form_id )
		) {
			
			$form_settings = (array) get_post_meta( $form_id, '_cred_form_settings', true );
			if (
				! is_array( $form_settings ) 
				|| empty( $form_settings ) 
				|| ! array_key_exists( 'form', $form_settings ) 
				|| ! array_key_exists( 'type', $form_settings['form'] )
			) {
				return;
			}
			
			$post_status = $post->post_status;
			
			if ( 'publish' != $post_status ) {
				return $out;
			}
			
			$post_id = $post->ID;

			$link = get_permalink( $post_id );
			$link = add_query_arg( $link_attributes, $link );
			
			$target = in_array( $atts['target'], array( 'top', 'blank' ) ) ? ( '_' . $atts['target'] ) : '';
			
			$content = wpv_translate( $translate_name, $content, true, 'Toolset Shortcodes' );
			
			$content = str_replace( '%%USER_LOGIN%%', $data->user_login, $content );
			$content = str_replace( '%%USER_NICENAME%%', $data->user_nicename, $content );
			$content = str_replace( '%%USER_ID%%', $user_id, $content );
			
			$out .= '<a'
				. ' href="' . esc_url( $link ) . '"' 
				. ( ! empty( $atts['class'] ) ? ( ' class="' . esc_attr( $atts['class'] ) . '"' ) : '' ) 
				. ( ! empty( $atts['style'] ) ? ( ' style="' . esc_attr( $atts['style'] ) . '"' ) : '' ) 
				. ( ! empty( $target ) ? ( ' target="' . esc_attr( $target ) . '"' ) : '' )
				. ' >'
				. $content
				. '</a>';

		}
		
		return $out;
		
	}
	
	/**
	 * Register the toolset-edit-user-link data for the Views shortcodes GUI API.
	 *
	 * @param array $view_shortcodes
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	function register_toolset_edit_user_link_data( $views_shortcodes ) {
		if ( ! $this->is_cred_installed ) {
			return $views_shortcodes;
		}
		$views_shortcodes['toolset-edit-user-link'] = array(
			'callback' => array( $this, 'edit_user_link_data' )
		);
		return $views_shortcodes;
	}
	
	/**
	 * Data provider for the toolset-edit-user-link shortcode GUI.
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	function edit_user_link_data() {
		
		$data = array(
			'name' => __( 'Forms edit-user link', 'wpv-views' ),
			'label' => __( 'Forms edit-user link', 'wpv-views' ),
			'user-selection' => true,
		);
		
		$attributes = array(
			'display-options' => array(
				'label' => __( 'Display options', 'wpv-views' ),
				'header' => __( 'Display options', 'wpv-views' ),
				'fields' => $this->get_edit_link_shortcodes_gui_basic_fields(),
				'content' => array(
					'label' => __( 'Link text', 'wpv-views' ),
					'description' => __( 'You can use %%USER_LOGIN%%, %%USER_NICENAME%% and %%USER_ID%% as placeholders.', 'wpv-views' ),
					'default' => sprintf( __( 'Edit %s', 'wpv-views' ), '%%USER_NICENAME%%' )
				)
			),
		);
		
		$data = $this->adjust_edit_link_shortcodes_gui_fields( $data, $attributes, 'user' );
		
		return $data;
		
	}
	
	/**
	 * Callback for the cred-form-message shortcode.
	 *
	 * Generates a conditional output for the selected message that belongs to the form which ID 
	 * is passed over the cred_referrer_form_id parameter of the current page URL.
	 * Any customization or HTML markup in the message should be included in the messages GUI.
	 *
	 * @param array  $atts    List of attributes passed to the shortcode
	 *     form_id 	int    The ID of the form to use in case there is no URL parameter, not in use at this point
	 *     message  string The key of the message as stored in the form settings
	 *
	 * @return string
	 *
	 * @note This will return nothing if the URL parameter cred_referrer_form_id is missing or empty
	 *
	 * @since 2.4.0
	 */
	public function form_message_shortcode( $atts, $content = null ) {
		
		if ( ! $this->is_cred_installed ) {
			return;
		}
		
		$atts = shortcode_atts(
			array(
				'form_id'	=> '',
				'message'	=> 'cred_message_post_saved'
			),
			$atts
		);
		
		if (
			$atts['form_id'] == '' 
			&& '' == toolset_getget( 'cred_referrer_form_id' )
		) {
			return;
		}
		
		$cred_referrer_form_id = (int) toolset_getget( 'cred_referrer_form_id' );
		$cred_messages = apply_filters( 'toolset_cred_form_messages', array(), $cred_referrer_form_id );
		if ( empty( $cred_messages ) ) {
			return;
		}

		$default_message      = ( array_key_exists( 'cred_message_post_saved', $cred_messages ) ? $cred_messages['cred_message_post_saved'] : '' );
		$cred_selected_message_with_markup = '<div class="alert alert-success"><p>' . $default_message . '</p></div>';

		/**
		 * Applies custom markup to cred form success message
		 *
		 * @since 2.4.0
		 *
		 * @param string  $cred_selected_message_with_markup The message to be displayed with the default markup.
		 * @param string  $cred_selected_message             The message to be displayed with no markup at all.
		 * @param int     $cred_referrer_form_id             The form ID to display the message for.
		 */
		$cred_selected_message_with_markup = apply_filters( 'toolset_filter_cred_form_message_shortcode_output', $cred_selected_message_with_markup, $default_message, $cred_referrer_form_id );
		
		/**
		 * Applies custom markup to cred form success message
		 *
		 * @since 2.4.0
		 *
		 * @param string  $cred_selected_message_with_markup The message to be displayed with the default markup.
		 * @param string  $cred_selected_message the message to be displayed with no markup at all.
		 */
		$cred_selected_message_with_markup = apply_filters( 'toolset_filter_cred_form_message_shortcode_output_' . $cred_referrer_form_id, $cred_selected_message_with_markup, $default_message );

		return $cred_selected_message_with_markup;
		
	}
	
	/**
	 * Register the cred-form-message data for the Views shortcodes GUI API.
	 *
	 * @param array $view_shortcodes
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	public function register_cred_form_message_data( $views_shortcodes ) {
		if ( ! $this->is_cred_installed ) {
			return $views_shortcodes;
		}
		$views_shortcodes['cred-form-message'] = array(
			'callback' => array( $this, 'form_message_data' )
		);
		return $views_shortcodes;
	}
	
	/**
	 * Data provider for the cred-form-message shortcode GUI.
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	public function form_message_data() {
		
		$data = array(
			'name' => __( 'Forms message', 'wpv-views' ),
			'label' => __( 'Forms message', 'wpv-views' ),
		);

		$attributes = array(
			'display-options' => array(
				'label' => __( 'Display options', 'wpv-views' ),
				'header' => __( 'Display options', 'wpv-views' ),
				'fields' => array(
					'information'	=> array(
						'type'		=> 'message',
						'content'	=> '<p class="toolset-alert toolset-alert-info">'
										. __( 'Forms can redirect to the edited post or user, or to a specific page, after they are submitted.', 'wpv-views' )
										. '<br />'
										. '<br />'
										. __( 'Add this shortcode to the redirect target to get a message from the form that just edited it.', 'wpv-views' )
										. '</p>'
					)
				)
			),
		);

		$data['attributes'] = $attributes;
		
		return $data;
		
	}
	
	/**
	 * Expand the cred-form-message shortcode early.
	 *
	 * As this shortcode might produce an HTML block element, the WordPress formatting mechanism 
	 * would wrap it into paragraph tags if expanded in the native point of the page rendering.
	 * Because of that, we parse and expand this shortcode early (at the_content:5) 
	 * to prevent formatting issues.
	 *
	 * @param string $content The current post content being rendered
	 *
	 * @return string
	 *
	 * @see WPV_Frontend_Render_Filters::on_load
	 *
	 * @since 2.4.0
	 */
	public function pre_process_form_message_shortcode( $content ) {
		if ( strpos( $content, '[cred-form-message' ) === false ) {
			return $content;
		}
		global $shortcode_tags;
		// Back up current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();			
		add_shortcode( 'cred-form-message', array( $this, 'form_message_shortcode' ) );
		$content = do_shortcode( $content );
		$shortcode_tags = $orig_shortcode_tags;
		return $content;
	}
	
	/**
	 * Generate the basic fields for the Toolset edit links.
	 *
	 * This method is shared by the toolset-edit-post-link and toolset-edit-user-link shortcodes, 
	 * and generates the style/class combo, as well as the target attribute GUI
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	public function get_edit_link_shortcodes_gui_basic_fields() {
		$basic_fields = array(
			'class_style_combo' => array(
				'label'		=> __( 'Element styling', 'wpv-views' ),
				'type'		=> 'grouped',
				'fields'	=> array(
					'class' => array(
						'pseudolabel'	=> __( 'Input classnames', 'wpv-views'),
						'type'			=> 'text',
						'description'	=> __( 'Space-separated list of classnames to apply. For example: classone classtwo', 'wpv-views' )
					),
					'style' => array(
						'pseudolabel'	=> __( 'Input style', 'wpv-views'),
						'type'			=> 'text',
						'description'	=> __( 'Raw inline styles to apply. For example: color:red;background:none;', 'wpv-views' )
					),
				),
			),
			'target'		=> array(
				'label'			=> __( 'Open the edit form in', 'wpv-views' ),
				'type'			=> 'select',
				'options'		=> array(
					'self'		=> __( 'The current window', 'wpv-views' ),
					'top'		=> __( 'The parent window', 'wpv-views' ),
					'blank'		=> __( 'A new window' )
				),
				'default'		=> 'self'
			),
		);
		return $basic_fields;
	}
	
	/**
	 * Adjust the fields for the Toolset edit links GUI.
	 *
	 * @param array  $data       The shortcode GUI data
	 * @param array  $attributes The shortcode GUI attributes to add to $data
	 * @param string $form_type  Whether this belongs to the toolset-edit-post-link or the toolset-edit-user-link shortcode: 'post'|'user'
	 *
	 * @return array
	 *
	 * @since 2.4.0
	 */
	public function adjust_edit_link_shortcodes_gui_fields( $data, $attributes, $form_type = 'post' ) {
		
		if ( ! in_array( $form_type, array( 'post', 'user' ) ) ) {
			return array();
		}
		
		if ( $this->is_layouts_installed ) {
			$filter_args = array(
				'property'	=> 'cell_type', 
				'value'		=> ( 'user' == $form_type ) ? 'cred-user-cell' : 'cred-cell'
			);
			$layouts_available = apply_filters( 'ddl-filter_layouts_by_cell_type', array(), $filter_args );
			if ( count( $layouts_available ) > 0 ) {
				$available_options = array(
					''	=> __( '-- Select a layout --', 'wpv-views' )
				);
				
				foreach ( $layouts_available as $layout_for_edit ) {
					$available_options[ $layout_for_edit->slug ] = $layout_for_edit->name;
				}
				$layouts_data = array(
					'layout_slug' => array(
						'label'		=> __( 'Using this layout', 'wpv-views' ),
						'type'		=> 'select',
						'options'	=> $available_options,
						'default'	=> '',
						'description'	=> __( 'Select a layout that contains a form cell', 'wpv-views' ),
						'documentation'	=> '<a href="' . self::EDIT_LINK_DOCUMENTATION . '" target="_blank">' . __( 'Toolset edit links', 'wpv-views' ) . '</a>',
						'required'	=> true,
					)
				);
				
				$layouts_data = array_merge( $layouts_data, $attributes['display-options']['fields'] );
				$attributes['display-options']['fields'] = $layouts_data;
			} else {
				$attributes['display-options']['fields'] = array(
					'information'	=> array(
						'type'		=> 'message',
						'content'	=> '<p>' 
										. __( 'Create a new Layout that will include the editing form. You can start from scratch or copy the template you use to display the content and modify it.', 'wpv-views' )
										. '</p>'
										. '<p>'
										. '<a href="' . self::EDIT_LINK_DOCUMENTATION . '" target="_blank">' . __( 'Documentation on Toolset edit links', 'wpv-views' ) . '</a>'
										. WPV_MESSAGE_SPACE_CHAR
										. '&bull;'
										. WPV_MESSAGE_SPACE_CHAR
										. '<a href="' . admin_url( 'admin.php?page=dd_layouts' ) . '" target="_blank">' . __( 'See all available layouts, or create a new one', 'wpv-views' ) . '</a>'
										. '</p>'
					)
				);
				unset( $attributes['display-options']['content'] );
				unset( $data[ $form_type . '-selection' ] );
			}
		} else {
			$content_templates_available = $this->get_content_templates_with_edit_forms( $form_type );
			if ( count( $content_templates_available ) > 0 ) {
				$available_options = array(
					''	=> __( '-- Select a Content Template --', 'wpv-views' )
				);
				
				foreach ( $content_templates_available as $content_templates_for_edit ) {
					$available_options[ $content_templates_for_edit->post_name ] = $content_templates_for_edit->post_title;
				}
				$content_templates_data = array(
					'content_template_slug' => array(
						'label'		=> __( 'Using this Content Template', 'wpv-views' ),
						'type'		=> 'select',
						'options'	=> $available_options,
						'default'	=> '',
						'description'	=> __( 'Select a Content Template that contains a form shortcode', 'wpv-views' ),
						'documentation'	=> '<a href="' . self::EDIT_LINK_DOCUMENTATION . '" target="_blank">' . __( 'Toolset edit links', 'wpv-views' ) . '</a>',
						'required'	=> true,
					)
				);
				
				$content_templates_data = array_merge( $content_templates_data, $attributes['display-options']['fields'] );
				$attributes['display-options']['fields'] = $content_templates_data;
			} else {
				$attributes['display-options']['fields'] = array(
					'information'	=> array(
						'type'		=> 'message',
						'content'	=> '<p>'
										. __( 'Create a new Content Template that will include the editing form. You can start from scratch or copy the template you use to display the content and modify it.', 'wpv-views' )
										. '</p>'
										. '<p>'
										. '<a href="' . self::EDIT_LINK_DOCUMENTATION . '" target="_blank">' . __( 'Documentation on Toolset edit links', 'wpv-views' ) . '</a>'
										. WPV_MESSAGE_SPACE_CHAR
										. '&bull;'
										. WPV_MESSAGE_SPACE_CHAR
										. '<a href="' . admin_url( 'admin.php?page=view-templates' ) . '" target="_blank">' . __( 'See all available Content Templates, or create a new one', 'wpv-views' ) . '</a>'
										. '</p>'
					)
				);
				unset( $attributes['display-options']['content'] );
				unset( $data[ $form_type . '-selection' ] );
			}
		}
		
		$data['attributes'] = $attributes;
		
		return $data;
		
	}
	
	/**
	 * Auxiliar method to get Content Templates that contain a form shortcode.
	 *
	 * Used by toolset-edit-post-link and toolset-edit-user-link shortcodes, to get 
	 * Content Templates that contain [cred_form or [cred_user_form shortcodes
	 *
	 * @param string $form_type Whether this belongs to the toolset-edit-post-link or the toolset-edit-user-link shortcode: 'post'|'user'
	 *
	 * @return array
	 *
	 * @note When not using WPML, or when Content Templates are not translatable, the expensive quer y is cached in a transient
	 *     that gets invalidated every time a Content Template is created, edited, or deleted.
	 *
	 * @see WPV_Cache::delete_shortcodes_gui_transients_action
	 *
	 * @since 2.4.0
	 */
	public function get_content_templates_with_edit_forms( $form_type = 'post' ) {
		
		if ( ! in_array( $form_type, array( 'post', 'user' ) ) ) {
			return array();
		}
		
		$content_templates_translatable = apply_filters( 'wpml_is_translated_post_type', false, 'view-template' );
		
		$transient_key = 'wpv_transient_pub_cts_for_cred_' . $form_type;
		
		$content_templates_available = get_transient( $transient_key );
		
		if ( 
			$content_templates_available !== false 
			&& $content_templates_translatable === false
		) {
			return $content_templates_available;
		}
		
		global $wpdb, $sitepress;
		$values_to_prepare = array();
		$wpml_join = $wpml_where = "";
		
		if ( $content_templates_translatable ) {
			$wpml_current_language = apply_filters( 'wpml_current_language', '' );
			$wpml_join = " JOIN {$wpdb->prefix}icl_translations t ";
			$wpml_where = " AND p.ID = t.element_id AND t.language_code = %s AND t.element_type LIKE 'post_%' ";
			$values_to_prepare[] = $wpml_current_language;
		}
		
		switch ( $form_type ) {
			case 'post':
				$values_to_prepare[] = '%[cred_form %';
				$values_to_prepare[] = '%{!{cred_form %';
				break;
			case 'user':
				$values_to_prepare[] = '%[cred_user_form %';
				$values_to_prepare[] = '%{!{cred_user_form %';
				break;
		}
		
		$values_to_prepare[] = 'view-template';
		$content_templates_available = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.post_name, p.post_title 
				FROM {$wpdb->posts} p {$wpml_join} 
				WHERE p.post_status = 'publish' 
				{$wpml_where} 
				AND ( p.post_content LIKE '%s' OR p.post_content LIKE '%s' ) 
				AND p.post_type = %s 
				ORDER BY p.post_title",
				$values_to_prepare
			)
		);
		
		if ( $content_templates_translatable === false ) {
			set_transient( $transient_key, $content_templates_available, WEEK_IN_SECONDS );
		}
		
		return $content_templates_available;
	}
	
	/**
	 * Get the form ID for a form shortcode inside a Content Template body.
	 *
	 * @param int    $ct_id
	 * @param string $form_type
	 *
	 * @return int
	 *
	 * @uses $this->content_templates_to_forms
	 *
	 * @since 2.4.0
	 */
	public function get_form_in_content_template( $ct_id, $form_type = 'post' ) {
		
		$form_id = 0;
		
		if ( ! $this->is_cred_installed ) {
			return $form_id;
		}
		
		if ( ! in_array( $form_type, array( 'post', 'user' ) ) ) {
			return $form_id;
		}
		
		if ( isset( $this->content_templates_to_forms[ $ct_id ] ) ) {
			return $this->content_templates_to_forms[ $ct_id ];
		}
		
		$ct_content = get_post_field( 'post_content', $ct_id );
		
		if ( 
			'post' == $form_type 
			&& strpos( $ct_content, '[cred_form ' ) === false 
		) {
			return $form_id;
		}
		
		if ( 
			'user' == $form_type 
			&& strpos( $ct_content, '[cred_user_form ' ) === false 
		) {
			return $form_id;
		}
		
		$this->current_content_template = $ct_id;
		
		global $shortcode_tags;
		// Back up current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();
		
		add_shortcode( 'cred_form', array( $this, 'fake_cred_post_form_shortcode_to_get_form' ) );
		add_shortcode( 'cred_user_form', array( $this, 'fake_cred_user_form_shortcode_to_get_form' ) );
		do_shortcode( $ct_content );
		
		$shortcode_tags = $orig_shortcode_tags;
		
		$this->current_content_template = 0;
		
		if ( isset( $this->content_templates_to_forms[ $ct_id ] ) ) {
			return $this->content_templates_to_forms[ $ct_id ];
		}
		
		return $form_id;
		
	}
	
	/**
	 * Fake a cred_form shortcode to get the form ID.
	 *
	 * @since 2.4.0
	 */
	public function fake_cred_post_form_shortcode_to_get_form( $atts ) {
		
		if ( ! $this->is_cred_installed ) {
			return;
		}
		
		$this->fake_cred_form_shortcode_to_get_form( $atts, CRED_FORMS_CUSTOM_POST_NAME );
		
		return;
		
	}
	
	/**
	 * Fake a cred_user_form shortcode to get the form ID.
	 *
	 * @since 2.4.0
	 */
	public function fake_cred_user_form_shortcode_to_get_form( $atts ) {
		
		if ( ! $this->is_cred_installed ) {
			return;
		}
		
		$this->fake_cred_form_shortcode_to_get_form( $atts, CRED_USER_FORMS_CUSTOM_POST_NAME );
		
		return;
		
	}
	
	/**
	 * Cache the form ID from a cred_form or cred_user_form shortcode attributes.
	 *
	 * @uses $this->current_content_template
	 * @uses $this->content_templates_to_forms
	 *
	 * @since 2.4.0
	 */
	public function fake_cred_form_shortcode_to_get_form( $atts, $form_post_type ) {
		
		if ( ! $this->is_cred_installed ) {
			return;
		}
		
		$atts = shortcode_atts( 
			array(
				'form' => ''
			), 
			$atts 
		);
		
		$form = $atts['form'];
		$current_content_template = $this->current_content_template;
		
		if ( empty( $form ) ) {
			return;
		}
		
		if ( 
			is_string( $form ) 
			&& ! is_numeric( $form ) 
		) {
			$result = get_page_by_path( html_entity_decode( $form ), OBJECT, $form_post_type );
			if ( 
				$result 
				&& is_object( $result ) 
				&& isset( $result->ID ) 
			) {
				$this->content_templates_to_forms[ $current_content_template ] = $result->ID;
				return;
			} else {
				$result = get_page_by_title( html_entity_decode( $form ), OBJECT, $form_post_type );
				if ( 
					$result 
					&& is_object( $result ) 
					&& isset( $result->ID ) 
				) {
					$this->content_templates_to_forms[ $current_content_template ] = $result->ID;
					return;
				}
			}
		} else {
			if ( is_numeric( $form ) ) {
				$result = get_post( $form );
				if ( 
					$result 
					&& is_object( $result ) 
					&& isset( $result->ID ) 
				) {
					$this->content_templates_to_forms[ $current_content_template ] = $result->ID;
					return;
				}
			}
		}
		
		return;
	}
	
	/**
	 * Get the form ID for a form cell in a layout.
	 *
	 * @param int    $layout_id
	 * @param string $form_type
	 *
	 * @return int
	 *
	 * @since 2.4.0
	 */
	public function get_form_in_layout( $layout_id, $form_type = 'post' ) {
		
		$form_id = 0;
		
		if ( ! $this->is_layouts_installed ) {
			return $form_id;
		}
		
		if ( isset( $this->layouts_to_forms[ $layout_id ] ) ) {
			return $this->layouts_to_forms[ $layout_id ];
		}
		
		$cell_type = ( 'user' == $form_type ) ? 'cred-user-cell' : 'cred-cell';
		$cell_content_property_key = ( 'user' == $form_type ) ? 'ddl_layout_cred_user_id' : 'ddl_layout_cred_id';
		
		$cells_in_layout = apply_filters( 'ddl-filter_get_layout_cells_by_type', array(), $layout_id, $cell_type );
		
		if ( 0 == count( $cells_in_layout ) ) {
			return $form_id;
		}
		
		$cred_cell = array_shift( $cells_in_layout );
		
		$form_id = apply_filters( 'ddl-filter_get_cell_content_property', $form_id, $cred_cell, $cell_content_property_key );
		
		return $form_id;
		
	}

}