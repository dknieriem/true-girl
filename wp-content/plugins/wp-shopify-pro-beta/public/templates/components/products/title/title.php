<?php

/*

@description   Title template for each product within the main products loop

@version       2.0.0
@since         1.0.49
@path          templates/components/products/title/title.php

@docs          https://wpshop.io/docs/templates/components/products/title

*/

defined('ABSPATH') ?: exit(); 

?>

<h2
   itemprop="name"
   class="wps-products-title <?= apply_filters('wps_products_title_class', '') ?>"
   data-wps-is-ready="0">
   
   <?= esc_html_e($data->product->title, WPS_PLUGIN_TEXT_DOMAIN) ?>

</h2>