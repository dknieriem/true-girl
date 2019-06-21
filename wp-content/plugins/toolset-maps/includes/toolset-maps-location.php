<?php
/**
 * Class Toolset_Maps_Location
 *
 * Location type. Immutable. Don't instantiate directly - use Toolset_Maps_Location_Factory, which does parameter
 * checking, so if you get an instance from there, you can be pretty sure it contains valid lat/lng.
 *
 * @package ToolsetMaps
 *
 * @since 1.5
 */
class Toolset_Maps_Location {
	/** @var float $lat */
	protected $lat;
	/** @var float $lng */
	protected $lng;

	/**
	 * Toolset_Maps_Location constructor.
	 *
	 * @param float $lat
	 * @param float $lng
	 */
	public function __construct( $lat, $lng ) {
		$this->lat = $lat;
		$this->lng = $lng;
	}

	/**
	 * @return float
	 */
	public function get_lat() {
		return $this->lat;
	}

	/**
	 * @return float
	 */
	public function get_lng() {
		return $this->lng;
	}
}
