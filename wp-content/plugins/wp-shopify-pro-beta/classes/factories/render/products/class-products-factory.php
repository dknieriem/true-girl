<?php

namespace WPS\Factories\Render\Products;

defined('ABSPATH') ?: die;

use WPS\Render\Products;
use WPS\Factories;

class Products_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new Products(
            Factories\Render\Templates_Factory::build(),
            Factories\Render\Data_Factory::build(),
            Factories\Render\Products\Defaults_Factory::build()
			);

		}
      
		return self::$instantiated;

	}

}