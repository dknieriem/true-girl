<?php

namespace WPS\API\Options;

defined('ABSPATH') ?: exit();

class Components extends \WPS\API
{
   public function __construct()
   {
   }

   /*

	Get Custom Components Count

   $data represents an associative array with "type" props, such as

   [
      products => [1223, 12313],
      collections => [1223, 12313],
      cartButtons => [32234, 32234]
   ]

	*/
   public function get_components_options($request)
   {

      $data = $request->get_param('data');

      $mappped = array_map(function ($component_options) {

         $component = [];
         $component['componentOptions'] = maybe_unserialize(get_option('wp_shopify_component_options_' . $component_options['componentId']));

         $component['componentId'] = $component_options['componentId'];

         return $component;
      }, $data);

      return $this->handle_response([
         'response' => $mappped
      ]);
   }

   /*

	Register route: cart_icon_color

	*/
   public function register_route_components_options()
   {
      return register_rest_route(WPS_SHOPIFY_API_NAMESPACE, '/components/options', [
         [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'get_components_options']
         ]
      ]);
   }

   /*

	Hooks

	*/
   public function hooks()
   {
      add_action('rest_api_init', [$this, 'register_route_components_options']);
   }

   /*

  Init

  */
   public function init()
   {
      $this->hooks();
   }
}
