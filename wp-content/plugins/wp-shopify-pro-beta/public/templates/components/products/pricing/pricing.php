<?php

/*

@description   Single price component. Used when product has one price, e.g., Small: $1, Medium: $1, Large: $1.
							 Used on both product single and product listing pages.

@version       2.0.0
@since         1.0.49
@path          templates/components/products/pricing/pricing.php

@docs          https://wpshop.io/docs/templates/components/products/pricing/pricing

*/

defined('ABSPATH') ?: exit(); ?>

<h3
   itemscope
   itemprop="offers"
   itemtype="https://schema.org/Offer"
   class="wps-products-price wps-product-pricing wps-products-price-one <?= apply_filters('wps_products_price_class', '') ?>"
   data-wps-is-showing-compare-at="<?= !empty($data->show_compare_at) ? $data->show_compare_at : false ?>"
   data-wps-is-showing-local="<?= !empty($data->show_local) ? $data->show_local : false ?>"
   data-wps-is-showing-price-range="<?= !empty($data->show_price_range) ? $data->show_price_range : false ?>"
   data-wps-product-id="<?= !empty($data->product) ? $data->product->product_id : false ?>">

   <?= !empty($data->price) ? apply_filters('wps_products_price', $data->price, $data) : ''; ?>

</h3>