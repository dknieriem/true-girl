<?php

/**
 * Controller for Related content WP meta boxes
 *
 * @since m2m
 */
class Types_Page_Extension_Meta_Box_Related_Content extends Types_Page_Extension_Meta_Box {


	/**
	 * Screen options 'Per page' name
	 *
	 * @since 2.3
	 * @var string The name of the parameter used for "per page" screen options
	 */
	const SCREEN_OPTION_PER_PAGE_NAME = 'toolset_associations_per_page';


	/**
	 * Screen options 'Per page' default value
	 *
	 * @since 2.3
	 * @var integer Default value of the parameter used for "per page" screen options
	 */
	const SCREEN_OPTION_PER_PAGE_DEFAULT_VALUE = 10;


	/**
	 * Meta box ID
	 *
	 * @var string
	 * @since m2m
	 */
	const ID = 'related-content'; // TODO in a future must admin different domains.


	/**
	 * Handler ID for styles and scripts
	 *
	 * @var string
	 * @since m2m
	 */
	const MAIN_ASSET_HANDLE = 'types-page-extension-related-content-main';
	/**
	 * Meta boxes data.
	 *	[id]    => Metabox ID.
	 *	[title] => Metabox title.
	 *	[args]  => Callback arguments.
	 *
	 * @var array
	 * @since m2m
	 */
	protected $metaboxes_data = array();


	/**
	 * Twig teplate path
	 *
	 * @var array
	 * @since m2m
	 */
	protected $twig_template_paths = array(
		// TODO in a future must admin different domains.
		self::ID => '/application/views/page/extension/related_content',
	);


	/**
	 * Dialog factory
	 *
	 * @var Toolset_Twig_Dialog_Box_Factory
	 * @since m2m
	 */
	private $dialog_factory;


	/**
	 * Current post type role
	 *
	 * @var string
	 * @since m2m
	 */
	private $role;


	/**
	 * The other post type, the one related to the current one
	 *
	 * @var string
	 * @since m2m
	 */
	private $other_post_type;


	/**
	 * For testing purposes
	 *
	 * @var Toolset_Twig_Dialog_Box_Factory
	 * @since m2m
	 */
	private $twig_dialog_factory;


	/**
	 * For testing purposes
	 *
	 * @var Toolset_Relationship_Query_V2
	 * @since m2m
	 */
	private $relationship_query;


	/**
	 * For testing purposes
	 *
	 * @var Types_Viewmodel_Related_Content
	 * @since m2m
	 */
	private $related_content_model;


	/**
	 * Checks if it can connect to another related content
	 *
	 * For testing purposes
	 *
	 * @var boolean
	 * @since m2m
	 */
	private $can_connect;


	/**
	 * Stores if the current post is translated in default language
	 *
	 * For testing purposes
	 *
	 * @var boolean
	 * @since m2m
	 */
	private $has_default_language_translation;

	/**
	 * Used for better instance
	 *
	 * @param Twig_Environment                $twig For testing purposes.
	 * @param Toolset_Twig_Dialog_Box_Factory $twig_dialog_factory For testing purposes.
	 * @param Toolset_Relationship_Query_V2      $relationship_query For testing purposes.
	 * @param Types_Viewmodel_Related_Content $related_content For testing purposes.
	 * @param boolean                         $can_connect_di Test injection purposes.
	 * @param boolean                         $has_default_language_translation_di Text injection purposes.
	 *
	 * @return Types_Page_Extension_Meta_Box Self object.
	 * @since m2m
	 */
	public static function get_instance( Twig_Environment $twig = null, Toolset_Twig_Dialog_Box_Factory $twig_dialog_factory = null, Toolset_Relationship_Query_V2 $relationship_query = null, Types_Viewmodel_Related_Content $related_content = null, $can_connect_di = null, $has_default_language_translation_di = null ) {
		if ( null === static::$instance ) {
			static::$instance = new static( $twig_dialog_factory, $relationship_query, $related_content, $can_connect_di, $has_default_language_translation_di );
		}
		return static::$instance;
	}


	/**
	 * Constructor
	 *
	 * @param Toolset_Twig_Dialog_Box_Factory $twig_dialog_factory For testing purposes.
	 * @param Toolset_Relationship_Query_V2      $relationship_query For testing purposes.
	 * @param Types_Viewmodel_Related_Content $related_content For testing purposes.
	 * @param boolean                         $can_connect_di Test injection purposes.
	 * @param boolean                         $has_default_language_translation Test injection puroposes.
	 */
	public function __construct( Toolset_Twig_Dialog_Box_Factory $twig_dialog_factory = null, Toolset_Relationship_Query_V2 $relationship_query = null, Types_Viewmodel_Related_Content $related_content = null, $can_connect_di = null, $has_default_language_translation_di = null ) {
		$this->dialog_factory = $twig_dialog_factory;
		$this->relationship_query = $relationship_query;
		$this->related_content_model = $related_content;

		// Needed for PHP 5.3.
		foreach ( $this->twig_template_paths as $id => $path ) {
			$this->twig_template_paths[ $id ] = TYPES_ABSPATH . $path;
		}
		$this->can_connect = $can_connect_di;
		$this->has_default_language_translation = $has_default_language_translation_di;

		$this->prepare();
	}


	/**
	 * Gets the Toolset_Relationship_Query_V2 object
	 *
	 * @return Toolset_Relationship_Query_V2
	 * @since m2m
	 */
	private function get_relationship_query() {
		if ( $this->relationship_query ) {
			return $this->relationship_query;
		}
		$query = new Toolset_Relationship_Query_V2();
		return $query;
	}

	/**
	 * Checks if there is some related content type
	 *
	 * @since m2m
	 */
	public function prepare() {

		$screen = get_current_screen();
		if ( null === $screen ) {
			return;
		}

		if ( 'post' !== $screen->base ) {
			return;
		}
		do_action( 'toolset_do_m2m_full_init' );
		$this->post_type = $screen->id;

		$relationship_query = $this->get_relationship_query();
		$relationship_query->add( $relationship_query->has_domain_and_type( $this->post_type, 'posts' ) );

		$relationships = $relationship_query->get_results();

		if ( empty( $relationships ) ) {
			return;
		}
		$metaboxes_data = array();

		$post_type_repository = Toolset_Post_Type_Repository::get_instance();

		$user_access = new \OTGS\Toolset\Types\User\Access( wp_get_current_user() );

		foreach ( $relationships as $relationship ) {
			if ( ! $relationship->get_origin()->show_on_post_edit_screen() ) {
				// should not be shown on post edit screen, continue with next.
				continue;
			}
			$types = array_merge( $relationship->get_parent_type()->get_types(), $relationship->get_child_type()->get_types() );
			$has_post_type_disabled = false;

			foreach ( $types as $type ) {
				if( ! $user_access->canEditOwn( $type ) ) {
					// user is not allowed to read the connected posts
					$has_post_type_disabled = true;
					continue;
				}

				$post_type_object = $post_type_repository->get( $type );
				if ( ! $post_type_object ) {
					$has_post_type_disabled = true;
				} elseif ( $post_type_object && $post_type_object instanceof IToolset_Post_Type_From_Types ) {
					$post_type_definition = $post_type_object->get_definition();
					if ( isset( $post_type_definition['disabled'] ) && $post_type_definition['disabled'] ) {
						$has_post_type_disabled = true;
					}
				}
			}
			if ( ! $has_post_type_disabled ) {
				$metaboxes_data[] = array(
					'id' => $relationship->get_slug(),
					'title' => $this->get_metabox_title( $relationship ),
					'arguments' => array(
						'relationship' => $relationship,
					),
				);
				$this->prepare_dialogs( $relationship );
			}
		}

		if ( empty( $metaboxes_data ) ) {
			// no meta boxes, abort...
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'on_admin_enqueue_scripts' ) );

		// Fix for GUTENBERG, which has already triggered 'admin_enqueue_scripts' add this point
		// It's a known issue see: https://github.com/WordPress/gutenberg/issues/4929
		if( did_action( 'admin_enqueue_scripts' ) ) {
			$this->on_admin_enqueue_scripts();
		}

		$this->add_meta_boxes( $metaboxes_data );
		$this->add_screen_options();
	}


	/**
	 * Display screen options on the page.
	 *
	 * @since 2.3
	 */
	public function add_screen_options() {
		$args = array(
			'label' => __( 'Number of displayed related items', 'wpcf' ),
			'default' => self::SCREEN_OPTION_PER_PAGE_DEFAULT_VALUE,
			'option' => self::SCREEN_OPTION_PER_PAGE_NAME,
		);
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Storage screen option of how many associations should be displayed
	 *
	 * This must be static because the constructor of this function is calling get_current_screen() which is not
	 * availble when this is needed.
	 *
	 * @param $original_value
	 * @param $option
	 * @param $option_value
	 *
	 * @return mixed
	 */
	public static function set_screen_option( $original_value, $option, $option_value ) {
		if ( self::SCREEN_OPTION_PER_PAGE_NAME == $option ) {
			return $option_value;
		}

		// not our option, return the original value (which is by default "false" = no saving)
		return $original_value;
	}


	/**
	 * Builds js/twig Data
	 *
	 * @param array $data Contains 'args' => Toolset_Relationship_Definition.
	 * @return array
	 * @since m2m
	 */
	private function build_js_data( $data ) {
		$ajax_controller = Types_Ajax::get_instance();

		$field_action_name = $ajax_controller->get_action_js_name( Types_Ajax::CALLBACK_RELATED_CONTENT_ACTION );

		if( ! $current_post_id = $this->get_current_post_id() ) {
			// no id found, disable metabox
			return array();
		}

		// When there is not default language post the metabox is disabled.
		if ( ! $this->has_default_language_translation( $current_post_id ) ) {
			return array();
		}

		/** @var Toolset_Relationship_Definition $relationship */
		$relationship = $data['args']['relationship'];
		$items_per_page = $this->get_items_per_page_setting();
		$related_content = $this->get_related_content( $relationship, 1, $items_per_page );
		$other_post_type = $this->get_other_post_type( $relationship );

		$can_connect_another = $this->can_connect_another( $relationship, $current_post_id );
		$only_one_related_conection = $this->get_other_cardinality( $relationship ) === 1;
		$user_access = new \OTGS\Toolset\Types\User\Access( wp_get_current_user() );

		return array(
			'jsIncludePath' => TYPES_RELPATH . '/public/page/extension/related_content',
			'typesVersion' => TYPES_VERSION,
			'itemsPerPage' => $items_per_page,
			'ajaxInfo' => array(
				'actionName' => $field_action_name,
				'nonce' => wp_create_nonce( $field_action_name ),
				'relatedPostType' => $other_post_type[0],
			),
			'relatedContent' => $related_content,
			'relationship_slug' => $relationship->get_slug(),
			'strings' => $this->build_strings( $data ),
			'postId' => $current_post_id,
			'canConnectAnother' => $can_connect_another,
			'onlyOneRelatedConection' => $only_one_related_conection,
			'hasTranslatableContent' => $relationship->is_translatable(),
			'isDefaultLanguage'=> $this->is_default_language(),
			'isWPMLActive' => $this->is_wpml_active(),
			'isIPTTranslatable' => Toolset_Wpml_Utils::is_post_type_translatable( $relationship->get_intermediary_post_type() ),
			'userId' => $user_access->getUser()->ID,
			'userCaps' => $user_access->getArrayOfCapsForPostType( reset( $other_post_type ) ),
		);
	}


	/**
	 * Value of the "items per page" setting for current page and current user.
	 *
	 * @since 2.3
	 * @return int
	 */
	private function get_items_per_page_setting() {
		$user = get_current_user_id();
		$screen = get_current_screen();

		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $option, true );
		if ( ! $per_page || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page' );
		}
		if ( ! $per_page || is_array( $per_page ) ) {
			$per_page = self::SCREEN_OPTION_PER_PAGE_DEFAULT_VALUE;
		}
		return (int) $per_page;
	}


	/**
	 * Builds strings
	 *
	 * @param array $data Contains 'args' => Toolset_Relationship_Definition.
	 * @return array Array of strings
	 * @since m2m
	 */
	private function build_strings( $data ) {
		$relationship = $data['args']['relationship'];
		$other_post_type = $this->get_other_post_type( $relationship );
		// It is an array.
		$other_post_type = $other_post_type[0];
		$post_type_object = get_post_type_object( $other_post_type );

		return array(
			'misc' => array(
				'relatedContentUpdated' => __( 'The related content has been updated successfully.', 'wpcf' ),
				'undefinedAjaxError' => __( 'There has been an error, please try again later.', 'wpcf' ),
				'disconnectRelatedContent' => __( 'Disconnect related content', 'wpcf' ),
				'deleteRelatedContent' => __( 'Trash related content', 'wpcf' ),
				// translators: Post type singular name label.
				'addNew' => sprintf( __( 'Add new %s', 'wpcf' ), $post_type_object->labels->singular_name ),
				// translators: Post type singular name label.
				'connectExisting' => sprintf( __( 'Connect existing %s', 'wpcf' ), $post_type_object->labels->singular_name ),
				'connect' => __( 'Connect', 'wpcf' ),
				'connectExistingPlaceholder' => __( 'Type the name', 'wpcf' ),
				'doYouReallyWantDisconnect' => __( 'Do you really want to disconnect this post? As a result, intermediary posts with relationships fields will be <strong>permanently deleted</strong>. Note that this involves all translations of affected posts.', 'wpcf' ),
				'doYouReallyWantTrash' => __( 'Do you really want to <strong>move the related post to Trash?</strong> All translations of that post will be moved to trash as well.', 'wpcf' ),
				'selectFieldsTitle' => __( 'Select columns to be displayed', 'wpcf' ),
			),
			'button' => array(
				'cancel' => __( 'Cancel', 'wpcf' ),
				'disconnect' => __( 'Disconnect', 'wpcf' ),
				'save' => __( 'Save', 'wpcf' ),
				'delete' => __( 'Trash', 'wpcf' ),
				'apply' => __( 'Apply', 'wpcf' ),
			),
		);
	}


	/**
	 * Gets the other post type
	 *
	 * @param Toolset_Relationship_Definition $relationship The relatioship.
	 * @return string
	 * @since m2m
	 */
	private function get_other_post_type( $relationship ) {
		$this->other_post_type = Toolset_Relationship_Role::PARENT === $this->role
				? $relationship->get_child_type()->get_types()
				: $relationship->get_parent_type()->get_types();
		return $this->other_post_type;
	}


	/**
	 * Builds the Twig context
	 *
	 * @param array $data Contains 'args' => Toolset_Relationship_Definition.
	 * @return array
	 * @since m2m
	 */
	protected function build_metabox_context( $data ) {
		// Basics for the listing page which we'll merge with specific data later on.
		$js_data = $this->build_js_data( $data );
		$base_context = $this->get_gui_base()->get_twig_context_base(
			Toolset_Gui_Base::TEMPLATE_LISTING, $js_data
		);

		$specific_context = (
			! empty( $js_data )
			? array(
					'strings' => $js_data['strings'],
					'columns' => $js_data['relatedContent']['columns'],
				)
			: array()
		);

		$context = toolset_array_merge_recursive_distinct( $base_context, $specific_context );
		$context['wrap_element_class'] = 'types-related-context-metabox-wrap';

		return $context? $context : array();
	}


	/**
	 * Returns the main Twig template
	 *
	 * @param array $data Contains 'args' => Toolset_Relationship_Definition.
	 * @return string
	 * @since m2m
	 */
	protected function get_main_twig_template( $data ) {
		if( ! $current_post_id = $this->get_current_post_id() ) {
			// no post id found, leave hint that relationships can only be added to a saved post
			return 'new_content.twig';
		}

		return $this->has_default_language_translation( $current_post_id )
			? 'main.twig'
			: 'disabled.twig';
	}

	/**
	 * Gets the role for a post type
	 *
	 * @param string                          $post_type The post type.
	 * @param Toolset_Relationship_Definition $relationship The definition.
	 * @return string
	 * @since m2m
	 */
	private function get_role( $post_type, $relationship ) {
		return in_array( $post_type, $relationship->get_parent_type()->get_types(), true )
			? Toolset_Relationship_Role::PARENT
			: Toolset_Relationship_Role::CHILD;
	}


	/**
	 * Gets the model by the role and relationship
	 *
	 * @param String                          $role Role.
	 * @param Toolset_Relationship_Definition $relationship The relationship.
	 *
	 * @return Types_Viewmodel_Related_Content
	 * @since m2m
	 */
	private function get_model_by_relationship( $role, $relationship ) {
		if ( $this->related_content_model ) {
			return $this->related_content_model;
		}
		return Types_Viewmodel_Related_Content_Factory::get_model_by_relationship( $role, $relationship );
	}

	/**
	 * Gets related content for a specific relationship
	 * TODO in a future must admin different domains.
	 *
	 * @param Toolset_Relationship_Definition $relationship The relationship.
	 * @param int                             $page_number Page number.
	 * @param int                             $items_per_page Limit.
	 * @return array Containing related content
	 */
	private function get_related_content( $relationship, $page_number = 1, $items_per_page = 0 ) {
		if ( $post_id = $this->get_current_post_id() ) {
			$this->role = $this->get_role( $this->post_type, $relationship );
			$related_content_viewmodel = $this->get_model_by_relationship( $this->role, $relationship );

			$related_content = $related_content_viewmodel->get_related_content_array( (int) $post_id, $this->post_type, $page_number, $items_per_page, $this->role );
			$related_content['items_found'] = $related_content_viewmodel->get_rows_found();
			foreach ( $related_content['data'] as $i => $item ) {
				// Formats the fields into preview and input render modes.
				$fields = $item['fields'];
				// Modify previous data.
				$related_content['data'][ $i ]['fields'] = $this->format_field_data( $fields, $item['association_uid'], $relationship );
			}
			return $related_content;
		}
		return array();
	}


	/**
	 * Formats fields data
	 * It receives unformatted fields data for post and relationship and format them into preview and HTML input elements.
	 *
	 * @param Toolset_Field_Instance[]        $fields An array of fields.
	 * @param int                             $association_uid Association UID.
	 * @param Toolset_Relationship_Definition $relationship The relationship.
	 * @return array Formatted data
	 * @since m2m
	 */
	private function format_field_data( $fields, $association_uid, $relationship ) {
		$ajax_controller = Types_Ajax::get_instance();

		$field_action_name = $ajax_controller->get_action_js_name( Types_Ajax::CALLBACK_RELATED_CONTENT_ACTION );
		$nonce = wp_create_nonce( $field_action_name );

		// Fields data is divided into preview fields and html input fields
		// HTML inputs fields are divided into post fields and relationship fields
		// because they are in different sections in the page.
		$fields_data = array(
			'association_uid' => $association_uid,
			'preview' => array(),
			'input' => array(
				'post' => array(),
				'relationship' => array(),
			),
		);

		// Post/Relationship fields.
		$fields = $this->check_field_integrity( $fields, $association_uid, $relationship );
		foreach ( array( 'post', 'relationship' ) as $field_type ) {
			$fields_input = new Types_Viewmodel_Field_Input( $fields[ $field_type ] );
			$fields_data['preview'][ $field_type ] = $fields_input->get_fields_data();
			$fields_container = new Types_Viewmodel_Fields_Edit_Container(
				$fields[ $field_type ],
				$this->get_twig(),
				array(
					'id' => 'field-input-container-' . $association_uid,
					'nonce' => $nonce,
				),
				'@' . self::ID . '/field_input.twig'
			);
			$fields_data['input'][ $field_type ] = $fields_container->to_html();
		}


		// Intermediary Title
		if( $intermediary_title_data = self::get_table_data_for_intermediary_title( $association_uid ) ) {
			$fields_data['preview']['relationship']['intermediary-title'] = $intermediary_title_data;
		}

		return $fields_data;
	}


	/**
	 * Enqueues scripts and styles
	 *
	 * @since m2m
	 */
	public function on_admin_enqueue_scripts() {

		wp_enqueue_script( Toolset_Assets_Manager::SCRIPT_WPTOOLSET_FORM_CONDITIONAL );

		$script_dependencies = array(
			'jquery',
			'backbone',
			'underscore',
			Toolset_Assets_Manager::SCRIPT_HEADJS,
			Toolset_Assets_Manager::SCRIPT_KNOCKOUT,
			Toolset_Gui_Base::SCRIPT_GUI_LISTING_PAGE_CONTROLLER,
			'toolset_select2',
			Types_Asset_Manager::SCRIPT_TINYMCE_COMPATIBILITY,
			Toolset_Assets_Manager::SCRIPT_WPTOOLSET_FORM_VALIDATION,
		);

		/* todo DELETE WITHOUT REPLACEMENT WHEN https://core.trac.wordpress.org/ticket/45289 is fixed */
		$dic = toolset_dic();
		/** @var \OTGS\Toolset\Types\Controller\Compatibility\Gutenberg $gutenberg */
		$gutenberg = $dic->make( '\OTGS\Toolset\Types\Controller\Compatibility\Gutenberg' );

		if ( $gutenberg->is_active_for_current_post_type() ) {
			array_push( $script_dependencies, 'wp-editor' );
		}
		/* END DELETE */

		wp_enqueue_script(
			self::MAIN_ASSET_HANDLE,
			TYPES_RELPATH . '/public/page/extension/related_content/main.js',
			$script_dependencies,
			TYPES_VERSION
		);

		wp_enqueue_style(
			self::MAIN_ASSET_HANDLE,
			TYPES_RELPATH . '/public/page/extension/related_content/style.css',
			array(
				Toolset_Gui_Base::STYLE_GUI_BASE,
				'toolset-select2-css',
				'toolset-notifications-css',
			),
			TYPES_VERSION
		);

		// Required by Types_Asset_Manager::SCRIPT_TINYMCE_COMPATIBILITY.
		$tinymce_helper = new Types_Helper_TinyMCE();
		$tinymce_helper->localize_dynamic_tinymce_init_script();
	}


	/**
	 * Prepares assets for all dialogs that are going to be used on the page.
	 *
	 * @param Toolset_Relationship_Definition $relationship Dialog boxes ID.
	 * @since m2m
	 */
	private function prepare_dialogs( $relationship ) {
		$twig = $this->get_twig();
		if ( null === $this->twig_dialog_factory ) {
			$this->twig_dialog_factory = new Toolset_Twig_Dialog_Box_Factory();
		}

		$this->twig_dialog_factory->get_twig_dialog_box(
			'types-disconnect-association-related-content-dialog',
			$twig,
			array(
				'strings' => array(
					'cannotBeUndone' => __( 'This cannot be undone!', 'wpcf' ),
					'doYouReallyWantDisconnect' => __( 'Do you really want to disconnect this post? As a result, the intermediary post with relationships fields will be deleted.', 'wpcf' ),
				),
			),
			'@' . self::ID . '/disconnect_dialog.twig'
		);

		$this->twig_dialog_factory->get_twig_dialog_box(
			'types-translatable-content-related-content-dialog',
			$twig,
			array(
				'strings' => array(
					'cannotBeUndone' => __( 'This cannot be undone!', 'wpcf' ),
				),
			),
			'@' . self::ID . '/translatable_content_dialog.twig'
		);

		new Types_Controller_Dialog_Box(
			'types-new-content-related-content-dialog-' . $relationship->get_slug(),
			$this,
			'render_new_relationship_dialog',
			array( $relationship )
		);

		new Types_Controller_Dialog_Box(
			'types-connect-existing-content-dialog-' . $relationship->get_slug(),
			$this,
			'render_connect_existing_dialog',
			array( $relationship )
		);

		new Types_Controller_Dialog_Box(
			'types-select-fields-related-content-dialog-' . $relationship->get_slug(),
			$this,
			'render_select_fields_dialog',
			array( $relationship )
		);

	}


	/**
	 * Renders the Add new relationship dialog
	 *
	 * @param Toolset_Relationship_Definition $relationship Dialog boxes ID.
	 * @since m2m
	 */
	public function render_new_relationship_dialog( $relationship ) {
		$fields_html = array(
			'fields' => array(),
		);

		$ajax_controller = Types_Ajax::get_instance();

		$field_action_name = $ajax_controller->get_action_js_name( Types_Ajax::CALLBACK_RELATED_CONTENT_ACTION );
		$nonce = wp_create_nonce( $field_action_name );
		$this->role = $this->get_role( $this->post_type, $relationship );

		$other_post_type = $this->get_other_post_type( $relationship );
		// It is an array.
		$other_post_type = $other_post_type[0];
		$post_type_object = get_post_type_object( $other_post_type );

		// Does the relationship has field.
		$has_relationship_fields = $relationship->has_association_field_definitions();

		// Post fields.
		$post_fields_title = $has_relationship_fields
			? sprintf( __( '%s post fields', 'wpcf' ), $post_type_object->labels->singular_name )
			: '';

		$post_fields = Toolset_Field_Utils::get_field_definitions_for_post_type( $other_post_type );

		$fields_container = new Types_Viewmodel_Fields_Edit_Container(
			$post_fields,
			$this->get_twig(),
			array(
				'id' => 'relationship-field-input-container-new',
				'nonce' => $nonce,
			),
			'@' . self::ID . '/field_input.twig'
		);
		$fields_html['fields'][] = array(
			'type' => 'post',
			'post_type_label' => $post_type_object->labels->singular_name,
			// translators: A post type.
			'title' => $post_fields_title,
			// Its neccesary to group the fields name by the type.
			// Can't be done in twig or knockout because it is a rendered dialog box.
			'rendered' => str_replace( '_wptoolset_checkbox[', '_wptoolset_checkbox[post][',
				str_replace( 'wpcf[', 'wpcf[post][', $fields_container->to_html() ) ),
		);

		// Relationship fields.
		if ( $has_relationship_fields ) {
			$relationship_fields = $relationship->get_driver()->get_field_definitions();
			$fields_container = new Types_Viewmodel_Fields_Edit_Container(
				$relationship_fields,
				$this->get_twig(),
				array(
					'id' => 'posts-field-input-container-new',
					'nonce' => $nonce,
				),
				'@' . self::ID . '/field_input.twig'
			);

			$fields_html['fields'][] = array(
				'type' => 'relationship',
				'title' => __( 'Relationship fields', 'wpcf' ),
				// Its neccesary to group the fields name by the type.
				// Can't be done in twig or knockout because it is a rendered dialog box.
				'rendered' =>  str_replace( '_wptoolset_checkbox[', '_wptoolset_checkbox[relationship][',
					str_replace( 'wpcf[', 'wpcf[relationship][', $fields_container->to_html() ) ),
			);
		}

		$fields_html['nonce'] = $nonce;
		$fields_html['wpnonce'] = $nonce;
		$fields_html['post_id'] = $this->get_current_post_id();
		$fields_html['id'] = 'types-new-content-related-content-dialog-container-' . $relationship->get_slug();

		$output = $this->get_twig()->render( '@' . self::ID . '/new_field_input.twig', $fields_html );

		return $output;
	}


	/**
	 * Renders the Connect existing relationship dialog
	 *
	 * @param Toolset_Relationship_Definition $relationship Dialog boxes ID.
	 * @since m2m
	 */
	public function render_connect_existing_dialog( $relationship ) {
		$fields_html = array(
			'fields' => array(),
		);

		$ajax_controller = Types_Ajax::get_instance();

		$field_action_name = $ajax_controller->get_action_js_name( Types_Ajax::CALLBACK_RELATED_CONTENT_ACTION );
		$nonce = wp_create_nonce( $field_action_name );

		$other_post_type = $this->get_other_post_type( $relationship );
		// It is an array.
		$other_post_type = $other_post_type[0];
		$post_type_object = get_post_type_object( $other_post_type );

		// Post.
		$fields_html['fields'][] = array(
			'type' => 'post',
			'post_type_label' => $post_type_object->labels->singular_name,
		);

		// Relationship fields.
		if( $relationship->has_association_field_definitions() ) {
			// fields available
			$fields_container = new Types_Viewmodel_Fields_Edit_Container(
				$relationship->get_driver()->get_field_definitions(),
				$this->get_twig(),
				array(
					'id' => 'posts-field-input-container-new',
					'nonce' => $nonce,
				),
				'@' . self::ID . '/field_input.twig'
			);
			$fields_html['fields'][] = array(
				'type' => 'relationship',
				'title' => __( 'Relationship fields', 'wpcf' ),
				// Its neccesary to group the fields name by the type.
				// Can't be done in twig or knockout because it is a rendered dialog box.
				'rendered' => str_replace( 'wpcf[', 'wpcf[relationship][', $fields_container->to_html() ),
			);
		}

		$fields_html['nonce'] = $nonce;
		$fields_html['wpnonce'] = $nonce;
		$fields_html['post_id'] = $this->get_current_post_id();
		$fields_html['id'] = 'types-connect-existing-content-dialog-container-' . $relationship->get_slug();

		$output = $this->get_twig()->render( '@' . self::ID . '/connect_existing_input.twig', $fields_html );

		return $output;
	}


	/**
	 * Renders the Select fields relationship dialog
	 *
	 * @param Toolset_Relationship_Definition $relationship Relationship.
	 * @since m2m
	 */
	public function render_select_fields_dialog( $relationship ) {
		$ajax_controller = Types_Ajax::get_instance();

		$field_action_name = $ajax_controller->get_action_js_name( Types_Ajax::CALLBACK_RELATED_CONTENT_ACTION );
		$nonce = wp_create_nonce( $field_action_name );

		$other_post_type = $this->get_other_post_type( $relationship );
		// It is an array.
		$other_post_type = $other_post_type[0];
		$post_type_object = get_post_type_object( $other_post_type );

		$field_definitions = array();
		$field_definitions['post'] = Toolset_Field_Utils::get_field_definitions_for_post_type( $other_post_type );

		// Relationship fields.
		if ( $relationship->has_association_field_definitions() ) {
			$field_definitions['relationship'] = $relationship->get_driver()->get_field_definitions();
		} else {
			$field_definitions['relationship'] = array();
		}

		$fields = array();
		foreach ( $field_definitions as $field_type => $fields_items ) {
			$fields[ $field_type ] = array();
			foreach ( $fields_items as $field ) {
				$fields[ $field_type ][] = array(
					'slug' => $field->get_slug(),
					'name' => $field->get_name(),
					'required' => $field->get_is_required(),
				);
			}
		}
		$fields['relatedPosts'] = $this->get_related_posts_columns( $other_post_type, $relationship );

		$fields_html = array();
		$fields_html['slug'] = $relationship->get_slug();
		$fields_html['fields'] = $fields;
		$fields_html['nonce'] = $nonce;
		$fields_html['wpnonce'] = $nonce;
		$fields_html['strings'] = array(
			// translators: Post type singular name label.
			'fieldsFromPostType' => sprintf( __( 'Fields from %s', 'wpcf' ), $post_type_object->labels->singular_name ),
		);
		$fields_html['post_id'] = $this->get_current_post_id();
		$fields_html['id'] = 'types-select-fields-related-content-dialog-container-' . $relationship->get_slug();
		$fields_html['post_type'] = $other_post_type;

		// intermediary
		if( $intermediary_post_type_string = $relationship->get_intermediary_post_type() ) {
			if( $intermediary_post_type = get_post_type_object( $intermediary_post_type_string ) ) {
				if( $intermediary_post_type->show_ui ) {
					$fields_html['intermediary'] = $intermediary_post_type->name;
				}
			}
		}

		$output = $this->get_twig()->render( '@' . self::ID . '/select_fields.twig', $fields_html );

		return $output;
	}

	/**
	 * Get table data for intermediary title
	 *
	 * @param $association_uid
	 *
	 * @return array|null
	 */
	public static function get_table_data_for_intermediary_title( $association_uid ) {
		/** Following exludes this static from unit tests
		 * @todo extract this to a proper model to resolve the static
		 */
		global $wpdb;
		if( ! is_object( $wpdb ) ) {
			return null;
		}

		$association_query = new \Toolset_Association_Query_V2();
		$association_query->do_not_add_default_conditions();
		$association_query->add( $association_query->association_id( $association_uid ) );
		$association_query->return_element_instances( new \Toolset_Relationship_Role_Intermediary() );

		/** @var \IToolset_Element $intermediary_element */
		$intermediary_elements = $association_query->get_results();

		/** @var \WP_Post|null $intermediary_post */
		$intermediary_post = ! empty( $intermediary_elements )
			? $intermediary_elements[0]->get_underlying_object()
			: null;

		if( $intermediary_post ) {
			return array(
				'name'     => $intermediary_post->post_name,
				'value'    => $intermediary_post->post_title,
				// Renders it as admin info.
				'rendered' => '<a href="' . get_edit_post_link( $intermediary_post->ID, false ) . '">' . $intermediary_post->post_title . '</a>',
			);
		}

		return null;
	}


	/**
	 * Checks if the relationships admits another association
	 *
	 * @param Toolset_Relationship_Definition $relationship The relationship definition.
	 * @param int                             $current_post_id The Post ID.
	 * @return boolean
	 * @since m2m
	 */
	private function can_connect_another( $relationship, $current_post_id ) {
		if ( isset( $this->can_connect ) ) {
			return $this->can_connect;
		}
		$potential_association_query_factory = new Toolset_Potential_Association_Query_Factory();
		$post_element = Toolset_Post::get_instance( $current_post_id );
		$target_role = Toolset_Relationship_Role::PARENT !== $this->role
			? new Toolset_Relationship_Role_Parent()
			: new Toolset_Relationship_Role_Child();
		$potential_association_query = $potential_association_query_factory->create( $relationship, $target_role, $post_element );

		$can_connect_another = $potential_association_query->can_connect_another_element();

		return $can_connect_another->is_success();
	}


	/**
	 * Gets the cardinality of the other role
	 *
	 * @param Toolset_Relationship_Definition $relationship Relationship.
	 * @param string                          $other_role Other role.
	 * @return int
	 * @since m2m
	 */
	private function get_other_cardinality( $relationship, $other_role = null ) {
		if ( ! $other_role ) {
			$other_role = Toolset_Relationship_Role::other( $this->role );
		}
		$cardinality = $relationship->get_cardinality();
		return $cardinality->get_limit( $other_role );
	}


	/**
	 * Gets the title for the meta box
	 *
	 * @param Toolset_Relationship_Definition $relationship Relationship.
	 * @return string
	 * @since m2m
	 */
	private function get_metabox_title( $relationship ) {
		$this->role = $this->get_role( $this->post_type, $relationship );
		$other_role = Toolset_Relationship_Role::other( $this->role );
		$other_cardinality = $this->get_other_cardinality( $relationship, $other_role );
		return 1 === $other_cardinality
			? $relationship->get_display_name_singular()
			: $relationship->get_display_name();
	}


	/**
	 * Returns if the post is translated in the default language
	 *
	 * @param int $post_id Post ID.
	 * @return boolean
	 * @since m2m
	 */
	private function has_default_language_translation( $post_id ) {
		if ( null !== $this->has_default_language_translation ) {
			return $this->has_default_language_translation;
		}
		$this->has_default_language_translation = Toolset_Wpml_Utils::has_default_language_translation( $post_id );
		return $this->has_default_language_translation;
	}

	/**
	 * Returns if the call is in default language
	 *
	 * @return boolean
	 * @since m2m
	 */
	private function is_default_language() {
		return Toolset_Wpml_Utils::get_current_language() === Toolset_Wpml_Utils::get_default_language();
	}


	/**
	 * Returns is WPML is active
	 *
	 * @return boolean
	 */
	private function is_wpml_active() {
		$wpml_compatibility = Toolset_WPML_Compatibility::get_instance();
		return $wpml_compatibility->is_wpml_active_and_configured();
	}


	/**
	 * If intermediary post has been deleted, relationship fields need to be fixed
	 *
	 * @param Toolset_Field_Instance[]        $fields An array of fields.
	 * @param int                             $association_uid Association ID.
	 * @param Toolset_Relationship_Definition $relationship Relationship.
	 * @return Toolset_Field_Instance[]
	 * @since m2m
	 */
	private function check_field_integrity( $fields, $association_uid, $relationship ) {
		if ( empty( $fields['relationship'] ) && $relationship->has_association_field_definitions() ) {
			$association = $this->get_association( $association_uid );

			$association_intermediary_post_persistence = new Toolset_Association_Intermediary_Post_Persistence( $relationship );
			$association_intermediary_post_persistence->create_empty_association_intermediary_post( $association );

			// Refresh data.
			$association = $this->get_association( $association_uid );

			$fields['relationship'] = $association->get_fields();
		}
		return $fields;
	}


	/**
	 * Retrieves an association
	 *
	 * @param int $association_uid Association UID.
	 * @return IToolset_Association
	 * @since m2m
	 */
	private function get_association( $association_uid ) {
		$association_query = new Toolset_Association_Query_V2();
		$association_query->add( $association_query->association_id( $association_uid ) );
		$associations = $association_query->get_results();
		if ( $associations ) {
			return $associations[0];
		}
		return null;
	}


	/**
	 * Returns the list of related posts $columns
	 *
	 * @param strings                         $post_type Post type slug.
	 * @param Toolset_Relationship_Definition $excluded_relationship Relationship to be excluded.
	 * @return array
	 * @since m2m
	 */
	public function get_related_posts_columns( $post_type, $excluded_relationship ) {
		$columns = array();
		$relationship_query = new Toolset_Relationship_Query_V2();
		$cardinality = $relationship_query->cardinality();
		$relationship_query->add( $relationship_query->has_domain_and_type( $post_type, Toolset_Element_Domain::POSTS, new Toolset_Relationship_Role_Child() ) )
			->add( $relationship_query->exclude_relationship( $excluded_relationship ) )
			->add( $relationship_query->do_or(
				$relationship_query->has_cardinality( $cardinality->one_to_many() ),
				$relationship_query->has_cardinality( $cardinality->one_to_one() )
			) )
			->add( $relationship_query->exclude_type( $this->post_type ) );
		// Used to avoid post types duplications.
		$used_post_types = array();
		foreach ( $relationship_query->get_results() as $relationship ) {
			$parent_types = $relationship->get_element_type( new Toolset_Relationship_Role_Parent() )->get_types();
			foreach ( $parent_types as $parent_type ) {
				if ( in_array( $parent_type, $used_post_types, true ) ) {
					continue;
				}
				$post_type_object = get_post_type_object( $parent_type );
				$columns[] = array(
					'slug' => $parent_type,
					'name' => $post_type_object->labels->singular_name,
					'required' => false,
				);
				$used_post_types[] = $parent_type;
			}
		}
		return $columns;
	}

}
