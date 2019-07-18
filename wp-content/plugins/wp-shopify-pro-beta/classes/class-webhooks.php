<?php

namespace WPS;

use WPS\Utils;
use WPS\Messages;

if (!defined('ABSPATH')) {
	exit;
}


class Webhooks {

	private $DB_Settings_Connection;
	private $DB_Settings_General;


	public function __construct($DB_Settings_Connection, $DB_Settings_General) {

		$this->DB_Settings_Connection 	= $DB_Settings_Connection;
		$this->DB_Settings_General 			= $DB_Settings_General;

	}


	/* @if NODE_ENV='pro' */


	/*

	Gets the webhook body from a supplied topic

	*/
	public function get_webhook_body_from_topic($topic) {

		$receiver = $this->get_callback_name_from_topic($topic);

		$webhook_body = $this->get_webhook_body_request($topic, $receiver);

		return $webhook_body;

	}


	/*

	Collections

	collections/create
	collections/update
	collections/delete

	*/
	public function collections_create_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/collections/collections-create.php');
	}

	public function collections_update_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/collections/collections-update.php');
	}

	public function collections_delete_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/collections/collections-delete.php');
	}


	/*

	Products

	products/create
	products/update
	products/delete

	*/
	public function product_listings_add_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/products/product-create.php');
	}

	public function product_listings_update_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/products/product-update.php');
	}

	public function product_listings_remove_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/products/product-delete.php');
	}


	/*

	Shop / App

	shop/update
	app/uninstalled

	*/
	public function shop_update_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/shop/shop-update.php');
	}

	public function app_uninstalled_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/app/app-uninstalled.php');
	}


	/*

	Orders

	orders/create
	orders/cancelled
	orders/delete
	orders/fulfilled
	orders/paid
	orders/partially-fulfilled
	orders/updated

	orders_draft/create
	orders_draft/updated
	orders_draft/delete

	orders_transactions/create

	*/
	public function orders_create_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-create.php');
	}

	public function orders_cancelled_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-cancelled.php');
	}

	public function orders_delete_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-delete.php');
	}

	public function orders_fulfilled_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-fulfilled.php');
	}

	public function orders_paid_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-paid.php');
	}

	public function orders_partially_fulfilled_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-partially-fulfilled.php');
	}

	public function orders_updated_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-updated.php');
	}

	public function draft_orders_create_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-draft-create.php');
	}

	public function draft_orders_delete_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-draft-delete.php');
	}

	public function draft_orders_update_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-draft-update.php');
	}

	public function order_transactions_create_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/orders/order-transactions-create.php');
	}


	/*

	Checkouts

	checkouts/create
	checkouts/delete
	checkouts/update

	*/
	public function checkouts_create_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/checkouts/checkout-create.php');
	}

	public function checkouts_delete_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/checkouts/checkout-delete.php');
	}

	public function checkouts_update_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/checkouts/checkout-update.php');
	}


	/*

	Customers

	customers/create
	customers/update
	customers/delete
	customers/disable
	customers/enable

	*/
	public function customers_create_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/customers/customer-create.php');
	}

	public function customers_update_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/customers/customer-update.php');
	}

	public function customers_delete_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/customers/customer-delete.php');
	}

	public function customers_disable_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/customers/customer-disable.php');
	}

	public function customers_enable_callback() {
		include(WPS_PLUGIN_DIR_PATH . 'webhooks/customers/customer-enable.php');
	}


	/*

	Default webhook topics for registering

	The total here must match the total during the get counts

	*/
	public function default_topics() {

		return [
			'product_listings/add',
			'product_listings/update',
			'product_listings/remove',
			'collections/create',
			'collections/update',
			'collections/delete',
			'shop/update',
			'app/uninstalled',
			'checkouts/create',
			'checkouts/delete',
			'checkouts/update',
			'orders/create',
			'orders/paid',
			'orders/cancelled',
			'orders/delete',
			'orders/fulfilled',
			'orders/partially_fulfilled',
			'orders/updated',
			'draft_orders/create',
			'draft_orders/delete',
			'draft_orders/update',
			'order_transactions/create',
			'customers/create',
			'customers/delete',
			'customers/disable',
			'customers/enable',
			'customers/update'
		];

	}

	public function invalid_topic_error_needle() {
		return 'Invalid topic specified';
	}

	public function is_invalid_topic_error($response) {

		if ( strpos($response->get_error_message(), $this->invalid_topic_error_needle() ) !== false ) {
			return true;
		}

		return false;

	}

	/*

	Get body for webhook request

	*/
	public function get_webhook_body_request($topic, $callback_receiver) {

		$general = $this->DB_Settings_General->get();

		// This is the URI where Shopify will send its POST request when an event occurs.
		$custom_webbooks_url = $general->url_webhooks;
		$home_url = Utils::get_site_url(); // also default webhook URL
		$admin_url = admin_url();

		if ($home_url !== $custom_webbooks_url) {

			$admin_path = Utils::construct_admin_path_from_urls($home_url, $admin_url);
			$callback_url = $custom_webbooks_url . $admin_path . "admin-ajax.php?action=" . $callback_receiver;

		} else {
			$callback_url = admin_url('admin-ajax.php') . "?action=" . $callback_receiver;
		}

		// Data to send to Shopify in our POST
		return [
			"webhook" => [
				"topic"     => $topic,
				"address"   => $callback_url,
				"format"		=> 'json'
			]
		];

	}


	private function calculate_hmac($data, $shared_secret) {
		return base64_encode( hash_hmac('sha256', $data, $shared_secret, true) );
	}


	/*

	Verifies the webhook response
	- Predicate Function (returns boolean)

	*/
	public function webhook_verified($data, $hmac_header) {

		$shared_secret = $this->DB_Settings_Connection->shared_secret();

		// Must have an active connection even if allow insecure is checked
		if (empty($shared_secret)) {
			return false;
		}

		if ($this->DB_Settings_General->allow_insecure_webhooks()) {
			return true;
		}

		return hash_equals($hmac_header, $this->calculate_hmac($data, $shared_secret));

	}


	/*

	Returns hmac value
	Used: to verify webhooks

	*/
	public function get_header_hmac() {

		if (isset($_SERVER[WPS_SHOPIFY_HEADER_VERIFY_WEBHOOKS])) {
			return $_SERVER[WPS_SHOPIFY_HEADER_VERIFY_WEBHOOKS];
		}

	}


	public function get_callback_name_from_topic($topic) {
		return str_replace('/', '_', $topic) . '_callback';
	}


	/*

	Constructs webhook warning messages by topic

	*/
	public function construct_warning_messages($topics) {

		$messages = [];

		foreach ($topics as $topic_name => $value) {
			$messages[] = Messages::get('webhooks_sync_warning') . $topic_name;
		}

		return $messages;

	}


	/*

	Filter for removal errors

	*/
	public function filter_for_removal_errors($webhooksDeletionsList) {

		return array_filter($webhooksDeletionsList, function ($value, $key) {
			return $value !== 200;
		}, ARRAY_FILTER_USE_BOTH);

	}


	/*

	Filter for register errors

	*/
	public function filter_for_register_errors($webhooksList) {

		return array_filter($webhooksList, function ($value, $key) {
			return $value == false;
		}, ARRAY_FILTER_USE_BOTH);

	}


	/*

	Webhook callback: When checkout-create is fired ...

	*/
	public function on_checkout_create($checkout) {
		do_action('wps_on_checkout_create', $checkout);
	}


	/*

	Webhook callback: When checkout-update is fired ...

	*/
	public function on_checkout_delete($checkout) {
		do_action('wps_on_checkout_delete', $checkout);
	}


	/*

	Webhook callback: When checkout-update is fired ...

	*/
	public function on_checkout_update($checkout) {
		do_action('wps_on_checkout_update', $checkout);
	}


	/*

	Webhook callback: When app-uninstall is fired ...

	*/
	public function on_app_uninstall($shop) {
		do_action('wps_on_app_uninstall', $shop);
	}


	/*

	Webhook callback: after app-uninstall work is done

	*/
	public function after_app_uninstall($shop) {
		do_action('wps_after_app_uninstall', $shop);
	}


	/*

	Webhook callback: When collections-create is fired ...

	*/
	public function on_collections_create($collection) {
		do_action('wps_on_collections_create', $collection);
	}


	/*

	Webhook callback: After collections-create is done ...

	*/
	public function after_collections_create($collection) {
		do_action('wps_after_collections_create', $collection);
	}


	/*

	Webhook callback: When collections-delete is fired ...

	*/
	public function on_collections_delete($collection) {
		do_action('wps_on_collections_delete', $collection);
	}


	/*

	Webhook callback: After collections-delete work is done ...

	*/
	public function after_collections_delete($collection) {
		do_action('wps_after_collections_delete', $collection);
	}


	/*

	Webhook callback: When collections-update is fired ...

	*/
	public function on_collections_update($collection) {
		do_action('wps_on_collections_update', $collection);
	}


	/*

	Webhook callback: After collections-update work is done

	*/
	public function after_collection_update($collection) {
		do_action('wps_after_collection_update', $collection);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function on_customer_create($customer) {
		do_action('wps_on_customer_create', $customer);
	}


	/*

	Webhook callback: wps_after_customer_create

	*/
	public function after_customer_create($customer) {
		do_action('wps_after_customer_create', $customer);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function on_customer_delete($customer) {
		do_action('wps_on_customer_delete', $customer);
	}


	/*

	Webhook callback: after customer-create work is done

	*/
	public function after_customer_delete($customer) {
		do_action('wps_after_customer_delete', $customer);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function on_customer_disable($customer) {
		do_action('wps_on_customer_disable', $customer);
	}


	/*

	Webhook callback: After customer-create is done

	*/
	public function after_customer_disable($customer) {
		do_action('wps_after_customer_disable', $customer);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function on_customer_enable($customer) {
		do_action('wps_on_customer_enable', $customer);
	}


	/*

	Webhook callback: After customer-create work is done

	*/
	public function after_customer_enable($customer) {
		do_action('wps_after_customer_enable', $customer);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function on_customer_update($customer) {
		do_action('wps_on_customer_update', $customer);
	}


	/*

	Webhook callback: After customer-create work is done ...

	*/
	public function after_customer_update($customer) {
		do_action('wps_after_customer_update', $customer);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function on_order_cancelled($order) {
		do_action('wps_on_order_cancelled', $order);
	}


	/*

	Webhook callback: When customer-create is fired ...

	*/
	public function after_order_cancelled($order) {
		do_action('wps_after_order_cancelled', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_create($order) {
		do_action('wps_on_order_create', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function after_order_create($order) {
		do_action('wps_after_order_create', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_delete($order) {
		do_action('wps_on_order_delete', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function after_order_delete($order) {
		do_action('wps_after_order_delete', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_draft_create($order) {
		do_action('wps_on_order_draft_create', $order);
	}


	/*

	Webhook callback:

	*/
	public function after_order_draft_create($order) {
		do_action('wps_after_order_draft_create', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_draft_delete($order) {
		do_action('wps_on_order_draft_delete', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function after_order_draft_delete($order) {
		do_action('wps_after_order_draft_delete', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_draft_update($order) {
		do_action('wps_on_order_draft_update', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function after_order_draft_update($order) {
		do_action('wps_after_order_draft_update', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_fulfilled($order) {
		do_action('wps_on_order_fulfilled', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function after_order_fulfilled($order) {
		do_action('wps_after_order_fulfilled', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_paid($order) {
		do_action('wps_on_order_paid', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function after_order_paid($order) {
		do_action('wps_after_order_paid', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_partially_fulfilled($order) {
		do_action('wps_on_order_partially_fulfilled', $order);
	}


	/*

	Webhook callback: wps_after_order_partially_fulfilled

	*/
	public function after_order_partially_fulfilled($order) {
		do_action('wps_after_order_partially_fulfilled', $order);
	}


	/*

	Webhook callback: When order-create is fired ...

	*/
	public function on_order_transactions_create($order) {
		do_action('wps_on_order_transactions_create', $order);
	}


	/*

	Webhook callback: wps_after_order_transactions_create

	*/
	public function after_order_transactions_create($order) {
		do_action('wps_after_order_transactions_create', $order);
	}


	/*

	Webhook callback: wps_on_order_updated

	*/
	public function on_order_updated($order) {
		do_action('wps_on_order_updated', $order);
	}


	/*

	Webhook callback: wps_after_order_updated

	*/
	public function after_order_updated($order) {
		do_action('wps_after_order_updated', $order);
	}


	/*

	Webhook callback: When product-create is fired ...

	*/
	public function on_product_create($product) {
		do_action('wps_on_product_create', $product);
	}


	/*

	Webhook callback: After product-create work is done ...

	*/
	public function after_product_create($product) {
		do_action('wps_after_product_create', $product);
	}


	/*

	Webhook callback: When product-delete is fired ...

	*/
	public function on_product_delete($product) {
		do_action('wps_on_product_delete', $product);
	}


	/*

	Webhook callback: After product-delete work is done ...

	*/
	public function after_product_delete($product) {
		do_action('wps_after_product_delete', $product);
	}


	/*

	Webhook callback: When product-update is fired ...

	*/
	public function on_product_update($product) {
		do_action('wps_on_product_update', $product);
	}


	/*

	Webhook callback: After product-update

	*/
	public function after_product_update($product) {
		do_action('wps_after_product_update', $product);
	}


	/*

	Webhook callback: When shop-update is fired ...

	*/
	public function on_shop_update($shop) {
		do_action('wps_on_shop_update', $shop);
	}


	/*

	Webhook callback: wps_after_shop_update

	*/
	public function after_shop_update($shop) {
		do_action('wps_after_shop_update', $shop);
	}


	/*

	Hooks

	*/
	public function hooks() {

		/* @if NODE_ENV='pro' */
		add_action( 'wp_ajax_customers_update_callback', [$this, 'customers_update_callback']);
		add_action( 'wp_ajax_nopriv_customers_update_callback', [$this, 'customers_update_callback']);

		add_action( 'wp_ajax_customers_create_callback', [$this, 'customers_create_callback']);
		add_action( 'wp_ajax_nopriv_customers_create_callback', [$this, 'customers_create_callback']);

		add_action( 'wp_ajax_customers_delete_callback', [$this, 'customers_delete_callback']);
		add_action( 'wp_ajax_nopriv_customers_delete_callback', [$this, 'customers_delete_callback']);

		add_action( 'wp_ajax_customers_disable_callback', [$this, 'customers_disable_callback']);
		add_action( 'wp_ajax_nopriv_customers_disable_callback', [$this, 'customers_disable_callback']);

		add_action( 'wp_ajax_customers_enable_callback', [$this, 'customers_enable_callback']);
		add_action( 'wp_ajax_nopriv_customers_enable_callback', [$this, 'customers_enable_callback']);

		add_action( 'wp_ajax_orders_create_callback', [$this, 'orders_create_callback']);
		add_action( 'wp_ajax_nopriv_orders_create_callback', [$this, 'orders_create_callback']);

		add_action( 'wp_ajax_orders_paid_callback', [$this, 'orders_paid_callback']);
		add_action( 'wp_ajax_nopriv_orders_paid_callback', [$this, 'orders_paid_callback']);

		add_action( 'wp_ajax_orders_cancelled_callback', [$this, 'orders_cancelled_callback']);
		add_action( 'wp_ajax_nopriv_orders_cancelled_callback', [$this, 'orders_cancelled_callback']);

		add_action( 'wp_ajax_orders_delete_callback', [$this, 'orders_delete_callback']);
		add_action( 'wp_ajax_nopriv_orders_delete_callback', [$this, 'orders_delete_callback']);

		add_action( 'wp_ajax_orders_fulfilled_callback', [$this, 'orders_fulfilled_callback']);
		add_action( 'wp_ajax_nopriv_orders_fulfilled_callback', [$this, 'orders_fulfilled_callback']);

		add_action( 'wp_ajax_orders_partially_fulfilled_callback', [$this, 'orders_partially_fulfilled_callback']);
		add_action( 'wp_ajax_nopriv_orders_partially_fulfilled_callback', [$this, 'orders_partially_fulfilled_callback']);

		add_action( 'wp_ajax_orders_updated_callback', [$this, 'orders_updated_callback']);
		add_action( 'wp_ajax_nopriv_orders_updated_callback', [$this, 'orders_updated_callback']);

		add_action( 'wp_ajax_draft_orders_create_callback', [$this, 'draft_orders_create_callback']);
		add_action( 'wp_ajax_nopriv_draft_orders_create_callback', [$this, 'draft_orders_create_callback']);

		add_action( 'wp_ajax_draft_orders_delete_callback', [$this, 'draft_orders_delete_callback']);
		add_action( 'wp_ajax_nopriv_draft_orders_delete_callback', [$this, 'draft_orders_delete_callback']);

		add_action( 'wp_ajax_draft_orders_update_callback', [$this, 'draft_orders_update_callback']);
		add_action( 'wp_ajax_nopriv_draft_orders_update_callback', [$this, 'draft_orders_update_callback']);

		add_action( 'wp_ajax_order_transactions_create_callback', [$this, 'order_transactions_create_callback']);
		add_action( 'wp_ajax_nopriv_order_transactions_create_callback', [$this, 'order_transactions_create_callback']);

		add_action( 'wp_ajax_product_listings_add_callback', [$this, 'product_listings_add_callback']);
		add_action( 'wp_ajax_nopriv_product_listings_add_callback', [$this, 'product_listings_add_callback']);

		add_action( 'wp_ajax_product_listings_update_callback', [$this, 'product_listings_update_callback']);
		add_action( 'wp_ajax_nopriv_product_listings_update_callback', [$this, 'product_listings_update_callback']);

		add_action( 'wp_ajax_product_listings_remove_callback', [$this, 'product_listings_remove_callback']);
		add_action( 'wp_ajax_nopriv_product_listings_remove_callback', [$this, 'product_listings_remove_callback']);

		add_action( 'wp_ajax_collections_create_callback', [$this, 'collections_create_callback']);
		add_action( 'wp_ajax_nopriv_collections_create_callback', [$this, 'collections_create_callback']);

		add_action( 'wp_ajax_collections_update_callback', [$this, 'collections_update_callback']);
		add_action( 'wp_ajax_nopriv_collections_update_callback', [$this, 'collections_update_callback']);

		add_action( 'wp_ajax_collections_delete_callback', [$this, 'collections_delete_callback']);
		add_action( 'wp_ajax_nopriv_collections_delete_callback', [$this, 'collections_delete_callback']);

		add_action( 'wp_ajax_shop_update_callback', [$this, 'shop_update_callback']);
		add_action( 'wp_ajax_nopriv_shop_update_callback', [$this, 'shop_update_callback']);

		add_action( 'wp_ajax_app_uninstalled_callback', [$this, 'app_uninstalled_callback']);
		add_action( 'wp_ajax_nopriv_app_uninstalled_callback', [$this, 'app_uninstalled_callback']);

		add_action( 'wp_ajax_checkouts_create_callback', [$this, 'checkouts_create_callback']);
		add_action( 'wp_ajax_nopriv_checkouts_create_callback', [$this, 'checkouts_create_callback']);

		add_action( 'wp_ajax_checkouts_delete_callback', [$this, 'checkouts_delete_callback']);
		add_action( 'wp_ajax_nopriv_checkouts_delete_callback', [$this, 'checkouts_delete_callback']);

		add_action( 'wp_ajax_checkouts_update_callback', [$this, 'checkouts_update_callback']);
		add_action( 'wp_ajax_nopriv_checkouts_update_callback', [$this, 'checkouts_update_callback']);

		/* @endif */

	}


	/*

	Init

	*/
	public function init() {
		$this->hooks();
	}



	/* @endif */


}
