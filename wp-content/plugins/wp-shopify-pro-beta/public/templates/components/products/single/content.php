<?php

/*

@description   Description that shows on product single pages

@version       1.0.1
@since         1.0.49
@path          templates/components/products/single/content.php

@docs          https://wpshop.io/docs/templates/products/single/content

*/

defined('ABSPATH') ?: die;

?>

<div
  itemprop="description"
  class="wps-product-content">

  <?php _e($data->product->body_html, WPS_PLUGIN_TEXT_DOMAIN); ?>

</div>
