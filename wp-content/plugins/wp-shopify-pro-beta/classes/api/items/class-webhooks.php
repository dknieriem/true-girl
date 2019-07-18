<?php

namespace WPS\API\Items;


if (!defined('ABSPATH')) {
	exit;
}


class Webhooks extends \WPS\API {

	public function __construct($DB_Settings_Syncing, $Webhooks, $Processing_Webhooks, $Processing_Webhooks_Deletions, $Shopify_API) {

		$this->DB_Settings_Syncing									= $DB_Settings_Syncing;
		$this->Webhooks															= $Webhooks;
		$this->Processing_Webhooks 									= $Processing_Webhooks;
		$this->Processing_Webhooks_Deletions				= $Processing_Webhooks_Deletions;
		$this->Shopify_API													= $Shopify_API;

	}

	/* @if NODE_ENV='pro' */

	/*

	Get Smart Collections Count

	Nonce checks are handled automatically by WordPress

	*/
	public function get_webhooks_count($request) {

		return [
			'webhooks' => WPS_TOTAL_WEBHOOKS_COUNT
		];

	}


	public function delete_webhooks($request) {

		$response = $this->Shopify_API->get_webhooks();

		return $this->handle_response([
			'response' 				=> $response,
			'access_prop' 		=> 'webhooks',
			'process_fns' 		=> [
				$this->Processing_Webhooks_Deletions
			]
		]);

	}


	/*

	Registers a single webhook

	*/
	public function register_webhooks($request) {

		if ($this->DB_Settings_Syncing->is_syncing()) {
			$this->Async_Processing_Webhooks->process($request);
		}

	}


	/*

	Registers all single webhook

	*/
	public function register_all_webhooks($request) {

		return $this->handle_response([
			'response' 				=> $this->Webhooks->default_topics(),
			'warning_message'	=> 'webhooks_not_found',
			'process_fns' 		=> [
				$this->Processing_Webhooks
			]
		]);

	}


	/*

	Register route: cart_icon_color

	*/
  public function register_route_webhooks_count() {

		return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/webhooks/count', [
			[
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => [$this, 'get_webhooks_count']
			]
		]);

	}


	/*

	Register route: cart_icon_color

	*/
  public function register_route_webhooks() {

		return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/webhooks', [
			[
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => [$this, 'register_all_webhooks']
			]
		]);

	}


	/*

	Register route: cart_icon_color

	*/
  public function register_route_webhooks_delete() {

		return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/webhooks/delete', [
			[
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => [$this, 'delete_webhooks']
			]
		]);

	}


	/*

	Hooks

	*/
	public function hooks() {

		add_action('rest_api_init', [$this, 'register_route_webhooks']);
		add_action('rest_api_init', [$this, 'register_route_webhooks_count']);
		add_action('rest_api_init', [$this, 'register_route_webhooks_delete']);

	}


  /*

  Init

  */
  public function init() {
		$this->hooks();
  }

	/* @endif */

}
