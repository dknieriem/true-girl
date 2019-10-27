<?php

namespace WPS\Render\Products;

use WPS\Utils;

if (!defined('ABSPATH')) {
   exit();
}

class Pre_Render_Gallery
{
   public function __construct()
   {
   }

   public function pre_render_product_gallery($params)
   {
      $params['data']['product']->images = Utils::sort_product_images_by_position($params['data']['product']->images);

      $data = [
         'product' => $params['data']['product'],
         'images' => $params['data']['product']->images,
         'index' => 0,
         'amount_of_thumbs' => count($params['data']['product']->images)
      ];

      $params['data'] = wp_parse_args($data, $params['data']);

      return $params;
   }
}
