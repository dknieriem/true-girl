<?php

defined('ABSPATH') ?: die;

get_header('wps');

global $post;

$Products = WPS\Factories\Render\Products\Products_Factory::build();

$Products->products([
   'title' => $post->post_title,
   'dropzone_product_buy_button' => '#product_buy_button',
   'dropzone_product_title' => '#product_title',
   'dropzone_product_description' => '#product_description',
   'dropzone_product_pricing' => '#product_pricing',
   'dropzone_product_gallery' => '#product_gallery',
   'hide_wrapper' => true,
]);

?>

<section class="wps-container">

   <?= do_action('wps_breadcrumbs') ?>

   <div class="wps-product-single row">

      <div class="wps-product-single-gallery col">
         <div id="product_gallery"></div>
      </div>

      <div class="wps-product-single-content col">
         <div id="product_title">
            <?php   

            // Renders title server-side for SEO
            $Products->title([
               'post_id' => $post->ID,
               'render_from_server' => true
            ]);

            ?>
         </div>
         <div id="product_pricing"></div>
         <div id="product_description">

         <?php   

            // Renders description server-side for SEO
            $Products->description([
               'post_id' => $post->ID,
               'render_from_server' => true
            ]);
         ?>

         </div>
         <div id="product_buy_button"></div>

      </div>

   </div>

</section>

<?php


get_footer('wps');