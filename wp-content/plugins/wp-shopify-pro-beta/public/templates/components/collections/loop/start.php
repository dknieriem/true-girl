<?php

/*

@description   Opening tag for the main collections loop

@version       2.0.0
@since         1.0.49
@path          templates/components/collections/loop/loop-start.php

@docs          https://wpshop.io/docs/templates/collections/loop/loop-start

*/

defined('ABSPATH') ?: die;

?>

<ul class="wps-row wps-contain wps-row-left wps-collections <?= apply_filters('wps_collections_class', ''); ?>">
