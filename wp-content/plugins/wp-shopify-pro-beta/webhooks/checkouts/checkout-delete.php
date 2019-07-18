<?php

http_response_code(200);

use WPS\Factories\Webhooks_Factory;
use WPS\Factories\Checkouts_Factory;

$Webhooks = Webhooks_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $checkout = json_decode($json_data);

  // Hook: wps_on_checkout_delete
  $Webhooks->on_checkout_delete($checkout);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from checkout-delete.php');
}
