<?php

/**
 * Interface Types_Field_Gateway_Interface
 *
 * @since 2.3
 */
interface Types_Field_Gateway_Interface {
	/**
	 * @param $id
	 *
	 * @return null|array
	 */
	public function get_field_by_id( $id );

	/**
	 * @return array
	 */
	public function get_fields();

	/**
	 * @param $id
	 * @param $field_slug
	 *
	 * @return array
	 */
	public function get_field_user_value( $id, $field_slug );
}