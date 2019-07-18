<?php

namespace WPS\Factories\Render\Storefront;

if (!defined('ABSPATH')) {
   exit();
}

use WPS\Render\Storefront;
use WPS\Factories;

class Storefront_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new Storefront(Factories\Render\Templates_Factory::build(), Factories\Render\Data_Factory::build());
      }

      return self::$instantiated;
   }
}
