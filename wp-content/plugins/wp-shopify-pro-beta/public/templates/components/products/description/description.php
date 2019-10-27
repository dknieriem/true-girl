<?php

/*

@description   Description for each product within the main products loop

@version       2.0.0
@since         1.3.1
@path          templates/components/products/description/description.php

@docs          https://wpshop.io/docs/templates/components/products/loop/item-description

*/

defined('ABSPATH') ?: exit(); ?>

<div
   itemprop="description"
   class="wps-products-description <?= apply_filters('wps_products_description_class', '') ?>"
   data-wps-is-ready="0">

   <?php _e($data->product->body_html, WPS_PLUGIN_TEXT_DOMAIN); ?>

</div>