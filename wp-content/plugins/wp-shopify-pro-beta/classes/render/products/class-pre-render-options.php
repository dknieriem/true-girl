<?php

namespace WPS\Render\Products;

use WPS\Utils;


if (!defined('ABSPATH')) {
	exit;
}


class Pre_Render_Options {


	public function __construct() {

	}

	public function pre_render_product_options($params) {

		// No options will be shown if only one variant exists!
		if ( !count($params['data']['product']->variants) > 1) {
			return;
		}

		// Filtering the variants
		$params['data']['product']->variants = Utils::only_available_variants($params['data']['product']->variants);

		$data = [
			'product' 									=> $params['data']['product'],
			'button_width'							=> Utils::get_options_button_width($params['data']['product']->options),
			'sorted_options'						=> Utils::get_sorted_options($params['data']['product'])
		];

		$params['data'] = wp_parse_args( $data, $params['data'] );

		return $params;

	}


}
