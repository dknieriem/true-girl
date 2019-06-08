<?php

/**
 * Class Types_Field_Type_Post_Factory
 *
 * @since 2.3
 */
class Types_Field_Type_Post_Factory implements Types_Field_Factory_Interface {

	/**
	 * @param array $data
	 *
	 * @return Types_Field_Type_Post
	 */
	public function get_field( $data = array() ) {
		return new Types_Field_Type_Post( $data );
	}

	/**
	 * @param Types_Field_Gateway_Interface $gateway
	 *
	 * @return Types_Field_Type_Post_Mapper_Legacy
	 */
	public function get_mapper( Types_Field_Gateway_Interface $gateway ) {
		return new Types_Field_Type_Post_Mapper_Legacy( $this, $gateway );
	}

	/**
	 * @param Types_Field_Interface $field
	 * @param $user_params
	 *
	 * @return Types_Interface_Value|false
	 */
	public function get_view_frontend( Types_Field_Interface $field, $user_params ) {
		if( ! $field instanceof Types_Field_Type_Post) {
			return false;
		}

		$view = new Types_Field_Type_Post_View_Frontend( $field, $user_params );

		return $view;
	}

	/**
	 * @param Types_Field_Interface $field
	 *
	 * @return Types_Field_Type_Post_View_Backend_Display|false
	 */
	public function get_view_backend_display( Types_Field_Interface $field ) {
		if( ! $field instanceof Types_Field_Type_Post ) {
			return false;
		}

		return new Types_Field_Type_Post_View_Backend_Display( $field );
	}

	/**
	 * @param Types_Field_Interface $field
	 *
	 * @return Types_Field_Type_Post_View_Backend_Creation|false
	 */
	public function get_view_backend_creation( Types_Field_Interface $field ) {
		if( ! $field instanceof Types_Field_Type_Post ) {
			return false;
		}

		return new Types_Field_Type_Post_View_Backend_Creation( $field );
	}
}