<?php

/*

@description   Opening tag for the main products loop

@version       2.0.0
@since         1.0.49
@path          templates/components/products/loop/loop-start.php

@docs          https://wpshop.io/docs/templates/components/products/loop/loop-start

*/

defined('ABSPATH') ?: die;

?>

<ul class="wps-row wps-contain wps-products <?= apply_filters('wps_products_class', ''); ?>">
