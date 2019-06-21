<?php

namespace OTGS\Toolset\Maps\Model\Distance;

use Toolset_Maps_Location;

/**
 * Just a small helper to make distance calculation available in all the places it's needed without duplication.
 *
 * Class Calculator
 * @package OTGS\Toolset\Maps\Model\Distance
 */
class Calculator {
	/**
	 * @param Toolset_Maps_Location $center_coords
	 * @param Toolset_Maps_Location $address_coords
	 * @param string $unit km|mi
	 *
	 * @return float|int
	 */
	public static function calculate(
		Toolset_Maps_Location $center_coords, Toolset_Maps_Location $address_coords, $unit = 'km'
	) {
		$earth_radius = ( $unit == 'mi' ? 3963.0 : 6371 );

		$lat_diff = deg2rad( $address_coords->get_lat() - $center_coords->get_lat() );
		$lon_diff = deg2rad( $address_coords->get_lng() - $center_coords->get_lng() );

		$lat_lon_delta = sin( $lat_diff / 2 ) * sin( $lat_diff / 2 ) + cos( deg2rad( $center_coords->get_lat() ) ) * cos( deg2rad( $address_coords->get_lat() ) ) * sin( $lon_diff / 2 ) * sin( $lon_diff / 2 );
		$lat_lon_angle = 2 * asin( sqrt( $lat_lon_delta ) );
		$distance      = $earth_radius * $lat_lon_angle;

		return $distance;
	}
}