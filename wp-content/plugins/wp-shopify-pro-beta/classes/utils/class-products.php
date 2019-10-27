<?php

namespace WPS\Utils;

use WPS\Utils;

if (!defined('ABSPATH')) {
	exit;
}


class Products {

	/*

	Gets the add to cart button width

	*/
	public static function add_to_cart_button_width($product) {

		if ( !$product || !is_object($product) ) {
			return 1;
		}

		if (count($product->options) === 1) {

			if (count($product->variants) > 1) {
				$col = 2;

			} else {
				$col = 1;
			}

		} else if (count($product->options) === 2) {
			$col = 1;

		} else if (count($product->options) === 3) {
			$col = 1;

		} else {
			$col = 1;
		}

		return $col;

	}

}
