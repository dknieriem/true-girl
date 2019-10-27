<?php

/*

@description   Opening tag for the element wrapping the product quantity add to cart and variant selections. This
               also wraps the actions-groups container.

               ** Important ** This partial depends on WP Shopify JavaScript. Modifying could potentially break the
               add to cart functionality. Do not remove any data- attributes.

@version       2.0.0
@since         1.0.49
@path          templates/components/products/add-to-cart/meta-start.php
@js            true

@docs          https://wpshop.io/docs/templates/components/products/add-to-cart/meta-start

*/

defined('ABSPATH') ?: die;

?>

<section
  class="wps-product-meta wps-is-disabled wps-is-loading <?= apply_filters('wps_product_single_meta_class', ''); ?>"
  data-product-quantity="1"
  data-product-variants-count="<?= count($data->product->variants); ?>"
  data-product-post-id="<?= $data->product->post_id; ?>"
  data-product-id="<?= $data->product->product_id; ?>"
  data-product-selected-options=""
	data-product-url="<?= $data->product->url; ?>"
	data-product-handle="<?= $data->product->handle; ?>"
  data-product-selected-variant="<?= count($data->product->variants) === 1 ? $data->product->variants[0]->id : ''; ?>"
  data-product-available-variants='<?= json_encode($data->filtered_options); ?>'
	data-product-graphql-id="<?= $data->product->admin_graphql_api_id; ?>"
	data-product-selected-options-and-variants="">
