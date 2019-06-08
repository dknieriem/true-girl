<?php

/**
 * Interface Types_Field_Interface
 *
 * @since 2.3
 */
interface Types_Field_Interface extends Types_Interface_Value {
	/**
	 * @return string
	 */
	public function get_slug();

	/**
	 * @return array|string
	 */
	public function get_title();

	/**
	 * User stored value with applied display filters
	 * @return array|string
	 */
	public function get_value_filtered();

	/**
	 * @return bool
	 */
	public function is_repeatable();

	/**
	 * @return string
	 */
	public function get_type();


	/**
	 * @return string
	 */
	public function get_description();
}