<?php

namespace WPS\Factories\Render\Products;

defined('ABSPATH') ?: die;

use WPS\Render\Products\Hooks;
use WPS\Factories;

class Hooks_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new Hooks(
				Factories\Render\Products\Products_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
