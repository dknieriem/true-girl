<?php

/**
 * Class Toolset_Maps_Location_Factory
 *
 * Creates Toolset_Maps_Location objects, with checking, so you can be pretty sure you have valid lat/lng available in
 * there. Also exposes its coordinates checking methods, if needed somewhere.
 *
 * @since 1.5
 */
class Toolset_Maps_Location_Factory {
	const COOKIE_NAME = 'toolset_maps_location';

	/**
	 * @param float $lat
	 * @param float $lng
	 *
	 * @return null|Toolset_Maps_Location
	 */
	public static function create( $lat, $lng ) {
		if ( self::are_valid_coords( $lat, $lng) ) {
			return new Toolset_Maps_Location( $lat, $lng );
		} else {
			return null;
		}
	}

	/**
	 * @return null|Toolset_Maps_Location
	 */
	public static function create_from_cookie() {
		if ( ! array_key_exists( self::COOKIE_NAME, $_COOKIE ) ) {
			return null;
		}

		$latLng = explode( ',', $_COOKIE[self::COOKIE_NAME] );
		$lat = filter_var( $latLng[0], FILTER_VALIDATE_FLOAT );
		$lng = filter_var( $latLng[1], FILTER_VALIDATE_FLOAT );

		if (
			$lat === false
			||$lng === false
			|| ! self::are_valid_coords( $lat, $lng )
		) {
			return null;
		}

		return new Toolset_Maps_Location( $lat, $lng );
	}

	/**
	 * @param string $address
	 *
	 * @return null|Toolset_Maps_Location
	 */
	public static function create_from_address( $address ) {
		if ( !$address ) return null; // An empty address would hit the API otherwise. We can cut it here.

		$coordinates = Toolset_Addon_Maps_Common::get_coordinates( $address );

		if ( is_array( $coordinates ) ) {
			return self::create( $coordinates['lat'], $coordinates['lon'] );
		} else {
			return null;
		}
	}

	/**
	 * @param float $latitude
	 *
	 * @return bool
	 */
	public static function is_valid_latitude( $latitude ) {
		return (bool) ( preg_match( "/^-?(0|[1-8]?[1-9]|[1-9]0)(\.{1}\d{1,20})?$/", $latitude ) );
	}

	/**
	 * @param float $longitude
	 *
	 * @return bool
	 */
	public static function is_valid_longitude( $longitude ) {
		return (bool) ( preg_match( "/^-?([0-9]|[1-9][0-9]|[1][0-7][0-9]|180)(\.{1}\d{1,20})?$/", $longitude ) );
	}

	/**
	 * @param float $lat
	 * @param float $lng
	 *
	 * @return bool
	 */
	public static function are_valid_coords( $lat, $lng ) {
		return ( self::is_valid_latitude( $lat ) && self::is_valid_longitude( $lng ) );
	}
}