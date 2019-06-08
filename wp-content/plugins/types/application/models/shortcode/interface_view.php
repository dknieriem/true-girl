<?php

/**
 * Interface Types_Shortcode_Interface_View
 *
 * @since 2.3
 */
interface Types_Shortcode_Interface_View {
	/**
	 * @param $atts
	 * @param $content
	 *
	 * @return mixed
	 */
	public function render( $atts, $content );
}