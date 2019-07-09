<?php

/**
 * Class MC4WP_Ecommerce
 */
class MC4WP_Ecommerce {

	/**
	 * @var MC4WP_Ecommerce_Tracker
	 */
	public $tracker;

	/**
	 * @const string
	 */
	const META_KEY = '_mc4wp_ecommerce_tracked';

	/**
	 * Constructor
	 *
	 * @param MC4WP_Ecommerce_Tracker $tracker
	 */
	public function __construct( MC4WP_Ecommerce_Tracker $tracker ) {
		$this->tracker = $tracker;
	}

	/**
	 * @param int $order_id
	 *
	 * @return boolean
	 */
	public function delete_order( $order_id ) {
		$api = $this->get_api();
		$success = $api->delete_ecommerce_order( $this->get_store_id(), $order_id );

		// remove meta on success
		if( $success ) {
			delete_post_meta( $order_id, self::META_KEY );

			// log message
			$this->get_log()->info( sprintf( 'eCommerce360 > Order %d deleted', $order_id ) );
		}

		return $success;
	}

	/**
	 * @param int $order_id
	 *
	 * @return boolean
	 */
	public function add_order( $order_id ) {

		$order_type = $this->get_order_type( $order_id );

		if( is_string( $order_type ) ) {
			$method = sprintf( 'add_%s_order', $order_type );
			$success = call_user_func( array( $this, $method ), $order_id );

			if( $success ) {
				add_post_meta( $order_id, self::META_KEY, 1 );
			}

			return $success;
		}

		// unknown order type
		return false;
	}

	/**
	 * @param array $data
	 *
	 * @return boolean
	 */
	protected function add_order_data( array $data ) {

		// add general order data
		$data = array_merge( $this->get_general_order_data(), $data );

		// add campaign id & email id
		$campaign_id = $this->tracker->get_campaign_id( $data['id'] );
		$email_id = $this->tracker->get_email_id( $data['id'] );
		if( ! empty( $campaign_id ) && ! empty( $email_id ) ) {
			$data['campaign_id'] = $campaign_id;
			$data['email_id'] = $email_id;
		}

		/**
		 * Filters the order data for all orders before it is sent to MailChimp.
		 *
		 * @param array $data
		 * @param int $order_id
		 */
		$data = apply_filters( 'mc4wp_ecommerce360_order_data', $data );


		$api = $this->get_api();
		$success = $api->add_ecommerce_order( $data );

		if( $success ) {
			$this->get_log()->info( sprintf( 'eCommerce360 > Successfully added order %d', $data['id'] ) );
		} else {
			$this->get_log()->error( sprintf( 'eCommerce360 > Error adding order %d: %s ', $data['id'], $api->get_error_message() ) );
		}

		return $success;
	}

	/**
	 * @param $order_id
	 *
	 * @return boolean
	 */
	public function add_woocommerce_order( $order_id ) {

		// prepare order data
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$data_items = array();

		foreach( $items as $item_id => $item ) {
			$category = $this->get_lowest_term( $item['product_id'], 'product_cat' );

			// calculate cost of a single item
			$item_cost = $item['line_total'] / $item['qty'];

			$item_data = array(
				'product_id' => $item['product_id'],
				'product_name' => $this->strip_special_characters( $item['name'] ),
				'qty' => $item['qty'],
				'category_id' => $category->term_id,
				'category_name' => $category->name,
				'cost' => $item_cost,
			);

			// find product
			$product_id = empty( $item['variation_id'] ) ? $item['product_id'] : $item['variation_id'];
			$product = wc_get_product( $product_id );

			if( $product instanceof WC_Product ) {

				// add SKU if set
				$sku = $product->get_sku();
				if( ! empty( $sku ) ) {
					$item_data['sku'] = $sku;
				}

				// use item price
				$item_data['cost'] = $product->get_price();
				$item_data['product_name'] = $this->strip_special_characters( $product->get_title() );

				// check if parent is set to prevent fatal error.... WooCommerce, ugh.
				if( $product instanceof WC_Product_Variation && $product->parent instanceof WC_Product && method_exists( $product, 'get_formatted_variation_attributes' )) {
					$variations = $product->get_formatted_variation_attributes( true );
					if( ! empty( $variations ) ) {
						$item_data['product_name'] .= ' (' . $variations . ')';
					}
				}
			}

			$data_items[] = $item_data;
		}

		$data = array(
			'id' => $order_id,
			'order_date' => date('Y-m-d', strtotime( $order->order_date ) ),
			'email' => $order->billing_email,
			'total' => $order->get_total(),
			'tax' => $order->get_total_tax(),
			'shipping' => $order->get_total_shipping(),
			'items' => $data_items
		);

		return $this->add_order_data( $data );
	}

	/**
	 * @param int $order_id
	 *
	 * @return boolean
	 */
	public function add_easy_digital_downloads_order( $order_id ) {
		$data_items = array();
		$items = edd_get_payment_meta_cart_details( $order_id );

		foreach( $items as $item ) {

			$item_name = $item['name'];

			// add price option name if this product has variable prices
			if( edd_has_variable_prices( $item['id'] ) && isset( $item['item_number']['options']['price_id'] ) && strlen( $item['item_number']['options']['price_id'] ) > 0 ) {
				$price_option_name = edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );

				if( ! empty( $price_option_name ) ) {
					$item_name .= ' - ' . $price_option_name;
				}
			}

			$category = $this->get_lowest_term( $item['id'], 'download_category' );
			$item_data = array(
				'product_id' => $item['id'],
				'product_name' => $this->strip_special_characters( $item_name ),
				'qty' => $item['quantity'],
				'category_id' => $category->term_id,
				'category_name' => $category->name,
				'cost' => $item['price'],
			);

			// add product SKU, if given
			$sku = edd_get_download_sku( $item['id'] );
			if( ! empty( $sku ) && $sku !== '-' ) {
				$item_data['sku'] = $sku;
			}

			$data_items[] = $item_data;
		}

		$payment_date = (string) edd_get_payment_completed_date( $order_id );

		$data = array(
			'id' => $order_id,
			'order_date' => date('Y-m-d', strtotime( $payment_date ) ),
			'email' => edd_get_payment_user_email( $order_id ),
			'total' => edd_get_payment_amount( $order_id ),
			'tax' => edd_get_payment_tax( $order_id ),
			'items' => $data_items,
		);

		return $this->add_order_data( $data );
	}

	/**
	 * @param int $order_id
	 *
	 * @return bool|string
	 */
	private function get_order_type( $order_id ) {

		// make sure we got a post
		if( ! ( $order = get_post( $order_id ) ) ) {
			$this->get_log()->warning( sprintf( 'eCommerce360 > Unknown order %d', $order_id ) );
			return false;
		}

		// figure out type of order (WooCommerce, Easy Digital Downloads, etc..)
		if( $order->post_type === 'edd_payment' ) {
			return 'easy_digital_downloads';
		} elseif( $order->post_type === 'shop_order' ) {
			return 'woocommerce';
		}

		$this->get_log()->warning( sprintf( 'eCommerce360 > Unknown order type for order %d', $order_id ) );
		return false;
	}

	/**
	 * Sets up the general data (which doesn't differ between store engines)
	 *
	 * @return array
	 */
	private function get_general_order_data() {

		$data = array(
			'store_name' => $this->get_store_name(),
			'store_id' => $this->get_store_id(),
		);

		return $data;
	}

	/**
	 * TODO: Generate string of subcategories here.
	 *
	 * @param $post_id
	 * @param $taxonomy
	 *
	 * @return mixed|object
	 */
	private function get_lowest_term( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if( empty( $terms ) || ! empty( $terms['errors'] ) ) {
			return (object) array(
				'term_id' => 1,
				'name' => 'No Category'
			);
		}

		$term_ids = array();
		$parent_ids = array();
		foreach( $terms as $term ) {
			$term_ids[] = $term->term_id;

			if( $term->parent ) {
				$parent_ids[] = $term->parent;
			}
		}

		// strip all parent categories
		$term_ids = array_diff( $term_ids, $parent_ids );

		// return last one
		$term_id = array_pop( $term_ids );

		return get_term( $term_id, $taxonomy );
	}

	/**
	 * @return MC4WP_API
	 */
	private function get_api() {
		return mc4wp_get_api();
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	private function get_log() {
		return mc4wp('log');
	}

	/**
	 * @return string
	 */
	private function get_store_id() {
		return (string) md5( parse_url( get_option('siteurl', ''), PHP_URL_HOST ) );
	}

	/**
	 * @return string
	 */
	private function get_store_name() {
		return (string) get_option( 'blogname', '' );
	}

    /**
     * Remove all special characters from a string except for the ones listed below.
     *
     * @param string $string
     * @return string
     */
	private function strip_special_characters( $string ) {
        return preg_replace('/[^A-Za-z0-9\-\s\.,:;!()]/', '', $string);
    }

}