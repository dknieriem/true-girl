<?php

namespace WPS\Factories\API\Settings;

defined('ABSPATH') ?: die;

use WPS\API;
use WPS\Factories;

class Products_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new API\Settings\Products(
				Factories\DB\Settings_General_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
