<?php

namespace WPS\Factories\API\Options;

if (!defined('ABSPATH')) {
   exit();
}

use WPS\API;
use WPS\Factories;

class Components_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new API\Options\Components();
      }

      return self::$instantiated;
   }
}
