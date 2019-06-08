<?php

/**
 * Class Types_View_Decorator_Separator
 *
 * @since 2.3
 */
class Types_View_Decorator_Separator implements Types_Interface_Value {

	/**
	 * @param string $value
	 * @param array $params
	 * @param string $field_type
	 *
	 * @return string
	 */
	public function get_value( $value = '', $params = array(), $field_type = '' ) {
		if( ! is_array( $value ) ) {
			return $value;
		}

		return implode( $this->get_separator( $params, $field_type ), $value );
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function get_separator( $params, $field_type ) {
		if( ! isset( $params['separator'] ) ) {
			return ' ';
		}

		if ( isset( $params['output'] ) && 'html' === $params['output'] ) {
			$class_field_separator = 'wpcf-field-separator ' .
				'wpcf-field-' . $field_type . '-separator';
			return '<span class="' . esc_attr( $class_field_separator ) . '">' . $params['separator'] . '</span>';
		}

		return $params['separator'];
	}
}
