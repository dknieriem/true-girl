<?php

http_response_code(200);

use WPS\Factories;

$Webhooks = Factories\Webhooks_Factory::build();
$DB_Orders = Factories\DB\Orders_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $order = json_decode($json_data);

  // Hook: wps_on_order_draft_update
  $Webhooks->on_order_draft_update($order);

  // Actual work
  $DB_Orders->update_items_of_type($order);

  // Hook: wps_after_order_draft_update
  $Webhooks->after_order_draft_update($order);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from order-draft-update.php');
}
