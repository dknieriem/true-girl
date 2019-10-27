<?php

namespace WPS\Factories\Render;

if (!defined('ABSPATH')) {
   exit();
}

use WPS\Render\Templates;
use WPS\Factories;

class Templates_Factory
{
   protected static $instantiated = null;

   public static function build()
   {
      if (is_null(self::$instantiated)) {
         self::$instantiated = new Templates(
            Factories\Template_Loader_Factory::build(),
            Factories\Render\Data_Factory::build(),
            Factories\Render\Products\Pre_Render_Pricing_Factory::build(),
            Factories\Render\Products\Pre_Render_Options_Factory::build(),
            Factories\Render\Products\Pre_Render_Gallery_Factory::build()
         );
      }

      return self::$instantiated;
   }
}
