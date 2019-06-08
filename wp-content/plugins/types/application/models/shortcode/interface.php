<?php


/**
 * Interface Types_Shortcode_Interface
 *
 * @since 2.3
 */
interface Types_Shortcode_Interface {
	/**
	 * @param $atts
	 * @param $content
	 *
	 * @return mixed
	 */
	public function get_value( $atts, $content );
}