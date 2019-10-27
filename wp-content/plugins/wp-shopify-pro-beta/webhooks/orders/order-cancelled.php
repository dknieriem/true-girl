<?php

http_response_code(200);

use WPS\Factories\DB\Orders_Factory;
use WPS\Factories\Webhooks_Factory;

$Webhooks = Webhooks_Factory::build();
$DB_Orders = Orders_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $order = json_decode($json_data);

  // Hook: wps_on_order_cancelled
  $Webhooks->on_order_cancelled($order);

  $DB_Orders->update_items_of_type($order);

  // Hook: wps_after_order_cancelled
  $Webhooks->after_order_cancelled($order);


} else {
  error_log('WP Shopify Error - Unable to verify webhook response from order-cancelled.php');
}
