<?php

namespace WPS\Factories;

defined('ABSPATH') ?: die;

use WPS\API;
use WPS\Factories;

class API_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new API(
				Factories\DB\Settings_Syncing_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
