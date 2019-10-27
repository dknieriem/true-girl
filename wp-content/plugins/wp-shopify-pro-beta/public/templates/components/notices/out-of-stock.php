<?php

/*

@description   Used for displaying "out of stock" and any related add to cart errors

@version       1.0.1
@since         1.0.49
@path          templates/components/notices/out-of-stock.php

@docs          https://wpshop.io/docs/templates/components/notices/out-of-stock

*/

defined('ABSPATH') ?: die;

?>

<aside class="wps-notice-inline wps-product-notice wps-notice-inline-sm wps-is-visible wps-notice-warning <?= apply_filters('wps_out_of_stock_class', ''); ?>">
  <?php esc_html_e('Out of stock', WPS_PLUGIN_TEXT_DOMAIN); ?>
</aside>
