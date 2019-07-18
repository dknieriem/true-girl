<?php

/*

@description   Buy button template used for products. This is a global partial used by
               the shortcode, single page, and listing page.

@version       2.0.0
@since         1.0.49
@path          templates/components/products/buy-button/buy-button.php

@docs          https://wpshop.io/docs/templates/products/buy-button/buy-button.php

*/

defined('ABSPATH') ?: exit(); ?>

<button
   itemprop="potentialAction"
   itemscope
   itemtype="https://schema.org/BuyAction"
   href="#!"
   class="wps-btn wps-col-1 wps-btn-secondary wps-add-to-cart <?= apply_filters('wps_add_to_cart_class', '') ?>"
   title="<?php esc_attr_e($data->button_text, WPS_PLUGIN_TEXT_DOMAIN); ?>"
   style="<?= !empty($data->button_color) ? 'background-color: ' . $data->button_color . ';' : '' ?>"
   data-post-id="<?= !empty($data->post_id) ? $data->post_id : '' ?>"
   data-product-id="<?= !empty($data->product->product_id) ? $data->product->product_id : '' ?>"
   data-wps-product-id="<?= $data->product->product_id ?>">

   <?php esc_html_e($data->button_text, WPS_PLUGIN_TEXT_DOMAIN); ?>

</button>
