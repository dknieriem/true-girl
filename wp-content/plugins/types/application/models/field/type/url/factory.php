<?php


class Types_Field_Type_Url_Factory implements Types_Field_Factory_Interface {

	/**
	 * @param array $data
	 *
	 * @return Types_Field_Type_Url
	 */
	public function get_field( $data = array() ) {
		// field is repeatable
		$repeatable = new Types_Field_Setting_Repeatable( $data );
		$data['repeatable'] = $repeatable;

		return new Types_Field_Type_Url( $data );
	}

	/**
	 * @param Types_Field_Gateway_Interface $gateway
	 *
	 * @return Types_Field_Type_Url_Mapper_Legacy
	 */
	public function get_mapper( Types_Field_Gateway_Interface $gateway ) {
		return new Types_Field_Type_Url_Mapper_Legacy( $this, $gateway );
	}

	/**
	 * @param Types_Field_Interface $field
	 * @param $user_params
	 *
	 * @return Types_Field_Type_Url_View_Frontend|false
	 */
	public function get_view_frontend( Types_Field_Interface $field, $user_params ) {
		if( ! $field instanceof Types_Field_Type_Url ) {
			return false;
		}

		$view = new Types_Field_Type_Url_View_Frontend( $field, $user_params );

		return $view;
	}

	/**
	 * @param Types_Field_Interface $field
	 *
	 * @return Types_Interface_Value|false
	 */
	public function get_view_backend_display( Types_Field_Interface $field ) {
		// TODO: Implement getViewBackendDisplay() method.
		return false;
	}

	/**
	 * @param Types_Field_Interface $field
	 *
	 * @return Types_Interface_Value|false
	 */
	public function get_view_backend_creation( Types_Field_Interface $field ) {
		// TODO: Implement getViewBackendCreation() method.
		return false;
	}
}