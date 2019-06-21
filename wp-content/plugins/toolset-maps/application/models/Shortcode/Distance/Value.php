<?php

namespace OTGS\Toolset\Maps\Model\Shortcode\Distance;

use OTGS\Toolset\Maps\Model\Shortcode\ShortcodeAbstract;
use OTGS\Toolset\Maps\Model\Distance;
use Toolset_Maps_Location_Factory;

/**
 * Implements the distance value shortcode.
 *
 * Class Value
 * @package OTGS\Toolset\Maps\Model\Shortcode\Distance
 * @since 1.6
 */
class Value extends ShortcodeAbstract {
	/**
	 * @return string
	 */
	public function render() {
		// Get compare location or bail out
		$compare_location = $this->get_compare_location( $this->atts['origin_source'], $this->atts['location'] );
		if ( !$compare_location ) return '';
		
		// Loop over (potentially repeating) addresses, and calculate all distances...
		$distances = array();
		foreach ( $this->get_addresses_from_field() as $address ) {
			$field_location = Toolset_Maps_Location_Factory::create_from_address( $address );
			if ( $field_location ) {
				$distances[] = Distance\Calculator::calculate( $compare_location, $field_location, $this->atts['unit']);
			}
		}
		// ...possibly bail out...
		if ( empty($distances) ) return '';
		// ...then use the smallest distance, which is consistent with what ordering and filtering does.
		$distance = min( $distances );

		return number_format_i18n( $distance, $this->atts['decimals'] );
	}

	/**
	 * Shortcode attributes:
	 *
	 * [
	 *  'unit'              => 'km'|'mi'
	 *  'origin_source'     => 'address'|'visitor_location'|'url_param'
	 *  'location'          => '', // if origin_source = address, the address, otherwise unused
	 *  'target_source'     => 'postmeta'|'termmeta'|'usermeta'
	 *  'decimals'          => 1 // integer, how many decimals to show for the number
	 *  'url_param'         => 'toolset_maps_distance_center' // default one also default for distance custom search
	 * ]
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'unit'              => 'km',
			'origin_source'     => 'address',
			'location'          => '',
			'target_source'     => 'postmeta',
			'decimals'          => 1,
			'url_param'         => 'toolset_maps_distance_center',
		);
	}

	/**
	 * @return int|null
	 */
	private function get_post_id() {
		if ( array_key_exists( 'postmeta_id', $this->atts ) ) {
			return $this->atts['postmeta_id'];
		} else {
			$post = get_post();
			if ( $post ) {
				return $post->ID;
			}
		}
		return null;
	}

	/**
	 * @return int|null
	 */
	private function get_term_id() {
		if ( array_key_exists( 'termmeta_id', $this->atts ) ) {
			return $this->atts['termmeta_id'];
		} elseif ( array_key_exists( 'WP_Views', $GLOBALS ) ) {
			global $WP_Views;
			return $WP_Views->taxonomy_data['term']->term_id;
		}
		return null;
	}

	/**
	 * @return int|null
	 */
	private function get_user_id() {
		if ( array_key_exists( 'usermeta_id', $this->atts ) ) {
			return $this->atts['usermeta_id'];
		} elseif ( array_key_exists( 'WP_Views', $GLOBALS ) ) {
			global $WP_Views;
			if ( empty( $WP_Views->users_data ) ) {
				return get_current_user_id();
			} else {
				return $WP_Views->users_data['term']->ID;
			}
		}
		return null;
	}

	/**
	 * Returns addresses from the the right field
	 *
	 * @return array
	 */
	private function get_addresses_from_field() {
		switch ( $this->atts['target_source'] ) {
			case 'postmeta':
				$id = $this->get_post_id();
				if ( $id !== null ) {
					return get_post_meta( $id, $this->atts['postmeta'] );
				}
				break;
			case 'termmeta':
				$id = $this->get_term_id();
				if ( $id !== null ) {
					return get_term_meta( $id, $this->atts['termmeta'] );
				}
				break;
			case 'usermeta':
				$id = $this->get_user_id();
				if ( $id !== null ) {
					return get_user_meta( $id, $this->atts['usermeta'] );
				}
				break;
		}
		return array();
	}

	/**
	 * Given $location_source, tries to return a Toolset_Maps_Location from that source.
	 *
	 * @param $location_source
	 * @param $location
	 *
	 * @return null|\Toolset_Maps_Location
	 */
	private function get_compare_location( $location_source, $location ) {
		switch ( $location_source ) {
			case 'address':
				return Toolset_Maps_Location_Factory::create_from_address( $location );
				break;
			case 'visitor_location':
				return Toolset_Maps_Location_Factory::create_from_cookie();
				break;
			case 'url_param':
				return Toolset_Maps_Location_Factory::create_from_address(
					toolset_getget( $this->atts['url_param'] )
				);
				break;
			default:
				return null;
		}
	}
}