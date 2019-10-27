<?php

/*

@description   Products all template

@version       2.0.0
@since         1.0.49
@path          templates/components/products/pricing/pricing.php

@docs          https://wpshop.io/docs/templates/components/products/pricing/pricing

*/

defined('ABSPATH') ?: exit();
$Render_Products = WPS\Factories\Render\Products\Products_Factory::build();

$Templates = WPS\Factories\Templates_Factory::build();

$Render_Products->title([
   'product' => $data->product,
   'skip_required_data' => true,
   'render_from_server' => $data->render_from_server
]);

$Render_Products->description([
   'product' => $data->product,
   'skip_required_data' => true,
   'render_from_server' => $data->render_from_server
]);

// $Render_Products->pricing([
//    'product' => $data->product,
//    'skip_required_data' => true,
//    'render_from_server' => $data->render_from_server
// ]);

$Render_Products->buy_button([
   'product' => $data->product,
   'skip_required_data' => true,
   'render_from_server' => false
]);

// $Render_Products->gallery([
//    'product' => $data->product,
//    'skip_required_data' => true,
//    'render_from_server' => true
// ]);

?>
