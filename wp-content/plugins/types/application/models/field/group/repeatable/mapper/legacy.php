<?php

/**
 * Class Types_Field_Group_Repeatable_Mapper_Legacy
 *
 * @since m2m
 */
class Types_Field_Group_Repeatable_Mapper_Legacy implements Types_Field_Group_Mapper_Interface {

	/**
	 * @var string
	 */
	private $element_language;

	/**
	 * Map repeatable group by the WP_Post object of the group.
	 *
	 * @param WP_Post $rfg_post
	 * @param WP_Post|null $parent_post To load items of RFG the associated post is necessary
	 *
	 * @param int $depth
	 *
	 * @param SitePress|null $wpml
	 *
	 * @param Toolset_Post_Type_Repository|null $post_type_repository
	 * @param Types_Field_Group_Repeatable_Item_Builder|null $rfg_item_builder
	 * @param Toolset_Association_Query_V2|null $relationship_associations_query
	 * @param IToolset_Relationship_Role_Parent_Child|null $relationship_role_parent
	 * @param IToolset_Relationship_Role_Parent_Child|null $relationship_role_child
	 *
	 * @return bool|Types_Field_Group_Repeatable
	 */
	public function find_by_post(
		WP_Post $rfg_post,
		WP_Post $parent_post = null,
		$depth = 1,
		SitePress $wpml = null,
		Toolset_Post_Type_Repository $post_type_repository = null,
		Types_Field_Group_Repeatable_Item_Builder $rfg_item_builder = null,
		Toolset_Association_Query_V2 $relationship_associations_query = null,
		IToolset_Relationship_Role_Parent_Child $relationship_role_parent = null,
		IToolset_Relationship_Role_Parent_Child $relationship_role_child = null
	) {
		// make sure depth is an int
		$depth = (int) $depth;

		if ( $rfg_post->post_type !== Toolset_Field_Group_Post::POST_TYPE ) {
			// no repeatable field group nor field group
			return false;
		}

		if ( $rfg_post->post_status !== 'hidden' ) {
			// we have a field group, BUT NO repeatable field group
			return false;
		}

		// start mapping group
		// TODO get rid of hard coded dependency
		$group = new Types_Field_Group_Repeatable( $rfg_post );

		// prove slug
		$slug = $group->get_slug();
		if ( empty( $slug ) ) {
			// invalid group. there shouldn't be a group without a slug.
			return false;
		}

		// WPML - make sure post rfg has same translation mode as parent
		if ( $wpml && $parent_post && function_exists( 'wpml_load_settings_helper' ) ) {
			// parent post can be another rfg (we need to use the post_name) or a usual post
			$parent_post_type = $parent_post->post_type != 'wp-types-group'
				? $parent_post->post_type
				: $parent_post->post_name;

			$settings_helper      = wpml_load_settings_helper();
			$translation_settings = $wpml->get_setting( 'custom_posts_sync_option' );

			if ( isset( $translation_settings[ $parent_post_type ] ) ) {
				$parent_translation_setting = $translation_settings[ $parent_post_type ];
				$rfg_translation_setting    = isset( $translation_settings[ $rfg_post->post_name ] )
					? $translation_settings[ $rfg_post->post_name ]
					: null;

				if ( $rfg_translation_setting != $parent_translation_setting ) {
					$translation_settings[ $rfg_post->post_name ] = $parent_translation_setting;
					$settings_helper->update_cpt_sync_settings( $translation_settings );
				}
			}
		}

		// Load post type of group
		if ( $post_type = $this->get_group_post_type( $group,
			Types_Field_Group_Repeatable::OPTION_NAME_LINKED_POST_TYPE,
			$post_type_repository )
		) {
			$group->set_post_type( $post_type );
		}

		// Load items of group
		if ( $depth > 0 && $parent_post ) {
			// default dependencies
			$rfg_item_builder                = $rfg_item_builder ?: new Types_Field_Group_Repeatable_Item_Builder();
			$relationship_associations_query = $relationship_associations_query ?: new Toolset_Association_Query_V2();
			$relationship_role_parent        = $relationship_role_parent ?: new Toolset_Relationship_Role_Parent();
			$relationship_role_child         = $relationship_role_child ?: new Toolset_Relationship_Role_Child();

			$items = $this->get_group_items(
				$group,
				$parent_post,
				$depth,
				$rfg_item_builder,
				$relationship_associations_query,
				$relationship_role_parent,
				$relationship_role_child
			);

			foreach ( $items as $item ) {
				$group->add_post( $item['object'], $item['sortorder'] );
			}
		}

		return $group;
	}


	/**
	 * Delete a item of an repeatable group
	 *
	 * - deletes post
	 * - deletes translations
	 * - deletes associations
	 *
	 * @param WP_Post $item
	 *
	 * @param Toolset_Post_Type_Repository $post_type_repository
	 * @param Toolset_Relationship_Service $relationship_service
	 *
	 * @param SitePress|null $wpml
	 *
	 * @return bool
	 */
	public function delete_item_by_post(
		WP_Post $item,
		Toolset_Post_Type_Repository $post_type_repository,
		Toolset_Relationship_Service $relationship_service,
		SitePress $wpml = null
	) {
		// Check that the item belongs to an repeatable field group
		$post_type_the_item_belongs_to = $post_type_repository->get( $item->post_type );
		if ( ! $post_type_the_item_belongs_to->is_repeating_field_group() ) {
			// no item of a repeatable field group
			throw new InvalidArgumentException( 'The item is not part of a repeatable field group' );
		};

		// Get children items (nested rfgs)
		if ( $children = $relationship_service->find_children_ids_by_parent_id( $item->ID ) ) {
			// remove children
			foreach ( $children as $child_id ) {
				if ( $item_post = get_post( $child_id ) ) {
					$this->delete_item_by_post( $item_post, $post_type_repository, $relationship_service, $wpml );
				}
			}
		}

		// Remove Translations
		if ( $wpml ) {
			$trid         = $wpml->get_element_trid( $item->ID );
			$translations = $wpml->get_element_translations(
				$trid,
				$item->post_type,
				false,                              // $skip_empty
				true                                // $all_statuses
			);

			if ( is_array( $translations ) && ! empty( $translations ) ) {
				$default_language_id = $translations[ $wpml->get_default_language() ]->element_id;

				if ( $default_language_id == $item->ID ) {
					// the default language item is delete... -> delete all translations of this item
					foreach ( $translations as $translation ) {
						if ( $translation->element_id == $item->ID ) {
							// original item is deleted later
							continue;
						}

						// delete translation post
						wp_delete_post( $translation->element_id );
					}
				}
			}
		}

		// Delete the items post
		return wp_delete_post( $item->ID );
	}

	/**
	 * Update item title
	 * This will NOT trigger update_post hook.
	 *
	 * @param WP_Post $item
	 * @param $title         Optional. If not set $item->post_title will be used
	 *
	 * @return bool
	 */
	public function update_item_title( WP_Post $item, $title = null ) {
		$new_title = $item->post_title;

		if( $title !== null ) {
			$new_title = $title;
		}

		if( is_array( $new_title ) ){
			throw new InvalidArgumentException( 'Title cannot be an array.' );
		}

		$result = wp_update_post( array(
			'ID' => $item->ID,
			'post_title' => sanitize_text_field( $new_title )
		) );

		if( ! $result ) {
			return false;
		}

		return true;
	}

	/**
	 * Load the post type, to which the Repeatable Field Group is assigned to.
	 *
	 * @param $group
	 *
	 * @param string $option_name_for_rfg_post
	 *
	 * @param Toolset_Post_Type_Repository $post_type_repository
	 *
	 * @return false|IToolset_Post_Type
	 */
	private function get_group_post_type(
		$group,
		$option_name_for_rfg_post,
		Toolset_Post_Type_Repository $post_type_repository
	) {
		$post_type_slug = get_post_meta(
			$group->get_id(),
			$option_name_for_rfg_post,
			true
		);

		if ( ! $post_type_slug || empty( $post_type_slug ) ) {
			// no linked post type
			return false;
		}

		if ( $post_type = $post_type_repository->get( $post_type_slug ) ) {
			return $post_type;
		}

		return false;
	}

	/**
	 * Get items of the group.
	 *
	 * @param Types_Field_Group_Repeatable $rfg
	 * @param WP_Post $parent_post
	 * @param int $depth
	 *
	 * @param Types_Field_Group_Repeatable_Item_Builder $rfg_item_builder
	 * @param Toolset_Association_Query_V2 $association_query
	 * @param IToolset_Relationship_Role_Parent_Child $relationship_role_parent
	 * @param IToolset_Relationship_Role_Parent_Child $relationship_role_child
	 *
	 * @return array
	 */
	private function get_group_items(
		Types_Field_Group_Repeatable $rfg,
		WP_Post $parent_post,
		$depth,
		Types_Field_Group_Repeatable_Item_Builder $rfg_item_builder,
		Toolset_Association_Query_V2 $association_query,
		IToolset_Relationship_Role_Parent_Child $relationship_role_parent,
		IToolset_Relationship_Role_Parent_Child $relationship_role_child
	) {
		do_action( 'toolset_do_m2m_full_init' );

		// when wpml is active we need to use the post of the default language
		$post_which_holds_associations = $this->get_post_which_holds_associations( $parent_post );
		$is_default_language_active    = $post_which_holds_associations->ID == $parent_post->ID;

		// post status "any"
		$post_status_any = Toolset_Association_Query_Condition_Element_Status::STATUS_ANY;

		// setup query
		$association_query
			->add( $association_query->element_id( $post_which_holds_associations->ID, $relationship_role_parent ) )
			->add( $association_query->has_type( $rfg->get_slug(), $relationship_role_child ) )
			->add( $association_query->element_status( $post_status_any, $relationship_role_parent ) )
			->add( $association_query->element_status( $post_status_any, $relationship_role_child ) );

		// get group elements as array of post ids
		$group_elements = $association_query
			->limit( 1000 )
			->return_element_ids( $relationship_role_child )
			->dont_translate_results()
			->get_results();

		$group_items = array();

		foreach ( $group_elements as $element_id ) {
			if ( ! $wp_post = get_post( $element_id ) ) {
				// the element id is invalid, skip it
				continue;
			}

			// sortorder
			$sortorder = get_post_meta( $wp_post->ID, Toolset_Post::SORTORDER_META_KEY, true );
			$sortorder = ! empty( $sortorder ) ? $sortorder : 0;


			if ( ! $is_default_language_active ) {
				// as we now loop through the items of the default language,
				// we need to get the translation of the current language
				$wp_post_translated_id = apply_filters(
					'wpml_object_id',       // calls wpml_object_id_filter()
					$wp_post->ID,           // $element_id
					$wp_post->post_type,    // $element_type = 'post'
					false,                  // $return_original_if_missing = false
					$this->element_language // $language_code = null (null = current language)
				// we cannot use "null" for current language here, as it would
				// not work when the user has the "All languages" mode active.
				);

				if ( $wp_post_translated_id === null ) {
					// wpml is active, but there is no translated item
					global $sitepress;

					// create a post for the translated item, based on the default language
					$wp_post_translated_id = wp_insert_post( array(
						'post_name'   => $wp_post->post_name,
						'post_title'  => $wp_post->post_title,
						'post_type'   => $wp_post->post_type,
						'post_status' => $wp_post->post_status
					) );

					// tell WPML that the new created post is the translation of default language
					$trid        = $sitepress->get_element_trid( $wp_post->ID );
					$source_lang = isset( $_REQUEST['source_lang'] ) ? $_REQUEST['source_lang'] : null;

					$sitepress->set_element_language_details(
						$wp_post_translated_id,
						'post_' . $wp_post->post_type,
						$trid,
						$sitepress->get_current_language(),
						$source_lang
					);

					// WPML
					$this->wpml_save_rfg_item( $wp_post_translated_id );
				}
				$wp_post = $wp_post_translated_id != $wp_post->ID
					? get_post( $wp_post_translated_id )
					: $wp_post;
			}

			// add the post (item) to the group
			$rfg_item_builder->reset();
			$rfg_item_builder->set_wp_post( $wp_post );
			$rfg_item_builder->set_belongs_to_rfg( $rfg );
			$rfg_item_builder->load_assigned_field_groups( $depth );
			$rfg_item = $rfg_item_builder->get_types_post();

			$group_items[] = array( 'object' => $rfg_item, 'sortorder' => $sortorder );
		};

		return $group_items;
	}

	/**
	 * For WPML setup (sync fields) we need to fire the save_post hook.
	 *
	 * @param $rfg_item_id
	 */
	private function wpml_save_rfg_item( $rfg_item_id ) {
		// normally WPML does not fire save hooks for a different post than the current
		// so we temporary set our rfg item to $_POST['post_ID']
		$_POST_id_backup = isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : null;
		$_POST['post_ID'] = $rfg_item_id;
		// tell WPML that our rfg item is a translatable post type
		add_filter( 'pre_wpml_is_translated_post_type', array( $this, 'filter_pre_wpml_is_translated_post_type' ) );


		$is_wpml_tm_save_post_action_active = has_action( 'wpml_tm_save_post', 'wpml_tm_save_post' );
		// disable wpml translation job update for RFG item (it will be updated by the parent post)
		if( $is_wpml_tm_save_post_action_active ) {
			remove_action( 'wpml_tm_save_post', 'wpml_tm_save_post', 10, 3 ); // prevent creating a translation job
		}

		// fire the save post hooks for the rfg item
		do_action( 'save_post', $rfg_item_id, get_post( $rfg_item_id ), true );

		if( $is_wpml_tm_save_post_action_active ) {
			// add_action( 'wpml_tm_save_post', 'wpml_tm_save_post', 10, 3 );
		}


		// undo all previous changes
		remove_filter( 'pre_wpml_is_translated_post_type', array( $this, 'filter_pre_wpml_is_translated_post_type' ) );

		if( $_POST_id_backup !== null ) {
			$_POST['post_ID'] = $_POST_id_backup;
		} else {
			unset( $_POST['post_ID'] );
		}
	}

	/**
	 * We need this to apply wpml settings for rfg item
	 * @return bool
	 */
	public function filter_pre_wpml_is_translated_post_type() {
		return true;
	}

	/**
	 * @param WP_Post $original_post
	 *
	 * @return array|null|WP_Post
	 */
	private function get_post_which_holds_associations( WP_Post $original_post ) {
		try{
			$element_factory = new Toolset_Element_Factory();
			/** @var IToolset_Post $post */
			$post = $element_factory->get_post( $original_post );

		} catch( Toolset_Element_Exception_Element_Doesnt_Exist $e ) {
			// something went wrong, return input
			return $original_post;
		}

		$default_language_id = $post->get_default_language_id();

		if( $default_language_id && ! empty( $default_language_id ) ) {
			return get_post( $default_language_id );
		}

		return $original_post;
	}
}
