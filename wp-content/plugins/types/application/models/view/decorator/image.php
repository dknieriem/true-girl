<?php

/**
 * Class Types_View_Decorator_Image
 *
 * @since 2.3
 */
class Types_View_Decorator_Image implements Types_Interface_Value {

	/**
	 * @param string $value
	 * @param array $params
	 *
	 * @return array|mixed|string
	 */
	public function get_value( $value = '', $params = array() ) {
		while( is_array( $value ) ) {
			$value = array_shift( $value );
		}
		
		if( empty( $value ) ) {
			return '';
		}

		if( strpos( $value, '<img' ) !== false || ( isset( $params['url'] ) && $params['url'] ) ) {
			// already a rendered image or raw url is wished
			return $value;
		}

		$parameters = isset( $params['alt'] ) && ! empty( $params['alt'] )
			? ' alt="' . $params['alt'] . '"'
			: '';

		$parameters .= isset( $params['title'] ) && ! empty( $params['title'] )
			? ' title="' . $params['title'] . '"'
			: '';

		$parameters .= isset( $params['onload'] ) && ! empty( $params['onload'] )
			? ' onload="' . esc_attr( $params['onload'] ) . '"'
			: '';

		$parameters .= isset( $params['class'] ) && ! empty( $params['class'] )
			? ' class="' . esc_attr( $params['class'] ) . '"'
			: '';

		$parameters .= isset( $params['style'] ) && ! empty( $params['style'] )
			? ' style="' . esc_attr( $params['style'] ) . '"'
			: '';

		return '<img src="' . $value . '"' . $parameters . ' />';
	}
}