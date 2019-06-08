<?php

/**
 * Interface Types_Field_Part_Interface
 *
 * @since 2.3
 */
interface Types_Field_Part_Interface {
	/**
	 * @return int
	 */
	public function get_id();

	/**
	 * @return bool
	 */
	public function is_active();

	/**
	 * @return string
	 */
	public function get_value();

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function get_value_filtered( $value );
}