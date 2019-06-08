<?php

/**
 * Fiels group repository
 *
 * Saves field groups from $_POST data
 *
 * It uses legacy code waiting for a refactoring
 * TODO add extra functionalities.
 *
 * @see Types_Admin_Edit_Custom_Fields_Group
 * @since m2m
 */
class Types_Field_Group_Viewmodel {


	/**
	 * Group types
	 *
	 * @var array
	 * @since m2m
	 */
	private $group_types = array( Toolset_Field_Group_Post::POST_TYPE, Toolset_Field_Group_Term::POST_TYPE, Toolset_Field_Group_User::POST_TYPE );


	/**
	 * Group type
	 *
	 * @var string
	 * @since m2m
	 */
	private $group_type;


	/**
	 * Group ID
	 *
	 * @var integer
	 * @since m2m
	 */
	private $group_id;


	/**
	 * Post Field Group
	 *
	 * @var Toolset_Field_Group_Post
	 * @since m2m
	 */
	private $field_group;


	/**
	 * Group. I contains:
	 *		[id]          optional
	 *		[name]        mandatory
	 *		[description] optional
	 *		[supports]    optional The post types, terms or users that have this group.
	 *
	 * @var array
	 * @since m2m
	 */
	private $group;


	/**
	 * Fields
	 *
	 * @var array
	 * @since m2m
	 */
	private $fields;


	/**
	 * Instance object
	 *
	 * @var Types_Fields_Group_Repository
	 * @since m2m
	 */
	private static $instance = null;

	/**
	 * Gets new instance
	 *
	 * @param string $group_type The group type related to the Fields Group: posts, users, meta.
	 */
	public static function get_instance( $group_type ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $group_type );
		}
		return self::$instance;
	}


	/**
	 * Constructor
	 *
	 * @param string $group_type The group type related to the Fields Group: posts, users, meta.
	 * @throws InvalidArgumentException If invalid argument.
	 * @since m2m
	 */
	function __construct( $group_type ) {
		if ( ! in_array( $group_type, $this->group_types, true ) ) {

			throw new InvalidArgumentException( sprintf(
				// translators: a list of allowed group types.
				__( 'Invalid group type. Only valid group types are allowed: (%s)', 'wpcf' ),
				implode( ', ', $this->group_types )
			) );
		}
		$this->group_type = $group_type;
	}


	/**
	 * Is a valid group type
	 */
	/**
	 * Load $_POST data into the object
	 *
	 * @param array $post_data The $_POST
	 *													 $_POST['wpcf']['group']
	 *													 $this->fields.
	 * @since m2m
	 */
	public function load_data( $post_data ) {
		$this->set_group( $post_data['group'] );
		$fields = isset( $post_data['wpcf']['fields'] )
			? $post_data['wpcf']['fields']
			: null;
		$this->set_fields( $fields );
	}


	/**
	 * Sets group fields
	 *
	 * @param array $fields Fields list from $_POST.
	 * @since m2m
	 */
	public function set_fields( $fields ) {
		$this->fields = $fields;
	}


	/**
	 * Gets fields.
	 *
	 * Useful for testing
	 *
	 * @return array
	 * @since m2m
	 */
	public function get_fields() {
		return $this->fields;
	}


	/**
	 * Sets group and sanitize data
	 *
	 * @param array $group Group name.
	 *                [id]          optional
	 *                [name]        mandatory
	 *                [description] optional
	 *                [supports]    optional The post types, terms or users that have this group.
	 * @throws InvalidArgumentException If invalid argument.
	 * @since m2m
	 */
	public function set_group( $group ) {
		if ( empty( $group['name'] ) ) {
			throw new InvalidArgumentException( __( 'Invalid group name', 'wpcf' ) );
		}
		$this->group = $group;
		if ( isset( $this->group['id'] ) ) {
			$this->group['id'] = (int) $this->group['id'];
		}
		$this->group['name'] = sanitize_text_field( $this->group['name'] );
		if ( isset( $this->group['description'] ) ) {
			$this->group['description'] = sanitize_text_field( $this->group['description'] );
		}
		if ( ! isset( $this->group['purpose'] ) ) {
			$this->group['purpose'] = Toolset_Field_Group_Post::PURPOSE_FOR_INTERMEDIARY_POSTS;
		}
	}


	/**
	 * Gets the group data.
	 *
	 * Useful for testing
	 *
	 * @return array
	 * @since m2m
	 */
	public function get_group() {
		return $this->group;
	}


	/**
	 * Saves the Field group
	 *
	 * @param String $purpose Purpose for the field group.
	 * @since m2m
	 */
	public function save( $purpose ) {
		$this->save_group( $purpose );
		$this->save_group_fields();
		$this->save_group_fields_supports();
		$this->save_condition_templates();
		$this->save_condition_taxonomies();
	}


	/**
	 * Saves or updates the group
	 *
	 * @param String $purpose Field group purpose.
	 * @since m2m
	 */
	private function save_group( $purpose ) {
		$name = sanitize_title( $this->group['name'] );
		$title = sanitize_text_field( $this->group['name'] );
		$this->field_group = Toolset_Field_Group_Post_Factory::create( $name, $title, 'publish', $purpose );
/*
		if ( empty( $this->group ) ) {
			throw new InvalidArgumentException( __( 'Invalid group', 'wpcf' ) );
		}

		$post = array(
				'post_status' => 'publish',
				'post_type' => $this->group_type,
				'post_title' => sanitize_text_field( $this->group['name'] ),
				'post_name' => sanitize_title( $this->group['name'] ),
				'post_content' => isset( $this->group['description'] ) ? $this->group['description'] : '',
		);

		$update = false;
		$slug_pre_save = false;

		if ( isset( $this->group['id'] ) && ! empty( $this->group['id'] ) ) {
			$update = true;
			$post_to_update = get_post( $this->group['id'] );
			if ( empty( $post_to_update ) || $post_to_update->post_type !== $this->group_type ) {
				throw new RuntimeException( __( 'Error getting existing field group', 'wpcf' ) );
			}
			$post['ID'] = $post_to_update->ID;
			$slug_pre_save = $post_to_update->post_name;
			$this->post['post_status'] = $post_to_update->post_status;
		}

		if ( $update ) {
			$group_id = wp_update_post( $post );
			if ( ! $group_id ) {
				throw new RuntimeException( __( 'Error updating field group', 'wpcf' ) );
			}
			update_post_meta( $group_id, TOOLSET_EDIT_LAST, time() );
		} else {
			$group_id = wp_insert_post( $post, true );
			if ( is_wp_error( $group_id ) ) {
				throw new RuntimeException( __( 'Error saving field group', 'wpcf' ) );
			}

			update_post_meta( $group_id, Toolset_Field_Group::POSTMETA_GROUP_PURPOSE, Toolset_Field_Group_Post::PURPOSE_FOR_INTERMEDIARY_POSTS );
		}

		if ( isset( $group['admin_styles'] ) ) {
			wpcf_admin_fields_save_group_admin_styles( $group_id, $group['admin_styles'] );
		}

		if ( ! empty( $group['filters_association'] ) ) {
			update_post_meta( $group_id, '_wp_types_group_filters_association', $group['filters_association'] );
		} else {
			delete_post_meta( $group_id, '_wp_types_group_filters_association' );
		}

		// WPML register strings.
		if ( function_exists( 'icl_register_string' ) ) {

			try {
				// Legacy function gives us only the underlying post type of the field group.
				$group_factory = Toolset_Field_Utils::get_group_factory_by_post_type( $post_type );
				$field_group = $group_factory->load_field_group( sanitize_title( $group['name'] ) );

				// Skip registering if the group does not exist.
				if ( null !== $field_group ) {
					$group_wpml = new Types_Wpml_Field_Group( $field_group );
					$group_wpml->register( $slug_pre_save );
				}
			} catch ( InvalidArgumentException $e ) {
				// Something is seriously wrong - there's no field group factory for given post type, bail.
				throw new RuntimeException( __( 'Something was wrong, please try again', 'wpcf' ) );
			}
		}
		$this->group_id = $group_id;
*/
	}


	/**
	 * Saves fields group
	 * TODO legacy code
	 *
	 * @throws RuntimeException In case of unexpected error.
	 * @since m2m
	 */
	private function save_group_fields() {
		if ( empty( $this->fields ) ) {
			delete_post_meta( $this->field_group->get_id(), '_wp_types_group_fields' );
			return;
		}
		$fields = array();

		// First check all fields.
		foreach ( $this->fields as $key => $field ) {
			$field = wpcf_sanitize_field( $field );
			$field = apply_filters( 'wpcf_field_pre_save', $field );
			if ( ! empty( $field['is_new'] ) ) {
				// Check name and slug.
				if ( wpcf_types_cf_under_control( 'check_exists', sanitize_title( $field['name'] ) ) ) {
					// translators: a field name.
					throw new RuntimeException( sprintf( __( 'Field with name "%s" already exists', 'wpcf' ), $field['name'] ) );
				}
				if ( isset( $field['slug'] ) && wpcf_types_cf_under_control( 'check_exists', sanitize_title( $field['slug'] ) ) ) {
					// translators: a field slug.
					throw new RuntimeException( sprintf( __( 'Field with slug "%s" already exists', 'wpcf' ), $field['slug'] ) );
				}
			}
			$field['submit-key'] = $key;
			// Field ID and slug are same thing.
			$field_id = wpcf_admin_fields_save_field( $field );
			if ( is_wp_error( $field_id ) ) {
				throw new RuntimeException( $field_id->get_error_message() );
			}
			if ( ! empty( $field_id ) ) {
				$fields[] = $field_id;
			}
			// WPML.
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '<' ) ) {
				if ( function_exists( 'wpml_cf_translation_preferences_store' ) ) {
					$real_custom_field_name = wpcf_types_get_meta_prefix( wpcf_admin_fields_get_field( $field_id ) ) . $field_id;
					wpml_cf_translation_preferences_store( $key, $real_custom_field_name );
				}
			}
		}
		wpcf_admin_fields_save_group_fields( $this->field_group->get_id(), $fields );
	}


	/**
	 * Saves field group supports: post types, terms, users attached to the group
	 *
	 * @see wpcf_admin_fields_save_group_post_types
	 * @since m2m
	 */
	private function save_group_fields_supports() {
		$supports = isset( $this->group['supports'] )
			? $this->group['supports']
			: array();
		foreach ( $supports as $post_type ) {
			$this->field_group->assign_post_type( $post_type );
		}
//		wpcf_admin_fields_save_group_post_types( $this->field_group->get_id(), $supports );
	}


	/**
	 * TODO implement
	 */
	private function save_condition_templates() {}

	/**
	 * TODO implement
	 */
	private function save_condition_taxonomies() {}
}
