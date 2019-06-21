<?php
namespace OTGS\Toolset\Maps\Model\Shortcode\Distance;

use OTGS\Toolset\Maps\Model\Shortcode\ShortcodeAbstract;
use OTGS\Toolset\Maps\Model\Distance;
use Toolset_Maps_Location_Factory;
use Toolset_Maps_Location;

/**
 * Implements the distance conditional display shortcode and a public function to do the checking that can be used in
 * wpv-conditional.
 *
 * @package OTGS\Toolset\Maps\Model\Shortcode\Distance
 * @since 1.6
 */
class ConditionalDisplay extends ShortcodeAbstract {

	public static function get_defaults() {
		return array(
			'distance' => 5,
			'location' => '',
			'unit'     => 'km',
			'display'  => 'inside'
		);
	}

	/**
	 * Render content conditionally, based on distance from visitor location to given location.
	 * @return string
	 */
	public function render() {
		if ( $this->is_content_allowed() ) {
			return do_shortcode( $this->content );
		}
		return '';
	}

	/**
	 * Checks if content is allowed to be shown, given parameters.
	 *
	 * @return bool
	 */
	public function is_content_allowed() {
		// No location to compare to given to shortcode, bail out
		if ( !$this->atts['location'] ) return false;

		// Get compare location or bail out
		$compare_location = Toolset_Maps_Location_Factory::create_from_address( $this->atts['location'] );
		if ( !$compare_location ) return false;

		// Get visitor location or bail out
		$visitor_location = Toolset_Maps_Location_Factory::create_from_cookie();
		if ( !$visitor_location ) return false;

		return $this->does_distance_satisfy( $visitor_location, $compare_location, $this->atts );
	}

	/**
	 * Calculate if distance from visitor location to given location is within radius, and whether content outside or
	 * inside the radius should be shown.
	 *
	 * @param Toolset_Maps_Location $visitor_location
	 * @param Toolset_Maps_Location $compare_location
	 * @param array $atts
	 *
	 * @return bool
	 */
	public function does_distance_satisfy(
		Toolset_Maps_Location $visitor_location, Toolset_Maps_Location $compare_location, array $atts
	) {
		$distance = Distance\Calculator::calculate( $visitor_location, $compare_location, $atts['unit'] );
		$is_inside = $distance < $atts['distance'];
		$show_inside = ( $atts['display'] === 'inside' );

		return ( $is_inside xor !$show_inside );
	}
}