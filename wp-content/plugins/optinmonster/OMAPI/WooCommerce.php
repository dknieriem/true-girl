<?php
/**
 * WooCommerce class.
 *
 * @since 1.7.0
 *
 * @package OMAPI
 * @author  Brandon Allen
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The WooCommerce class.
 *
 * @since 1.7.0
 */
class OMAPI_WooCommerce {

	/**
	 * Holds the class object.
	 *
	 * @since 1.7.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.7.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.7.0
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();
	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.7.0
	 */
	public function set() {
		self::$instance = $this;
		$this->base     = OMAPI::get_instance();
	}

	/**
	 * Connects WooCommerce to OptinMonster.
	 *
	 * @param array $data The array of consumer key and consumer secret.
	 *
	 * @since 1.7.0
	 *
	 * @returns WP_Error|array
	 */
	public function connect( $data ) {
		if ( empty( $data['consumerKey'] ) || empty( $data['consumerSecret'] ) ) {
			return new WP_Error(
				'omapi-invalid-woocommerce-keys',
				__( 'The consumer key or consumer secret appears to be invalid. Try again.', 'optin-monster-api' )
			);
		}

		$data['woocommerce'] = OMAPI::woocommerce_version();

		// Get the OptinMonster API credentials.
		$creds = $this->get_request_api_credentials();

		// Initialize the API class.
		$api = new OMAPI_Api( 'woocommerce/shop', $creds );

		// Update the `base` and `url` properties to use the `/v2` route.
		$api->set( 'base', trailingslashit( rtrim( OPTINMONSTER_APP_API_URL, 'v1/' ) ) . 'v2/' );
		$api->set( 'url', $api->base . $api->route );

		return $api->request( $data );
	}

	/**
	 * Disconnects WooCommerce from OptinMonster.
	 *
	 * @since 1.7.0
	 */
	public function disconnect() {

		// Get the OptinMonster API credentials.
		$creds = $this->get_request_api_credentials();

		// Get the shop.
		$shop = esc_attr( $this->base->get_option( 'woocommerce', 'shop' ) );

		if ( empty( $shop ) ) {
			return true;
		}

		// Initialize the API class.
		$api = new OMAPI_Api( 'woocommerce/shop/' . rawurlencode( $shop ), $creds, 'DELETE' );

		// Update the `base` and `url` properties to use the `/v2` route.
		$api->set( 'base', trailingslashit( rtrim( OPTINMONSTER_APP_API_URL, 'v1/' ) ) . 'v2/' );
		$api->set( 'url', $api->base . $api->route );

		return $api->request();
	}

	/**
	 * Returns the API credentials to be used in an API request.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	public function get_request_api_credentials() {
		$creds = $this->base->get_api_credentials();

		// If set, return only the API key, not the legacy API credentials.
		if ( $creds['apikey'] ) {
			$_creds = array(
				'apikey' => $creds['apikey'],
			);
		} else {
			$_creds = array(
				'user' => $creds['user'],
				'key'  => $creds['key'],
			);
		}

		return $_creds;
	}

	/**
	 * Validates the passed consumer key and consumer secret.
	 *
	 * @since 1.7.0
	 *
	 * @param array $data The consumer key and consumer secret.
	 *
	 * @return array
	 */
	public function validate_keys( $data ) {
		$key    = isset( $data['consumer_key'] ) ? $data['consumer_key'] : '';
		$secret = isset( $data['consumer_secret'] ) ? $data['consumer_secret'] : '';

		if ( ! $key ) {
			return array(
				'error' => __( 'Consumer key is missing.', 'optin-monster-api' ),
			);
		}

		if ( ! $secret ) {
			return array(
				'error' => __( 'Consumer secret is missing.', 'optin-monster-api' ),
			);
		}

		// Attempt to find the passed consumer key in the database.
		$keys = $this->get_keys_by_consumer_key( $data['consumer_key'] );

		// If the consumer key is valid, then validate the consumer secret.
		if (
			empty( $keys['error'] )
			&& $this->is_consumer_secret_valid( $keys['consumer_secret'], $secret )
		) {
			$keys['consumer_key'] = $key;
		} else {
			$keys['error'] = __( 'Consumer secret is invalid.', 'optin-monster-api' );
		}

		return $keys;
	}

	/**
	 * Return the keys for the given consumer key.
	 *
	 * This is a rough copy of the same method used by WooCommerce.
	 *
	 * @since 1.7.0
	 *
	 * @param string $consumer_key The consumer key passed by the user.
	 *
	 * @return array
	 */
	private function get_keys_by_consumer_key( $consumer_key ) {
		global $wpdb;

		$consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );

		$keys = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT key_id, consumer_secret
					FROM {$wpdb->prefix}woocommerce_api_keys
					WHERE consumer_key = %s
				",
				$consumer_key
			),
			ARRAY_A
		);

		if ( empty( $keys ) ) {
			$keys = array(
				'error' => __( 'Consumer key is invalid.', 'optin-monster-api' ),
			);
		}

		return $keys;
	}

	/**
	 * Check if the consumer secret provided for the given user is valid
	 *
	 * This is a copy of the same method used by WooCommerce.
	 *
	 * @since 1.7.0
	 *
	 * @param string $keys_consumer_secret The consumer secret from the database.
	 * @param string $consumer_secret      The consumer secret passed by the user.
	 *
	 * @return bool
	 */
	private function is_consumer_secret_valid( $keys_consumer_secret, $consumer_secret ) {
		return hash_equals( $keys_consumer_secret, $consumer_secret );
	}

	/**
	 * Get WooCommerce API description and truncated key info by the key id.
	 *
	 * @since 1.7.0
	 *
	 * @param string $key_id The WooCommerce API key id.
	 *
	 * @return array
	 */
	public static function get_key_details_by_id( $key_id ) {
		if ( empty( $key_id ) ) {
			return array();
		}

		global $wpdb;

		$data = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT key_id, description, truncated_key
					FROM {$wpdb->prefix}woocommerce_api_keys
					WHERE key_id = %d
				",
				absint( $key_id )
			),
			ARRAY_A
		);

		return $data;
	}

	/**
	 * Determines if the current site is has WooCommerce connected.
	 *
	 * Checks that the site stored in the OptinMonster option matches the
	 * current `siteurl` WP option, and that the saved key id still exists in
	 * the WooCommerce key table. If these two things aren't true, then the
	 * current site is not connected.
	 *
	 * @since 1.7.0
	 *
	 * @return boolean
	 */
	public static function is_connected() {
		// Get current site details.
		// NOTE: Error suppression is used as prior to PHP 5.3.3, an
		// E_WARNING would be generated when URL parsing failed.
		$site = function_exists( 'wp_parse_url' )
			? wp_parse_url( site_url() )
			: parse_url( site_url() );
		$host = isset( $site['host'] ) ? $site['host'] : '';

		// Get any options we have stored.
		$option = OMAPI::get_instance()->get_option( 'woocommerce' );
		$shop   = isset( $option['shop'] ) ? $option['shop'] : '';
		$key_id = isset( $option['key_id'] ) ? $option['key_id'] : '';
		$key    = $key_id ? self::get_key_details_by_id( $key_id ) : array();

		return ! empty( $key['key_id'] ) && $host === $shop;
	}

	/**
	 * Add the category base to the category REST API response.
	 *
	 * @since 1.7.0
	 *
	 * @param WP_REST_Response $response The REST API response.
	 *
	 * @return WP_REST_Response
	 */
	public static function add_category_base_to_api_response( $response ) {
		return self::add_base_to_api_response( $response, 'category_rewrite_slug' );
	}

	/**
	 * Add the tag base to the tag REST API response.
	 *
	 * @since 1.7.0
	 *
	 * @param WP_REST_Response $response The REST API response.
	 *
	 * @return WP_REST_Response
	 */
	public static function add_tag_base_to_api_response( $response ) {
		return self::add_base_to_api_response( $response, 'tag_rewrite_slug' );
	}

	/**
	 * Add the category/tag base to the category/tag REST API response.
	 *
	 * @since 1.7.0
	 *
	 * @param WP_REST_Response $response The REST API response.
	 * @param string           $base     The base setting to retrieve.
	 *
	 * @return WP_REST_Response
	 */
	public static function add_base_to_api_response( $response, $base ) {
		$permalink_options = wc_get_permalink_structure();
		if ( isset( $permalink_options[ $base ] ) ) {
			$response->data['base'] = $permalink_options[ $base ];
		}

		return $response;
	}
}
