<?php
/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Ajax {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		// Load non-WordPress style ajax requests.
		if ( isset( $_REQUEST['optin-monster-ajax-route'] ) && $_REQUEST['optin-monster-ajax-route'] ) {
			if ( isset( $_REQUEST['action'] ) ) {
				add_action( 'init', array( $this, 'ajax' ), 999 );
			}
		}
	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();
		$this->view     = 'ajax';
	}

	/**
	 * Callback to process external ajax requests.
	 *
	 * @since 1.0.0
	 */
	public function ajax() {

		switch ( $_REQUEST['action'] ) {
			case 'mailpoet':
				$this->mailpoet();
				break;
			case 'om_wc_cart':
				add_action( 'wp_ajax_om_wc_cart', array( $this, 'woocommerce_cart' ) );
				add_action( 'wp_ajax_nopriv_om_wc_cart', array( $this, 'woocommerce_cart' ) );
				break;
			case 'om_wc_products':
				add_action( 'wp_ajax_om_wc_products', array( $this, 'woocommerce_products' ) );
				add_action( 'wp_ajax_nopriv_om_wc_products', array( $this, 'woocommerce_products' ) );
				break;
			case 'om_wc_categories':
				add_action( 'wp_ajax_om_wc_categories', array( $this, 'woocommerce_categories' ) );
				add_action( 'wp_ajax_nopriv_om_wc_categories', array( $this, 'woocommerce_categories' ) );
				break;
			case 'om_wc_tags':
				add_action( 'wp_ajax_om_wc_tags', array( $this, 'woocommerce_tags' ) );
				add_action( 'wp_ajax_nopriv_om_wc_tags', array( $this, 'woocommerce_tags' ) );
				break;
			case 'om_wp_categories':
				add_action( 'wp_ajax_om_wp_categories', array( $this, 'wordpress_categories' ) );
				add_action( 'wp_ajax_nopriv_om_wp_categories', array( $this, 'wordpress_categories' ) );
				break;
			case 'om_wp_tags':
				add_action( 'wp_ajax_om_wp_tags', array( $this, 'wordpress_tags' ) );
				add_action( 'wp_ajax_nopriv_om_wp_tags', array( $this, 'wordpress_tags' ) );
				break;
			default:
				break;
		}
	}

	/**
	 * Opts the user into MailPoet.
	 *
	 * @since 1.0.0
	 */
	public function mailpoet() {

		// Run a security check first.
		check_ajax_referer( 'omapi', 'nonce' );

		// Prepare variables.
		$optin = $this->base->get_optin_by_slug( stripslashes( $_REQUEST['optin'] ) );
		$list  = get_post_meta( $optin->ID, '_omapi_mailpoet_list', true );
		$email = ! empty( $_REQUEST['email'] ) ? stripslashes( $_REQUEST['email'] ) : false;
		$name  = ! empty( $_REQUEST['name'] ) ? stripslashes( $_REQUEST['name'] ) : false;
		$user  = array();

		// Possibly split name into first and last.
		if ( $name ) {
			$names = explode( ' ', $name );
			if ( isset( $names[0] ) ) {
				$user['firstname'] = $names[0];
			}

			if ( isset( $names[1] ) ) {
				$user['lastname'] = $names[1];
			}
		}

		// Save the email address.
		$user['email'] = $email;

		// Store the data.
		$data = array(
			'user'      => $user,
			'user_list' => array( 'list_ids' => array( $list ) ),
		);
		$data = apply_filters( 'optin_monster_pre_optin_mailpoet', $data, $_REQUEST, $list, null );

		// Save the subscriber. Check for MailPoet 3 first. Default to legacy.
		if ( class_exists( '\\MailPoet\\Config\\Initializer' ) ) {
			// Customize the lead data for MailPoet 3.
			if ( isset( $user['firstname'] ) ) {
				$user['first_name'] = $user['firstname'];
				unset( $user['firstname'] );
			}

			if ( isset( $user['lastname'] ) ) {
				$user['last_name'] = $user['lastname'];
				unset( $user['lastname'] );
			}

			if ( \MailPoet\Models\Subscriber::findOne( $user['email'] ) ) {
				try {
					\MailPoet\API\API::MP( 'v1' )->subscribeToList( $user['email'], $list );
				} catch ( Exception $e ) {
					// Do nothing.
				}
			} else {
				try {
					\MailPoet\API\API::MP( 'v1' )->addSubscriber( $user, array( $list ) );
				} catch ( Exception $e ) {
					// Do nothing.
				}
			}
		} else {
			$userHelper = WYSIJA::get( 'user', 'helper' );
			$userHelper->addSubscriber( $data );
		}

		// Send back a response.
		wp_send_json_success();
	}

	/**
	 * AJAX callback for returning WooCommerce cart information.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function woocommerce_cart() {
		// Run our WooCommerce checks.
		$this->can_woocommerce();

		// Calculate the cart totals.
		WC()->cart->calculate_totals();

		// Get initial cart data.
		$cart               = WC()->cart->get_totals();
		$cart['cart_items'] = WC()->cart->get_cart();

		// Set the currency data.
		$currencies         = get_woocommerce_currencies();
		$currency_code      = get_woocommerce_currency();
		$cart['currency']   = array(
			'code'   => $currency_code,
			'symbol' => get_woocommerce_currency_symbol( $currency_code ),
			'name'   => isset( $currencies[ $currency_code ] ) ? $currencies[ $currency_code ] : '',
		);

		// Add in some extra data to the cart item.
		foreach ( $cart['cart_items'] as $key => $item ) {
			$item_details = array(
				'type'              => $item['data']->get_type(),
				'sku'               => $item['data']->get_sku(),
				'categories'        => $item['data']->get_category_ids(),
				'tags'              => $item['data']->get_tag_ids(),
				'regular_price'     => $item['data']->get_regular_price(),
				'sale_price'        => $item['data']->get_sale_price() ? $item['data']->get_sale_price() : $item['data']->get_regular_price(),
				'virtual'           => $item['data']->is_virtual(),
				'downloadable'      => $item['data']->is_downloadable(),
				'sold_individually' => $item['data']->is_sold_individually(),
			);
			unset( $item['data'] );
			$cart['cart_items'][ $key ] = array_merge( $item, $item_details );
		}

		// Send back a response.
		wp_send_json_success( $cart );
	}

	/**
	 * AJAX callback for returning WooCommerce cart information.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function woocommerce_products() {
		// Run our WooCommerce checks.
		$this->can_woocommerce();

		// Get the currently published products.
		$products = wc_get_products( $this->get_posts_args() );

		// Filter out all product data except product id and product name.
		// We use the formatted name that appends SKU or ID.
		$product_list = array();
		foreach ( $products as $product ) {
			$product_list[ $product->get_id() ] = $product->get_formatted_name();
		}

		// Send back a response.
		wp_send_json_success( $product_list );
	}

	/**
	 * AJAX callback for returning WooCommerce product categories.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function woocommerce_categories() {
		// Run our WooCommerce checks.
		$this->can_woocommerce();

		// Get the product categories.
		$categories = $this->get_term_list( 'product_cat', $this->get_terms_args() );

		// Send back a response.
		wp_send_json_success( $categories );
	}

	/**
	 * AJAX callback for returning WooCommerce product tags.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function woocommerce_tags() {
		// Run our WooCommerce checks.
		$this->can_woocommerce();

		// Get the product tags.
		$tags = $this->get_term_list( 'product_tag', $this->get_terms_args() );

		// Send back a response.
		wp_send_json_success( $tags );
	}

	/**
	 * AJAX callback for returning WordPress categories.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function wordpress_categories() {
		// Get the product categories.
		$categories = $this->get_term_list( 'category', $this->get_terms_args() );

		// Send back a response.
		wp_send_json_success( $categories );
	}

	/**
	 * AJAX callback for returning WordPress tags.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function wordpress_tags() {
		// Get the product tags.
		$tags = $this->get_term_list( 'post_tag', $this->get_terms_args() );

		// Send back a response.
		wp_send_json_success( $tags );
	}

	/**
	 * Determines if WooCommerce is active and meets the minimum WooCommerce
	 * version requirement.
	 *
	 * @since 1.6.5
	 *
	 * @return boolean
	 */
	public function can_woocommerce() {
		// Bail if WooCommerce isn't currently active.
		if ( ! $this->base->is_woocommerce_active() ) {
			wp_send_json_error();
		}

		// Check WooCommerce is version 3.0.0 or greater.
		if ( version_compare( $this->base->woocommerce_version(), '3.0.0', '<' ) ) {
			wp_send_json_error();
		}
	}

	/**
	 * Returns the filtered/formatted term list for a specified taxonomy.
	 *
	 * @since 1.6.5
	 *
	 * @param string $taxonomy
	 *
	 * @return void
	 */
	public function get_term_list( $taxonomy, $args = array() ) {
		// Get the taxonomy object.
		$tax = get_taxonomy( $taxonomy );

		// Set the default tags list value.
		$term_list = array(
			'taxonomy' => array(
				'name'          => $tax->labels->name,
				'singular_name' => $tax->labels->singular_name,
				'slug'          => $tax->name,
			),
			'terms'    => array(),
		);

		// Get the product tags.
		$terms = get_terms( $taxonomy, $args );

		if ( ! is_wp_error( $terms ) ) {
			// Filter out all product data except product id and product name.
			// We use the formatted name that appends SKU or ID.
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				$term_list['terms'][ $term_id ] = array(
					'term_id'  => $term_id,
					'name'     => $term->name,
				);
			}
		}

		return $term_list;
	}

	/**
	 * Retrieves, sanitizes, and formats the `page` and `per page` arguments for
	 * use in our post/term requests.
	 *
	 * @since 1.6.5
	 *
	 * @return array The paging request args.
	 */
	public function get_paging_request_args() {
		// Get the `post_per_page` default.
		$posts_per_page = absint( get_option( 'posts_per_page' ) );
		$limit_default  = $posts_per_page ? $posts_per_page : 10;

		// Set the default paging args.
		$args = array(
			'page'  => 1,
			'limit' => $limit_default,
		);

		// Check for the `page` query arg. Default to 1.
		if ( isset( $_REQUEST['page'] ) ) {
			$page         = absint( wp_unslash( $_REQUEST['page'] ) );
			$args['page'] = $page ? $page : 1;
		}

		// Check for the `limit` query arg. Default to, and max of, 100.
		if ( isset( $_REQUEST['limit'] ) ) {
			$limit         = absint( wp_unslash( $_REQUEST['limit'] ) );
			$args['limit'] = $limit && 101 > $limit ? $limit : $limit_default;
		}

		return $args;
	}

	/**
	 * Returns the `WP_Query` args for use when making AJAX requests.
	 *
	 * @since 1.6.5
	 *
	 * @return array The `WP_Query` arguments array.
	 */
	public function get_posts_args() {
		// Get the paging request args.
		$request_args = $this->get_paging_request_args();

		return array(
			'order'          => 'ASC',
			'orderby'        => 'name',
			'paged'          => $request_args['page'],
			'post_status'    => 'publish',
			'posts_per_page' => $request_args['limit'],
		);
	}

	/**
	 * Returns the `WP_Term_Query` args for use when making AJAX requests.
	 *
	 * @since 1.6.5
	 *
	 * @return array The `WP_Term_Query` arguments array.
	 */
	public function get_terms_args() {
		// Get the paging request args.
		$request_args = $this->get_paging_request_args();

		return array(
			'hide_empty' => false,
			'number'     => $request_args['limit'],
			'offset'     => ( $request_args['page'] - 1 ) * $request_args['limit'],
		);
	}
}
