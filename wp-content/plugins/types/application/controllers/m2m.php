<?php


/**
 * Class Types_M2M
 *
 * This file only handles controls which are m2m AND Types related.
 * So far we only need to remove GUI elements when m2m is disabled.
 *
 * @since m2m
 */
class Types_M2M {

	/**
	 * @var bool
	 */
	private $is_m2m_active;

	/**
	 * Run...
	 */
	public function initialize() {
		$this->is_m2m_active = apply_filters( 'toolset_is_m2m_enabled', false );

		$this->actions_when_m2m_is_active();
		$this->limitations_when_m2m_is_not_active();
	}

	/**
	 * Init intermediary edit
	 *
	 * @action load-post.php, load-post-new.php
	 */
	public function load_intermediary_edit() {
		// This block is not required,
		// but to make sure we just initialise the whole object tree, if the current post is really an intermediary
		if( ! isset( $_REQUEST['post'] ) && ! isset( $_REQUEST['post_type'] ) ) {
			// no post edit page
			return;
		}

		$post_type = isset( $_REQUEST['post_type'] )
			? $_REQUEST['post_type']
			: get_post_type( $_REQUEST['post'] );

		if( ! $post_type ) {
			return;
		}

		$post_type_repository = Toolset_Post_Type_Repository::get_instance();
		$intermediary_post_type = $post_type_repository->get( $post_type );
		// If it is a third party post type, it could be register too late, so it will not be handled by Types.
		if ( ! $intermediary_post_type || ! $intermediary_post_type->is_intermediary() ) {
			// no intermediary -> abort
			return;
		}

		// Required
		$request = new \OTGS\Toolset\Types\Model\Post\Intermediary\Request(
			new Toolset_Element_Factory(),
			Toolset_Post_Type_Repository::get_instance(),
			new Toolset_Association_Query_V2(),
			new Toolset_Relationship_Query_V2(),
			new Toolset_Relationship_Role_Parent(),
			new Toolset_Relationship_Role_Child(),
			new Toolset_Relationship_Role_Intermediary()
		);

		$view = new \OTGS\Toolset\Types\Model\Post\Intermediary\View\PostEdit(
			new Types_Helper_Twig()
		);

		new \OTGS\Toolset\Types\Controller\Page\Extension\EditPostIntermediary( $request, $view );
	}

	/**
	 * Hooks when m2m is active
	 */
	private function actions_when_m2m_is_active() {
		if( ! $this->is_m2m_active ) {
			// abort
			return;
		}

		// Intermediary post page actions
		add_action( 'load-post.php', array( $this, 'load_intermediary_edit' ) );
		add_action( 'load-post-new.php', array( $this, 'load_intermediary_edit' ) );

		// tasks on post deletion
		add_action( 'before_delete_post', function( $postid ) {
			$types_post_deletion = new Types_Post_Deletion();
			$types_post_deletion->before_delete_post( $postid );
		} );

		// we need to adjust the "WPML POST KEY" which is used to detect changes on a post, before updating translation
		// jobs to make rfgs being updated, even if nothing else changes, we need to add rfg data to the key.
		add_filter( 'wpml_post_md5_key', function( $key, $post ) {
			/* @var Types_Field_Group_Repeatable_Wpml_Post_Md5_Key $rfg_wpml_post_md5_key */
			$rfg_wpml_post_md5_key = Toolset_Singleton_Factory::get( 'Types_Field_Group_Repeatable_Wpml_Post_Md5_Key' );

			return $rfg_wpml_post_md5_key->modify_key_for_post( $key, $post );
		}, 10, 2 );

		// adding rfg item fields to translation job
		add_filter( 'wpml_tm_translation_job_data',  function( $package, $post ){
			$rfg_wpml_translation = new Types_Field_Group_Repeatable_Wpml_Translation_Job_Data();
			$types_post_builder = new Types_Post_Builder();
			$translation_job_helper = new WPML_Translation_Job_Helper();
			$package = $rfg_wpml_translation->wpml_tm_translation_job_data(
				$package, $post, $types_post_builder, $translation_job_helper );
			return $package;
		}, 10 , 2 );

		// this will adjust the field titles on the translation job
		add_filter( 'wpml_tm_adjust_translation_fields', function( $fields ) {
			$rfg_wpml_translation = new Types_Field_Group_Repeatable_Wpml_Translation_Job_Data();
			return $rfg_wpml_translation->wpml_tm_adjust_translation_fields( $fields );
		} );

		// when translation job is complete, store translated rfg field values
		add_action( 'wpml_pro_translation_completed', function( $new_post_id, $fields, $job ) {
			$rfg_wpml_translation = new Types_Field_Group_Repeatable_Wpml_Translation_Job_Data();
			$rfg_wpml_translation->wpml_pro_translation_completed( $new_post_id, $fields, $job );
		}, 10 , 3 );

		// remove rfgs from translation management dashboard
		add_filter( 'wpml_tm_dashboard_translatable_types',
			array( $this, 'remove_rfgs_from_tm_dashboard'), 10, 1 );

		// remove rfgs from cpt translation mode settings
		add_filter( 'wpml_disable_translation_mode_radio',
			array( $this, 'remove_rfgs_from_cpt_translation_mode_settings' ), 10, 3 );

		// register RFG post status
		$this->register_rfg_post_status();

		// export of associations
		add_action( 'export_wp', function( $args ) {
			// better init here than always loading Types_Import_Export
			$types_import_export = Types_Import_Export::get_instance();
			$types_import_export->wp_export( $args );
		}, -1 );

		// import of associations
		add_action( 'current_screen', function() {
			if( ! function_exists( 'get_current_screen' ) ) {
				// loaded to early
				return;
			}

			/** @var WP_Screen $current_screen */
			if( ! $current_screen = get_current_screen() ) {
				// no screen, no notice
				return null;
			}

			if( $current_screen->id != 'toplevel_page_toolset-dashboard' && $current_screen->id != 'dashboard' ) {
				// no WP dashboard and no Toolset Dashboard
				return;
			}

			// current screen is WP Dashboard or Toolset Dashboard
			global $wpdb;

			new \OTGS\Toolset\Types\Controller\Page\Extension\AssociationsImport(
				new \OTGS\Toolset\Types\Wordpress\Postmeta\Storage( $wpdb ),
				new \OTGS\Toolset\Types\Post\Meta\Associations(),
				new \OTGS\Toolset\Types\Post\Import\Association\View\Notice(
					new \OTGS\Toolset\Common\Utility\Admin\Notices\Builder()
				),
				new \OTGS\Toolset\Types\Wordpress\Option\Associations\ImportAvailable()
			);
		} );
	}
	/**
	 * Limitations when M2M is disabled
	 * - Remove RFG and Post Reference from the GUI on group edit page
	 * - Remove RFG and Post Reference from post edit page
	 * - Remove RFG and Post Reference from the field control page
	 */
	private function limitations_when_m2m_is_not_active() {
		if( $this->is_m2m_active ) {
			// abort
			return;
		}

		// remove m2m fields from available field types (used for the dialog "Add new field")
		add_filter( 'types_register_fields', array( $this, 'filter_disable_m2m_fields_of_available_field_types' ) );

		// remove m2m fields from groups
		add_filter( 'types_post_field_group_fields', array( $this, 'filter_disable_m2m_fields_of_field_group' ) );

		// remove m2m fields also for uses outside of a group (like field control page)
		add_filter( 'types_fields', array( $this, 'filter_disable_m2m_fields' ) );
	}

	/**
	 * Filter callback for disabling m2m field types (post reference field and repeatable group)
	 * This will be fired on filter 'types_register_fields' if 'toolset_is_m2m_enabled' returns false
	 *
	 * @param $field_types
	 *
	 * @return array
	 * @since m2m
	 */
	public function filter_disable_m2m_fields_of_available_field_types( $field_types ) {
		if( isset( $field_types['post'] ) ) {
			// remove post reference field
			unset( $field_types['post'] );
		}

		// NOTE: repeatable group is not registered as a field type, so nothing to do for it at this point

		return $field_types;
	}

	/**
	 * Filter callback for removing all m2m fields (post reference field and repeatable group) of a field group
	 * This will be fired on filter 'types_field_of_group' if 'toolset_is_m2m_enabled' returns false
	 *
	 * @param $group_fields
	 *
	 * @return string (string because group fields are stored like this: field_1_slug, field_2_slug,...)
	 * @since m2m
	 */
	public function filter_disable_m2m_fields_of_field_group( $group_fields ) {
		$service_field_group = new Types_Field_Group_Repeatable_Service();
		$group_fields = array_filter( explode( ',', $group_fields ) );

		foreach ( $group_fields as $index => $slug ) {
			if( $repeatable_group = $service_field_group->get_object_from_prefixed_string( $slug ) ) {
				// repeatable field group
				unset( $group_fields[ $index ] );
				continue;
			}

			$a_field = wpcf_fields_get_field_by_slug( $slug );
			if( is_array( $a_field ) && isset( $a_field['type'] ) && $a_field['type'] == 'post' ) {
				// post reference field
				unset( $group_fields[ $index ] );
				continue;
			}
		}

		return implode( ',', $group_fields );
	}

	/**
	 * Filter callback for disabling m2m fields (post reference field and repeatable group)
	 * This will be fired on filter 'types_fields' if 'toolset_is_m2m_enabled' returns false
	 *
	 * @param $fields
	 *
	 * @return array
	 * @since m2m
	 */
	public function filter_disable_m2m_fields( $fields ) {
		foreach( $fields as $slug => $a_field ) {
			if( is_array( $a_field ) && isset( $a_field['type'] ) && $a_field['type'] == 'post' ) {
				// post reference field
				unset( $fields[ $slug ] );
				continue;
			}

			// NOTE: nothing to do here for repeatable group, as it is not really stored as a field
		}

		return $fields;
	}

	/**
	 *
	 */
	private function admin_notice_m2m_migration() {
		if( ! current_user_can( 'manage_options' ) ) {
			// no administrator
			return;
		}
		if( isset( $_GET['page'] ) && $_GET['page'] == 'toolset-settings'
			&& isset( $_GET['tab'] ) && $_GET['tab'] == 'custom-content'
		) {
			// don't show the message if the link was clicked
			return;
		}

		$legacy_relationships = toolset_ensarr( get_option( 'wpcf_post_relationship', array() ) );

		 if( empty( $legacy_relationships ) ) {
		 	// no legacy relationships
			 return;
		 };

		$notice = new Toolset_Admin_Notice_Dismissible( 'tbt-inactive-dashboard' );
		$notice->set_content( TYPES_ABSPATH . '/application/views/admin_notice/m2m-migration.phtml' );
		Toolset_Admin_Notices_Manager::add_notice( $notice );
	}

	/**
	 * Remove Repeatable Field Groups from Translation Manangement Dashboard
	 *
	 * @action wpml_tm_dashboard_translatable_types
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public function remove_rfgs_from_tm_dashboard( $post_types ) {
		foreach ( $post_types as $slug => $wp_post_type ) {
			if ( is_object( $wp_post_type )
			     && property_exists( $wp_post_type, 'is_repeating_field_group' )
			     && $wp_post_type->is_repeating_field_group
			) {
				// repeatable field group
				unset( $post_types[ $slug ] );
			}
		}

		return $post_types;
	}


	/**
	 * Remove Repeatable Field Groups from Custom Post Type translation settings
	 *
	 * @action wpml_disable_translation_mode_radio
	 * @param $disabled_state_for_mode
	 * @param $mode
	 * @param $content_slug
	 *
	 * @return mixed
	 */
	public function remove_rfgs_from_cpt_translation_mode_settings( $disabled_state_for_mode, $mode, $content_slug ) {
		$wp_post_type = get_post_type_object( $content_slug );

		if ( is_object( $wp_post_type )
		     && property_exists( $wp_post_type, 'is_repeating_field_group' )
		     && $wp_post_type->is_repeating_field_group
		) {
			$disabled_state_for_mode['state'] = true;
			$disabled_state_for_mode['reason_message'] = 'You cannot change the mode of this post type".';
		}

		return $disabled_state_for_mode;
	}

	/**
	 * For RFGs we have a custom post status 'hidden'
	 */
	public function register_rfg_post_status() {
		// Registering hidden post status
		register_post_status( 'hidden', array(
			'label'                     => 'Hidden',
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'label_count'               => array(
				'Hidden <span class="count">(%s)</span>',
				'Hidden <span class="count">(%s)</span>'
			),
		) );
	}
}
