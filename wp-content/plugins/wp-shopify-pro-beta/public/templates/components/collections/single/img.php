<?php

/*

@description   Image for single collection

@version       2.0.0
@since         1.0.49
@path          templates/components/collections/single/img.php

@docs          https://wpshop.io/docs/templates/collections/single/img

*/

defined('ABSPATH') ?: die;

?>

<img
  itemprop="image"
  src="<?= esc_url($data->image->src); ?>"
  alt="<?php esc_attr_e($data->image->alt); ?>"
  class="wps-collection-img <?= apply_filters('wps_collections_single_img_class', ''); ?>">
