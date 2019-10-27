<?php

namespace WPS\Factories\Render\Cart;

if (!defined('ABSPATH')) {
    exit();
}

use WPS\Render\Cart\Defaults;
use WPS\Factories;

class Defaults_Factory
{
    protected static $instantiated = null;

    public static function build($plugin_settings = false)
    {

      if (!$plugin_settings) {
         $plugin_settings = Factories\DB\Settings_Plugin_Factory::build();
      }

        if (is_null(self::$instantiated)) {
            self::$instantiated = new Defaults(
             $plugin_settings,
             Factories\Render\Attributes_Factory::build()
         );
        }

        return self::$instantiated;
    }
}