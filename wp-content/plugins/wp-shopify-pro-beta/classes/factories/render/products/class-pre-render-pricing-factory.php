<?php

namespace WPS\Factories\Render\Products;

defined('ABSPATH') ?: die;

use WPS\Render\Products\Pre_Render_Pricing;
use WPS\Factories;

class Pre_Render_Pricing_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new Pre_Render_Pricing(
        Factories\Render\Products\Defaults_Factory::build(),
				Factories\DB\Variants_Factory::build(),
				Factories\Money_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
