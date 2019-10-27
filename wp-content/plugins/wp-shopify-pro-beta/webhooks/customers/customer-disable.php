<?php

http_response_code(200);

use WPS\Factories;

$Webhooks = Factories\Webhooks_Factory::build();
$DB_Customers = Factories\DB\Customers_Factory::build();
$json_data = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $customer = json_decode($json_data);

  // Hook: wps_on_customer_disable
  $Webhooks->on_customer_disable($customer);

  $DB_Customers->update_items_of_type($customer);

  // Hook: wps_after_customer_disable
  $Webhooks->after_customer_disable($customer);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from customer-disable.php');
}
