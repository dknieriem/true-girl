<?php

/**
 * Class Types_Ajax_Handler_Repeatable_Group
 *
 * @since 3.0
 */
class Types_Ajax_Handler_Repeatable_Group extends Toolset_Ajax_Handler_Abstract {

	const NOTICE_KEY_FOR_RFG_ITEM_INTRODUCTION = 'rfg-item-title-introduction';

	/**
	 * @var Types_Field_Group_Repeatable_Service
	 */
	private $service_rg;

	/**
	 * @var Toolset_Relationship_Service
	 */
	private $service_relationship;

	/**
	 * @var bool
	 */
	private $_is_default_language_active;

	/**
	 * Collection of all field conditions used on the RFG and also the nested RFGs
	 * @var array
	 */
	private $field_conditions_collection = array();


	/** @var bool True if there has been a WYSIWYG field rendered during processing of the current AJAX request. */
	private $has_wysiwyg_field = false;


	/**
	 * @param array $arguments Original action arguments.
	 */
	function process_call( $arguments ) {
		$this->get_ajax_manager()
		     ->ajax_begin(
			     array(
				     'nonce' => $this->get_ajax_manager()->get_action_js_name( Types_Ajax::CALLBACK_REPEATABLE_GROUP ),
				     'capability_needed' => 'edit_posts',
				     'is_public' => toolset_getarr( $_REQUEST, 'skip_capability_check', false )
			     )
		     );

		// Read and validate input
		$action = sanitize_text_field( toolset_getpost( 'repeatable_group_action' ) );

		// load service
		$this->service_rg           = new Types_Field_Group_Repeatable_Service();
		$this->service_relationship = new Toolset_Relationship_Service();

		// route action
		return $this->route( $action );
	}

	/**
	 * Route ajax calls
	 *
	 * @param $action
	 */
	private function route( $action ) {
		switch ( $action ) {
			case 'json_repeatable_group':
				return $this->json_repeatable_group();
			case 'json_repeatable_group_add_item':
				return $this->json_repeatable_group_add_item();
			case 'json_repeatable_group_remove_item':
				return $this->json_repeatable_group_remove_item();
			case 'json_repeatable_group_field_original_translation':
				return $this->json_repeatable_group_field_original_translation();
			case 'json_repeatable_group_item_title_introduction_dismiss':
				return $this->json_repeatable_group_item_title_introduction_dismiss();
			case 'json_repeatable_group_item_title_update':
				return $this->json_repeatable_group_item_title_update();
		}
	}

	/**
	 * A repeatable group can be set by post 'repeatable_group_id'.
	 * Will return Types_Field_Group_Repeatable if the given id is valid.
	 *
	 * @return false|Types_Field_Group_Repeatable
	 */
	private function get_repeatable_group_by_post_data() {
		$rfg_id      = sanitize_text_field( toolset_getpost( 'repeatable_group_id' ) );
		$parent_post = get_post( sanitize_text_field( toolset_getpost( 'parent_post_id' ) ) );

		if ( ! $repeatable_group = $this->service_rg->get_object_by_id( $rfg_id, $parent_post ) ) {
			// shouldn't happen as long as the user doesn't manipulate the DOM
			return false;
		};

		return $repeatable_group;
	}

	/**
	 * The parent post can be set by post 'parent_post_id'.
	 * Will return WP_Post if the given id is valid.
	 *
	 * @return false|WP_Post
	 */
	private function get_parent_post_by_post_data() {
		if ( ! $post = get_post( toolset_getpost( 'parent_post_id' ) ) ) {
			// shouldn't happen as long as the user doesn't manipulate the DOM
			return false;
		}

		return $post;
	}

	/**
	 * Returns a repeatable group with all it items in json format
	 *
	 * This function exits the script (ajax response).
	 * @print json
	 */
	private function json_repeatable_group() {
		if ( ! $repeatable_group = $this->get_repeatable_group_by_post_data() ) {
			// shouldn't happen as long as the user doesn't manipulate the DOM
			$this->get_ajax_manager()->ajax_finish(
				__( 'Technical issue. Please reload the page and try again.', 'wpcf' ), false
			);
		}

		if ( ! $parent_post = $this->get_parent_post_by_post_data() ) {
			// shouldn't happen as long as the user doesn't manipulate the DOM
			$this->get_ajax_manager()->ajax_finish(
				__( 'Technical issue. Please reload the page and try again.', 'wpcf' ), false
			);
		}

		// get Translation Mode of current post
		$toolset_wpml = new Toolset_WPML_Compatibility();

		$wpml_is_translation_mode_supported = true;
		$parent_translation_mode = $toolset_wpml->get_post_type_translation_mode( $parent_post->post_type );

		if( $parent_translation_mode == Toolset_WPML_Compatibility::MODE_TRANSLATE ) {
			// in this mode we do not support repeatable field groups
			$wpml_is_translation_mode_supported = false;
		}

		// only load items when translation mode is supported (or wpml is inactive)
		$items = $wpml_is_translation_mode_supported
			? $this->get_rfg_items( $parent_post, $repeatable_group )
			: array();

		// field conditions
		$this->add_to_field_conditions_collection_by_items( $items );

		// controls are active in default language or if the post type is not translated at all
		$controls_active = $this->is_default_language_active()
		                   || $parent_translation_mode == Toolset_WPML_Compatibility::MODE_DONT_TRANSLATE;

		
		// rfg item title introduction active
		$is_title_introduction_active = Toolset_Admin_Notices_Manager::is_notice_dismissed_by_notice_id( self::NOTICE_KEY_FOR_RFG_ITEM_INTRODUCTION )
			? false
			: true;
		
		$repeatable_group_array = array(
			'id'                                 => $repeatable_group->get_id(),
			'parent_post_id'                     => $parent_post->ID,
			'title'                              => $repeatable_group->get_display_name(),
			'headlines'                          => $this->get_headlines_of_group( $repeatable_group ),
			'controlsActive'                     => $controls_active,
			'wpmlIsTranslationModeSupported'     => $wpml_is_translation_mode_supported,
			'wpmlFilterExistsForOriginalData'    => class_exists( 'WPML_Custom_Fields_Post_Meta_Info' ),
			'items'                              => $items,
			'itemTitleIntroductionActive'        => $is_title_introduction_active,
			'fieldConditions'                    => $this->field_conditions_collection
		);

		$response = array( 'repeatableGroup' => $repeatable_group_array );
		$response = $this->maybe_add_tinymce_settings( $response );

		$this->get_ajax_manager()->ajax_finish( $response );
	}


	/**
	 * Adds an item (new post) to the rfg and sets up the association entry.
	 * The new post is returned as json
	 *
	 * This function exits the script (ajax response).
	 * @print json
	 */
	private function json_repeatable_group_add_item() {
		if ( ! $repeatable_group = $this->get_repeatable_group_by_post_data() ) {
			// shouldn't happen as long as the user doesn't manipulate the DOM
			$this->get_ajax_manager()->ajax_finish(
				__( 'Technical issue. Please reload the page and try again.', 'wpcf' ), false
			);
		}

		if ( ! $parent_post = $this->get_parent_post_by_post_data() ) {
			// shouldn't happen as long as the user doesn't manipulate the DOM
			$this->get_ajax_manager()->ajax_finish( __( 'Technical issue. Please reload the page and try again.',
				'wpcf' ), false );
		}

		$relationship_definition = $this->get_relationship_definition(
			$parent_post->post_type,
			$repeatable_group->get_post_type()->get_slug()
		);

		if ( ! $relationship_definition ) {
			// error, no relationship found
			// shouldn't happen as long as the user doesn't manipulate the DOM, still:
			$this->get_ajax_manager()->ajax_finish( __( 'Technical issue. Please reload the page and try again.',
				'wpcf' ), false );
		};

		$new_post_id = wp_insert_post( array(
			'post_title'   => 'RFG',
			'post_status'  => 'publish',
			'post_content' => ' ',
			'post_type'    => $repeatable_group->get_post_type()->get_slug()
		) );

		$new_post             = get_post( $new_post_id );
		$new_post->post_title = $new_post_id;

		wp_update_post( $new_post );

		$association_result = $relationship_definition->create_association( $parent_post, $new_post );

		if( $association_result instanceof \Toolset_Result && $association_result->is_error() ) {
			// the association couldn't be build, delete rfg post and throw message to save the post first
			// (currently this only happens when the post is translateable by WPML)
			wp_delete_post( $new_post_id );
			$this->get_ajax_manager()->ajax_finish( array(
					'message' => __( 'Could not create item for this unsaved post. This can happen due to other plugins interacting with the Repeatable Field Group. Please save the post and try again.', 'wpcf' )
				),
				false
			);
		}
		/*
		 * Action 'toolset_post_update'
		 *
		 * @var WP_Post $new_post
		 *
		 * @since 3.0
		 */
		$affected_post = get_post( $new_post );
		do_action( 'toolset_post_update', $affected_post );

		// get rfg item by WP_Post
		$new_item = array( $this->get_rfg_item( $new_post, $parent_post, $repeatable_group ) );

		// field conditions
		$this->add_to_field_conditions_collection_by_items( $new_item );

		$response = array(
			'item' => $new_item[0],
			'fieldConditions' => $this->field_conditions_collection
		);
		$response = $this->maybe_add_tinymce_settings( $response );

		$this->get_ajax_manager()->ajax_finish( $response );
	}

	/**
	 * Get the field conditions for rfg items
	 *
	 * @param array $items Note: even a single item must be passed by an array: array( $single_item )
	 *                     passed by reference to remove data which is only necessary for this function
	 *
	 */
	private function add_to_field_conditions_collection_by_items( &$items ){
		if( ! $parent_post = $this->get_parent_post_by_post_data() ) {
			// technical issue
			return;
		}

		$form_condition = new WPToolset_Forms_Conditional_RFG( '#post', $parent_post->post_type );

		foreach( $items as &$item ) {
			foreach( $item['fields'] as &$field ) {
				if( ! isset( $field['fieldConfig'] ) ) {
					continue;
				}
				$field['fieldConfig']['id'] = $field['fieldConfig']['name'];
				$form_condition->add( $field['fieldConfig'] );
				unset( $field['fieldConfig'] );
			}
		}

		$this->field_conditions_collection =
			array_merge_recursive( $this->field_conditions_collection, $form_condition->get_conditions() );
	}

	/**
	 * Remove item
	 */
	private function json_repeatable_group_remove_item() {
		if ( ! $item_to_delete = get_post( toolset_getpost( 'remove_id' ) ) ) {
			// error, no post found
			// shouldn't happen as long as the user doesn't manipulate the DOM
			$this->get_ajax_manager()->ajax_finish( __( 'System Error. Item could not be deleted. Reload the page and try again. If the issue remains, contact our support please.', 'wpcf' ), false );
		}

		// translation management action
		if ( defined( 'WPML_TM_VERSION' ) ) {
			// delete removed item from translation job by updating the parent post
			if( $belongs_to_post = get_post( toolset_getpost( 'belongs_to_post_id' ) ) ) {
				wp_update_post( $belongs_to_post );
			}
		}

		if ( $this->service_rg->delete_item( $item_to_delete ) ) {
			// all good, item deleted
			$this->get_ajax_manager()->ajax_finish( 'Item deleted.' );
		}

		// something went wrong
		$this->get_ajax_manager()->ajax_finish( __( 'System Error. Item could not be deleted. Reload the page and try again. If the issue remains, contact our support please.', 'wpcf' ), false );
	}


	/**
	 * Get original language of field
	 */
	private function json_repeatable_group_field_original_translation() {
		$rfg_id   = sanitize_text_field( toolset_getpost( 'repeatable_group_id' ) );
		$meta_key = sanitize_text_field( toolset_getpost( 'field_meta_key' ) );
		
		$original_meta = apply_filters( 'wpml_custom_field_original_data', null, $rfg_id, $meta_key );

		if( ! isset( $original_meta['value'] ) ){
			$this->get_ajax_manager()->ajax_finish( __( 'Error. The original value could not be loaded.', 'wpcf' ) );
		}

		$field_slug = strpos( $meta_key, 'wpcf-' ) === 0
			? substr( $meta_key, strlen( 'wpcf-' ) )
			: $meta_key;

		$field_definitions = Toolset_Field_Definition_Factory_Post::get_instance();
		$field_definitions->load_all_definitions();
		$field_definition = $field_definitions->load_field_definition( $field_slug );


		$value = '';

		// checkboxes
		if( $field_definition->get_type()->get_slug() == 'checkboxes' ) {
			$field_def_array = $field_definition->get_definition_array();
			foreach( $field_def_array['data']['options'] as $option_slug => $option_data ){
				$value .= isset( $original_meta['value'][$option_slug] ) && ! empty( $original_meta['value'][$option_slug] )
					? '<i class="fa fa-check-square-o"></i><br />'
					: '<i class="fa fa-square-o"></i><br />';
			}

			$this->get_ajax_manager()->ajax_finish( $value );
		}

		// checkbox
		if( $field_definition->get_type()->get_slug() == 'checkbox' ) {
			$value =  ! empty( $original_meta['value'] )
				? '<i class="fa fa-check-square-o"></i>'
				: '<i class="fa fa-square-o"></i>';

			$this->get_ajax_manager()->ajax_finish( $value );
		}

		// radio
		if( $field_definition->get_type()->get_slug() == 'radio' ) {
			$field_def_array = $field_definition->get_definition_array();
			foreach( $field_def_array['data']['options'] as $option_slug => $option_data ){
				if( $option_slug == 'default' ) {
					continue;
				}

				$value .= $original_meta['value'] == $option_data['value']
					? '<i class="fa fa-dot-circle-o"></i><br />'
					: '<i class="fa fa-circle-o"></i><br />';
			}

			$this->get_ajax_manager()->ajax_finish( $value );
		}

		// radio
		if( $field_definition->get_type()->get_slug() == 'select' ) {
			$field_def_array = $field_definition->get_definition_array();
			foreach( $field_def_array['data']['options'] as $option_slug => $option_data ){
				if( $option_slug == 'default' ) {
					continue;
				}

				if( $original_meta['value'] == $option_data['value'] ) {
					$value = $option_data['title'];
					break;
				}
			}

			$this->get_ajax_manager()->ajax_finish( $value );
		}

		// date
		if( $field_definition->get_type()->get_slug() == 'date' ) {
			$value = is_array( $original_meta ) && ! empty( $original_meta['value'] )
				? date( get_option( 'date_format' ), $original_meta['value'] )
				: __( 'The original value is empty.', 'wpcf' );

			$this->get_ajax_manager()->ajax_finish( $value );
		}

		// all others
		$value = is_array( $original_meta ) && ! empty( $original_meta['value'] )
			? nl2br( stripslashes( $original_meta['value'] ) )
			: __( 'The original value is empty.', 'wpcf' );

		$this->get_ajax_manager()->ajax_finish( $value );
	}

	/**
	 * Detects relationship between two slugs and returns the first found relationship definition
	 *
	 * @param $parent_slug
	 * @param $child_slug
	 *
	 * @param string $domain
	 *
	 * @return bool|IToolset_Relationship_Definition
	 */
	private function get_relationship_definition( $parent_slug, $child_slug, $domain = Toolset_Element_Domain::POSTS ) {
		do_action( 'toolset_do_m2m_full_init' );

		$relationship_query = new Toolset_Relationship_Query_V2();
		$relationship_query->do_not_add_default_conditions();
		$relationship_query->add( $relationship_query->has_domain_and_type( $child_slug, $domain,
			new Toolset_Relationship_Role_Child() ) );

		$definitions = $relationship_query->get_results();

		foreach ( $definitions as $definition ) {
			if ( ! in_array( $parent_slug, $definition->get_parent_type()->get_types() ) ) {
				continue;
			}

			return $definition;
		}

		return false;
	}

	/**
	 * Get if current language is the default language
	 * @return bool
	 */
	private function is_default_language_active() {
		if ( $this->_is_default_language_active === null ) {
			$wpml_compatibility                = Toolset_Wpml_Compatibility::get_instance();
			$this->_is_default_language_active = $wpml_compatibility->get_current_language() === $wpml_compatibility->get_default_language();
		}

		return $this->_is_default_language_active;
	}

	/**
	 * Returns items of group
	 *
	 * @param WP_Post $parent_post
	 * @param Types_Field_Group_Repeatable $repeatable_group
	 *
	 * @return array
	 * @internal param $parent_post_type
	 */
	private function get_rfg_items( WP_Post $parent_post, Types_Field_Group_Repeatable $repeatable_group ) {
		$items = array();

		foreach ( (array) $repeatable_group->get_posts() as $rfg_item ) {
			$items[] = $this->get_rfg_item( $rfg_item->get_wp_post(), $parent_post, $repeatable_group );
		}

		return $items;
	}

	/**
	 * Single item by item (post) id
	 *
	 * @param WP_Post $item_post
	 * @param WP_Post $parent_post
	 * @param Types_Field_Group_Repeatable $repeatable_group
	 *
	 * @return array
	 */
	private function get_rfg_item(
		WP_Post $item_post,
		WP_Post $parent_post,
		Types_Field_Group_Repeatable $repeatable_group
	) {
		$item = array(
			'id'     => $item_post->ID,
			'title'  => ($item_post->post_title == $item_post->ID) ? '' : $item_post->post_title,
			'fields' => array()
		);

		foreach ( $repeatable_group->get_field_slugs() as $field_slug ) {

			if ( $nested_repeatable_group = $this->service_rg->get_object_from_prefixed_string( $field_slug,
				$item_post )
			) {
				// nested group
				$item['fields'][] = $this->format_rfg_for_response( $nested_repeatable_group, $parent_post,
					$item_post );
				continue;
			}

			// field
			$item['fields'][] = $this->format_rfg_field_for_response( $field_slug, $item_post->ID, $repeatable_group );
		}

		return $item;
	}

	/**
	 * Formats a repeatable field group to match the requirements of rfg.js
	 *
	 * @param Types_Field_Group_Repeatable $rfg
	 * @param WP_Post $belongs_to_post
	 * @param WP_Post $item
	 *
	 * @return array
	 */
	private function format_rfg_for_response(
		Types_Field_Group_Repeatable $rfg,
		WP_Post $belongs_to_post,
		WP_Post $item
	) {
		// field conditions
		$items = $this->get_rfg_items( $item, $rfg );
		$this->add_to_field_conditions_collection_by_items( $items );

		// return formated rfg
		return array(
			'repeatableGroup' => array(
				'id'                              => $rfg->get_id(),
				'parent_post_id'                  => $belongs_to_post->ID,
				'title'                           => $rfg->get_display_name(),
				'headlines'                       => $this->get_headlines_of_group( $rfg ),
				'controlsActive'                  => $this->is_default_language_active(),
				'items'                           => $items,
				'wpmlFilterExistsForOriginalData' => class_exists( 'WPML_Custom_Fields_Post_Meta_Info' ),
			)
		);
	}

	/**
	 * Formats a field to match the requirements of rfg.js
	 *
	 * @param $field_slug
	 * @param $belongs_to_post_id
	 * @param Types_Field_Group_Repeatable $rfg
	 *
	 * @return array
	 */
	private function format_rfg_field_for_response(
		$field_slug,
		$belongs_to_post_id,
		Types_Field_Group_Repeatable $rfg
	) {
		$field_definition_service = Toolset_Field_Definition_Factory_Post::get_instance();
		$field_definition         = $field_definition_service->load_field_definition( $field_slug );

		$field = $field_definition->instantiate( $belongs_to_post_id );

		// this is required to make WPML "Copy" "Copy once" work. The form manipulations are done by enlimbo
		// and this way we do not need to edit any existing code in Enlimbo.
		$_REQUEST['repeatable_group_item_post'] = get_post( $belongs_to_post_id );

		$wpml_is_copied = wpcf_wpml_field_is_copied( $field_definition->get_definition_array() );

		/** @var Toolset_Field_Renderer_Toolset_Forms_Repeatable_Group $renderer */
		$renderer = $field_definition
			->get_type()
			->get_renderer(
				Toolset_Field_Renderer_Purpose::INPUT_REPEATABLE_GROUP,
				\OTGS\Toolset\Common\Utils\RequestMode::ADMIN, $field,
				array( 'hide_field_title' => true )
			);

		$return = array(
			'title'        => $field_definition->get_display_name(),
			'metaKey'      => $field_definition->get_meta_key(),
			'value'        => $field->get_value(),
			'wpmlIsCopied' => $wpml_is_copied,
			'htmlInput'    => $renderer->render( false, $rfg->get_wp_post()->ID ),
			'fieldConfig'  => $renderer->get_field_config( $rfg->get_wp_post()->ID ),
		);

		$this->has_wysiwyg_field = ( $this->has_wysiwyg_field || $field_definition->get_type_slug() === Toolset_Field_Type_Definition_Factory::WYSIWYG );

		if( TOOLSET_TYPES_YOAST ) {
			$field_repository = new \OTGS\Toolset\Types\Compatibility\Yoast\Field\Repository(
				\Toolset_Field_Group_Post_Factory::get_instance(),
				new \OTGS\Toolset\Types\Compatibility\Yoast\Field\Factory()
			);
			if( $field_yoast = $field_repository->getFieldByDefinition( $field_definition, $belongs_to_post_id ) ) {
				$return['yoast'] = $field_yoast;
			}
		}

		return $return;
	}

	/**
	 * @param Types_Field_Group_Repeatable $group
	 *
	 * @return array
	 */
	private function get_headlines_of_group( $group ) {
		$headlines                = array();
		$field_definition_service = Toolset_Field_Definition_Factory_Post::get_instance();

		foreach ( $group->get_field_slugs() as $field_slug ) {
			if ( $nested_repeatable_group = $this->service_rg->get_object_from_prefixed_string( $field_slug, null,
				0 )
			) {
				$headlines[] = array( 'title' => $nested_repeatable_group->get_display_name() );
				continue;
			}

			$field_definition = $field_definition_service->load_field_definition( $field_slug );
			if ( $rfg_items = $group->get_posts() ) {
				$rfg_item       = reset( $rfg_items );
				$wpml_is_copied = wpcf_wpml_field_is_copied( $field_definition->get_definition_array(),
					$rfg_item->get_wp_post() );
			} else {
				$wpml_is_copied = false;
			}

			$headlines[] = array(
				'title'        => $field_definition->get_display_name(),
				'wpmlIsCopied' => $wpml_is_copied
			);
		}

		return $headlines;
	}

	/**
	 * Store that the user don't want to see the RFG Item introduction anymore
	 */
	private function json_repeatable_group_item_title_introduction_dismiss() {
		Toolset_Admin_Notices_Manager::dismiss_notice_by_id( self::NOTICE_KEY_FOR_RFG_ITEM_INTRODUCTION );
	}

	/**
	 * Update RFG Item title
	 */
	private function json_repeatable_group_item_title_update() {
		$item = get_post( toolset_getpost( 'item_id', null ) );
		$item_title = toolset_getpost( 'item_title', null );
		if ( ! $item || $item_title === null ) {
			$this->get_ajax_manager()
			     ->ajax_finish( __( 'Technical issue. Please reload the page and try again.', 'wpcf' ), false );
		}

		$result = $this->service_rg->update_item_title( $item, $item_title );

		if ( ! $result ) {
			$this->get_ajax_manager()
			     ->ajax_finish( __( 'Technical issue. Please reload the page and try again.', 'wpcf' ), false );
		}

		// update translation posts (the title is only editable on the default language)
		$wpml = Toolset_Wpml_Compatibility::get_instance();
		$translated_post_ids = $wpml->get_post_translations_directly( $item->ID );

		foreach( $translated_post_ids as $translated_post_id ) {
			if( $translated_post_id == $item->ID ) {
				// this is the original post, no need to update it again
				continue;
			}

			if( ! $translated_post = get_post( $translated_post_id ) ) {
				// no post for $translated_post_id found
				continue;
			}

			// update the translation
			$this->service_rg->update_item_title( $translated_post, $item_title );
		}

		// title updated
		$this->get_ajax_manager()->ajax_finish( 'Title updated.' );
	}


	/**
	 * If there has been a WYSIWYG field added during processing this AJAX call, append settings for the TinyMCE toolbar
	 * to the response array.
	 *
	 * @param array $response Associative array with the AJAX call response.
	 *
	 * @return array Modified response.
	 * @since 3.3.1
	 */
	private function maybe_add_tinymce_settings( $response ) {
		if ( $this->has_wysiwyg_field ) {
			$tinymce_helper = new Types_Helper_TinyMCE();
			$response['tinyMCEToolbarSettings'] = $tinymce_helper->get_toolbar_settings_for_dynamic_tinymce();
		}

		return $response;
	}
}
