<?php

http_response_code(200);

use WPS\Transients;
use WPS\Factories\DB\Shop_Factory;
use WPS\Factories\Webhooks_Factory;
use WPS\Factories\DB\Settings_General_Factory;

$DB_Shop = Shop_Factory::build();
$Webhooks = Webhooks_Factory::build();
$DB_Settings_General = Settings_General_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $shop = json_decode($json_data);

  // Hook: wps_on_app_uninstall
  $Webhooks->on_app_uninstall($shop);

  $DB_Settings_General->set_app_uninstalled(1);
  $DB_Shop->update_items_of_type($shop);

  // Hook: wps_after_app_uninstall
  $Webhooks->after_app_uninstall($shop);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from app-uninstall.php');
}
