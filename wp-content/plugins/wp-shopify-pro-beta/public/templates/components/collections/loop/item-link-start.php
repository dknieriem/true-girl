<?php

/*

@description   Opening tag for each collection link within loop

@version       2.0.0
@since         1.0.49
@path          templates/components/collections/loop/item-link-start.php

@docs          https://wpshop.io/docs/templates/collections/loop/item-link-start

*/

defined('ABSPATH') ?: die;

?>

<a href="<?= esc_url( home_url() . '/' . $data->settings->url_collections . '/' . $data->collection->handle); ?>" class="wps-collection-link <?= apply_filters( 'wps_collections_link_class', '' ); ?>" title="<?php esc_attr_e($data->collection->title, WPS_PLUGIN_TEXT_DOMAIN ); ?>">
