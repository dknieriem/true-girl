<?php

class MC4WP_Ecommerce_Cart_Observer {

	/**
	 * @var string
	 */
	private $plugin_file;

	/**
	 * @var MC4WP_Queue
	 */
	private $queue;

	/**
	 * @var MC4WP_Ecommerce
	 */
	private $ecommerce;

	/**
	* @var MC4WP_Transformer
	*/
	private $transformer;

	/**
	 * MC4WP_Ecommerce_Tracker constructor.
	 *
	 * @param string $plugin_file
	 * @var MC4WP_Ecommerce $ecommerce
	 * @param MC4WP_Queue $queue
	 * @param MC4WP_Ecommerce_Object_Transformer $transformer
	 */
	public function __construct( $plugin_file, MC4WP_Ecommerce $ecommerce, MC4WP_Queue $queue, MC4WP_Ecommerce_Object_Transformer $transformer ) {
		$this->plugin_file = $plugin_file;
		$this->ecommerce = $ecommerce;
		$this->queue = $queue;
		$this->transformer = $transformer;
	}

	/**
	 * Add hooks
	 */
	public function hook() {
		add_action( 'parse_request', array( $this, 'repopulate_cart_from_mailchimp' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_mc4wp_ecommerce_schedule_cart', array( $this, 'on_checkout_form_change' ) );
		add_action( 'wp_ajax_nopriv_mc4wp_ecommerce_schedule_cart', array( $this, 'on_checkout_form_change' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'on_order_processed' ) );
		add_action( 'woocommerce_after_cart_item_restored', array( $this, 'on_cart_updated' ) );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'on_cart_updated' ) );
		add_action( 'woocommerce_add_to_cart', array( $this, 'on_cart_updated' ), 9 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'on_cart_updated' ) );
		add_action( 'woocommerce_cart_emptied', array( $this, 'on_cart_updated' ) );
		add_action( 'wp_login', array( $this, 'on_cart_updated' ) );
	}

	/**
	 * Repopulates a cart from MailChimp if the "mc_cart_id" parameter is set.
	 */
	public function repopulate_cart_from_mailchimp() {
		if( empty( $_GET['mc_cart_id'] ) ) {
			return;
		}

		$cart_id = $_GET['mc_cart_id'];
		try {
			$cart_data = $this->ecommerce->get_cart($cart_id);
		} catch( Exception $e ) {
			return;
		}

		/**
		 * Fires just before an abandoned cart from MailChimp is added to the WooCommerce cart session.
		 *
		 * If you use this to override the default cart population, make sure to redirect after you are done.
		 *
		 * @param object $cart_data The data retrieved from MailChimp.
		 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#read-get_ecommerce_stores_store_id_carts_cart_id
		 */
		do_action( 'mc4wp_ecommerce_restore_abandoned_cart', $cart_data );

		// empty cart
		$wc_cart = WC()->cart;
		$wc_cart->empty_cart();

		// add items from MailChimp cart object
		foreach( $cart_data->lines as $line ) {
			$variation_id = $line->product_variant_id != $line->product_id ? $line->product_variant_id : 0;
			$wc_cart->add_to_cart( $line->product_id, $line->quantity, $variation_id );
		}

		// remove pending update & delete jobs
		$this->remove_pending_jobs( 'delete_cart', $cart_id );
		$this->remove_pending_jobs( 'update_cart', $cart_id );
		$this->queue->save();

		wp_redirect( remove_query_arg( 'mc_cart_id' ) );
		exit;
	}

	/**
	 * @param string $method
	 *
	 * @param int $object_id
	 */
	private function remove_pending_jobs( $method, $object_id ) {
		$jobs = $this->queue->all();
		foreach( $jobs as $job ) {
			if( $job->data['method'] === $method && $job->data['args'][0] == $object_id ) {
				$this->queue->delete( $job );
			}
		}
	}

	/**
	 * @param string $method
	 * @param array $args
	 */
	private function add_pending_job( $method, array $args ) {
		$this->queue->put(
			array(
				'method' => $method,
				'args' => $args
			)
		);
	}

	/**
	 * Enqueue script on checkout page that periodically sends form data for guest checkouts.
	 */
	public function enqueue_assets() {
		if( is_checkout() && ! is_user_logged_in() ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'mc4wp-ecommerce-cart', plugins_url( "/assets/js/cart{$suffix}.js", $this->plugin_file ), array(), MC4WP_PREMIUM_VERSION, true );
			wp_localize_script( 'mc4wp-ecommerce-cart', 'mc4wp_ecommerce_cart', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);
		}
	}

	// triggered via JavaScript hooked into checkoutForm.change
	public function on_checkout_form_change() {

		$data = json_decode( stripslashes( file_get_contents("php://input") ), false );

		// make sure we have at least a valid email_address
		if( empty( $data->billing_email ) || ! is_email( $data->billing_email ) ) {
			wp_send_json_error();
			exit;
		}

		// get cart, safely.
		$wc_cart = WC()->cart;
		if(!  $wc_cart instanceof WC_Cart ) {
			return;
		}

		$cart_contents = method_exists( $wc_cart, 'get_cart_contents' ) ? $wc_cart->get_cart_contents() : $wc_cart->cart_contents;
		$cart_id = $this->transformer->get_cart_id( $data->billing_email );

		// remove other pending updates from queue
		$this->remove_pending_jobs( 'update_cart', $cart_id );

		if( ! empty( $cart_contents ) ) {
			// update remote cart if we have items in cart
			$this->add_pending_job( 'update_cart', array( $cart_id, $data, $cart_contents ) );
		} else {
			$this->add_pending_job( 'delete_cart', array( $cart_id ) );
		}

		// delete previous cart if email address changed
		if( ! empty( $data->previous_billing_email )
			&& is_email( $data->previous_billing_email )
			&& $data->previous_billing_email !== $data->billing_email ) {

			// get previous cart ID
			$previous_cart_id = $this->transformer->get_cart_id( $data->previous_billing_email );

			// schedule cart deletion
			$this->add_pending_job( 'delete_cart', array( $previous_cart_id ) );
		}

		$this->queue->save();
		wp_send_json_success();
		exit;
	}

	// hook: woocommerce_checkout_order_processed
	public function on_order_processed( $order_id ) {
		$order = wc_get_order( $order_id );
		$billing_email = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
		$cart_id = $this->transformer->get_cart_id( $billing_email );

		// remove updates from queue
		$this->remove_pending_jobs( 'update_cart', $cart_id );

		// schedule cart deletion
		$this->add_pending_job( 'delete_cart', array( $cart_id ) );
		$this->queue->save();
	}

	// hook: woocommerce_add_to_cart, woocommerce_cart_item_removed
	public function on_cart_updated() {
		// check if we have a logged in user
		$user = wp_get_current_user();
		if( ! $user instanceof WP_User || ! $user->exists() ) {
			return; // TODO: Get user data for guests from some cookie or session?
		}

		// get email address from billing_email or user_email property
		$email_address = ! empty( $user->billing_email ) ? $user->billing_email : $user->user_email;
		if( empty( $email_address ) ) {
			$this->get_log()->info("E-Commerce: Skipping cart update for user without email address.");
			return;
		}

		$cart_id = $this->transformer->get_cart_id( $email_address );

		// sanity check, sometimes this returns null apparently?
		$wc_cart = WC()->cart;
		if(!  $wc_cart instanceof WC_Cart ) {
			return;
		}

		// delete cart from MailChimp if it is now empty
		if( $wc_cart->is_empty() ) {
			// remove pending updates from queue
			$this->remove_pending_jobs( 'update_cart', $cart_id );

			// schedule cart deletion
			$this->add_pending_job( 'delete_cart', array( $cart_id ) );
			$this->queue->save();
			return;
		}

		// remove other pending updates from queue
		$this->remove_pending_jobs( 'update_cart', $cart_id );

		// schedule new update with latest data
		$cart_contents = method_exists( $wc_cart, 'get_cart_contents' ) ? $wc_cart->get_cart_contents() : $wc_cart->cart_contents;

		$this->add_pending_job( 'update_cart', array( $cart_id, $user->ID, $cart_contents ) );
		$this->queue->save();
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	private function get_log() {
		return mc4wp('log');
	}

}
