<?php

class MC4WP_RGB_Color {

	/**
	 * @var number
	 */
	public $r;

	/**
	 * @var number
	 */
	public $g;

	/**
	 * @var number
	 */
	public $b;

	/**
	 * @var string
	 */
	public $hex;

	/**
	 * @param string $color Hexadecimal color value
	 */
	public function __construct( $color ) {

		// create hex string of 6 chars
		$hex = str_replace( '#', '', $color );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ).str_repeat( substr( $hex, 1, 1 ), 2 ).str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		$this->hex = '#' . $hex;

		// Get decimal values
		$this->r = hexdec( substr( $hex, 0, 2 ) );
		$this->g = hexdec( substr( $hex, 2, 2 ) );
		$this->b = hexdec( substr( $hex, 4, 2 ) );
	}

	/**
	 * @param $percentage
	 *
	 * @return string
	 */
	public function darken( $percentage ) {

		$amount = ( $percentage / 100 * 255 );

		$r = max( 0, min( 255, $this->r - $amount ) );
		$g = max( 0, min( 255, $this->g - $amount ) );
		$b = max( 0, min( 255, $this->b - $amount ) );

		$r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
		$g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
		$b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );

		return '#'.$r_hex.$g_hex.$b_hex;
	}

	/**
	 * @param $percentage
	 *
	 * @return string
	 */
	public function lighten( $percentage ) {
		$amount = ( $percentage / 100 * 255 );

		$r = max( 0, min( 255, $this->r + $amount ) );
		$g = max( 0, min( 255, $this->g + $amount ) );
		$b = max( 0, min( 255, $this->b + $amount ) );

		$r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
		$g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
		$b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
		return '#'.$r_hex.$g_hex.$b_hex;
	}

	/**
	 * @return float
	 */
	public function lightness() {
		$avg = (($this->r + $this->g + $this->b) / 3);
		return $avg / 255 * 100;
	}

}