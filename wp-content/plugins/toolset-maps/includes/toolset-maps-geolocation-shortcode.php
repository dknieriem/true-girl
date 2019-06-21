<?php
/**
 * Shortcode to ensure everything inside will have backend access to visitors location.
 *
 * @package ToolsetMaps
 *
 * @since 1.5
 */
class Toolset_Maps_Geolocation_Shortcode {
	/** @var Toolset_Maps_Location $location */
	protected $location = null;

	/**
	 * Toolset_Maps_Geolocation constructor.
	 */
	public function __construct() {
		$this->location = Toolset_Maps_Location_Factory::create_from_cookie();

		add_action( 'init',	array( $this, 'init' ) );
	}

	/**
	 * Do stuff on WP init event
	 */
	public function init() {
		add_shortcode( 'wpv-geolocation', array( $this, 'wpv_geolocation_shortcode' ) );

		// Currently used only here, but if some other code starts using this library, it's a candidate for Toolset
		// Common. https://github.com/js-cookie/js-cookie
		wp_register_script(
			'cookie',
			TOOLSET_ADDON_MAPS_URL_JS . 'js.cookie-2.2.0.min.js',
			array(),
			'2.2.0'
		);

		wp_register_script(
			'toolset-maps-location',
			TOOLSET_ADDON_MAPS_URL_JS . 'toolset_maps_location.js',
			array('jquery', 'cookie'),
			TOOLSET_ADDON_MAPS_VERSION,
			true
		);
	}

	/**
	 * Renders wpv-geolocation shortcode
	 * @param array $atts
	 * @param string|null $content
	 * @return string
	 */
	public function wpv_geolocation_shortcode( $atts, $content = null ) {
		$geo_atts = shortcode_atts(
			array(
				'message_when_missing' => __('Your location is needed to show this content.', 'toolset-maps')
			),
			$atts
		);

		wp_enqueue_script( 'toolset-maps-location' );

		if ( $this->location ) {
			return do_shortcode( $content );
		}
		return $this->render_location_obtainer( $geo_atts['message_when_missing'] );
	}

	/**
	 * Returns message that location is needed and inits JS to obtain it (with reload)
	 * @param string $message
	 * @return string
	 */
	protected function render_location_obtainer( $message ) {
		$js = <<<JS
			$( document ).ready( function() {
				$( window ).load( function() {
					WPViews.location.initWithReload();
				} );
			} );
JS;

		wp_add_inline_script(
			'toolset-maps-location',
			$js
		);

		return $message;
	}
}

$Toolset_Maps_Geolocation_Shortcode = new Toolset_Maps_Geolocation_Shortcode();