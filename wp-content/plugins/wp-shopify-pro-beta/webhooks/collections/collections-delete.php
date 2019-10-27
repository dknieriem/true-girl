<?php

http_response_code(200);

use WPS\Transients;
use WPS\Factories;

$DB_Collects       = Factories\DB\Collects_Factory::build();
$DB_Collections    = Factories\DB\Collections_Factory::build();
$Webhooks          = Factories\Webhooks_Factory::build();
$DB_Posts          = Factories\DB\Posts_Factory::build();
$json_data         = file_get_contents('php://input');


if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $collection = json_decode($json_data);

  // Hook: on collection delete
  $Webhooks->on_collections_delete($collection);

  $post_result        = $DB_Posts->delete_posts_by_ids( $DB_Collections->find_post_id_from_collection_id($collection) );
  $collects_result    = $DB_Collects->delete_collects_from_collection_id($collection->id);
  $collection_result  = $DB_Collections->delete_collection_from_collection_id($collection->id);

  // Hook: After collection delete
  $Webhooks->after_collections_delete($collection);

  Transients::delete_cached_collection_queries();
  Transients::delete_cached_single_collections();

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from collections-delete.php');
}
