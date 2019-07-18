<?php

namespace WPS\Factories\API\Misc;

defined('ABSPATH') ?: die;

use WPS\Factories;
use WPS\API;

class Routes_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new API\Misc\Routes(
				Factories\Routes_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
