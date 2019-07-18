<?php

http_response_code(200);

use WPS\Transients;
use WPS\Factories;

$Webhooks         = Factories\Webhooks_Factory::build();
$DB_Posts         = Factories\DB\Posts_Factory::build();
$DB_Products      = Factories\DB\Products_Factory::build();
$DB_Variants      = Factories\DB\Variants_Factory::build();
$DB_Options       = Factories\DB\Options_Factory::build();
$DB_Images        = Factories\DB\Images_Factory::build();
$DB_Collects      = Factories\DB\Collects_Factory::build();
$DB_Tags          = Factories\DB\Tags_Factory::build();

$json_data        = file_get_contents('php://input');


if ($Webhooks->webhook_verified($json_data, $Webhooks->get_header_hmac())) {

  $product = json_decode($json_data);

  // Hook: wps_on_product_delete
  $Webhooks->on_product_delete($product);

  $product_id = $product->product_listing->product_id;

  $post_id = $DB_Products->find_post_id_from_product_id($product_id);

  $DB_Products->delete_products_from_product_id($product_id);
  $DB_Variants->delete_variants_from_product_id($product_id);
  $DB_Options->delete_options_from_product_id($product_id);
  $DB_Images->delete_images_from_product_id($product_id);
  $DB_Collects->delete_collects_from_product_id($product_id);
  $DB_Tags->delete_tags_from_product_id($product_id);
  $DB_Posts->delete_posts_by_ids($post_id);

  Transients::delete_cached_prices();
  Transients::delete_cached_variants();
  Transients::delete_cached_product_single();
  Transients::delete_cached_product_queries();

  // Hook: wps_after_product_delete
  $Webhooks->after_product_delete($product);

} else {
  error_log('WP Shopify Error - Unable to verify webhook response from product-delete.php');

}
