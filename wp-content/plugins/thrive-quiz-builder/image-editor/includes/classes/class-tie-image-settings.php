<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Image_Settings {

	const WIDTH = 1200;
	const HEIGHT = 628;

	private $_defaults = array(
		'overlay'          => array(
			'bg_color' => 'transparent',
			'opacity'  => 50,
		),
		'size'             => array(
			'width'  => 0,
			'height' => 0,
		),
		'background_image' => array(
			'url'      => 'none',
			'size'     => 'auto',
			'position' => '0px 0px',
		),
		'fonts'            => array(),
		'template'         => null,
	);

	private $_data = array();

	/**
	 * @var int Post_ID
	 */
	private $post_id;

	public function __construct( $post ) {

		$this->_defaults['size']['width']  = self::WIDTH;
		$this->_defaults['size']['height'] = self::HEIGHT;

		$this->post_id = $post instanceof WP_Post ? $post->ID : intval( $post );
		$this->init_data();
	}

	public function __get( $key ) {
		return $this->get_data( $key );
	}

	public function get_data( $key = '' ) {
		if ( '' === $key ) {
			return $this->_data;
		}

		$default = null;

		// accept a/b/c as ['a']['b']['c']
		if ( strpos( $key, '/' ) ) {
			$keyArr = explode( '/', $key );
			$data   = $this->_data;
			foreach ( $keyArr as $k ) {
				if ( $k === '' ) {
					return $default;
				}
				if ( is_array( $data ) ) {
					if ( ! isset( $data[ $k ] ) ) {
						return $default;
					}
					$data = $data[ $k ];
				} else {
					return $default;
				}
			}

			return $data;
		}

		return $this->_get_data( $key );
	}

	public function save( $data ) {
		$this->_data = array_merge( $this->_defaults, $data );
		update_post_meta( $this->post_id, 'tie_image_settings', $this->_data );

		return true;
	}

	public function get_bg_filename() {

		$chunks      = explode( '/', $this->get_data( 'background_image/url' ) );
		$bg_filename = end( $chunks );

		return $bg_filename;
	}

	private function _get_data( $key ) {
		return isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null;
	}

	private function init_data() {
		$data = $this->post_id ? get_post_meta( $this->post_id, 'tie_image_settings', true ) : false;
		if ( is_array( $data ) ) {
			$this->_data = array_merge( $this->_defaults, $data );
		} else {
			$this->_data = $this->_defaults;
		}
	}
}
