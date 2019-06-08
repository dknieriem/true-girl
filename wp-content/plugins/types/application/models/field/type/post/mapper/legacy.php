<?php

/**
 * Class Types_Field_Type_Post_Mapper_Legacy
 *
 * Mapper for "Post" field
 *
 * @since 2.3
 */
class Types_Field_Type_Post_Mapper_Legacy extends Types_Field_Mapper_Abstract {

	/**
	 * @var Types_Field_Type_Post_Factory
	 */
	protected $field_factory;

	/**
	 * @param $id
	 * @param $id_post
	 *
	 * @return null|Types_Field_Type_Post
	 * @throws Exception
	 */
	public function find_by_id( $id, $id_post ) {
		if( ! $field = $this->database_get_field_by_id( $id ) ) {
			return null;
		};

		if( $field['type'] !== 'post' ) {
			throw new Exception( 'Types_Field_Type_Post_Mapper_Legacy can not map type: ' . $field['type'] );
		}

		$field = $this->map_common_field_properties( $field );

		if( isset( $field['data'] ) && isset( $field['data']['post_reference_type'] ) ) {
			$field['post_reference_type'] = $field['data']['post_reference_type'];
		}

		$relationship_repository = Toolset_Relationship_Definition_Repository::get_instance();

		if( $relationship = $relationship_repository->get_definition( $id ) ) {
			$associations_query = new Toolset_Association_Query_V2();
			$user_selected_post = $associations_query
				->add( $associations_query->child_id( $id_post ) )
				->add( $associations_query->relationship( $relationship ) )
				->limit( 1 )
				->return_element_ids( new Toolset_Relationship_Role_Parent() )
				->get_results();

			if( ! empty( $user_selected_post ) ) {
				$field['value'] =  strval( $user_selected_post[0] );
			}
		}

		$entity = $this->field_factory->get_field( $field );
		return $entity;
	}
}