<?php

/**
 * Class Types_Field_Type_Image_Factory
 *
 * @since 2.3
 */
class Types_Field_Type_Image_Factory implements Types_Field_Factory_Interface {

	/**
	 * @param array $data
	 *
	 * @return Types_Field_Type_Image
	 */
	public function get_field( $data = array() ) {
		// field is repeatable
		$repeatable = new Types_Field_Setting_Repeatable( $data );
		$data['repeatable'] = $repeatable;

		return new Types_Field_Type_Image( $data );
	}

	/**
	 * @param Types_Field_Gateway_Interface $gateway
	 *
	 * @return Types_Field_Type_Image_Mapper_Legacy
	 */
	public function get_mapper( Types_Field_Gateway_Interface $gateway ) {
		return new Types_Field_Type_Image_Mapper_Legacy( $this, $gateway );
	}

	/**
	 * @param Types_Field_Interface $field
	 * @param $user_params
	 *
	 * @return Types_Field_Type_Image_View_Frontend|false
	 */
	public function get_view_frontend( Types_Field_Interface $field, $user_params ) {
		if( ! $field instanceof Types_Field_Type_Image ) {
			return false;
		}

		$view = new Types_Field_Type_Image_View_Frontend(
			$field,
			new Types_Wordpress_Media(),
			new Types_Media_Service(),
			new Types_Site_Domain( get_site_url() ),
			new Types_View_Placeholder_Media(),
			new Types_View_Decorator_Image(),
		    new Types_View_Decorator_Index(),
		    $user_params
		);

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