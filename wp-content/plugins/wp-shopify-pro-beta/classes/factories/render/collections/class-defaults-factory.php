<?php

namespace WPS\Factories\Render\Collections;

if (!defined('ABSPATH')) {
    exit();
}

use WPS\Factories;
use WPS\Render\Collections\Defaults;

class Defaults_Factory
{
    protected static $instantiated = null;

    public static function build()
    {
        if (is_null(self::$instantiated)) {
            self::$instantiated = new Defaults(
                Factories\DB\Settings_General_Factory::build(),
                Factories\Render\Attributes_Factory::build(),
                Factories\Render\Products\Defaults_Factory::build()
            );
        }

        return self::$instantiated;
    }
}
