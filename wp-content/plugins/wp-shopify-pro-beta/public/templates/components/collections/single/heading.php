<?php

/*

@description   Heading for single collection

@version       1.0.1
@since         1.0.49
@path          templates/components/collections/single/heading.php

@docs          https://wpshop.io/docs/templates/collections/single/heading

*/

defined('ABSPATH') ?: die;

?>

<h1 class="wps-collection-heading <?= apply_filters('wps_collections_single_heading_class', ''); ?>">
  <?php esc_html_e($data->collection->title, WPS_PLUGIN_TEXT_DOMAIN); ?>
</h1>
