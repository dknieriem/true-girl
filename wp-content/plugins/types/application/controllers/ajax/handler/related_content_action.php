<?php

use OTGS\Toolset\Types\Page\Extension\RelatedContent\DirectEditStatusFactory;

/**
 * Handle action with related content in edit posts page.
 *
 * @since 2.3
 * @refactoring Split this into smaller classes, one per action.
 */
class Types_Ajax_Handler_Related_Content_Action extends Toolset_Ajax_Handler_Abstract {


	const ACTION_INSERT = 'insert';
	const ACTION_CONNECT = 'connect';
	const ACTION_UPDATE = 'update';
	const ACTION_ENABLE_FIELDS = 'enable_fields';
	const ACTION_DISCONNECT = 'disconnect';
	const ACTION_DELETE = 'delete';
	const ACTION_SEARCH_RELATED_CONTENT = 'search_related_content';
	const ACTION_LOAD = 'load';
	const ACTION_GET_TRANSLATABLE_CONTENT = 'get_translatable_content';
	const ACTION_UPDATE_FIELDS_DISPLAYED = 'update_fields_displayed';


	const SEARCH_RESULTS_PER_PAGE = 25;


	/**
	 * Unsafe actions list
	 *
	 * @var array
	 */
	private $unsafe_actions = array(
		self::ACTION_CONNECT,
		self::ACTION_DISCONNECT,
		self::ACTION_SEARCH_RELATED_CONTENT,
		self::ACTION_LOAD,
	);


	/**
	 * Twig class
	 *
	 * @var Twig_Environment Twig Enviroment.
	 * @since m2m
	 */
	private $twig;


	/** @var null|Toolset_Gui_Base */
	private $_gui_base;


	/**
	 * Definition repository injection for testing purposes
	 *
	 * @var Toolset_Relationship_Definition_Repository Definition repository.
	 * @since m2m
	 */
	private $_definition_repository;


	/**
	 * Related Content Viewmodel injection for testing purposes
	 *
	 * @var Types_Viewmodel_Related_Content Related Content Viewmodel.
	 * @since m2m
	 */
	private $related_content_viewmodel;


	/**
	 * Fields Edit Container injection for testing purposes
	 *
	 * @var Types_Viewmodel_Fields_Edit_Container Fields Edit Container Viewmodel.
	 * @since m2m
	 */
	private $fields_edit_container;


	/**
	 * Post type repository
	 *
	 * @var Toolset_Post_Type_Repository
	 * @since m2m
	 */
	private $post_type_repository;

	/**
	 * Association
	 *
	 * @var IToolset_Association
	 * @since m2m
	 */
	private $association;


	/**
	 * Relationship definition
	 *
	 * @var Toolset_Relationship_Definition
	 * @since m2m
	 */
	private $definition;


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
	 * Association query
	 *
	 * @var Toolset_Association_Query_V2
	 * @since m2m
	 */
	private $_association_query;


	/** @var Toolset_Element_Factory|null */
	private $_element_factory;


	/** @var DirectEditStatusFactory */
	private $direct_edit_status_factory;


	/** @var Toolset_WPML_Compatibility */
	private $wpml_service;


	/**
	 * Constructor
	 *
	 * @param Types_Ajax $ajax_manager Ajax manager.
	 * @param Toolset_Gui_Base $gui_base_di Test injection purposes.
	 * @param Toolset_Relationship_Definition_Repository $definition_repository Test injection purposes.
	 * @param Types_Viewmodel_Related_Content $related_content_viewmodel Test injection purposes.
	 * @param Types_Viewmodel_Fields_Edit_Container $fields_edit_container Test injection purposes.
	 * @param Toolset_Association_Query_V2 $association_query_di Test injection purposes.
	 * @param boolean $can_connect_di Test injection purposes.
	 * @param Toolset_Post_Type_Repository|null $post_type_repository_di
	 * @param Toolset_Element_Factory|null $element_factory_di
	 * @param DirectEditStatusFactory|null $direct_edit_status_factory
	 *
	 * @param Toolset_WPML_Compatibility|null $wpml_service
	 *
	 * @since m2m
	 */
	public function __construct(
		Types_Ajax $ajax_manager,
		Toolset_Gui_Base $gui_base_di = null,
		Toolset_Relationship_Definition_Repository $definition_repository = null,
		Types_Viewmodel_Related_Content $related_content_viewmodel = null,
		Types_Viewmodel_Fields_Edit_Container $fields_edit_container = null,
		Toolset_Association_Query_V2 $association_query_di = null,
		$can_connect_di = null,
		Toolset_Post_Type_Repository $post_type_repository_di = null,
		Toolset_Element_Factory $element_factory_di = null,
		DirectEditStatusFactory $direct_edit_status_factory = null,
		Toolset_WPML_Compatibility $wpml_service = null
	) {
		parent::__construct( $ajax_manager );
		$this->_gui_base = $gui_base_di;
		$this->_definition_repository = $definition_repository;
		$this->related_content_viewmodel = $related_content_viewmodel;
		$this->fields_edit_container = $fields_edit_container;
		$this->can_connect = $can_connect_di;
		$this->_association_query = $association_query_di;
		$this->post_type_repository = $post_type_repository_di;
		$this->_element_factory = $element_factory_di;
		$this->direct_edit_status_factory = $direct_edit_status_factory ?: new DirectEditStatusFactory();
		$this->wpml_service = $wpml_service ?: Toolset_WPML_Compatibility::get_instance();
	}


	/**
	 * Returns GUI Base
	 *
	 * @return Toolset_Gui_Base
	 * @since m2m
	 */
	protected function get_gui_base() {

		if ( null === $this->_gui_base ) {
			$toolset_common_bootstrap = Toolset_Common_Bootstrap::get_instance();
			$toolset_common_bootstrap->register_gui_base();
			$this->_gui_base = Toolset_Gui_Base::get_instance();
			$this->_gui_base->init();
		}

		return $this->_gui_base;
	}


	/**
	 * Returns the definition repository
	 *
	 * @return Toolset_Relationship_Definition_Repository
	 * @since m2m
	 */
	private function get_definition_repository() {
		if ( null === $this->_definition_repository ) {
			$this->_definition_repository = Toolset_Relationship_Definition_Repository::get_instance();
		}

		return $this->_definition_repository;
	}


	/**
	 * Returns the related content viewmodel
	 *
	 * It is used for testing purposes, if is set during class instance, it will return, other case it will be generated.
	 *
	 * @param string                          $role Relationship element role.
	 * @param Toolset_Relationship_Definition $definition The relationship.
	 *
	 * @return Types_Viewmodel_Related_Content
	 * @since m2m
	 */
	private function get_related_content_viewmodel( $role, $definition ) {
		if ( $this->related_content_viewmodel ) {
			return $this->related_content_viewmodel;
		} else {
			return Types_Viewmodel_Related_Content_Factory::get_model_by_relationship( $role, $definition );
		}
	}


	/**
	 * Returns the fields edit container viewmodel
	 *
	 * @param Toolset_Field_Definition[]  $fields Array of fields.
	 * @param Twig_Environment            $twig Twig environment.
	 * @param array                      $context Initial Twig context.
	 * @param string                      $template Template path.
	 * @param Types_Viewmodel_Field_Input $viewmodel Viewmodel for getting formatted data.
	 *
	 * @return Types_Viewmodel_Fields_Edit_Container
	 */
	private function get_fields_edit_container( $fields, $twig, $context, $template, $viewmodel = null ) {
		if ( ! $this->fields_edit_container ) {
			return new Types_Viewmodel_Fields_Edit_Container( $fields, $twig, $context, $template, $viewmodel );
		} else {
			return $this->fields_edit_container;
		}
	}

	/**
	 * Retrieve a Twig environment initialized by the Toolset GUI base.
	 *
	 * @return Twig_Environment
	 * @since m2m
	 * @throws Twig_Error_Loader In case of error.
	 */
	protected function get_twig() {
		if ( null === $this->twig ) {

			$gui_base = $this->get_gui_base();
			$twig_templates_path = array(
				'related-content' => TYPES_ABSPATH . '/application/views/page/extension/related_content',
			);
			$this->twig = $gui_base->create_twig_environment( $twig_templates_path );
		}

		return $this->twig;
	}


	/**
	 * Process the Ajax call
	 *
	 * @param array $arguments List of POST arguments.
	 *
	 * @since m2m
	 * @throws Twig_Error_Loader
	 * @throws Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function process_call( $arguments ) {
		do_action( 'toolset_do_m2m_full_init' );

		$action = toolset_getpost( 'related_content_action' );

		$this->get_ajax_manager()->ajax_begin(
			array(
				'nonce' => $this->get_ajax_manager()->get_action_js_name( Types_Ajax::CALLBACK_RELATED_CONTENT_ACTION ),
				'capability_needed' => $this->is_safe_action( $action ) ? 'read' : 'edit_posts',
				'is_public' => toolset_getarr( $_REQUEST, 'skip_capability_check', false )
			)
		);

		$this->maybe_set_current_language();

		switch ( $action ) {
			case self::ACTION_UPDATE:
				$this->update();
				break;
			case self::ACTION_INSERT:
				$this->insert_connect( self::ACTION_INSERT );
				break;
			case self::ACTION_ENABLE_FIELDS:
				$this->enable_editing_fields();
				break;
			case self::ACTION_DISCONNECT:
				$this->disconnect_association();
				break;
			case self::ACTION_DELETE:
				$this->trash_related_posts();
				break;
			case self::ACTION_SEARCH_RELATED_CONTENT:
				$this->search_related_content();
				break;
			case self::ACTION_CONNECT:
				$this->insert_connect( self::ACTION_CONNECT );
				break;
			case self::ACTION_LOAD:
				$this->load_related_content();
				break;
			case self::ACTION_GET_TRANSLATABLE_CONTENT:
				$this->get_translatable_content();
				break;
			case self::ACTION_UPDATE_FIELDS_DISPLAYED:
				$this->update_fields_displayed();
				break;
			default:
				$this->fail( __( 'Something was wrong, please try again.', 'wpcf' ) );
		}
	}


	/**
	 * If WPML is active, we will tell it what is the current language - it cannot determine
	 * it on its own in an AJAX call.
	 *
	 * This is especially important when querying items to connect the current post with.
	 *
	 * @since m2m
	 */
	private function maybe_set_current_language() {
		$lang_code = toolset_getpost( 'current_language', '' );
		if ( ! empty( $lang_code ) ) {
			do_action( 'wpml_switch_language', $lang_code );
		}
	}


	/**
	 * Updates related content fields, both post and relationship fields
	 *
	 * @since m2m
	 * @throws Twig_Error_Loader
	 */
	private function update() {
		$association = $this->get_association();
		if ( ! $association ) {
			$this->fail( __( 'Related content not found.', 'wpcf' ) );
		}

		$role = toolset_getpost( 'role' );
		if ( ! in_array( $role, Toolset_Relationship_Role::parent_child_role_names(), true ) ) {
			$this->fail( __( 'Wrong related content role.', 'wpcf' ) );
		}

		$fields = toolset_getpost( 'wpcf' );

		if ( ! is_array( $fields ) || empty( $fields ) || ( empty( $fields['post'] ) && empty( $fields['relationship'] ) ) ) {
			$this->fail( __( 'The related content has not been updated, as no fields were modified.', 'wpcf' ), 'warning' );
		}

		// Post fields.
		$related_post_id = toolset_getpost( 'post_id' );
		if ( isset( $fields['post'] ) ) {
			$this->update_post_fields( $related_post_id, $association, $fields['post'], $role );
		}
		// Relationship fields.
		if ( isset( $fields['relationship'] ) ) {
			$this->update_relationship_fields( $association, $fields['relationship'] );
		}

		// Handle unchecked checkboxes
		$this->update_unchecked_checkboxes( $fields, $association, $related_post_id );

		$other_role = Toolset_Relationship_Role::other( $role );
		/** @var Types_Viewmodel_Related_Content_Post $related_content_viewmodel (true for now) */
		$related_content_viewmodel = $this->get_related_content_viewmodel( $other_role, $association->get_definition() );
		$results = $related_content_viewmodel->get_related_content_from_uid_array( $association->get_uid() );

		if ( isset( $results[0]['fields'] ) && is_array( $results[0]['fields'] ) ) {
			$results[0]['fields'] = $this->format_field_data( $results[0]['fields'], $association->get_uid() );
		}

		if( $role == Toolset_Relationship_Role::CHILD ) {
			// legacy action 'wpcf_relationship_save_child'
			$child_post = get_post( $association->get_element_id( new Toolset_Relationship_Role_Child() ) );
			$parent_post = get_post( $association->get_element_id( new Toolset_Relationship_Role_Parent() ) );

			do_action( 'wpcf_relationship_save_child', $child_post, $parent_post );
		}


		/*
		 * Action 'toolset_post_update'
		 *
		 * @var WP_Post $affected_post
		 *
		 * @since 3.0
		 */
		$affected_post = $association->get_element( $role )->get_underlying_object();
		do_action( 'toolset_post_update', $affected_post );

		$data = array(
			'results' => $results,
		);
		$this->get_ajax_manager()->ajax_finish( $data, true );

	}


	/**
	 * Gets the role of the relationship from the post type
	 *
	 * @param IToolset_Relationship_Definition $definition Relationship definition.
	 * @param string                           $post_type The post type.
	 *
	 * @return string
	 * @throws InvalidArgumentException In case of error.
	 * @since m2m
	 */
	private function get_role_from_post_type( $definition, $post_type ) {
		$parent_type = $definition->get_element_type( Toolset_Relationship_Role::PARENT )->get_types();
		$child_type = $definition->get_element_type( Toolset_Relationship_Role::CHILD )->get_types();
		if ( $post_type === $parent_type[0] ) {
			return Toolset_Relationship_Role::PARENT;
		} elseif ( $post_type === $child_type[0] ) {
			return Toolset_Relationship_Role::CHILD;
		} else {
			throw new InvalidArgumentException( __( 'The post type doesn\'t belong to the relationship', 'wpcf' ) );
		}
	}


	/**
	 * Insert/connect related content fields, both post and relationship fields
	 * 1- Gets and check the relationship
	 * 2- Retreviews the roles (parent/child) for association create_association
	 * 3- If the related post doesn't exist, then create it
	 * 4- If it exists, check if it is not related to the same post if it is not a many to many relationship
	 * 5- Create the association
	 * 6- Update the fields (post and/or relationship)
	 *
	 * @param boolean $action It insert is needed.
	 *
	 * @since m2m
	 * @throws Twig_Error_Loader
	 * @throws Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	private function insert_connect( $action = true ) {
		$current_post_id = toolset_getpost( 'post_id' );
		$post = get_post( $current_post_id );
		if ( ! $post ) {
			$this->fail( __( 'Post not found.', 'wpcf' ) );
		}

		$definition = $this->get_definition();

		if ( ! $definition ) {
			$this->fail( __( 'Relationship not found.', 'wpcf' ) );
		}

		// The role belonging to the current post.
		$role = $this->get_role_from_post_type( $definition, $post->post_type );
		// The role beloinging to the related post.
		$other_role = Toolset_Relationship_Role::other( $role );
		$other_element_type = $definition->get_element_type( $other_role );
		$other_types = $other_element_type->get_types();

		$this->check_can_be_used( $other_types );

		$fields = toolset_getpost( 'wpcf' );

		if ( self::ACTION_INSERT === $action ) {
			$related_post = $this->create_post( $fields, $other_types[0] );
		} else {
			$related_post = $this->connect_post( $fields, $other_types[0] );
		}

		// Once the related post exists (inserted o retreviewed), it is neccesary to create the intermediary post.
		list( $parent_post, $child_post ) = Toolset_Relationship_Role::sort_elements( $post, $related_post, $role );
		$association = $definition->create_association( $parent_post, $child_post );

		if ( $association instanceof Toolset_Result && $association->is_error() ) {
			$this->fail(
				sprintf(
					__( 'There was a problem creating the related content: %s', 'wpcf' ),
					$association->get_message()
				)
			);
		}

		if ( ! $association instanceof IToolset_Association ) {
			// This should never happen.
			$this->fail( __( 'There was a problem creating the related content.', 'wpcf' ) );
		}

		if ( ! is_array( $fields ) || empty( $fields ) || ( empty( $fields['post'] ) && empty( $fields['relationship'] ) ) ) {
			$this->fail( __( 'No fields sent.', 'wpcf' ) );
		}

		// Post fields.
		if ( self::ACTION_INSERT === $action && isset( $fields['post'] ) ) {
			$this->update_post_fields( $related_post->ID, $association, $fields['post'], $other_role );
		}
		// Relationship fields.
		if ( isset( $fields['relationship'] ) ) {
			$this->update_relationship_fields( $association, $fields['relationship'] );
		}

		// Handle unchecked checkboxes
		$this->update_unchecked_checkboxes( $fields, $association, $related_post->ID );

		/** @var Types_Viewmodel_Related_Content_Post $related_content_viewmodel (true for now)*/
		$related_content_viewmodel = $this->get_related_content_viewmodel( $role, $association->get_definition() );
		$results = $related_content_viewmodel->get_related_content_from_uid_array( $association->get_uid() );

		if ( isset( $results[0]['fields'] ) && is_array( $results[0]['fields'] ) ) {
			$results[0]['fields'] = $this->format_field_data( $results[0]['fields'], $association->get_uid() );
		}

		$current_post_type_object = get_post_type_object( $post->post_type );
		$related_post_type_object = get_post_type_object( $other_types[0] );
		$message = self::ACTION_INSERT === $action
			// translators: both are post types.
			? sprintf( __( 'A new <strong>%1$s</strong> has been connected to this <strong>%2$s</strong>', 'wpcf' ), $related_post_type_object->labels->singular_name, $current_post_type_object->labels->singular_name )
			: sprintf( __( 'An existing <strong>%1$s</strong> has been connected to this <strong>%2$s</strong>', 'wpcf' ), $related_post_type_object->labels->singular_name, $current_post_type_object->labels->singular_name );

		$can_connect_another = $this->can_connect_another();

		$data = array(
			'results' => $results,
			'message' => $message,
			'canConnectAnother' => $can_connect_another,
		);
		$this->get_ajax_manager()->ajax_finish( $data, true );

	}


	/**
	 * Connect a post
	 *
	 * @param Toolset_Field_Instance[] $fields List of fields.
	 * @param String                   $post_type Actual Post type.
	 *
	 * @return WP_Post
	 *
	 * @since m2m
	 */
	private function connect_post( $fields, $post_type ) {
		if ( empty( $fields['post']['post-id'] ) ) {
			$this->fail( __( 'Related content not found.', 'wpcf' ) );
		}
		$related_post = get_post( $fields['post']['post-id'] );

		if ( ! $related_post ) {
			$this->fail( __( 'Related content not found.', 'wpcf' ) );
		}
		if ( ! Toolset_Wpml_Utils::has_default_language_translation( $related_post->ID ) ) {
			$this->fail( __( 'Related content has to be translated to default language.', 'wpcf' ) );
		}
		if ( $related_post->post_type !== $post_type ) {
			$this->fail( __( 'Wrong related content', 'wpcf' ) );
		}

		return $related_post;
	}


	/**
	 * Create a new post
	 *
	 * @param Toolset_Field_Instance[] $fields List of fields.
	 * @param String                   $post_type Actual Post type.
	 *
	 * @return WP_Post
	 * @since m2m
	 */
	private function create_post( $fields, $post_type ) {
		// Post creation must be done in default language.
		$current_language = Toolset_WPML_Compatibility::get_instance()->get_current_language();
		do_action( 'wpml_switch_language', Toolset_WPML_Compatibility::get_instance()->get_default_language() );

		// Insert
		// Creating new related post type.
		if ( empty( $fields['post']['post-title'] ) ) {
			$this->fail( __( 'Empty post title.', 'wpcf' ) );
		}
		$related_post_id = wp_insert_post(
			array(
				'post_type' => $post_type,
				'post_title' => sanitize_text_field( $fields['post']['post-title'] ),
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $related_post_id ) ) {
			$post_type_object = get_post_type_object( $post_type );
			$this->fail(
				sprintf(
					// translators: Post type singular name.
					__( 'Something was wrong while creating a new %s.', 'wpcf' ),
					$post_type_object->labels->singular_name
				)
			);
		}
		$related_post = get_post( $related_post_id );

		// Switch back to current language.
		do_action( 'wpml_switch_language', $current_language );

		/*
		 * Action 'toolset_post_update'
		 *
		 * @var WP_Post $related_post
		 *
		 * @since 3.0
		 */
		do_action( 'toolset_post_update', $related_post );

		return $related_post;
	}

	/**
	 * Formats fields data
	 * It receives unformatted fields data for post and relationship and format them into preview and HTML input
	 * elements.
	 *
	 * @param Toolset_Field_Instance[] $fields An array of fields.
	 * @param int $association_uid Association UID.
	 *
	 * @return array Formatted data
	 * @since m2m
	 * @throws Twig_Error_Loader
	 */
	private function format_field_data( $fields, $association_uid ) {
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

		// Post fields.
		$fields_input = new Types_Viewmodel_Field_Input( $fields['post'] );
		$fields_data['preview']['post'] = $fields_input->get_fields_data();

		// Relationship fields.
		$fields_input = new Types_Viewmodel_Field_Input( $fields['relationship'] );
		$fields_data['preview']['relationship'] = $fields_input->get_fields_data();

		foreach ( array( 'post', 'relationship' ) as $field_type ) {
			$fields_container = $this->get_fields_edit_container(
				$fields[ $field_type ],
				$this->get_twig(),
				array(
					'id' => 'field-input-container-' . $association_uid,
					'nonce' => $nonce,
				),
				'@' . Types_Page_Extension_Meta_Box_Related_Content::ID . '/field_input.twig'
			);
			$fields_data['input'][ $field_type ] = $fields_container->to_html();
		}

		// WYSIWYG extra data. mceInit is needed to set for new tinymce initialization.
		$all_fields = array();
		if ( is_array( $fields['post'] ) ) {
			$all_fields = array_merge( $all_fields, $fields['post'] );
		}
		if ( is_array( $fields['relationship'] ) ) {
			$all_fields = array_merge( $all_fields, $fields['relationship'] );
		}
		$tinymce_helper = new Types_Helper_TinyMCE();
		$fields_data['extra'] = array(
			'wysiwyg' => $tinymce_helper->generate_mceinit_data(
				$all_fields,
				$fields_data['input']['post'] . $fields_data['input']['relationship']
			),
		);

		// Intermediary Title
		if( $intermediary_title_data = \Types_Page_Extension_Meta_Box_Related_Content::get_table_data_for_intermediary_title( $association_uid ) ) {
			$fields_data['preview']['relationship']['intermediary-title'] = $intermediary_title_data;
		}

		return $fields_data;
	}


	/**
	 * Updates post fields and post title
	 *
	 * @param integer              $post_id The post ID to be updated.
	 * @param IToolset_Association $association The related content association.
	 * @param array                $fields An array of fields pairs: slug => value.
	 * @param string               $role Parent or child.
	 *
	 * @since m2m
	 */
	private function update_post_fields( $post_id, $association, $fields, $role ) {
		// I don't know why the fields content is scaped.
		foreach ( $fields as $i => $field ) {
			if ( ! is_array( $field ) ) {
				$fields[ $i ] = stripslashes( $field );
			}
		}

		$post_id = (int) $post_id;
		$post = $association->get_element( Toolset_Relationship_Role::role_from_name( $role ) );

		if ( $post->get_id() !== $post_id ) {
			$this->fail( __( 'Wrong related post.', 'wpcf' ) );
		}
		// Post title.
		$title = isset( $fields['post-title'] ) ? sanitize_text_field( $fields['post-title'] ) : false;
		if ( ! $title ) {
			$this->fail( __( 'Invalid related post title.', 'wpcf' ) );
		}

		// Toolset_Post doesn't have a getter for the post object (WP_Post).
		$wp_post = get_post( $post_id );
		$wp_post->post_title = $title;
		wp_update_post( $wp_post );
		// Removing title in order to avoid filter error.
		unset( $fields['post-title'] );

		$this->update_fields( $fields, $post->get_id() );
	}


	/**
	 * Updates relationship fields
	 *
	 * @param IToolset_Association $association The related content association.
	 * @param array                $fields An array of fields pairs: slug => value.
	 *
	 * @since m2m
	 */
	private function update_relationship_fields( $association, $fields ) {
		$this->update_fields( $fields, $association->get_intermediary_id() );
	}

	/**
	 * Handle unchecked checkboxes
	 *
	 * @param $fields
	 * @param IToolset_Association $association
	 * @param $related_post_id
	 *
	 * @since
	 */
	private function update_unchecked_checkboxes( $fields, IToolset_Association $association, $related_post_id) {
		$hidden_inputs_for_empty_checkboxes = toolset_getpost( '_wptoolset_checkbox', array() );

		// hidden checkboxes inputs for related post (to unchecked / 0)
		$hidden_inputs_for_empty_checkboxes_post = isset( $hidden_inputs_for_empty_checkboxes['post'] )
			? $hidden_inputs_for_empty_checkboxes['post']
			: array();

		foreach ( array_keys( $hidden_inputs_for_empty_checkboxes_post ) as $slug ) {
			wpcf_fields_checkbox_update_one( $related_post_id, $slug, $fields['post'] );
		}

		// hidden checkboxes inputs for intermediary post (to unchecked / 0)
		$hidden_inputs_for_empty_checkboxes_relationship = isset( $hidden_inputs_for_empty_checkboxes['relationship'] )
			? $hidden_inputs_for_empty_checkboxes['relationship']
			: array();

		foreach ( array_keys( $hidden_inputs_for_empty_checkboxes_relationship ) as $slug ) {
			wpcf_fields_checkbox_update_one( $association->get_intermediary_id(), $slug, $fields['relationship'] );
		}
	}


	/**
	 * Updates fields values
	 *
	 * @param array $fields Array of fields pairs: slug => value.
	 * @param int   $post_id The fields' post ID.
	 *
	 * @since m2m
	 */
	private function update_fields( $fields, $post_id ) {
		foreach ( $fields as $slug => $field_value ) {
			if ( types_is_repetitive( $slug ) ) {
				$my_field = new WPCF_Repeater();
			} else {
				$my_field = new WPCF_Field();
			}
			$my_field->set( $post_id, wpcf_admin_fields_get_field( $slug ) );
			$my_field->save( $field_value );
		}
	}


	/**
	 * Updates the flag for editing post fields
	 * It uses Transient API for store it.
	 *
	 * @since m2m
	 */
	private function enable_editing_fields() {
		$association_uid = toolset_getpost( 'association_uid' );
		$association_is_enabled = $this->direct_edit_status_factory->create( $association_uid, null );
		$association_is_enabled->set( true );
		$this->get_ajax_manager()->ajax_finish(
			array(
				'message' => 'OK',
			), true
		);
	}


	/**
	 * Do disconnect association
	 *
	 * @return Toolset_Result
	 * @since m2m
	 */
	private function do_disconnect_association() {
		$association = $this->get_association();

		if ( ! $association ) {
			$this->fail( __( 'The association you are trying to disconnect doesn\'t exist, perhaps it has been disconnected before. Please, refresh the page and try again.', 'wpcf' ) );
		}
		$definition = $association->get_definition();
		$driver = $definition->get_driver();
		$result = $driver->delete_association( $association );

		return $result;
	}


	/**
	 * Disconnect association
	 * It uses the driver to remove the association and intermediary post if exists.
	 *
	 * @since m2m
	 */
	private function disconnect_association() {
		$result = $this->do_disconnect_association();

		$can_connect_another = $this->can_connect_another();

		if ( $result->is_success() ) {
			$this->get_ajax_manager()->ajax_finish(
				array(
					'message' => __( 'Association disconnected successfully.', 'wpcf' ),
					'canConnectAnother' => $can_connect_another,
				), true
			);
		} else {
			$this->fail( __( 'Something was wrong, please try again.', 'wpcf' ) );
		}
	}


	/**
	 * Trash the related post and its translations.
	 *
	 * @since m2m
	 */
	private function trash_related_posts() {
		$post_id = toolset_getpost( 'post_id' );
		if ( ! $post_id ) {
			$this->fail( __( 'Post not found.', 'wpcf' ) );
		}

		$post_translations = $this->wpml_service->get_post_translations_directly( $post_id );
		// If $post_translations is not empty, it will also include $post_id.
		foreach ( $post_translations as $post_translation_id ) {
			wp_trash_post( $post_translation_id );
		}
		if ( empty( $post_translations ) ) {
			// If there is no WPML or the post type is not translatable, we need to delete at least the
			// one given post.
			wp_trash_post( $post_id );
		}

		$this->get_ajax_manager()->ajax_finish(
			array(
				'message' => __( 'Related post moved to Trash successfully.', 'wpcf' ),
				'canConnectAnother' => $this->can_connect_another(),
			),
			true
		);
	}


	/**
	 * Get a list of related post types.
	 *
	 * It is used when the user wants to connect a existing post and search for an specific post using a select2 combo
	 *
	 * @since m2m
	 */
	private function search_related_content() {
		$relationship_definition = $this->get_definition();
		$post_type = toolset_getpost( 'post_type' );

		if ( ! $relationship_definition instanceof IToolset_Relationship_Definition ) {
			$this->fail( __( 'Something went wrong when fetching the related content: Couldn\'t load the relationship.', 'wpcf' ) );
		}

		// For now, the assumption about the domain and number of types is safe.
		$parent_post_types = $relationship_definition->get_element_type( Toolset_Relationship_Role::PARENT )->get_types();
		$parent_post_type = array_pop( $parent_post_types );

		$target_role = (
		$post_type === $parent_post_type
			? new Toolset_Relationship_Role_Parent()
			: new Toolset_Relationship_Role_Child()
		);

		try {
			$current_post = $this->get_element_factory()->get_post(
				(int) toolset_getpost( 'current_post_id' )
			);
		} catch ( Toolset_Element_Exception_Element_Doesnt_Exist $e ) {
			$this->get_ajax_manager()->ajax_finish( array( 'items' => array(), ), false );
			return;
		}

		$requested_page = (int) toolset_getpost( 'page', 1 );

		$query_args_builder = new Toolset_Potential_Association_Query_Arguments();
		$query_args_builder
			->addFilter( new Toolset_Potential_Association_Query_Filter_Search_String() )
			->addFilter(
				new Types_Potential_Association_Query_Filter_Posts_Author_For_New_Association( $relationship_definition, $target_role )
			)->addFilter(
				new Types_Potential_Association_Query_Filter_Posts_Status( $relationship_definition, $target_role )
			);

		$query_args = $query_args_builder->get();
		$query_args['page'] = $requested_page;
		$query_args['items_per_page'] = self::SEARCH_RESULTS_PER_PAGE;
		$query_args['count_results'] = true;

		// @refactoring, use dependency injection
		$query_factory = new Toolset_Potential_Association_Query_Factory();
		$query = $query_factory->create(
			$relationship_definition, $target_role, $current_post, $query_args
		);

		$posts = $query->get_results();

		$user = wp_get_current_user();
		$user_access = new \OTGS\Toolset\Types\User\Access( $user );
		$user_can_edit_any = $user_access->canEditAny( $post_type );
		$user_can_edit_own = $user_access->canEditOwn( $post_type );

		$formatted_posts = array();
		foreach ( $posts as $post ) {
			if( ! $user_can_edit_any
			    && (
			    	! $user_can_edit_own
				    || ( $user_can_edit_own && $post->get_underlying_object()->post_author != $user->ID )
			    )
			) {
				// user is not allowed to edit this post
				continue;
			}
			$wpml_compatibility = Toolset_WPML_Compatibility::get_instance();
			if ( $wpml_compatibility->is_wpml_active_and_configured() ) {
				$disabled = ! Toolset_Wpml_Utils::has_default_language_translation( $post->get_id() );
				$default_lang = $wpml_compatibility->get_default_language();
				$translation_link = apply_filters( 'wpml_get_link_to_edit_translation', '', $post->get_id(), $default_lang );
				$translation_link = $disabled
					? '<span class="types-select2-disabled-link"><a href="' . $translation_link . '" target="_blank">' . _x( 'Translate', 'related_content', 'wpcf' ) . '</a></span> <span class="types-select2-disabled-tooltip">' . esc_html__( 'Needs to be translated to default language', 'wpcf' ) . '</span>'
					: '';
			} else {
				$disabled = false;
				$translation_link = '';
			}

			$formatted_posts[] = array(
				'id' => $post->get_id(),
				'text' => $post->get_title() . $translation_link,
				'disabled' => $disabled,
			);
		}

		$total_returned_items = self::SEARCH_RESULTS_PER_PAGE * $requested_page;
		$has_more_items = ( $query->get_found_elements() > $total_returned_items );

		$this->get_ajax_manager()->ajax_finish(
			array(
				'items' => $formatted_posts,
				'pagination' => array(
					'more' => $has_more_items,
				),
			),
			true
		);
	}


	/**
	 * Loads related content depending of the relationship definition and a page number or search string
	 *
	 * @since m2m
	 */
	private function load_related_content() {

		$relationship = $this->get_definition();
		if ( ! $relationship ) {
			$this->fail( __( 'Relationship not found.', 'wpcf' ) );
		}

		$related_post_type = toolset_getpost( 'related_post_type' );
		$parent_type = $relationship->get_parent_type()->get_types();
		$child_type = $relationship->get_child_type()->get_types();

		if ( ! in_array( $related_post_type, $parent_type, true )
			&& ! in_array( $related_post_type, $child_type, true )
		) {
			$this->fail( __( 'Invalid related post type.', 'wpcf' ) );
		}

		$role = in_array( $related_post_type, $relationship->get_parent_type()->get_types(), true )
			? Toolset_Relationship_Role::CHILD
			: Toolset_Relationship_Role::PARENT;

		$post_id = toolset_getpost( 'post_id' );
		$post = get_post( $post_id );

		// Many to many relationships where both CPT are the same, the role must be parent.
		if ( $post->post_type === $related_post_type ) {
			$role = Toolset_Relationship_Role::PARENT;
		}

		$items_per_page = toolset_getpost( 'items_per_page' );
		$page_number = toolset_getpost( 'page' );

		// Sorting.
		$sort = toolset_getpost( 'sort', 'ASC' );
		$sort_by = toolset_getpost( 'sort_by', 'displayName' );
		$sort_origin = toolset_getpost( 'sort_origin', 'post_title' );

		$related_content_viewmodel = $this->get_related_content_viewmodel( $role, $relationship );
		$related_content = $related_content_viewmodel->get_related_content_array( (int) $post_id, $post->post_type, $page_number, $items_per_page, $role, $sort, $sort_by, $sort_origin );
		$related_content['items_found'] = $related_content_viewmodel->get_rows_found();

		foreach ( $related_content['data'] as $i => $item ) {
			// Formats the fields into preview and input render modes.
			$fields = $item['fields'];
			// Modify previous data.
			$related_content['data'][ $i ]['fields'] = $this->format_field_data( $fields, $item['association_uid'] );
		}
		$this->get_ajax_manager()->ajax_finish(
			array(
				'relatedContent' => $related_content,
				'canConnectAnother' => $this->can_connect_another(),
			), true
		);
	}


	/**
	 * Gets translatable content for displaying in the confirmation dialog.
	 *
	 * @since m2m
	 */
	private function get_translatable_content() {
		$definition = $this->get_definition();
		$translatable_posts = array();
		$wpml_compatibility = Toolset_WPML_Compatibility::get_instance();
		if ( $definition->is_translatable() && $wpml_compatibility->is_wpml_active_and_configured() ) {
			$wpml_lang = toolset_getpost( 'wpml_lang' );
			$association = $this->get_association();
			$post_type_repository = $this->get_post_type_repository();
			$elements = array(
				$association->get_element( Toolset_Relationship_Role::PARENT ),
				$association->get_element( Toolset_Relationship_Role::CHILD ),
			);
			$language_flags = $this->get_language_flags();
			$current_language = Toolset_Wpml_Utils::get_current_language();
			foreach ( $elements as $element ) {
				$post_type = $post_type_repository->get( $element->get_type() );
				$post_type_name = $post_type
					? $post_type->get_label( Toolset_Post_Type_Labels::NAME )
					: '';
				$id = $element->get_id();
				$titles = array();
				$translated_ids = Toolset_Wpml_Utils::get_post_translations_directly( $id );
				$current_language_title = array();
				foreach ( $translated_ids as $lang => $tid ) {
					$item = array(
						'title' => get_the_title( $tid ),
						'flag' => array_key_exists( $lang, $language_flags ) ? '<img src="' . $language_flags[ $lang ] . '" />': '',
					);
					// Default language is stored in a different var, so it can be placed in first position.
					if ( $lang === $current_language ) {
						$current_language_title[] = $item;
					} else {
						$titles[] = $item;
					}
				}
				$titles = array_merge( $current_language_title, $titles );
				$translatable_posts[ $post_type_name ] = $titles;
			}
		}
		$this->get_ajax_manager()->ajax_finish(
			array(
				'translatablePosts' => $translatable_posts,
			), true
		);
	}


	/**
	 * Gets an array with language flags links
	 *
	 * @return array
	 * @since m2m
	 */
	private function get_language_flags() {
		$flags = array();
		$languages = Toolset_Wpml_Utils::get_active_languages();
		if ( is_array( $languages ) && ! empty( $languages ) ) {
			foreach ( $languages as $lang => $info ) {
				$flags[ $lang ] = $info['country_flag_url'];
			}
		}
		return $flags;
	}


	/**
	 * Gets the relationship definition
	 *
	 * @return Toolset_Relationship_Definition
	 * @since m2m
	 */
	private function get_definition() {
		if ( $this->definition ) {
			return $this->definition;
		}
		$relationship_slug = toolset_getpost( 'relationship_slug' );

		if ( $relationship_slug ) {
			$definition_repository = $this->get_definition_repository();
			$this->definition = $definition_repository->get_definition( $relationship_slug );
		} else {
			$association = $this->get_association();
			$this->definition = $association->get_definition();
		}
		return $this->definition;
	}


	/**
	 * Gets the association
	 *
	 * @return IToolset_Association
	 * @since m2m
	 */
	private function get_association() {
		if ( $this->association ) {
			return $this->association;
		}
		$association_query = $this->get_association_query();

		// disable default conditions to make this work without having the post saved
		$association_query->do_not_add_default_conditions();

		$association_uid = toolset_getpost( 'association_uid' );
		$association_query->add( $association_query->association_id( $association_uid ) );
		$association = $association_query->get_results();
		if ( empty( $association ) ) {
			$this->fail( __( 'The association you are trying to disconnect doesn\'t exist, perhaps it has been disconnected before. Please, refresh the page and try again.', 'wpcf' ) );
		}

		$this->association = $association[0];

		return $this->association;
	}


	/**
	 * Handles error messages
	 *
	 * @param string $message The error message.
	 * @param string $type The message type.
	 *
	 * @since m2m
	 */
	private function fail( $message, $type = 'error' ) {
		$this->get_ajax_manager()->ajax_finish( array( 'message' => $message, 'messageType' => $type ), false );
	}


	/**
	 * Checks if the relationships admits another association
	 *
	 * @return boolean
	 * @since m2m
	 */
	private function can_connect_another() {
		if ( isset( $this->can_connect ) ) {
			return $this->can_connect;
		}
		$definition = $this->get_definition();
		$post_id = toolset_getpost( 'post_id' );
		$post = get_post( $post_id );
		$role = $this->get_role_from_post_type( $definition, $post->post_type );
		// The role beloinging to the related post.
		$other_role = Toolset_Relationship_Role::other( $role );

		$potential_factory = new Toolset_Potential_Association_Query_Factory();
		$post_element = Toolset_Post::get_instance( $post_id );
		$target_role = Toolset_Relationship_Role::PARENT === $other_role
			? new Toolset_Relationship_Role_Parent()
			: new Toolset_Relationship_Role_Child();
		$potential_association_query = $potential_factory->create( $definition, $target_role, $post_element );
		$can_connect_another = $potential_association_query->can_connect_another_element();

		return $can_connect_another->is_success();
	}


	/**
	 * Get association query
	 *
	 * @return Toolset_Association_Query_V2
	 * @since m2m
	 */
	private function get_association_query() {
		if ( ! $this->_association_query ) {
			return new Toolset_Association_Query_V2();
		}
		return $this->_association_query;
	}


	/**
	 * Checks if a list of post types can be used in a relationship
	 * If a post type fails Ajax call will return an error
	 *
	 * @param string[] $post_types List of post type slugs.
	 */
	private function check_can_be_used( $post_types ) {
		$post_type_repository = $this->get_post_type_repository();
		foreach ( $post_types as $other_type ) {
			$post_type_object = $post_type_repository->get( $other_type );
			if ( $post_type_object && $post_type_object->can_be_used_in_relationship()->is_error() ) {
				$this->fail( sprintf( __( 'Post type %s can not be used in a relationship.', 'wpcf' ), $post_type_object->get_label( Toolset_Post_Type_Labels::SINGULAR_NAME ) ) );
			}
		}
	}


	/**
	 * Gets Post Type repository
	 *
	 * @return Toolset_Post_Type_Repository
	 */
	private function get_post_type_repository() {
		if ( ! $this->post_type_repository ) {
			$this->post_type_repository = Toolset_Post_Type_Repository::get_instance();
		}
		return $this->post_type_repository;
	}


	/**
	 * Updates visible fields in the related content metabox
	 *
	 * @since m2m
	 */
	private function update_fields_displayed() {
		$fields_post = toolset_getpost( 'field-post', array() );
		$fields_relationship = toolset_getpost( 'field-relationship', array() );
		$fields_related_posts = toolset_getpost( 'field-relatedPosts', array() );
		$post_type = toolset_getpost( 'post_type' );
		$definition = $this->get_definition();
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $definition ) {
			$this->fail( __( 'Wrong relationship.', 'wpcf' ) );
		}
		if ( ! $post_type_object ) {
			$this->fail( __( 'Wrong post type.', 'wpcf' ) );
		}
		$ipt = $definition->get_intermediary_post_type();
		$data = array(
			$post_type => array( $fields_post, $fields_related_posts ),
			$ipt => array( $fields_relationship ),
		);
		$classes = array(
			$post_type => array( 'Types_Post_Type_Relationship_Settings', 'Types_Post_Type_Relationship_Related_Posts_Settings' ),
			$ipt => array( 'Types_Post_Type_Relationship_Settings' ),
		);
		foreach ( $data as $_post_type => $fields_list ) {
			foreach ( $fields_list as $i => $fields ) {
				$relationship_settings = new $classes[$_post_type][$i]( $_post_type, $definition );
				$relationship_settings->set_fields_list_related_content( $fields );
				$relationship_settings->save_data();
			}
		}
		$result = array(
			'post' => $fields_post,
			'relationship' => $fields_relationship,
			'relatedPosts' => $fields_related_posts,
		);
		$this->get_ajax_manager()->ajax_finish( $result, true );
	}


	/**
	 * Checks if it is an unsafe action. For unsage I mean not permissions are required.
	 *
	 * @param string $action Ajax action.
	 * @return boolean
	 * @since 3.0
	 */
	private function is_safe_action( $action ) {
		return in_array( $action, $this->unsafe_actions, true );
	}


	private function get_element_factory() {
		if( null === $this->_element_factory ) {
			$this->_element_factory = new Toolset_Element_Factory();
		}

		return $this->_element_factory;
	}
}
