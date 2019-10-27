<?php

namespace WPS\Factories\Render\Cart;

if (!defined('ABSPATH')) {
   exit();
}

use WPS\Render\Cart;
use WPS\Factories;

class Cart_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new Cart(
            Factories\Render\Templates_Factory::build(), 
            Factories\Render\Data_Factory::build(),
            Factories\Render\Cart\Defaults_Factory::build()
         );
      }

      return self::$instantiated;
   }
}
