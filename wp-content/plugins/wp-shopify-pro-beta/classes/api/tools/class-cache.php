<?php

namespace WPS\API\Tools;

use WPS\Messages;
use WPS\Options;


if (!defined('ABSPATH')) {
	exit;
}


class Cache extends \WPS\API {


	public function __construct($DB_Settings_Syncing) {
      $this->DB_Settings_Syncing = $DB_Settings_Syncing;
	}


	/*

	Clear Cache

	Once this point is reached, all the data has been synced.
	set_transient allows for /products and /collections permalinks to work

	Does not save errors / warnings to DB. Passes them to client directly.

	*/
	public function delete_cache($request) {

      return $this->DB_Settings_Syncing->expire_sync();

	}


   public function toggle_cache_clear($request) {
      return Options::update('wp_shopify_cache_cleared', false);
   }


	/*

	Register route: cart_icon_color

	*/
  public function register_route_tools_delete_cache() {

		return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/cache', [
			[
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => [$this, 'delete_cache']
			]
		]);

   }
   

   /*

	Register route: cart_icon_color

	*/
  public function register_route_tools_cache_toggle() {

   return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/cache/toggle', [
      [
         'methods'         => \WP_REST_Server::CREATABLE,
         'callback'        => [$this, 'toggle_cache_clear']
      ]
   ]);

}


	/*

	Hooks

	*/
	public function hooks() {
      add_action('rest_api_init', [$this, 'register_route_tools_delete_cache']);
      add_action('rest_api_init', [$this, 'register_route_tools_cache_toggle']);
	}


  /*

  Init

  */
  public function init() {
		$this->hooks();
  }


}
