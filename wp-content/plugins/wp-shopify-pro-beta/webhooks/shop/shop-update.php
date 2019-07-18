<?php

http_response_code(200);

use WPS\Transients;
use WPS\Factories\DB\Shop_Factory;
use WPS\Factories\Webhooks_Factory;

$Webhooks = Webhooks_Factory::build();
$DB_Shop = Shop_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $shop = json_decode($json_data);

  // Hooks: wps_on_shop_update
  $Webhooks->on_shop_update($shop);

  // Actual work
  $result_shop = $DB_Shop->update_items_of_type($shop);

  Transients::delete_cached_prices();

  // Hooks: wps_after_shop_update
  $Webhooks->after_shop_update($shop);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from shop-update.php');

}
