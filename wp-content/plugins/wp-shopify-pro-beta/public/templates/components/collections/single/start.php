<?php

/*

@description   Opening tag for single collection page

@version       2.0.0
@since         1.0.49
@path          templates/components/collections/single/start.php

@docs          https://wpshop.io/docs/templates/collections/single/start

*/

defined('ABSPATH') ?: die;

?>

<div
  itemprop="offers"
  itemscope=""
  itemtype="https://schema.org/Offer"
  class="wps-collection-single wps-contain <?= apply_filters('wps_collections_single_start_class', ''); ?>">
