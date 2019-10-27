<?php

http_response_code(200);

use WPS\Transients;
use WPS\Factories;

$Webhooks             = Factories\Webhooks_Factory::build();
$CPT_Model            = Factories\CPT_Model_Factory::build();
$DB_Collections       = Factories\DB\Collections_Factory::build();
$DB_Collects          = Factories\DB\Collects_Factory::build();
$API_Items_Collects   = Factories\API\Items\Collects_Factory::build();

$json_data            = file_get_contents('php://input');

if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $collection = json_decode($json_data);

  // Hook: wps_on_collections_create
  $Webhooks->on_collections_create($collection);

  $post_id               = $CPT_Model->insert_or_update_collection_post( $collection );
  $collects_result       = $DB_Collects->modify_from_shopify(
                            $DB_Collects->modify_options(
                              $API_Items_Collects->get_collects_from_collection($collection),
                              WPS_COLLECTIONS_LOOKUP_KEY
                            )
                          );

  $collection_result     = $DB_Collections->insert_items_of_type( $DB_Collections->mod_before_change($collection, $post_id) );

  Transients::delete_cached_collection_queries();
  Transients::delete_cached_single_collections();

  // Hook: wps_after_collections_create
  $Webhooks->after_collections_create($collection);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from collections-create.php');
}
