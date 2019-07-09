<?php

/**
 * class WPV_Shortcode_Base_GUI
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Base_GUI implements WPV_Shortcode_Interface_GUI  {
	
	/**
	 * WPV_Shortcode_Base_GUI constructor.
	 */
	public function __construct() {
		add_filter( 'wpv_filter_wpv_shortcodes_gui_data', array( $this, 'register_shortcode_data' ) );
	}
	
	/**
	 * @param $views_shortcodes
	 *
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function register_shortcode_data( $views_shortcodes ) {
		return $views_shortcodes;
	}
	
	/**
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function get_shortcode_data() {
		return;
	}
	
}