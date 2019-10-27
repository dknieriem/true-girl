<?php

http_response_code(200);

use WPS\Factories\DB\Orders_Factory;
use WPS\Factories\Webhooks_Factory;

$Webhooks = Webhooks_Factory::build();
$DB_Orders = Orders_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $order = json_decode($json_data);

  // Hook: wps_on_order_draft_create
  $Webhooks->on_order_draft_create($order);

  $DB_Orders->insert_items_of_type($order);

  // Hook: wps_after_order_draft_create
  $Webhooks->after_order_draft_delete($order);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from order-draft-create.php');
}
