<?php

namespace WPS\Factories\Shortcodes;

defined('ABSPATH') ?: exit();

use WPS\Shortcodes;

class Shortcodes_Data_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new Shortcodes\Shortcodes_Data();
      }

      return self::$instantiated;
   }
}
