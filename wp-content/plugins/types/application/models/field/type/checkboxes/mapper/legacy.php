<?php

/**
 * Class Types_Field_Type_Checkboxes_Mapper_Legacy
 *
 * Mapper for "Checkboxes" field
 *
 * @since 2.3
 */
class Types_Field_Type_Checkboxes_Mapper_Legacy extends Types_Field_Mapper_Abstract {

	/**
	 * @var Types_Field_Type_Checkboxes_Factory
	 */
	protected $field_factory;

	public function find_by_id( $id, $id_post ) {
		if( ! $field = $this->database_get_field_by_id( $id ) ) {
			return null;
		};

		if( $field['type'] !== 'checkboxes' ) {
			throw new Exception( 'Types_Field_Type_Checkboxes_Mapper_Legacy can not map type: ' . $field['type'] );
		}

		$field = $this->map_common_field_properties( $field );

		$values = array();
		$options = isset( $field['data']['options'] )
		    ? $field['data']['options']
			: array();

		$controlled = $this->is_controlled_by_types( $field );
		if( $value = $this->get_user_value( $id_post, $field['slug'], $field['repeatable'], $controlled ) ) {
			$values = is_array( $value ) && array_key_exists( 0, $value ) && ! array_key_exists( 1, $value )
				? $value[0]
				: $value;

			if ( is_array( $values ) ) {
				foreach( $values as $key => $val ) {
					$values[$key] = is_array( $val ) && array_key_exists( 0, $val ) && ! array_key_exists( 1, $val )
						? $val[0]
						: $val;
				}
			}
		}

		$entity = $this->field_factory->get_field( $field );

		foreach( $options as $option_id => $option_data ) {
			$option_data['id'] = $option_id;
			$option_data['checked'] = false;

			if(
				isset( $values[ $option_id ] ) // there is a value stored
			    && ! ( // and the value is not ...
			    	$values[ $option_id ] == 0 // ... 0 ...
				    && $field['data']['save_empty'] == 'yes' // ... while "save 0" is active.
				)
			) {
				// it's checked
				$option_data['checked'] = true;
			}

			$option_data['db_value'] = isset( $values[ $option_id ] )
				? $values[ $option_id ]
				: null;

			if( isset( $option_data['set_value'] ) ) {
				$option_data['store_value'] = $option_data['set_value'];
			}
			if( isset( $option_data['display_value_selected'] ) ) {
				$option_data['display_value_checked'] = $option_data['display_value_selected'];
			}
			if( isset( $option_data['display_value_not_selected'] ) ) {
				$option_data['display_value_unchecked'] = $option_data['display_value_not_selected'];
			}

			$option = new Types_Field_Part_Option( $entity, $option_data );
			$entity->add_option( $option );
		}

		return $entity;
	}
}
