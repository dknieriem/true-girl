<?php

/**
 * Class Types_Field_Group_Repeatable_Wpml_Translation_Job_Data
 *
 * @since m2m
 */
class Types_Field_Group_Repeatable_Wpml_Translation_Job_Data {
	const HASH_PART_POSTID = '__postid__';
	const HASH_PART_FIELDSLUG = '__fieldslug__';

	/**
	 * @var Types_Field_Abstract[]
	 */
	private $fields = array();

	/**
	 * @var Toolset_Field_Definition_Factory_Interface
	 */
	private $field_definitions;

	/**
	 * Types_Field_Group_Repeatable_Wpml_Translation_Job_Data constructor.
	 *
	 * @param Toolset_Field_Definition_Factory_Interface $field_definitions
	 */
	public function __construct( Toolset_Field_Definition_Factory_Interface $field_definitions = null ) {
		$field_definitions = $field_definitions ?: Toolset_Field_Definition_Factory_Post::get_instance();

		$this->field_definitions = $field_definitions;
	}

	/**
	 * Add repeatable field group items to parent post translation job
	 *
	 * @action wpml_tm_adjust_translation_fields
	 *
	 * @param $package
	 * @param WP_Post|WPML_Package $post
	 *
	 * @param Types_Post_Builder $types_post_builder
	 * @param WPML_Translation_Job_Helper $translation_job_helper
	 *
	 * @return array
	 */
	public function wpml_tm_translation_job_data(
		$package,
		$post,
		Types_Post_Builder $types_post_builder,
		WPML_Translation_Job_Helper $translation_job_helper
	) {
		if( ! $post instanceof WP_Post ) {
			// Something else than a normal post is being translated. We do nothing because these things
			// can't have RFGs.
			//
			// This can happen when a Layout is sent to translation, for example.
			return $package;
		}

		$types_post_builder->set_wp_post( $post );
		$types_post_builder->load_assigned_field_groups( 9999 );
		$types_post   = $types_post_builder->get_types_post();
		$field_groups = $types_post->get_field_groups();

		foreach ( $field_groups as $field_group ) {
			if( ! $rfgs = $field_group->get_repeatable_groups() ) {
				// no repeatable field groups
				continue;
			}

			foreach ( $rfgs as $rfg ) {
				$package = $this->add_rfg_items( $package, $rfg, $translation_job_helper );
			}
		}

		return $package;
	}

	/**
	 * Adjust the titles our previously added fields
	 *
	 * @action wpml_tm_adjust_translation_fields
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public function wpml_tm_adjust_translation_fields( $fields ) {
		foreach ( $fields as $key => $field ) {
			if( ! $hash_resolved = $this->resolve_translation_hash( $field['field_type'] ) ) {
				// no rfg item field
				continue;
			}

			$field_definition = $this->field_definitions->load_field_definition( $hash_resolved['fieldslug'] );

			if( $field_definition ) {
				$fields[ $key ]['title'] = $field_definition->get_display_name();
			}
		}

		return $fields;
	}

	/**
	 * Update fields when the job is done
	 *
	 * @action wpml_pro_translation_completed
	 *
	 * @param $new_post_id
	 * @param $fields
	 * @param $job
	 */
	public function wpml_pro_translation_completed( $new_post_id, $fields, $job ) {
		foreach ( $fields as $field_hash => $field_data ) {
			if ( ! $field_details = $this->resolve_translation_hash( $field_hash ) ) {
				// no rfg item field
				continue;
			}

			$id_source_lang = $field_details['id'];
			$id_target_lang = apply_filters(
				'wpml_object_id',
				$id_source_lang,
				'any',
				false, // $return_original_if_missing
				$job->language_code
			);

			$field_definition = $this->field_definitions->load_field_definition( $field_details['fieldslug'] );

			if ( $id_target_lang ) {
				$field_slug = $field_definition->get_meta_key();
				update_post_meta( $id_target_lang, $field_slug, $field_data['data'] );
			}
		}
	}

	/**
	 * @param array $package
	 * @param Types_Field_Group_Repeatable $rfg
	 * @param WPML_Translation_Job_Helper $translation_job_helper
	 *
	 * @return mixed
	 */
	private function add_rfg_items(
		array $package,
		Types_Field_Group_Repeatable $rfg,
		WPML_Translation_Job_Helper $translation_job_helper
	) {
		if( ! $posts = $rfg->get_posts() ) {
			return $package;
		}

		foreach ( $posts as $rfg_item ) {
			foreach ( $rfg_item->get_fields() as $field ) {
				if ( ! $field->is_translatable() ) {
					continue;
				}

				$field_unique_translation_id = $this->get_translation_hash( $rfg_item->get_wp_post(), $field->get_slug() );

				// save field by $field_unique_translation_id
				$this->fields[ $field_unique_translation_id ] = $field;

				$field_value = $field->get_value();
				$field_value = ! empty( $field_value )
					? reset( $field_value )
					: '';

				$package['contents'][ $field_unique_translation_id ] = array(
					'translate' => 1,
					'data'      => $translation_job_helper->encode_field_data( $field_value ),
					'format'    => 'base64'
				);
			}

			$nested_repeatable_fields_groups = $rfg_item->get_field_groups();
			foreach ( $nested_repeatable_fields_groups as $nested_rfg ) {
				$package = $this->add_rfg_items( $package, $nested_rfg, $translation_job_helper );
			}
		}

		return $package;
	}

	/**
	 * @param WP_Post $post
	 * @param $field_slug
	 *
	 * @return string
	 */
	private function get_translation_hash( WP_Post $post, $field_slug ) {
		return self::HASH_PART_POSTID . $post->ID . self::HASH_PART_FIELDSLUG . $field_slug;
	}

	/**
	 * @param $hash
	 *
	 * @return array|bool
	 */
	private function resolve_translation_hash( $hash ) {
		if ( ! strpos( $hash, self::HASH_PART_FIELDSLUG ) ) {
			// no valid hash (less expensive check, before doing heavy process)
			return false;
		}

		$pattern = '#' . self::HASH_PART_POSTID . '(.*)' . self::HASH_PART_FIELDSLUG . '(.*)#u';
		if ( preg_match( $pattern, $hash, $matches ) ) {
			return array( 'id' => $matches[1], 'fieldslug' => $matches[2] );
		};

		return false;
	}
}
