<?php

namespace WPS\Factories\Render\Collections;

if (!defined('ABSPATH')) {
    exit();
}

use WPS\Factories;
use WPS\Render\Collections;


class Collections_Factory
{
    protected static $instantiated = null;

    public static function build()
    {
        if (is_null(self::$instantiated)) {
            self::$instantiated = new Collections(
                Factories\Render\Templates_Factory::build(),
                Factories\Render\Data_Factory::build(),
                Factories\Render\Collections\Defaults_Factory::build()
            );
        }

        return self::$instantiated;
    }
}
