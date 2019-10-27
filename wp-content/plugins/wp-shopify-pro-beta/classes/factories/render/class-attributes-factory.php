<?php

namespace WPS\Factories\Render;

if (!defined('ABSPATH')) {
   exit();
}


use WPS\Render\Attributes;

class Attributes_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new Attributes();
      }

      return self::$instantiated;
   }
}