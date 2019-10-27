<?php

/*

@description   Product image for each product within the main products loop

@version       2.0.0
@since         1.0.49
@path          templates/components/products/loop/item-img.php

@docs          https://wpshop.io/docs/templates/components/products/loop/item-img

*/

defined('ABSPATH') ?: die;

?>

<img
  itemprop="image"
  src="<?= esc_url( $data->custom_sizing ? $data->custom_image_src : $data->image->src ); ?>"
  alt="<?php esc_attr_e($data->image->alt); ?>"
  class="wps-products-img <?= apply_filters( 'wps_products_img_class', '' ); ?>">
