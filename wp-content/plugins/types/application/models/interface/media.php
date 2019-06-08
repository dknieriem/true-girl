<?php

/**
 * Interface Types_Interface_Media
 *
 * @since 2.3
 */
interface Types_Interface_Media  {
	/**
	 * @param $id
	 */
	public function set_id( $id );

	/**
	 * @return int
	 */
	public function get_id();

	/**
	 * @return string
	 */
	public function get_url();

	/**
	 * @return string
	 */
	public function get_title();

	/**
	 * @return string
	 */
	public function get_description();

	/**
	 * @return string
	 */
	public function get_alt();

	/**
	 * @return string
	 */
	public function get_caption();
}