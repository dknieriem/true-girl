<?php

/*

@description   Opening tag for image gallery thumbnails wrapper on product single page

@version       2.0.0
@since         1.0.49
@path          templates/components/products/single/thumbs-start.php

@docs          https://wpshop.io/docs/templates/products/single/thumbs-start

*/

defined('ABSPATH') ?: die;

?>

<div class="wps-row wps-product-thumbs <?= apply_filters('wps_product_single_thumbs_class', ''); ?>">
