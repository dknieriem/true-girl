<?php

/*

@description   Header for the product single page

@version       2.0.0
@since         1.0.49
@path          templates/components/products/single/header-price.php

@docs          https://wpshop.io/docs/templates/products/single/header-price

*/

defined('ABSPATH') ?: die;

?>

<header class="wps-product-header">
  <?php do_action('wps_product_single_heading', $data->product); ?>
</header>
