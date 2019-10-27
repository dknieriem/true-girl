<?php

namespace WPS\DB;

use WPS\Utils;
use WPS\Transients;
use WPS\CPT;


if (!defined('ABSPATH')) {
	exit;
}


class Orders extends \WPS\DB {

	public $table_name_suffix;
	public $table_name;
	public $version;
	public $primary_key;
	public $lookup_key;
	public $cache_group;
	public $type;

	public $default_order_id;
	public $default_customer_id;
	public $default_email;
	public $default_closed_at;
	public $default_created_at;
	public $default_updated_at;
	public $default_number;
	public $default_note;
	public $default_token;
	public $default_gateway;
	public $default_total_price;
	public $default_subtotal_price;
	public $default_total_weight;
	public $default_total_tax;
	public $default_taxes_included;
	public $default_currency;
	public $default_financial_status;
	public $default_confirmed;
	public $default_total_discounts;
	public $default_total_line_items_price;
	public $default_cart_token;
	public $default_buyer_accepts_marketing;
	public $default_name;
	public $default_referring_site;
	public $default_landing_site;
	public $default_cancelled_at;
	public $default_cancel_reason;
	public $default_total_price_usd;
	public $default_checkout_token;
	public $default_reference;
	public $default_user_id;
	public $default_location_id;
	public $default_source_identifier;
	public $default_source_url;
	public $default_processed_at;
	public $default_device_id;
	public $default_phone;
	public $default_customer_locale;
	public $default_app_id;
	public $default_browser_ip;
	public $default_landing_site_ref;
	public $default_order_number;
	public $default_discount_codes;
	public $default_note_attributes;
	public $default_payment_gateway_names;
	public $default_processing_method;
	public $default_checkout_id;
	public $default_source_name;
	public $default_fulfillment_status;
	public $default_tax_lines;
	public $default_tags;
	public $default_contact_email;
	public $default_order_status_url;
	public $default_line_items;
	public $default_shipping_lines;
	public $default_billing_address;
	public $default_shipping_address;
	public $default_fulfillments;
	public $default_client_details;
	public $default_refunds;
	public $default_customer;
	public $default_test;
	public $default_discount_applications;
	public $default_admin_graphql_api_id;
	public $default_payment_details;


	public function __construct() {

		// Table info
		$this->table_name_suffix  								= WPS_TABLE_NAME_ORDERS;
		$this->table_name         								= $this->get_table_name();
		$this->version            								= '1.0';
		$this->primary_key        								= 'id';
		$this->lookup_key        									= 'order_id';
		$this->cache_group        								= 'wps_db_orders';

		// Used for hook identifiers within low level db methods like insert, update, etc
		$this->type        												= 'order';

		// Defaults
		$this->default_order_id                 	= 0;
		$this->default_customer_id              	= 0;
		$this->default_email                    	= '';
		$this->default_closed_at                	= date_i18n( 'Y-m-d H:i:s' );
		$this->default_created_at               	= date_i18n( 'Y-m-d H:i:s' );
		$this->default_updated_at               	= date_i18n( 'Y-m-d H:i:s' );
		$this->default_number                   	= 0;
		$this->default_note                     	= '';
		$this->default_token                    	= '';
		$this->default_gateway                  	= '';
		$this->default_total_price              	= 0;
		$this->default_subtotal_price           	= 0;
		$this->default_total_weight             	= 0;
		$this->default_total_tax                	= '';
		$this->default_taxes_included           	= 0;
		$this->default_currency                 	= '';
		$this->default_financial_status         	= '';
		$this->default_confirmed                	= 0;
		$this->default_total_discounts          	= '';
		$this->default_total_line_items_price   	= 0;
		$this->default_cart_token               	= '';
		$this->default_buyer_accepts_marketing  	= 0;
		$this->default_name                     	= '';
		$this->default_referring_site           	= '';
		$this->default_landing_site             	= '';
		$this->default_cancelled_at             	= date_i18n( 'Y-m-d H:i:s' );
		$this->default_cancel_reason            	= '';
		$this->default_total_price_usd          	= 0;
		$this->default_checkout_token           	= '';
		$this->default_reference                	= '';
		$this->default_user_id                  	= 0;
		$this->default_location_id              	= 0;
		$this->default_source_identifier        	= '';
		$this->default_source_url               	= '';
		$this->default_processed_at             	= date_i18n( 'Y-m-d H:i:s' );
		$this->default_device_id                	= 0;
		$this->default_phone                    	= '';
		$this->default_customer_locale          	= '';
		$this->default_app_id                   	= 0;
		$this->default_browser_ip               	= '';
		$this->default_landing_site_ref         	= '';
		$this->default_order_number             	= 0;
		$this->default_discount_codes           	= '';
		$this->default_note_attributes          	= '';
		$this->default_payment_gateway_names    	= '';
		$this->default_processing_method        	= '';
		$this->default_checkout_id              	= 0;
		$this->default_source_name              	= '';
		$this->default_fulfillment_status       	= '';
		$this->default_tax_lines                	= '';
		$this->default_tags                     	= '';
		$this->default_contact_email            	= '';
		$this->default_order_status_url         	= '';
		$this->default_line_items               	= '';
		$this->default_shipping_lines           	= '';
		$this->default_billing_address          	= '';
		$this->default_shipping_address         	= '';
		$this->default_fulfillments             	= '';
		$this->default_client_details           	= '';
		$this->default_refunds                  	= '';
		$this->default_customer                 	= '';
		$this->default_test												= '';
		$this->default_discount_applications			= '';
		$this->default_admin_graphql_api_id				= '';
		$this->default_payment_details						= '';

	}


	/* @if NODE_ENV='pro' */

	public function get_columns() {

		return [
			'id'                        => '%d',
			'order_id'                  => '%d',
			'customer_id'               => '%d',
			'email'                     => '%s',
			'closed_at'                 => '%s',
			'created_at'                => '%s',
			'updated_at'                => '%s',
			'number'                    => '%d',
			'note'                      => '%s',
			'token'                     => '%s',
			'gateway'                   => '%s',
			'total_price'               => '%f',
			'subtotal_price'            => '%f',
			'total_weight'              => '%d',
			'total_tax'                 => '%s',
			'taxes_included'            => '%d',
			'currency'                  => '%s',
			'financial_status'          => '%s',
			'confirmed'                 => '%d',
			'total_discounts'           => '%s',
			'total_line_items_price'    => '%f',
			'cart_token'                => '%s',
			'buyer_accepts_marketing'   => '%d',
			'name'                      => '%s',
			'referring_site'            => '%s',
			'landing_site'              => '%s',
			'cancelled_at'              => '%s',
			'cancel_reason'             => '%s',
			'total_price_usd'           => '%f',
			'checkout_token'            => '%s',
			'reference'                 => '%s',
			'user_id'                   => '%d',
			'location_id'               => '%d',
			'source_identifier'         => '%s',
			'source_url'                => '%s',
			'processed_at'              => '%s',
			'device_id'                 => '%d',
			'phone'                     => '%s',
			'customer_locale'           => '%s',
			'app_id'                    => '%d',
			'browser_ip'                => '%s',
			'landing_site_ref'          => '%s',
			'order_number'              => '%d',
			'discount_codes'            => '%s',
			'note_attributes'           => '%s',
			'payment_gateway_names'     => '%s',
			'processing_method'         => '%s',
			'checkout_id'               => '%d',
			'source_name'               => '%s',
			'fulfillment_status'        => '%s',
			'tax_lines'                 => '%s',
			'tags'                      => '%s',
			'contact_email'             => '%s',
			'order_status_url'          => '%s',
			'line_items'                => '%s',
			'shipping_lines'            => '%s',
			'billing_address'           => '%s',
			'shipping_address'          => '%s',
			'fulfillments'              => '%s',
			'client_details'            => '%s',
			'refunds'                   => '%s',
			'customer'                  => '%s',
			'test'											=> '%s',
			'discount_applications'			=> '%s',
			'admin_graphql_api_id'			=> '%s',
			'payment_details'						=> '%s'
		];

	}


	public function get_column_defaults() {

		return [
			'order_id'                  => $this->default_order_id,
			'customer_id'               => $this->default_customer_id,
			'email'                     => $this->default_email,
			'closed_at'                 => $this->default_closed_at,
			'created_at'                => $this->default_created_at,
			'updated_at'                => $this->default_updated_at,
			'number'                    => $this->default_number,
			'note'                      => $this->default_note,
			'token'                     => $this->default_token,
			'gateway'                   => $this->default_gateway,
			'total_price'               => $this->default_total_price,
			'subtotal_price'            => $this->default_subtotal_price,
			'total_weight'              => $this->default_total_weight,
			'total_tax'                 => $this->default_total_tax,
			'taxes_included'            => $this->default_taxes_included,
			'currency'                  => $this->default_currency,
			'financial_status'          => $this->default_financial_status,
			'confirmed'                 => $this->default_confirmed,
			'total_discounts'           => $this->default_total_discounts,
			'total_line_items_price'    => $this->default_total_line_items_price,
			'cart_token'                => $this->default_cart_token,
			'buyer_accepts_marketing'   => $this->default_buyer_accepts_marketing,
			'name'                      => $this->default_name,
			'referring_site'            => $this->default_referring_site,
			'landing_site'              => $this->default_landing_site,
			'cancelled_at'              => $this->default_cancelled_at,
			'cancel_reason'             => $this->default_cancel_reason,
			'total_price_usd'           => $this->default_total_price_usd,
			'checkout_token'            => $this->default_checkout_token,
			'reference'                 => $this->default_reference,
			'user_id'                   => $this->default_user_id,
			'location_id'               => $this->default_location_id,
			'source_identifier'         => $this->default_source_identifier,
			'source_url'                => $this->default_source_url,
			'processed_at'              => $this->default_processed_at,
			'device_id'                 => $this->default_device_id,
			'phone'                     => $this->default_phone,
			'customer_locale'           => $this->default_customer_locale,
			'app_id'                    => $this->default_app_id,
			'browser_ip'                => $this->default_browser_ip,
			'landing_site_ref'          => $this->default_landing_site_ref,
			'order_number'              => $this->default_order_number,
			'discount_codes'            => $this->default_discount_codes,
			'note_attributes'           => $this->default_note_attributes,
			'payment_gateway_names'     => $this->default_payment_gateway_names,
			'processing_method'         => $this->default_processing_method,
			'checkout_id'               => $this->default_checkout_id,
			'source_name'               => $this->default_source_name,
			'fulfillment_status'        => $this->default_fulfillment_status,
			'tax_lines'                 => $this->default_tax_lines,
			'tags'                      => $this->default_tags,
			'contact_email'             => $this->default_contact_email,
			'order_status_url'          => $this->default_order_status_url,
			'line_items'                => $this->default_line_items,
			'shipping_lines'            => $this->default_shipping_lines,
			'billing_address'           => $this->default_billing_address,
			'shipping_address'          => $this->default_shipping_address,
			'fulfillments'              => $this->default_fulfillments,
			'client_details'            => $this->default_client_details,
			'refunds'                   => $this->default_refunds,
			'customer'                  => $this->default_customer,
			'test'											=> $this->default_test,
			'discount_applications'			=> $this->default_discount_applications,
			'admin_graphql_api_id'			=> $this->default_admin_graphql_api_id,
			'payment_details'						=> $this->default_payment_details
		];

	}


	/*

	The modify options used for inserting / updating / deleting

	*/
	public function modify_options($shopify_item, $item_lookup_key = WPS_PRODUCTS_LOOKUP_KEY) {

		return [
			'item'									=> $shopify_item,
			'item_lookup_key'				=> $item_lookup_key,
			'item_lookup_value'			=> $shopify_item->id,
			'prop_to_access'				=> 'orders',
			'change_type'				    => 'order'
		];

	}


	/*

	Mod before change

	Things to do before updating / inserting database records.

	*/
	public function mod_before_change($order) {

		$order_copy = $this->copy($order);

		$order_copy = $this->maybe_rename_to_lookup_key($order_copy);
		$order_copy = $this->add_customer_id_to_order($order_copy);

		return $order_copy;

	}


	/*

	Inserts single customer

	*/
	public function insert_order($order) {
		return $this->insert($order);
	}


	/*

	Updates a single variant

	*/
	public function update_order($order) {
		return $this->update($this->lookup_key, $this->get_lookup_value($order), $order);
	}


	/*

	Deletes a single order

	*/
	public function delete_order($order) {
		return $this->delete_rows($this->lookup_key, $this->get_lookup_value($order));
	}


	/*

	Get Orders

	*/
	public function get_orders() {
		return $this->get_all_rows();
	}


	/*

	Helper to simply find an order id.

	$order can come either from Shopify or our own DB so we need to check for both order_id and id

	*/
	public function get_order_id($order) {

	}


	/*

	Delete orders from product ID

	*/
	public function delete_orders_from_product_id($product_id) {
		return $this->delete_rows('product_id', $product_id);
	}


	/*

	Gets all orders associated with a given product, by product id

	*/
	public function get_orders_from_product_id($product_id) {
		return $this->get_rows('product_id', $product_id);
	}


	/*

	Add customer id to order

	*/
	public function add_customer_id_to_order($order) {

		$order = Utils::convert_array_to_object($order);

		if ( Utils::has($order, 'customer') && Utils::has($order->customer, 'id') ) {
			$order->customer_id = $order->customer->id;
		}

		return $order;

	}


	/*

	Get Single Order

	*/
	public function get_order($orderID = null) {

		global $wpdb;

		if ($orderID === null) {
			$orderID = get_the_ID();
		}

		$query = "SELECT orders.* FROM $this->table_name as orders WHERE orders.post_id = %d";

		return $wpdb->get_row( $wpdb->prepare($query, $orderID) );

	}


	/*

	Creates a table query string

	*/
	public function create_table_query($table_name = false) {

		if ( !$table_name ) {
			$table_name = $this->table_name;
		}

		$collate = $this->collate();

		return "CREATE TABLE $table_name (
			id bigint(100) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(100) unsigned NOT NULL DEFAULt '{$this->default_order_id}',
			customer_id bigint(100) unsigned DEFAULT '{$this->default_customer_id}',
			email varchar(255) DEFAULT '{$this->default_email}',
			closed_at datetime DEFAULT '{$this->default_closed_at}',
			created_at datetime DEFAULT '{$this->default_created_at}',
			updated_at datetime DEFAULT '{$this->default_updated_at}',
			number bigint(100) unsigned DEFAULT '{$this->default_number}',
			note longtext DEFAULT '{$this->default_note}',
			token varchar(255) DEFAULT '{$this->default_token}',
			gateway varchar(255) DEFAULT '{$this->default_gateway}',
			total_price decimal(12,2) DEFAULT '{$this->default_total_price}',
			subtotal_price decimal(12,2) DEFAULT '{$this->default_subtotal_price}',
			total_weight bigint(100) unsigned DEFAULT '{$this->default_total_weight}',
			total_tax varchar(100) DEFAULT '{$this->default_total_tax}',
			taxes_included tinyint(1) DEFAULT '{$this->default_taxes_included}',
			currency varchar(100) DEFAULT '{$this->default_currency}',
			financial_status varchar(100) DEFAULT '{$this->default_financial_status}',
			confirmed tinyint(1) DEFAULT '{$this->default_confirmed}',
			total_discounts varchar(100) DEFAULT '{$this->default_total_discounts}',
			total_line_items_price decimal(12,2) DEFAULT '{$this->default_total_line_items_price}',
			cart_token longtext DEFAULT '{$this->default_cart_token}',
			buyer_accepts_marketing tinyint(1) DEFAULT '{$this->default_buyer_accepts_marketing}',
			name varchar(100) DEFAULT '{$this->default_name}',
			referring_site longtext DEFAULT '{$this->default_referring_site}',
			landing_site longtext DEFAULT '{$this->default_landing_site}',
			cancelled_at datetime DEFAULT '{$this->default_cancelled_at}',
			cancel_reason varchar(255) DEFAULT '{$this->default_cancel_reason}',
			total_price_usd decimal(12,2) DEFAULT '{$this->default_total_price_usd}',
			checkout_token varchar(255) DEFAULT '{$this->default_checkout_token}',
			reference varchar(255) DEFAULT '{$this->default_reference}',
			user_id bigint(100) unsigned DEFAULT '{$this->default_user_id}',
			location_id bigint(100) unsigned DEFAULT '{$this->default_location_id}',
			source_identifier varchar(255) DEFAULT '{$this->default_source_identifier}',
			source_url varchar(255) DEFAULT '{$this->default_source_url}',
			processed_at datetime DEFAULT '{$this->default_processed_at}',
			device_id bigint(100) unsigned DEFAULT '{$this->default_device_id}',
			phone varchar(100) DEFAULT '{$this->default_phone}',
			customer_locale varchar(100) DEFAULT '{$this->default_customer_locale}',
			app_id bigint(100) unsigned DEFAULT '{$this->default_app_id}',
			browser_ip varchar(100) DEFAULT '{$this->default_browser_ip}',
			landing_site_ref longtext DEFAULT '{$this->default_landing_site_ref}',
			order_number bigint(100) unsigned DEFAULT '{$this->default_order_number}',
			discount_codes longtext DEFAULT '{$this->default_discount_codes}',
			note_attributes longtext DEFAULT '{$this->default_note_attributes}',
			payment_gateway_names varchar(100) DEFAULT '{$this->default_payment_gateway_names}',
			processing_method varchar(100) DEFAULT '{$this->default_processing_method}',
			checkout_id bigint(100) unsigned DEFAULT '{$this->default_checkout_id}',
			source_name varchar(100) DEFAULT '{$this->default_source_name}',
			fulfillment_status varchar(100) DEFAULT '{$this->default_fulfillment_status}',
			tax_lines longtext DEFAULT '{$this->default_tax_lines}',
			tags longtext DEFAULT '{$this->default_tags}',
			contact_email varchar(100) DEFAULT '{$this->default_contact_email}',
			order_status_url longtext DEFAULT '{$this->default_order_status_url}',
			line_items longtext DEFAULT '{$this->default_line_items}',
			shipping_lines longtext DEFAULT '{$this->default_shipping_lines}',
			billing_address longtext DEFAULT '{$this->default_billing_address}',
			shipping_address longtext DEFAULT '{$this->default_shipping_address}',
			fulfillments longtext DEFAULT '{$this->default_fulfillments}',
			client_details longtext DEFAULT '{$this->default_client_details}',
			refunds longtext DEFAULT '{$this->default_refunds}',
			customer longtext DEFAULT '{$this->default_customer}',
			test longtext DEFAULT '{$this->default_test}',
			discount_applications longtext DEFAULT '{$this->default_discount_applications}',
			admin_graphql_api_id longtext DEFAULT '{$this->default_admin_graphql_api_id}',
			payment_details longtext DEFAULT '{$this->default_payment_details}',
			PRIMARY KEY  (id)
		) ENGINE=InnoDB $collate";

	}

	/* @endif */

}
