<?php

namespace WPS\Factories\API\Items;

defined('ABSPATH') ?: die;

use WPS\API;
use WPS\Factories;

class Variants_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new API\Items\Variants(
				Factories\DB\Products_Factory::build(),
				Factories\DB\Variants_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
