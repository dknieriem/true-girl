<?php

/**
 * Class MC4WP_Ecommerce_Object_Transformer_Legacy
 *
 * Handles WooCommerce < 3.0 objects.
 */
class MC4WP_Ecommerce_Object_Transformer_WC2 implements MC4WP_Ecommerce_Object_Transformer {

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
	public function __construct( array $settings, MC4WP_Ecommerce_Tracker $tracker ) {
		$this->settings = $settings;
		$this->tracker = $tracker;
	}

	/**
	 * @param string $email_address
	 *
	 * @return string
	 */
	public function get_customer_id( $email_address ) {
		return (string) md5( strtolower( $email_address ) );
	}

	/**
	 * @param string $customer_email_address
	 * @see get_customer_id
	 * @return string
	 */
	public function get_cart_id( $customer_email_address ) {
	    $date = date( 'Y-m-d' );
        $customer_email_address = strtolower( trim( $customer_email_address ) );
	    return md5( $date . $customer_email_address );
	}

	/**
	 * @param object|WP_User|WC_Order $object
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function customer( $object ) {

		if( empty( $object->billing_email ) ) {
			throw new Exception( "Customer data requires a billing_email property", 100 );
		}
		$billing_email = $object->billing_email;

		$helper = new MC4WP_Ecommerce_Helper();

		$customer_data = array(
			'email_address' => (string) $billing_email,
			'opt_in_status' => false,
			'address' => array(),
		);

		// add order count
		$order_count = $helper->get_order_count_for_email( $billing_email );
		if( ! empty( $order_count ) ) {
			$customer_data['orders_count'] = $order_count;
		}

		// add total spent
		$total_spent = $helper->get_total_spent_for_email( $billing_email );
		if( ! empty( $total_spent ) ) {
			$customer_data['total_spent'] = $total_spent;
		}

		// fill top-level keys
		$map = array(
			'billing_first_name' => 'first_name',
			'billing_last_name' => 'last_name'
		);
		foreach( $map as $source_property => $target_property ) {
			if( ! empty( $object->$source_property ) ) {
				$customer_data[ $target_property ] = $object->$source_property;
			}
		}


		// fill address keys
		$map = array(
			'billing_address_1' => 'address1',
			'billing_address_2' => 'address2',
			'billing_city' => 'city',
			'billing_state' => 'province',
			'billing_postcode' => 'postal_code',
			'billing_country' => 'country'
		);
		foreach( $map as $source_property => $target_property ) {
			if( ! empty( $object->$source_property ) ) {
				$customer_data['address'][ $target_property ] = $object->$source_property;
			}
		}

		// strip off empty address property
		if( empty( $customer_data['address'] ) ) {
			unset( $customer_data['address'] );
		}

		/**
		 * Filter the customer data before it is sent to MailChimp.
		 */
		$customer_data = apply_filters( 'mc4wp_ecommerce_customer_data', $customer_data );

		// set ID because we don't want that to be filtered.
		$customer_data['id'] = $this->get_customer_id( $billing_email );

		return $customer_data;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function order( WC_Order $order ) {
		$billing_email = $order->billing_email;

		// generate order data
		$items = $order->get_items();

		// generate item lines data
		$order_lines_data = array();
		foreach( $items as $item_id => $item ) {
			// calculate cost of a single item
			$item_price = $item['line_total'] / $item['qty'];

			$line_data = array(
				'id' => (string) $item_id,
				'product_id' => (string) $item['product_id'],
				'product_variant_id' => (string) $item['product_id'],
				'quantity' => (int) $item['qty'],
				'price' => floatval( $item_price ),
			);

			// use variation ID if set.
			if( ! empty( $item['variation_id'] ) ) {
				$line_data['product_variant_id'] = (string) $item['variation_id'];
			}

			$order_lines_data[] = $line_data;
		}

		// add order
		$order_data = array(
			'id' => (string) $order->id,
			'customer' => array( 'id' => $this->get_customer_id( $billing_email ) ),
			'order_total' => floatval( $order->get_total() ),
			'tax_total' => floatval( $order->get_total_tax() ),
			'financial_status' => (string) $order->get_status(),
			'shipping_total' => floatval( $order->get_total_shipping() ),
			'currency_code' => (string) $order->get_order_currency(),
			'lines' => (array) $order_lines_data,
			'processed_at_foreign' => date('Y-m-d H:i:s', strtotime( $order->order_date ) ),
		);

		// add tracking code(s)
		$tracking_code = $this->tracker->get_tracking_code( $order->id );
		if( ! empty( $tracking_code ) ) {
			$order_data['tracking_code'] = $tracking_code;
		}

		$campaign_id = $this->tracker->get_campaign_id( $order->id );
		if( ! empty( $campaign_id ) ) {
			$order_data['campaign_id'] = $campaign_id;
		}

		/**
		 * Filter order data that is sent to MailChimp.
		 *
		 * @param array $order_data
		 * @param WC_Order $order
		 */
		$order_data = apply_filters( 'mc4wp_ecommerce_order_data', $order_data, $order );

		return $order_data;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public function product( WC_Product $product ) {
		// init product variants
		$variants = array();
		if( $product instanceof WC_Product_Variable ) {
			$children = $product->get_children();
			foreach( $children as $product_variation_id ) {
				$product_variation = wc_get_product( $product_variation_id );
				$variants[] = $this->get_product_variant_data( $product_variation );
			}
		} else {
			// default variant
			$variants[] = $this->get_product_variant_data( $product );
		}

		// data to send to MailChimp
		$product_data = array(
			// required
			'id' => (string) $product->id,
			'title' => (string) strip_tags( $product->get_title() ),
			'url' => (string) $product->get_permalink(),
			'variants' => (array) $variants,

			// optional
			'type' => (string) $product->get_type(),
			'image_url' => function_exists( 'get_the_post_thumbnail_url' ) ? (string) get_the_post_thumbnail_url( $product->id, 'shop_single' ) : '',
		);

		// add product categories, joined together by "|"
		$category_names = array();
		$category_objects = get_the_terms( $product->id, 'product_cat' );
		if( is_array( $category_objects ) ) {
			foreach( $category_objects as $term ) {
				$category_names[] = $term->name;
			}
			if( ! empty( $category_names ) ) {
				$product_data['vendor'] = join( '|', $category_names );
			}
		}

		/**
		 * Filter product data that is sent to MailChimp.
		 *
		 * @param array $product_data
		 */
		$product_data = apply_filters( 'mc4wp_ecommerce_product_data', $product_data );

		// filter out empty values
		$product_data = array_filter( $product_data, function($v) { return ! empty( $v ); } );

		return $product_data;
	}

	/**
	 * @param WC_Product $product
	 * @return array
	 */
	private function get_product_variant_data( WC_Product $product ) {

		// determine inventory quantity; default to 0 for unpublished products
		$post = $product->get_post_data();
		$inventory_quantity = 0;

		// only get actual stock qty when product is published & visible
		if( $post->post_status === 'publish' && $product->visibility !== 'hidden' ) {
			if( $product->managing_stock()) {
				$inventory_quantity = $product->get_stock_quantity();
			} else {
				$out_of_stock = $product->stock_status !== 'instock';
				$inventory_quantity = $out_of_stock ? 0 : 1; // default to 1 when not managing stock & not manually set to "out of stock"
			}
		}

		$data = array(
			// required
			'id' => (string) ( ! empty( $product->variation_id ) ? $product->variation_id : $product->id ),
			'title' => (string) strip_tags( $product->get_title() ),
			'url' => (string) $product->get_permalink(),

			// optional
			'sku' => (string) $product->get_sku(),
			'price' => floatval( $product->get_price() ),
			'image_url' => function_exists( 'get_the_post_thumbnail_url' ) ? (string) get_the_post_thumbnail_url( $product->id, 'shop_single' ) : '',
			'inventory_quantity' => (int) $inventory_quantity
		);

		// if product is variation, replace title with variation attributes.
		// check if parent is set to prevent fatal error.... WooCommerce, ugh.
		if( $product instanceof WC_Product_Variation && method_exists( $product, 'get_formatted_variation_attributes' ) && $product->parent ) {
			$variations = $product->get_formatted_variation_attributes( true );
			if( ! empty( $variations ) ) {
				$data['title'] = (string) $variations;
			}
		}

		// filter out empty values
		$data = array_filter( $data, function($v) { return ! empty( $v ); } );

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
		foreach( $cart_items as $line_id => $cart_item ) {
			$product_variant_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product = wc_get_product( $product_variant_id );

			// check if product exists before adding to line data
			if( ! $product ) {
				continue;
			}

			$lines_data[] = array(
				'id' => (string) $line_id,
				'product_id' => (string) $cart_item['product_id'],
				'product_variant_id' => (string) $product_variant_id,
				'quantity' => (int) $cart_item['quantity'],
				'price' => floatval( $product->get_price() ),
			);

			$order_total += floatval( $product->get_price() ) * $cart_item['quantity'];
		}

		$cart_id = $this->get_cart_id( $customer['email_address'] );
		$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : get_cart_url();
		$checkout_url = add_query_arg( array( 'mc_cart_id' => $cart_id ), $cart_url );
		$cart_data = array(
			'id' => (string) $cart_id,
			'customer' => $customer,
			'checkout_url' => (string) $checkout_url,
			'currency_code' => (string) $this->settings['store']['currency_code'],
			'order_total' => (float) $order_total,
			'lines' => (float) $lines_data,
		);

		/**
		 * Filters the cart data that is sent to MailChimp.
		 *
		 * @param array $cart_data
		 * @param array $cart_items
		 */
		$cart_data = apply_filters( 'mc4wp_ecommerce_cart_data', $cart_data, $cart_items );

		return $cart_data;
	}

}
