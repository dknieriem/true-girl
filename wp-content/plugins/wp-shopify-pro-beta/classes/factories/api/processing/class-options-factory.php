<?php

namespace WPS\Factories\API\Processing;

defined('ABSPATH') ?: die;

use WPS\Factories;
use WPS\API;

class Options_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new API\Processing\Options(
				Factories\Processing\Options_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
