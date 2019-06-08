<?php

namespace OTGS\Toolset\Common\PublicAPI;

/**
 * Public interface for a custom field definition.
 *
 * Safe to be used in third-party software.
 *
 * @package OTGS\Toolset\Common\PublicAPI
 * @since 3.4 (Types 3.3)
 */
interface CustomFieldDefinition {


	/**
	 * Display name of the field definition.
	 *
	 * @return string
	 */
	public function get_name();


	/**
	 * Field slug.
	 *
	 * @return string
	 */
	public function get_slug();


	/**
	 * Slug of the field type.
	 *
	 * @return string
	 */
	public function get_type_slug();


	/**
	 * Is the field repeatable - can it have multiple values?
	 *
	 * Do not confuse with repeatable field groups.
	 *
	 * @return bool
	 */
	public function is_repeatable();

}
