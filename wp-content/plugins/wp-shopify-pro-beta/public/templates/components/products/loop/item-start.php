<?php

/*

@description   Opening tags for each product item within the main products loop

@version       2.0.0
@since         1.0.49
@path          templates/components/products/loop/item-start.php

@docs          https://wpshop.io/docs/templates/components/products/loop/item-start

*/

defined('ABSPATH') ?: die;

?>

<li class="wps-product-item wps-product-item-id-<?= $data->product->id; ?> wps-col wps-col-<?= $data->items_per_row; ?> <?= apply_filters('wps_product_class', ''); ?>" data-wps-product-wrapper="true">

  <div itemscope itemtype="https://schema.org/Product" class="wps-box">
