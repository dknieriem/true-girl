<?php

namespace WPS\Factories\API\Settings;

defined('ABSPATH') ?: die;

use WPS\API;
use WPS\Factories;

class License_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new API\Settings\License(
				Factories\DB\Settings_License_Factory::build(),
				Factories\HTTP_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
