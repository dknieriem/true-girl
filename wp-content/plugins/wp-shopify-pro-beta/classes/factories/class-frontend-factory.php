<?php

namespace WPS\Factories;

use WPS\Frontend;
use WPS\Factories;

if (!defined('ABSPATH')) {
	exit;
}

class Frontend_Factory {

	protected static $instantiated = null;

	public static function build($plugin_settings = false) {

      if (!$plugin_settings) {
         $plugin_settings = Factories\DB\Settings_Plugin_Factory::build();
      }

		if (is_null(self::$instantiated)) {

			$Frontend = new Frontend($plugin_settings);

			self::$instantiated = $Frontend;

		}

		return self::$instantiated;

	}

}
