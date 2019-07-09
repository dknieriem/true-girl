<?php

class MC4WP_Ecommerce_Object_Transformer_WC3 implements MC4WP_Ecommerce_Object_Transformer  {

	/**
	 * @var MC4WP_Ecommerce_Tracker
	 */
	protected $tracker;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * MC4WP_Ecommerce_Object_Transformer constructor.
	 *
	 * @param array $settings
	 * @param MC4WP_Ecommerce_Tracker $tracker
	 */
	public function __construct(array $settings, MC4WP_Ecommerce_Tracker $tracker) {
		$this->settings = $settings;
		$this->tracker = $tracker;
	}

	/**
	 * @param WC_Order|WP_User $object
	 * @param string $property
	 *
	 * @return string
	 */
	private function get_object_property($object, $property) {
		// since WooCommerce 3.0, but only on instances of WC_Order
		$method_name = 'get_' . $property;
		if (method_exists($object, $method_name)) {
			$value = $object->{$method_name}();
			return trim( $value );
		}

		// instances of WP_User
		if (!empty($object->{$property})) {
			return trim( $object->{$property} );
		}

		return '';
	}

	/**
	 * @param string $email_address
	 *
	 * @return string
	 */
	public function get_customer_id($email_address) {
		return (string) md5(strtolower($email_address));
	}

	/**
     * Generate unique cart ID based on email address + today's date in Y-m-d
     *
	 * @param string $customer_email_address
	 * @see get_customer_id
	 * @return string
	 */
	public function get_cart_id($customer_email_address) {
		$date = date('Y-m-d' );
		$customer_email_address = strtolower( trim( $customer_email_address ) );
		$cart_id = md5( $date . $customer_email_address );
		return $cart_id;
	}

	/**
	 * @param object|WP_User|WC_Order $object
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function customer($object) {
		// first, attempt to get billing_email from order or customer object.
		$billing_email = method_exists( $object, 'get_billing_email' ) ? $object->get_billing_email() : $this->get_object_property($object, 'billing_email');
		
		// if the above failed, try "user_email" property
		if ( empty($billing_email) && ! empty($object->user_email) ) {
			$billing_email = $object->user_email;	
		}

		if( empty($billing_email) ) {
			throw new Exception("Customer data requires a billing_email property", 100);
		}

		$helper = new MC4WP_Ecommerce_Helper();

		$customer_data = array(
			'email_address' => (string) $billing_email,
			'opt_in_status' => false,
			'address' => array(),
		);

		// add order count
		$order_count = $helper->get_order_count_for_email($billing_email);
		if (!empty($order_count)) {
			$customer_data['orders_count'] = $order_count;
		}

		// add total spent
		$total_spent = $helper->get_total_spent_for_email($billing_email);
		if (!empty($total_spent)) {
			$customer_data['total_spent'] = $total_spent;
		}

		// fill top-level keys
		// TODO: use first_name and last_name here (vs. billing_* fields)?
		$map = array(
			'billing_first_name' => 'first_name',
			'billing_last_name' => 'last_name',
			'billing_company' => 'company',
		);
		foreach ($map as $source_property => $target_property) {
			$value = $this->get_object_property($object, $source_property);
			if (!empty($value)) {
				$customer_data[$target_property] = $value;
			}
		}

		// fill address keys
		$map = array(
			'billing_address_1' => 'address1',
			'billing_address_2' => 'address2',
			'billing_city' => 'city',
			'billing_state' => 'province',
			'billing_postcode' => 'postal_code',
			'billing_country' => 'country_code',
		);
		foreach ($map as $source_property => $target_property) {
			$value = $this->get_object_property($object, $source_property);
			if (!empty($value)) {
				$customer_data['address'][$target_property] = $value;
			}
		}

		// strip off empty address property
		if (empty($customer_data['address'])) {
			unset($customer_data['address']);
		}

		/**
		 * Filter the customer data before it is sent to MailChimp.
		 *
		 * @array $customer_data
		 */
		$customer_data = apply_filters('mc4wp_ecommerce_customer_data', $customer_data);

		// set ID because we don't want that to be filtered.
		$customer_data['id'] = $this->get_customer_id($billing_email);

		return $customer_data;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function order(WC_Order $order) {
	   $order_id = $order->get_id();
		$order_number = $order->get_order_number();
		$billing_email = $order->get_billing_email(); 

		// generate item lines data
		$items = $order->get_items( 'line_item' );
		$order_lines_data = array();
		foreach ($items as $item_id => $item) {
			// product may not exist anymore by now, we validate this outside of this class
			$product_id = $item->get_product_id();

			// calculate cost of a single item
			$qty = (int) $item->get_quantity();

			// sanity check for quantity
			if($qty === 0) {
				continue;
			}

			$item_price = (float) ($item->get_total() / $qty);
			$line_data = array(
				'id' => (string) $item_id,
				'product_id' => (string) $product_id,
				'product_variant_id' => (string) $product_id,
				'quantity' => $qty,
				'price' =>  $item_price,
			);

			// use variation ID if set & product variation exists
			$variation_id = $item->get_variation_id();
			if (!empty($variation_id) ) {
				$variation_product = wc_get_product( $variation_id );

				if( $variation_product && $variation_product->get_parent_id() === $product_id ) {
					$line_data['product_variant_id'] = (string) $variation_id;
				}
			}

			$order_lines_data[] = $line_data;
		}

		// add order
		$order_data = array(
			'id' => (string) $order_number, // use order number instead of ID, because emails.
			'customer' => array( 'id' => $this->get_customer_id( $billing_email )) ,
			'order_total' => floatval($order->get_total()),
			'tax_total' => floatval($order->get_total_tax()),
			'shipping_total' => floatval($order->get_shipping_total()),
			'currency_code' => (string) $order->get_currency(),
			'lines' => (array) $order_lines_data,
			'billing_address' => $this->order_billing_address( $order ),
		);

		if( $order->has_shipping_address() ) {
			$order_data['shipping_address'] = $this->order_shipping_address( $order );
		}

		// merge in order statuses (financial_status, fulfillment_status)
		$statuses = $this->order_status($order);
		$order_data = array_merge($order_data, $statuses);

		$date_created = $order->get_date_created();
		if ($date_created !== null) {
			$order_data['processed_at_foreign'] = $date_created->format('Y-m-d H:i:s');
		}

		// add tracking code(s)
		$tracking_code = $this->tracker->get_tracking_code( $order_id, false );
		if (!empty($tracking_code)) {
			$order_data['tracking_code'] = $tracking_code;
		}

		$campaign_id = $this->tracker->get_campaign_id( $order_id, false );
		if (!empty($campaign_id)) {
			$order_data['campaign_id'] = $campaign_id;
		}

		$landing_site = $this->tracker->get_landing_site( $order_id, false );
		if( ! empty( $landing_site ) ) {
			$order_data['landing_site'] = $landing_site;
		}

		// only send `order_url` if it looks like an actual domain, because mailchimp will reject values like "localhost/order/5"
		$order_url = $order->get_view_order_url();
		if( strpos( $order_url, '.' ) && strpos( $order_url, 'wordpress.' ) === false && strpos( $order_url, '.localhost' ) === false ) {
			$order_data['order_url'] = $order_url;
		}

		/**
		 * Filter order data that is sent to MailChimp.
		 *
		 * @param array $order_data
		 * @param WC_Order $order
		 */
		$order_data = apply_filters('mc4wp_ecommerce_order_data', $order_data, $order);

		return $order_data;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public function product(WC_Product $product) {
		// add product variants if this is a variable product
		$variants = array();
		$children = $product->get_children();
		if(! empty($children) ) {
			foreach ($children as $product_variation_id) {
				$product_variation = wc_get_product($product_variation_id);

				// only add variation if it exists
				if( $product instanceof WC_Product ) {
					$variants[] = $this->get_product_variant_data($product_variation);
				}
			}
		}

		// always add main product to the available variants
		// TODO: Check if this can cause issues with product recommendations?
		$variants[] = $this->get_product_variant_data($product);

		// data to send to MailChimp
		$product_data = array(
			// required
			'id' => (string) $product->get_id(),
			'title' => (string) strip_tags($product->get_title()),
			'url' => (string) $product->get_permalink(),
			'variants' => (array) $variants,

			// optional
			'type' => (string) $product->get_type(),
			'image_url' => function_exists( 'get_the_post_thumbnail_url' ) ? (string) get_the_post_thumbnail_url($product->get_id(), 'shop_single') : '',
		);

		// // add variant images
		// $product_data['images'] = array();
		// foreach( $product_data['variants'] as $variant ) {
		// 	if( empty( $variant['image_url'] ) ) {
		// 		continue;
		// 	}
		// 	$product_data['images'][] = array(
		// 		'id' => $variant['id'],
		// 		'url' => $variant['image_url'],
		// 		'variant_ids' => array( $variant['id'] )
		// 	);
		// }

		// add product categories, joined together by "|"
		$category_names = array();
		$category_objects = get_the_terms($product->get_id(), 'product_cat');
		if (is_array($category_objects)) {
			foreach ($category_objects as $term) {
				$category_names[] = $term->name;
			}
			if (!empty($category_names)) {
				$product_data['vendor'] = join('|', $category_names);
			}
		}

		/**
		 * Filter product data that is sent to MailChimp.
		 *
		 * @param array $product_data
		 */
		$product_data = apply_filters('mc4wp_ecommerce_product_data', $product_data);

		// filter out empty values
		$product_data = array_filter($product_data, function ($v) {return !empty($v);});

		return $product_data;
	}

	/**
	 * @param WC_Product $product
	 * @return array
	 */
	private function get_product_variant_data(WC_Product $product) {

		// determine inventory quantity; default to 0 for unpublished products
		$inventory_quantity = 0;

		// only get actual stock qty when product is published & visible
		if ($product->get_status() === 'publish' && $product->get_catalog_visibility() !== 'hidden') {
			if ($product->managing_stock()) {
				$inventory_quantity = $product->get_stock_quantity();
			} else {
				$out_of_stock = $product->get_stock_status() !== 'instock';
				$inventory_quantity = $out_of_stock ? 0 : 1; // default to 1 when not managing stock & not manually set to "out of stock"
			}
		}

		$data = array(
			// required
			'id' => (string) $product->get_id(),
			'title' => (string) strip_tags($product->get_title()),
			'url' => (string) $product->get_permalink(),

			// optional
			'sku' => (string) $product->get_sku(),
			'price' => floatval($product->get_price()),
			'image_url' => function_exists( 'get_the_post_thumbnail_url' ) ? (string) get_the_post_thumbnail_url($product->get_id(), 'shop_single') : '',
			'inventory_quantity' => (int) $inventory_quantity,
		);

		// if product is variation, replace title with variation attributes.
		// check if parent is set to prevent fatal error.... WooCommerce, ugh.

		if ($product instanceof WC_Product_Variation && function_exists('wc_get_formatted_variation') && $product->get_parent_id() !== '') {
			$variations = trim( strip_tags( wc_get_formatted_variation($product, true) ) );
			if (!empty($variations)) {
				$data['title'] = (string) $variations;
			}
		}

		return $data;
	}

	/**
	 * @param array $customer
	 * @param array $cart_items
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function cart(array $customer, array $cart_items ) {
		$lines_data = array();
		$order_total = 0.00;

		// check if cart has lines
		if (empty($cart_items)) {
			throw new Exception("Cart has no item lines", MC4WP_Ecommerce::ERR_NO_ITEMS );
		}

		// generate data for cart lines
		foreach ($cart_items as $line_id => $cart_item) {
			$product_variant_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product = wc_get_product($product_variant_id);
			
			// check if product exists before adding to line data
			if( ! $product ) {
				continue;
			}

			$lines_data[] = array(
				'id' => (string) $line_id,
				'product_id' => (string) $cart_item['product_id'],
				'product_variant_id' => (string) $product_variant_id,
				'quantity' => (int) $cart_item['quantity'],
				'price' => floatval($product->get_price()),
			);

			$order_total += floatval($product->get_price()) * $cart_item['quantity'];
		}

		$cart_id = $this->get_cart_id( $customer['email_address'] );
		$checkout_url = add_query_arg(array('mc_cart_id' => $cart_id), wc_get_cart_url());
		$cart_data = array(
			'id' => (string) $cart_id,
			'customer' => $customer,
			'checkout_url' => (string) $checkout_url,
			'currency_code' => (string) $this->settings['store']['currency_code'],
			'order_total' => (float) $order_total,
			'lines' => (array) $lines_data,
		);

		/**
		 * Filters the cart data that is sent to MailChimp.
		 *
		 * @param array $cart_data
		 * @param array $cart_items Raw cart items array coming from WooCommerce.
		 */
		$cart_data = apply_filters('mc4wp_ecommerce_cart_data', $cart_data, $cart_items);

		return $cart_data;
	}

	/**
	 * @param WC_Order $order
	 * @return array
	 */
	private function order_status(WC_Order $order) {
		$map = array(
			'pending' => array(
				'financial_status' => 'pending', // Sends the order confirmation
			),
			'on-hold' => array(
				'financial_status' => 'pending', // Sends the order confirmation
			),
			'processing' => array(
				'financial_status' => 'pending', // Sends the order confirmation
			),
			'completed' => array(
				'financial_status' => 'paid', // Sends the order invoice
				'fulfillment_status' => 'fulfilled', // Sends the shipping confirmation
			),
			'cancelled' => array(
				'financial_status' => 'cancelled', // Sends cancellation confirmation
			),
			'refunded' => array(
				'financial_status' => 'refunded', // Sends refund confirmation
			),
			'failed' => array(),
		);

		$status = (string) $order->get_status();
		if (isset($map[$status])) {
			return $map[$status];
		}

		return array();
	}

    /**
     * @param WC_Order $order
     * @return object
     */
	private function order_shipping_address( $order ) {
		return (object) array(
			'name' => sprintf( '%s %s', $order->get_shipping_first_name(), $order->get_shipping_last_name() ),
			'company' => $order->get_shipping_company(),
			'address1' => $order->get_shipping_address_1(),
			'address2' => $order->get_shipping_address_2(),
			'city' => $order->get_shipping_city(),
			'province' => $order->get_shipping_state(),
			'postal_code' => $order->get_shipping_postcode(),
			'country' => $order->get_shipping_country(),
		);
	}

    /**
     * @param WC_Order $order
     * @return object
     */
	private function order_billing_address( $order ) {
		return (object) array(
			'name' => sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() ),
			'company' => $order->get_billing_company(),
			'phone' => $order->get_billing_phone(),
			'address1' => $order->get_billing_address_1(),
			'address2' => $order->get_billing_address_2(),
			'city' => $order->get_billing_city(),
			'province' => $order->get_billing_state(),
			'postal_code' => $order->get_billing_postcode(),
			'country' => $order->get_billing_country(),
		);
	}

}
