<?php

/**
 * Class Types_Field_Type_Checkboxes_View_Frontend
 *
 * Handles view specific tasks for field "Checkboxes"
 *
 * @since 2.3
 */
class Types_Field_Type_Checkboxes_View_Frontend extends Types_Field_Type_View_Frontend_Abstract {

	/**
	 * Types_Field_Type_Checkboxes_View_Frontend constructor.
	 *
	 * @param Types_Field_Type_Checkboxes $entity
	 * @param array $params
	 */
	public function __construct( Types_Field_Type_Checkboxes $entity, $params = array() ) {
		$this->entity = $entity;
		$this->params = $this->normalise_user_values( $params );
	}

	/**
	 * @return string
	 */
	public function get_value() {
		$options = $this->entity->get_options();

		if( empty( $options ) ) {
			return '';
		}

		if ( $this->is_raw_output() ) {
			$values = $this->get_value_raw( $options );
		} elseif( isset( $this->params['option'] ) ) {
			$values = $this->get_value_single_option( $options );
		} else {
			$values = $this->get_value_all_options( $options );
		}

		if ( $this->empty_values( $values ) ) {
			return '';
		}
		$filtered = array();
		foreach( (array) $values as $value ) {
			$filtered[] = $this->filter_field_value_after_decorators( $value );
		}

		if( empty( $filtered ) ) {
			return '';
		}

		$this->params['separator'] = isset( $this->params['separator'] )
			? $this->params['separator']
			: ', ';

		$filtered = array_filter( $filtered, function( $v ) {
			return ! empty( $v );
		} );

		$value = is_array( $filtered )
			? implode( $this->params['separator'], $filtered )
			: $filtered;

		$value = $this->get_decorated_value( $value );

		return $this->maybe_show_field_name( $value );
	}

	/**
	 * @param Types_Field_Part_Option[] $options
	 *
	 * @return string
	 */
	private function get_value_raw( $options ) {
		$to_display = array();

		foreach( $options as $id => $option ) {
			$to_display[] = $option->get_value_raw();
		}

		$to_display = array_values( $to_display );

		return $to_display;
	}

	/**
	 * @param Types_Field_Part_Option[] $options
	 *
	 * @return string
	 */
	private function get_value_all_options( $options ) {
		unset( $this->params['state'] );

		$to_display = array();
		foreach( $options as $id => $option ) {
			$to_display[] = $this->maybe_skip_empty_display_value( $option );
		}

		$to_display = array_filter( $to_display, array( $this, 'maybe_skip_save_empty_value' ) );
		$to_display = array_values( $to_display );

		if( empty( $to_display ) ) {
			return '';
		}

		return $to_display;
	}

	/**
	 * Make sure that alternative outcome values for unchecked checkboxes get skipped by setting them to FALSE.
	 *
	 * @param Types_Field_Part_Option $option
	 *
	 * @return string|bool
	 */
	private function maybe_skip_empty_display_value( $option ) {
		$option_data = $option->get_data();
		$display_candidate = $option->get_value_filtered( $this->params );

		if (
			isset( $option_data['display'] )
			&& $option_data['display'] !== Types_Field_Abstract::DISPLAY_MODE_DB
		) {
			if ( ! $option->is_active() ) {
				return false;
			}
			return empty( $display_candidate ) ? false : $display_candidate;
		}

		return $display_candidate;
	}

	/**
	 * Make sure that we do not output anything for unchecked instances when they store zero in the database.
	 *
	 * @param string
	 *
	 * @return bool
	 */
	private function maybe_skip_save_empty_value( $value ) {
		$field_data = $this->entity->to_array();
		if (
			isset( $field_data['data']['save_empty'] )
			&& $field_data['data']['save_empty'] == 'yes'
			&& $value == '0'
		) {
			return false;
		}
		return true;
	}

	/**
	 * @param Types_Field_Part_Option[] $options
	 *
	 * @return string
	 * @throws Exception
	 */
	private function get_value_single_option( $options ){
		$options_keys = array_keys( $options );
		if( ! isset( $options_keys[$this->params['option']] ) ) {
			// user-selected-option does not exist
			return '';
		}

		/** @var Types_Field_Part_Option $option */
		$option = $options[$options_keys[$this->params['option']]];

		if( ! $option instanceof Types_Field_Part_Option ) {
			// something went wrong
			throw new Exception( '$options must be an array of Types_Field_Part_Option' );
		}

		if( isset( $this->params['state'] ) ) {
			$decorator = new Types_View_Decorator_Option_State( $option, $this->params );
			return $decorator->get_value();
		}

		$to_display = $option->get_value_filtered( $this->params );
		return ! empty( $to_display ) ? $to_display : '';
	}
}
