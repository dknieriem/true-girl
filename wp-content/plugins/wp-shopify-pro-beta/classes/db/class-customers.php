<?php

namespace WPS\DB;

use WPS\Utils;

if (!defined('ABSPATH')) {
	exit;
}


class Customers extends \WPS\DB {

	public $table_name_suffix;
	public $table_name;
	public $version;
	public $primary_key;
	public $lookup_key;
	public $cache_group;
	public $type;

	public $default_customer_id;
	public $default_email;
	public $default_accepts_marketing;
	public $default_created_at;
	public $default_updated_at;
	public $default_first_name;
	public $default_last_name;
	public $default_orders_count;
	public $default_state;
	public $default_total_spent;
	public $default_last_order_id;
	public $default_note;
	public $default_verified_email;
	public $default_multipass_identifier;
	public $default_tax_exempt;
	public $default_phone;
	public $default_tags;
	public $default_last_order_name;
	public $default_default_address;
	public $default_addresses;


	public function __construct() {

		// Table info
		$this->table_name_suffix  						= WPS_TABLE_NAME_CUSTOMERS;
		$this->table_name         						= $this->get_table_name();
		$this->version            						= '1.0';
		$this->primary_key        						= 'id';
		$this->lookup_key        							= 'customer_id';
		$this->cache_group        						= 'wps_db_customers';
		$this->type        										= 'customer';

		// Defaults
		$this->default_customer_id						= 0;
		$this->default_email									= '';
		$this->default_accepts_marketing			= 0;
		$this->default_created_at							= date_i18n( 'Y-m-d H:i:s' );
		$this->default_updated_at							= date_i18n( 'Y-m-d H:i:s' );
		$this->default_first_name							= '';
		$this->default_last_name							= '';
		$this->default_orders_count						= 0;
		$this->default_state									= '';
		$this->default_total_spent						= '';
		$this->default_last_order_id					= 0;
		$this->default_note										= '';
		$this->default_verified_email					= 0;
		$this->default_multipass_identifier		= '';
		$this->default_tax_exempt							= 0;
		$this->default_phone									= '';
		$this->default_tags										= '';
		$this->default_last_order_name				= '';
		$this->default_default_address				= '';
		$this->default_addresses							= '';


	}


	/* @if NODE_ENV='pro' */

	public function get_columns() {

		return [
			'id'                        => '%d',
			'customer_id'               => '%d',
			'email'                     => '%s',
			'accepts_marketing'         => '%d',
			'created_at'                => '%s',
			'updated_at'                => '%s',
			'first_name'                => '%s',
			'last_name'                 => '%s',
			'orders_count'              => '%d',
			'state'                     => '%s',
			'total_spent'               => '%s',
			'last_order_id'             => '%d',
			'note'                      => '%s',
			'verified_email'            => '%d',
			'multipass_identifier'      => '%s',
			'tax_exempt'                => '%d',
			'phone'                     => '%s',
			'tags'                      => '%s',
			'last_order_name'           => '%s',
			'default_address'           => '%s',
			'addresses'                 => '%s'
		];

	}


	/*

	Get column defaults

	*/
	public function get_column_defaults() {

		return [
			'customer_id'               => $this->default_customer_id,
			'email'                     => $this->default_email,
			'accepts_marketing'         => $this->default_accepts_marketing,
			'created_at'                => $this->default_created_at,
			'updated_at'                => $this->default_updated_at,
			'first_name'                => $this->default_first_name,
			'last_name'                 => $this->default_last_name,
			'orders_count'              => $this->default_orders_count,
			'state'                     => $this->default_state,
			'total_spent'               => $this->default_total_spent,
			'last_order_id'             => $this->default_last_order_id,
			'note'                      => $this->default_note,
			'verified_email'            => $this->default_verified_email,
			'multipass_identifier'      => $this->default_multipass_identifier,
			'tax_exempt'                => $this->default_tax_exempt,
			'phone'                     => $this->default_phone,
			'tags'                      => $this->default_tags,
			'last_order_name'           => $this->default_last_order_name,
			'default_address'           => $this->default_default_address,
			'addresses'                 => $this->default_addresses
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
			'prop_to_access'				=> 'customers',
			'change_type'				    => 'customer'
		];

	}


	/*

	Mod before change

	*/
	public function mod_before_change($customer) {

		$customer_copy = $this->copy($customer);
		$customer_copy = $this->maybe_rename_to_lookup_key($customer_copy);

		return $customer_copy;

	}


	/*

	Inserts single customer

	*/
	public function insert_customer($customer) {
		return $this->insert($customer);
	}


	/*

	Updates a single variant

	*/
	public function update_customer($customer) {
		return $this->update($this->lookup_key, $customer->id, $customer);
	}


	/*

	Deletes a single customer

	*/
	public function delete_customer($customer) {
		return $this->delete_rows($this->lookup_key, $this->get_lookup_value($customer));
	}


	/*

	Gets all customers

	*/
	public function get_customers() {
		return $this->get_all_rows();
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
			customer_id bigint(100) unsigned DEFAULT '{$this->default_customer_id}',
			email varchar(255) DEFAULT '{$this->default_email}',
			accepts_marketing tinyint(1) DEFAULT '{$this->default_accepts_marketing}',
			created_at datetime DEFAULT '{$this->default_created_at}',
			updated_at datetime DEFAULT '{$this->default_updated_at}',
			first_name longtext DEFAULT '{$this->default_first_name}',
			last_name longtext DEFAULT '{$this->default_last_name}',
			orders_count tinyint(1) DEFAULT '{$this->default_orders_count}',
			state varchar(255) DEFAULT '{$this->default_state}',
			total_spent varchar(255) DEFAULT '{$this->default_total_spent}',
			last_order_id bigint(100) unsigned DEFAULT '{$this->default_last_order_id}',
			note longtext DEFAULT '{$this->default_note}',
			verified_email tinyint(1) DEFAULT '{$this->default_verified_email}',
			multipass_identifier longtext DEFAULT '{$this->default_multipass_identifier}',
			tax_exempt tinyint(1) DEFAULT '{$this->default_tax_exempt}',
			phone varchar(255) DEFAULT '{$this->default_phone}',
			tags longtext DEFAULT '{$this->default_tags}',
			last_order_name longtext DEFAULT '{$this->default_last_order_name}',
			default_address longtext DEFAULT '{$this->default_default_address}',
			addresses longtext DEFAULT '{$this->default_addresses}',
			PRIMARY KEY  (id)
		) ENGINE=InnoDB $collate";

	}

	/* @endif */



}
