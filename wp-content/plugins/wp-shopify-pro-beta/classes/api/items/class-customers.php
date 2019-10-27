<?php

namespace WPS\API\Items;

use WPS\Messages;
use WPS\Utils;
use WPS\Utils\Data as Utils_Data;

if (!defined('ABSPATH')) {
	exit;
}


class Customers extends \WPS\API {


	public function __construct($DB_Settings_General, $DB_Settings_Syncing, $Shopify_API, $Processing_Customers) {
		$this->DB_Settings_General 			= $DB_Settings_General;
		$this->DB_Settings_Syncing 			= $DB_Settings_Syncing;
		$this->Shopify_API 							= $Shopify_API;
		$this->Processing_Customers			= $Processing_Customers;
	}

	/* @if NODE_ENV='pro' */

	/*

	Get Collections Count

	*/
	public function get_customers_count($request) {

		$response = $this->Shopify_API->get_customers_count();

		return $this->handle_response([
			'response' 				=> $response,
			'access_prop'			=> 'count',
			'return_key' 			=> 'customers',
			'warning_message'	=> 'customers_count_not_found'
		]);

	}


	/*

	Get Customers

	Runs for each "page" of the Shopify API

	*/
	public function get_customers($request) {

		// Grab customers from Shopify
		$param_limit 						= $this->DB_Settings_General->get_items_per_request();
		$param_current_page 		= $request->get_param('page');
		$param_status 					= 'any';

		$response = $this->Shopify_API->get_customers_per_page($param_limit, $param_current_page, $param_status);

		return $this->handle_response([
			'response' 				=> $response,
			'access_prop'			=> 'customers',
			'return_key' 			=> 'customers',
			'warning_message'	=> 'missing_customers_for_page',
			'process_fns'			=> [
				$this->Processing_Customers
			]
		]);

	}


	/*

	Register route: cart_icon_color

	*/
  public function register_route_customers_count() {

		return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/customers/count', [
			[
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => [$this, 'get_customers_count']
			]
		]);

	}


	/*

	Register route: cart_icon_color

	*/
  public function register_route_customers() {

		return register_rest_route( WPS_SHOPIFY_API_NAMESPACE, '/customers', [
			[
				'methods'         => \WP_REST_Server::CREATABLE,
				'callback'        => [$this, 'get_customers']
			]
		]);

	}


	/*

	Hooks

	*/
	public function hooks() {

		add_action('rest_api_init', [$this, 'register_route_customers_count']);
		add_action('rest_api_init', [$this, 'register_route_customers']);

	}


  /*

  Init

  */
  public function init() {
		$this->hooks();
  }

	/* @endif */

}
