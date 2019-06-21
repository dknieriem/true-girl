<?php
namespace OTGS\Toolset\Maps\Model\Shortcode;

/**
 * Interface ShortcodeInterface
 * @package OTGS\Toolset\Maps\Model\Shortcode
 * @since 1.6
 */
interface ShortcodeInterface {
	/**
	 * Should return shortcode output
	 * @return string
	 */
	public function render();

	/**
	 * Must return an array of shortcode defaults. Useful for testing and building GUI, therefore static.
	 * @return array
	 */
	public static function get_defaults();
}