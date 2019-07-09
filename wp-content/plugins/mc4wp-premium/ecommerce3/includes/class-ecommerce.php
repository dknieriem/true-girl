<?php

/**
* Class MC4WP_Ecommerce
*
* @since 4.0
*/
class MC4WP_Ecommerce {

	/**
	* @const string
	*/
	const META_KEY = 'mc4wp_updated_at';

	/**
	* @var MC4WP_Ecommerce_Object_Transformer
	*/
	public $transformer;

	/**
	* @var string The ID of the store object in MailChimp
	*/
	private $store_id;

	const ERR_NO_ITEMS = 29001;

	/**
	* Constructor
	*
	* @param string $store_id
	* @param MC4WP_Ecommerce_Object_Transformer $transformer
	*/
	public function __construct( $store_id, MC4WP_Ecommerce_Object_Transformer $transformer ) {
		$this->store_id = $store_id;
		$this->transformer = $transformer;
	}

	/**
	* @param string $store_id
	*/
	public function set_store_id( $store_id ) {
		$this->store_id = $store_id;
	}

	/**
	* Update the "last updated" settings to now.
	*
	* @param int $post_id
	*/
	public function touch( $post_id = 0 ) {
		if( $post_id ) {
			update_post_meta( $post_id, self::META_KEY, date( 'c' ) );
		}

		mc4wp_ecommerce_update_settings( array( 'last_updated' => time() ) );
	}

	/**
	* @param string $cart_id
	*
	* @return object
	*/
	public function get_cart( $cart_id ) {
		$api = $this->get_api();

		return $api->get_ecommerce_store_cart( $this->store_id, $cart_id );
	}

	/**
	* Add OR update a cart in MailChimp.
	*
	* @param string $cart_id
	* @param object|WP_User $email_address
	* @param array $cart_contents
	*
	* @return bool
	*/
	public function update_cart( $cart_id, $customer, array $cart_contents ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		if( is_array( $customer ) && isset( $customer['customer'] ) ) {
			// For backwards compatibility with queue data from before MC4WP Premium v3.4
			$cart_data = $customer;
		} else {
			$customer_data = $this->transformer->customer( $customer );
			$cart_data = $this->transformer->cart( $customer_data, $cart_contents );
		}		

		// add (or update) customer
		$customer_data = $api->add_ecommerce_store_customer( $store_id, $cart_data['customer'] );

		// replace customer object in cart data with array with just an id
		$cart_data['customer'] = array(
			'id' => $customer_data->id,
		);

		// add or update cart
		try {
			$cart_data = $api->update_ecommerce_store_cart( $store_id, $cart_id, $cart_data );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			$cart_data = $api->add_ecommerce_store_cart( $store_id, $cart_data );
		}

		$this->touch();

		return true;
	}

	/**
	* @param string $cart_id
	*
	* @return bool
	*/
	public function delete_cart( $cart_id ) {
		$api = $this->get_api();
		$store_id = $this->store_id;
		$result = $api->delete_ecommerce_store_cart( $store_id, $cart_id );
		$this->touch();
		return $result;
	}

	/**
	* @param WP_User|WC_Order|object $customer_data
	*
	* @return string
	*/
	public function update_customer( $customer_data ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		// get customer data
		$customer_data = $this->transformer->customer( $customer_data );

		// add (or update) customer
		$api->add_ecommerce_store_customer( $store_id, $customer_data );

		$this->touch();

		return $customer_data['id'];
	}

	/**
	* @param int|WC_Order $order
	* @return boolean
	* @throws Exception
	*/
	public function update_order( $order ) {
		// get & validate order
		$order = wc_get_order( $order );
		if( ! $order ) {
			throw new Exception( sprintf( "Order #%d is not a valid order ID.", $order ) );
		}

		$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;

		/**
		* Filters whether the order should be sent to MailChimp.
		*
		* @param boolean $send Whether to send the order to MailChimp, defaults to true.
		* @param WC_Order $order The order object.
		*/
		$send_to_mailchimp = apply_filters( 'mc4wp_ecommerce_send_order_to_mailchimp', true, $order );
		if( ! $send_to_mailchimp ) {
			return false;
		}

		// add or update customer in MailChimp
		$this->update_customer( $order );

		// get order data
		$data = $this->transformer->order( $order );

		// validate existence of products in order
		foreach( $data['lines'] as $key => $line ) {
			$product = wc_get_product( $line['product_id'] );
			$product_variation = wc_get_product( $line['product_variant_id'] );
			if( ! $product || ! $product_variation ) {
				// product or variant does no longer exist, replace with a generic deleted product.
				$this->ensure_deleted_product();

				// replace ID with ID of the generic "deleted product"
				$data['lines'][$key]['product_id'] = 'deleted';
				$data['lines'][$key]['product_variant_id'] = 'deleted';
			}
		}

		// throw exception if order contains no lines
		if( empty( $data['lines'] ) ) {
			throw new Exception("Order contains no items.", self::ERR_NO_ITEMS);
		}

		// add OR update order in MailChimp
		return $this->is_object_tracked( $order_id ) ? $this->order_update( $order, $data ) : $this->order_add( $order, $data );
	}

	/**
	* @param int $order_id
	*
	* @return boolean
	*
	* @throws Exception
	*/
	public function delete_order( $order_id ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		try {
			$success = $api->delete_ecommerce_store_order( $store_id, $order_id );
		} catch ( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// good, order already non-existing
			$success = true;
		}

		// remove meta on success
		delete_post_meta( $order_id, self::META_KEY );

		$this->touch();

		return $success;
	}

	/**
	* @param WC_Order $order
	* @param array $data
	* @param bool $recurse
	*
	* @return bool
	*
	* @throws MC4WP_API_Exception
	*/
	private function order_add( WC_Order $order, array $data, $recurse = true ) {
		$api = $this->get_api();
		$store_id = $this->store_id;
		
		// check for method existence first because wc pre-3.0
		$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
		$order_number = method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : $order_id;

		try {
			$response = $api->add_ecommerce_store_order( $store_id, $data );
		}  catch( MC4WP_API_Exception $e ) {
			// update order if it already exists
			if( $recurse && stripos( $e->detail, 'order with the provided ID already exists' ) !== false ) {
				return $this->order_update( $order, $data, false );
			}

			// if campaign_id data is corrupted somehow, retry without campaign data.
			if( ! empty( $data['campaign_id'] ) && stripos( $e->detail, 'campaign with the provided ID does not exist' ) !== false  ) {
				unset( $data['campaign_id'] );
				return $this->order_add( $order, $data );
			}

			throw $e;
		}

		$this->touch( $order_id );
		return true;
	}

    /**
     * @param WC_Order $order
     * @param array $data
     * @param bool $recurse
     * @return bool
     * @throws MC4WP_API_Resource_Not_Found_Exception
     */
	private function order_update( WC_Order $order, array $data, $recurse = true ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		// check for method existence first because wc pre-3.0
		$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
		$order_number = method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : $order_id;

		try {
			// use order number here as that is what we send in order_add too.
			$response = $api->update_ecommerce_store_order( $store_id, $order_number, $data );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			if( $recurse ) {
				return $this->order_add( $order, $data, false );
			}

			throw $e;
		} catch( MC4WP_API_Exception $e ) {
			// if campaign_id data is corrupted somehow, retry without campaign data.
			if( ! empty( $data['campaign_id'] ) && stripos( $e->detail, 'campaign with the provided ID does not exist' ) !== false  ) {
				unset( $data['campaign_id'] );
				return $this->order_update( $order, $data );
			}

			throw $e;
		}

		$this->touch( $order_id );
		return true;
	}

	/**
	* Since MailChimp does not allow connecting two sites that share the same domain (they strip off the entire subdirectory part) we generate a unique domain here.
	* @return string
	*/
	private function get_site_domain() {
		$domain = str_ireplace( array( 'https://', 'http://', '://' ), '', get_option( 'siteurl' ) );

		if( is_multisite() && strpos( $domain, '/' ) > strpos( $domain, '.' ) ) {
			$subdir_pos = strpos( $domain, '/' );
			$subdir = substr( $domain, $subdir_pos + 1 );
			$domain = substr( $domain, 0, $subdir_pos );
			$domain = $subdir . '.' . $domain;
		}

		return $domain;
	}

	/**
	* Add or update store in MailChimp.
	*
	* @param array $data
	* @throws MC4WP_API_Exception
	* @return object
	*/
	public function update_store( array $data ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		$data['id'] = (string) $store_id;
		$data['platform'] = 'WooCommerce';
		$data['domain'] = $this->get_site_domain();
		$data['email_address'] = get_option( 'admin_email' );
		$data['primary_locale'] = substr( get_locale(), 0, 2 );
		$data['address'] = array(
			'address1' => get_option( 'woocommerce_store_address', '' ),
			'address2' => get_option( 'woocommerce_store_address_2', '' ),
			'city' => get_option( 'woocommerce_store_city', '' ),
			'postal_code' => get_option( 'woocommerce_store_postcode', '' ),
			'country_code' => get_option( 'woocommerce_default_country', '' ),
		);

		// make sure we got a boolean value.
		if( isset( $data['is_syncing'] ) ) {
			$data['is_syncing'] = !!$data['is_syncing'];
		}

		/**
		* Filter the store data we send to MailChimp.
		*
		* @param array $data
		*/
		$data = apply_filters( 'mc4wp_ecommerce_store_data', $data );

		try {
			$res = $api->update_ecommerce_store( $store_id, $data );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			$res = $api->add_ecommerce_store( $data );
		} catch( MC4WP_API_Exception $e ) {
			if( $e->status == 400 && stripos( $e->detail, "list may not be changed" ) !== false ) {
				// delete local tracking indicators
				delete_post_meta_by_key( MC4WP_Ecommerce::META_KEY );

				// delete old store
				$api->delete_ecommerce_store( $store_id );

				// add new store
				$res = $api->add_ecommerce_store( $data );
			} else {
				throw $e;
			}
		}

		$this->touch();

		return $res;
	}

	public function ensure_connected_site() {
		$api = $this->get_api();
		$client = $api->get_client();

		try {
			// first, query site to see if it exists
			$resource = sprintf( '/connected-sites/%s', $this->store_id );
			$response = $client->get( $resource, array() );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// if it does not exist, add it
			$response = $client->post( '/connected-sites', array( 
				'foreign_id' => $this->store_id, 
				'domain' => $this->get_site_domain(),
			) );
		}
	}

	public function verify_store_script_installation() {
		$api = $this->get_api();
		$client = $api->get_client();
		$resource = sprintf( '/connected-sites/%s/actions/verify-script-installation', $this->store_id );
		$client->post( $resource, array() );
	}

	/**
	* Add or update a product + variants in MailChimp.
	*
	* TODO: MailChimp interface does not yet reflect product "updates".
	*
	* @param int|WC_Product $product Post object or post ID of the product.
	* @return boolean
	* @throws Exception
	*/
	public function update_product( $product ) {
		$product = wc_get_product( $product );

		// check if product exists
		if( ! $product ) {
			throw new Exception( sprintf( "#%d is not a valid product ID", $product ) );
		}

		// get product id (with backwards compat for WooCommerce < 3.x)
		$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;

		// make sure product is not a product-variation
		if( $product instanceof WC_Product_Variation ) {
			throw new Exception( sprintf( "#%d is a variation of another product. Use the variable parent product instead.", $product_id ) );
		}

		$data = $this->transformer->product( $product );

		return $this->is_object_tracked( $product_id ) ? $this->product_update( $product, $data ) : $this->product_add( $product, $data );
	}

	/**
	* @param int $product_id
	* @return boolean
	*
	* @throws Exception
	*/
	public function delete_product( $product_id ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		try {
			$success = $api->delete_ecommerce_store_product( $store_id, $product_id );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// product or store already non-existing: good!
			$success = true;
		}

		delete_post_meta( $product_id, self::META_KEY );

		$this->touch();

		return $success;
	}


	/**
	* @param WC_Product $product
	* @param array $data
	* @param bool $recurse
	*
	* @return bool
	*
	* @throws MC4WP_API_Exception
	*/
	private function product_add( WC_Product $product, array $data, $recurse = true ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		try {
			$response = $api->add_ecommerce_store_product( $store_id, $data );
		} catch( MC4WP_API_Exception $e ) {
			// update product if it already exists remotely.
			if( $recurse && ( stripos( $e->detail, 'product with the provided ID already exists' ) || stripos( $e->detail, 'variant with the provided ID already exists' ) ) ) {
				return $this->product_update( $product, $data, false );
			}

			throw $e;
		}

		// get product id (with backwards compat for WooCommerce < 3.x)
		$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
		$this->touch( $product_id );
		
		return true;
	}

    /**
     * @param WC_Product $product
     * @param array $data
     * @param bool $recurse
     * @return bool
     * @throws MC4WP_API_Resource_Not_Found_Exception
     */
	private function product_update( WC_Product $product, array $data, $recurse = true ) {
		$api = $this->get_api();
		$store_id = $this->store_id;

		// get product id (with backwards compat for WooCommerce < 3.x)
		$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;

		try {
			// this method was added in MailChimp for WordPress v4.0.12
			if( method_exists( $api, 'update_ecommerce_store_product' ) ) {
				$response = $api->update_ecommerce_store_product( $store_id, $product_id, $data );
			} else {
				// FALLBACK: update each variant individually
				foreach ($data['variants'] as $variant_data) {
					$response = $api->add_ecommerce_store_product_variant( $store_id, $product_id, $variant_data );
				}
			}
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			if( $recurse ) {
				return $this->product_add( $product, $data, false );
			}

			throw $e;
		} 

		$this->touch( $product_id );
		return true;
	}

	/**
	* @param int $object_id
	*
	* @return bool
	*/
	public function is_object_tracked( $object_id ) {
		return !! get_post_meta( $object_id, self::META_KEY, true );
	}

	/**
	* @return MC4WP_API_v3
	*/
	private function get_api() {
		return mc4wp('api');
	}

	/**
	* Ensures the existence of a deleted product in MailChimp, to be used in orders referencing a no-longer existing product.
	*
	* @return void
	*/
	private function ensure_deleted_product() {
		static $exists = false;

		if( $exists ) {
			return;
		}

		// create or update deleted product in MailChimp
		$store_id = $this->store_id;
		$api = $this->get_api();

		$product_id = 'deleted';
		$product_title = '(deleted product)';
		$data = array(
			'id' => $product_id,
			'title' => $product_title,
			'variants' => array(
				array(
					'id' => $product_id,
					'title' => $product_title,
					'inventory_quantity' => 0,
				)
			)
		);

		try {
			$response = $api->update_ecommerce_store_product( $store_id, $product_id, $data );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			$response = $api->add_ecommerce_store_product( $store_id, $data );
		}

		// set flag to short-circuit this function next time it runs
		$exists = true;
	}


	/**********************
	* 		Promo codes 	 *
	**********************/

	/**
	* Deletes the associated promo from the connected store.
	* @param int $coupon_id
	*/
	public function delete_promo( $coupon_id ) {
		$store_id = $this->store_id;
		$api = $this->get_api();

		// fail silently if API class does not have method. This means user should update their MailChimp for WordPress version.
		if( ! method_exists( $api, 'delete_ecommerce_store_promo_rule' ) ) {
			return;
		}

		try {
			// TODO: Check if we need to delete children of the promo rule?
			//$api->delete_ecommerce_store_promo_rule_promo_code( $store_id, $coupon_id, $coupon_id );
			$api->delete_ecommerce_store_promo_rule( $store_id, $coupon_id );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// good. promo was not there to begin with.
		}

		$this->touch();
	}

	/**
	* Adds or updates the associated promo in the connected store.
	* @param int $coupon_id
	*/
	public function update_promo( $coupon_id ) {
		$store_id = $this->store_id;
		$api = $this->get_api();

		// fail silently if API class does not have method. This means user should update their MailChimp for WordPress version.
		if( ! method_exists( $api, 'delete_ecommerce_store_promo_rule' ) ) {
			return;
		}

		$wc_coupon = new WC_Coupon( $coupon_id );
		
		// create promo rule
		$promo_rule_data = array(
			'id' => (string) $coupon_id,
			'title' => (string) $wc_coupon->get_code(),
			'description' => (string) $wc_coupon->get_description(),
			'enabled' => true,
			'amount' => (float) $wc_coupon->get_amount( 'edit' ),
		);

		if( empty( $promo_rule_data['description'] ) ) {
			$promo_rule_data['description'] = (string) $wc_coupon->get_code();
		}

		// determine whether rule is enabled
		$expires = $wc_coupon->get_date_expires();
		if( $expires ) { 
			$promo_rule_data['ends_at'] = (string) $expires;

			if( current_time('timestamp', true) >= $expires->getTimestamp() ) {
				$promo_rule_data['enabled'] = false;
			}
		}

		switch( $wc_coupon->get_discount_type() ) {
			case 'fixed_product':
				$promo_rule_data['type'] = 'fixed';
				$promo_rule_data['target'] = 'per_item';
			break;

			case 'fixed_cart':
				$promo_rule_data['type'] = 'fixed';
				$promo_rule_data['target'] = 'total';
			break;

			case 'percent':
				$promo_rule_data['type'] = 'percentage';
				$promo_rule_data['target'] = 'total';
				$promo_rule_data['amount'] = (float) $wc_coupon->get_amount( 'edit' ) / 100;
			break;
		}

		/**
		* Filters the promo rule data before it is sent to MailChimp
		*
		* @param array $promo_rule_data
		*/
		$promo_rule_data = apply_filters( 'mc4wp_ecommerce_promo_rule_data', $promo_rule_data );

		try {
			$api->update_ecommerce_store_promo_rule( $store_id, $promo_rule_data['id'], $promo_rule_data );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			$api->add_ecommerce_store_promo_rule( $store_id, $promo_rule_data );
		}

		// create promo code (child of promo rule)
		$redemption_url = add_query_arg( array( 
			'coupon_code' => urlencode( $wc_coupon->get_code() ),
		), get_home_url() );

		$promo_code_data = array(
			'id' => (string) $coupon_id,
			'code' => (string) $wc_coupon->get_code(),
			'redemption_url' => (string) $redemption_url,
			'usage_count' => (int) $wc_coupon->get_usage_count(),
			'enabled' => $promo_rule_data['enabled'],
		);

		/**
		* Filters the promo code data before it is sent to MailChimp
		*
		* @param array $promo_code_data
		*/
		$promo_code_data = apply_filters( 'mc4wp_ecommerce_promo_code_data', $promo_code_data );

		try {
			$api->update_ecommerce_store_promo_rule_promo_code( $store_id, $promo_rule_data['id'], $promo_code_data['id'], $promo_code_data );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
			$api->add_ecommerce_store_promo_rule_promo_code( $store_id, $promo_rule_data['id'], $promo_code_data );
		}

		// update stats on when we last updated
		$this->touch();
	}
}
