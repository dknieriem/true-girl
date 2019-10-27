<?php

/*

@description   The main entry point for the 'collections all' page. Used internally by the custom post type archive
               template as well as the [wps_collections] shortcode

@version       1.0.1
@since         1.0.49
@path          templates/collections-all.php
@component      templates/components/collections

@docs          https://wpshop.io/docs/templates/collections-all

*/

?>

<aside class="wps-col wps-col-2 wps-fill wps-product-gallery">
  <div class="wps-product-gallery-imgs">

		<?php do_action('wps_product_images', $data); ?>

  </div>
</aside>
