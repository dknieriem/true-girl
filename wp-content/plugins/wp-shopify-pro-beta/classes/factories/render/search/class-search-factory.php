<?php

namespace WPS\Factories\Render\Search;

if (!defined('ABSPATH')) {
   exit();
}

use WPS\Render\Search;
use WPS\Factories;

class Search_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new Search(Factories\Render\Templates_Factory::build(), Factories\Render\Data_Factory::build());
      }

      return self::$instantiated;
   }
}
