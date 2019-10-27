<?php

namespace WPS\Factories;

use WPS\Migrations\Migrations;
use WPS\Factories;


if (!defined('ABSPATH')) {
	exit;
}

class Migrations_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new Migrations(
				Factories\DB\Products_Factory::build(),
				Factories\DB\Variants_Factory::build(),
				Factories\DB\Collects_Factory::build(),
				Factories\DB\Options_Factory::build(),
				Factories\DB\Collections_Custom_Factory::build(),
				Factories\DB\Collections_Smart_Factory::build(),
				Factories\DB\Images_Factory::build(),
				Factories\DB\Tags_Factory::build(),
				Factories\DB\Customers_Factory::build(),
				Factories\DB\Orders_Factory::build()
			);

		}

		return self::$instantiated;

	}

}
