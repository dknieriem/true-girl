<?php
/**
 * Save class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save class.
 *
 * @since 1.0.0
 */
class OMAPI_Save {

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
	 * Holds any save errors.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $errors = array();

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

		// Possibly save settings.
		$this->maybe_save();

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();
		$this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();

	}

	/**
	 * Maybe save options if the action has been requested.
	 *
	 * @since 1.0.0
	 */
	public function maybe_save() {

		// If we are missing our save action, return early.
		if ( empty( $_POST['omapi_save'] ) ) {
			return;
		}

		// If the subkey is empty, return early.
		if ( empty( $_POST['omapi'][ $this->view ] ) ) {
			return;
		}

		// Run a current user check on saving.
		if ( ! current_user_can( apply_filters( 'optin_monster_api_save_cap', 'manage_options' ) ) ) {
			return;
		}

		// Verify the nonce field.
		check_admin_referer( 'omapi_nonce_' . $this->view, 'omapi_nonce_' . $this->view );

		// Save the settings.
		$this->save();

		// Provide action to save settings.
		do_action( 'optin_monster_api_save_settings', $this->view );

	}

	/**
	 * Save the plugin options.
	 *
	 * @since 1.0.0
	 */
	public function save() {

		// Prepare variables.
		$data = stripslashes_deep( $_POST['omapi'][ $this->view ] );

		// Save the data.
		switch ( $this->view ) {
			case 'api' :
				// Create a new API instance to verify API credentials.
				$option     = $this->base->get_option();
				$apikey     = isset( $data['apikey'] ) ? $data['apikey'] : false;
				$user       = isset( $data['user'] ) ? $data['user'] : false;
				$key        = isset( $data['key'] ) ? $data['key'] : false;
				$old_user   = isset( $option['api']['user'] ) ? $option['api']['user'] : false;
				$old_key    = isset( $option['api']['key'] ) ? $option['api']['key'] : false;
				$old_apikey = isset( $option['api']['apikey'] ) ? $option['api']['apikey'] : false;
				if ( isset( $data['omwpdebug'] ) ) {
					$option['api']['omwpdebug'] = true;
				} else {
					unset( $option['api']['omwpdebug'] );
				}

				// Check for new single apikey and break early with only that data check
				if ( $apikey ) {
					// Verify this new API Key works but posting to the Legacy route
					$api = new OMAPI_Api( 'verify', array( 'apikey' => $apikey ) );
					$ret = $api->request();

					if ( is_wp_error( $ret ) ) {
						$this->errors['error'] = $ret->get_error_message();
					} else {
						$option['api']['apikey'] = $apikey;

						// Go ahead and remove the old user and key so we get the 'new' user stuff
						$option['api']['user'] = '';
						$option['api']['key']  = '';

						// Remove any error messages.
						$option['is_invalid']  = false;
						$option['is_expired']  = false;
						$option['is_disabled'] = false;

						// Store the optin data.
						$this->store_optins( $ret );

						// Save the option.
						$this->update_optin_monster_api_option( $option, $data, $this->view );
					}
					// End since we are working with the new apikey
					break;
				}

				// Catch apikey not set errors
				if ( ! $apikey ) {

					// Did we used to have one and user is trying to remove it?
					if ( $old_apikey ) {
						$option['api']['apikey'] = '';

						// Save the option.
						update_option( 'optin_monster_api', $option );

						// Explicitly end here so we don't accidentally try grabbing the next round of checks on $user and $key
						break;
					}
				}

				// If one or both items are missing, fail.
				if ( ! $user || ! $key ) {

					// If it had been stored and it is now empty, reset the keys altogether.
					if ( ! $user && $old_user || ! $key && $old_key ) {
						$option['api']['user'] = '';
						$option['api']['key']  = '';

						// Save the option.
						$this->update_optin_monster_api_option( $option, $data, $this->view );
					} else {
						$this->errors['error'] = __( 'You must provide a valid API Key to authenticate with OptinMonster.', 'optin-monster-api' );
					}
				} else {
					$api = new OMAPI_Api( 'verify', array( 'user' => $user, 'key' => $key ) );
					$ret = $api->request();
					if ( is_wp_error( $ret ) ) {
						$this->errors['error'] = $ret->get_error_message();
					} else {
						// This user and key are good to go!
						$option['api']['user'] = $user;
						$option['api']['key']  = $key;

						// Remove any error messages.
						$option['is_invalid']  = false;
						$option['is_expired']  = false;
						$option['is_disabled'] = false;

						// Store the optin data.
						$this->store_optins( $ret );

						// Save the option.
						$this->update_optin_monster_api_option( $option, $data, $this->view );
					}
				}
			break;

			case 'optins' :
				// Prepare variables.
				$data['categories']   = isset( $_POST['post_category'] ) ? stripslashes_deep( $_POST['post_category'] ) : array();
				$data['taxonomies']   = isset( $_POST['tax_input'] ) ? stripslashes_deep( $_POST['tax_input'] ) : array();
				$optin_id             = absint( $_GET['optin_monster_api_id'] );
				$fields               = array();
				$fields['enabled']    = isset( $data['enabled'] ) ? 1 : 0;

				$fields['automatic']  = isset( $data['automatic'] ) ? 1 : 0;
				$fields['users']      = isset( $data['users'] ) ? esc_attr( $data['users'] ) : 'all';
				$fields['never']      = isset( $data['never'] ) ? explode( ',', $data['never'] ) : array();
				$fields['only']       = isset( $data['only'] ) ? explode( ',', $data['only'] ) : array();
				$fields['categories'] = isset( $data['categories'] ) ? $data['categories'] : array();
				$fields['taxonomies'] = isset( $data['taxonomies'] ) ? $data['taxonomies'] : array();
				$fields['show']       = isset( $data['show'] ) ? $data['show'] : array();

				// WooCommerce Fields.
				$fields['show_on_woocommerce']               = isset( $data['show_on_woocommerce'] ) ? 1 : 0;
				$fields['is_wc_shop']                        = isset( $data['is_wc_shop'] ) ? 1 : 0;
				$fields['is_wc_product']                     = isset( $data['is_wc_product'] ) ? 1 : 0;
				$fields['is_wc_cart']                        = isset( $data['is_wc_cart'] ) ? 1 : 0;
				$fields['is_wc_checkout']                    = isset( $data['is_wc_checkout'] ) ? 1 : 0;
				$fields['is_wc_account']                     = isset( $data['is_wc_account'] ) ? 1 : 0;
				$fields['is_wc_endpoint']                    = isset( $data['is_wc_endpoint'] ) ? 1 : 0;
				$fields['is_wc_endpoint_order_pay']          = isset( $data['is_wc_endpoint_order_pay'] ) ? 1 : 0;
				$fields['is_wc_endpoint_order_received']     = isset( $data['is_wc_endpoint_order_received'] ) ? 1 : 0;
				$fields['is_wc_endpoint_view_order']         = isset( $data['is_wc_endpoint_view_order'] ) ? 1 : 0;
				$fields['is_wc_endpoint_edit_account']       = isset( $data['is_wc_endpoint_edit_account'] ) ? 1 : 0;
				$fields['is_wc_endpoint_edit_address']       = isset( $data['is_wc_endpoint_edit_address'] ) ? 1 : 0;
				$fields['is_wc_endpoint_lost_password']      = isset( $data['is_wc_endpoint_lost_password'] ) ? 1 : 0;
				$fields['is_wc_endpoint_customer_logout']    = isset( $data['is_wc_endpoint_customer_logout'] ) ? 1 : 0;
				$fields['is_wc_endpoint_add_payment_method'] = isset( $data['is_wc_endpoint_add_payment_method'] ) ? 1 : 0;

				// Save the data from the regular taxonomies fields into the WC specific tax field.
				$fields['is_wc_product_category']            = isset( $data['taxonomies']['product_cat'] ) ? $data['taxonomies']['product_cat'] : array();
				$fields['is_wc_product_tag']                 = isset( $data['taxonomies']['product_tag'] ) ? $data['taxonomies']['product_tag'] : array();


				// Convert old test mode data and remove.
				$test_mode = get_post_meta( $optin_id, '_omapi_test', true );
				if ( isset( $test_mode ) && $test_mode ) {
					$fields['users'] = 'in';
					delete_post_meta( $optin_id, '_omapi_test' );
				}

				if ( $this->base->is_mailpoet_active() ) {
					$fields['mailpoet']      = isset( $data['mailpoet'] ) ? 1 : 0;
					$fields['mailpoet_list'] = isset( $data['mailpoet_list'] ) ? esc_attr( $data['mailpoet_list'] ) : 'none';
				}

				// Allow fields to be filtered.
				$fields = apply_filters( 'optin_monster_save_fields', $fields, $optin_id );

				// Loop through each field and save the data.
				foreach ( $fields as $key => $val ) {
					update_post_meta( $optin_id, '_omapi_' . $key, $val );
				}
			break;

			case 'settings' :
				$option = $this->base->get_option();
				$option['settings']['cookies'] = isset( $data['cookies'] ) ? 1 : 0;

				// Save the option.
				$this->update_optin_monster_api_option( $option, $data, $this->view );
			break;

			case 'woocommerce':
				if ( ! empty( $data['autogenerate'] ) ) {
					// Auto-generate a key pair.
					$auto_generated_keys = $this->woocommerce_autogenerate();
					if ( empty( $auto_generated_keys ) ) {
						$this->errors['error'] = __( 'WooCommerce REST API keys could not be auto-generated on your behalf. Please try again.', 'optin-monster-api' );
						break;
					}

					// Merge data array, with auto-generated keys array.
					$data = array_merge( $data, $auto_generated_keys );
				}

				if ( empty( $data['disconnect'] ) ) {
					$this->woocommerce_connect( $data );
				} else {
					$this->woocommerce_disconnect( $data );
				}
				break;
		}

		// If selected, clear out all local cookies.
		if ( $this->base->get_option( 'settings', 'cookies' ) ) {
			$this->base->actions->cookies();
		}

		// Add message to show error or success messages.
		if ( ! empty( $this->errors ) ) {
			add_action( 'optin_monster_api_messages_' . $this->view, array( $this, 'errors' ) );
		} else {
			// Add a success message.
			add_action( 'optin_monster_api_messages_' . $this->view, array( $this, 'message' ) );
		}

	}

	/**
	 * Store the optin data locally on the site.
	 *
	 * @since 1.0.0
	 *
	 * @param array $optins Array of optin objects to store.
	 */
	public function store_optins( $optins ) {
		/**
		 * Allows the filtering of what campaigns are stored locally.
		 *
		 * @since 1.6.3
		 *
		 * @param array  $optins An array of `WP_Post` objects.
		 * @param object $this   The OMAPI object.
		 *
		 * @return array The filtered `WP_Post` objects array.
		 */
		$optins = apply_filters( 'optin_monster_pre_store_options', $optins, $this );

		// Do nothing if this is just a success message.
		if ( isset( $optins->success ) ) {
			return;
		}

		// Loop through all of the local optins so we can try to match and update.
		$local_optins = $this->base->get_optins();
		if ( $local_optins ) {
			$this->sync_optins( $local_optins, $optins );
		} else {
			$this->add_optins( $optins );
		}

	}

	/**
	 * Add the retrieved optins as new optin post objects in the DB.
	 *
	 * @since 1.3.5
	 *
	 * @param array $optins       Array of optin objects to store.
	 */
	public function add_optins( $optins ) {
		foreach ( (array) $optins as $slug => $optin ) {
			// Maybe update an optin rather than add a new one.
			$local = $this->base->get_optin_by_slug( $slug );
			if ( $local ) {
				$this->update_optin( $local, $optin );
			} else {
				$this->new_optin( $slug, $optin );
			}
		}
	}

	/**
	 * Sync the retrieved optins with our stored optins.
	 *
	 * @since 1.3.5
	 *
	 * @param array $local_optins Array of local optin objects to sync.
	 * @param array $optins       Array of optin objects to store.
	 */
	public function sync_optins( $local_optins, $remote_optins ) {
		foreach ( $local_optins as $local ) {

			if ( isset( $remote_optins[ $local->post_name ] ) ) {

				$this->update_optin( $local, $remote_optins[ $local->post_name ] );

				unset( $remote_optins[ $local->post_name ] );
			} else {
				// Delete the local optin. It does not exist remotely.
				wp_delete_post( $local->ID, true );
				unset( $remote_optins[ $local->post_name ] );
			}
		}

		// If we still have optins, they are new and we need to add them.
		if ( ! empty( $remote_optins ) ) {
			foreach ( (array) $remote_optins as $slug => $optin ) {
				$this->new_optin( $slug, $optin );
			}
		}
	}

	/**
	 * Update an existing optin post object in the DB with the one fetched from the API.
	 *
	 * @since  1.3.5
	 *
	 * @param  object  $local The local optin post object.
	 * @param  object  $optin The optin object.
	 *
	 * @return void
	 */
	public function update_optin( $local, $optin ) {
		wp_update_post( array(
			'ID'           => $local->ID, // Existing ID
			'post_title'   => $optin->title,
			'post_content' => $optin->output,
			'post_status'  => 'publish',
		) );

		$this->update_optin_meta( $local->ID, $optin );
	}

	/**
	 * Generate a new optin post object in the DB.
	 *
	 * @since  1.3.5
	 *
	 * @param  string  $slug  The campaign slug.
	 * @param  object  $optin The optin object.
	 *
	 * @return void
	 */
	public function new_optin( $slug, $optin ) {
		$post_id = wp_insert_post( array(
			'post_name'    => $slug,
			'post_title'   => $optin->title,
			'post_excerpt' => $optin->id,
			'post_content' => $optin->output,
			'post_status'  => 'publish',
			'post_type'    => 'omapi',
		) );

		if ( 'post' === $optin->type ) {
			update_post_meta( $post_id, '_omapi_automatic', 1 );
		}

		$this->update_optin_meta( $post_id, $optin );
	}

	/**
	 * Update the optin post object's post-meta with an API object's values.
	 *
	 * @since  1.3.5
	 *
	 * @param  int    $post_id The post (optin) ID.
	 * @param  object $optin   The optin object.
	 *
	 * @return void
	 */
	public function update_optin_meta( $post_id, $optin ) {
		update_post_meta( $post_id, '_omapi_type', $optin->type );
		update_post_meta( $post_id, '_omapi_ids', $optin->ids );

		$shortcodes = ! empty( $optin->shortcodes ) ? $optin->shortcodes : null;

		$this->update_shortcodes_meta( $post_id, $shortcodes );
	}

	/**
	 * Store the raw shortcodes to the optin's meta for later retrieval/parsing.
	 *
	 * @since  1.3.5
	 *
	 * @param  int  $post_id     The post (optin) ID.
	 * @param  string|array|null The shortcodes to store to meta, or delete from meta if null.
	 *
	 * @return void
	 */
	protected function update_shortcodes_meta( $post_id, $shortcodes = null ) {
		if ( ! empty( $shortcodes ) ) {

			$shortcodes = is_array( $shortcodes )
				? implode( '|||', array_map( 'htmlentities', $shortcodes ) )
				: (array) htmlentities( $shortcodes );

			update_post_meta( $post_id, '_omapi_shortcode_output', $shortcodes );
			update_post_meta( $post_id, '_omapi_shortcode', true );
		} else {
			delete_post_meta( $post_id, '_omapi_shortcode_output' );
			delete_post_meta( $post_id, '_omapi_shortcode' );
		}
	}

	/**
	 * Updated the `optin_monster_api` option in the database.
	 *
	 * @since 1.7.0
	 *
	 * @param array  $option The full `optin_monster_api` option array.
	 * @param array  $data   The parameters passed in via POST request.
	 * @param string $view   The current settings menu view.
	 *
	 * @return void
	 */
	public function update_optin_monster_api_option( $option, $data, $view ) {
		/**
		 * Filters the `optin_monster_api` option before being saved to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $option The full `optin_monster_api` option array.
		 * @param array  $data   The parameters passed in via POST request.
		 * @param string $view   The current settings menu view.
		 */
		$option = apply_filters( 'optin_monster_api_save', $option, $data, $view );

		// Save the option.
		update_option( 'optin_monster_api', $option );
	}

	/**
	 * Handles auto-generating WooCommerce API keys for use with OM.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	public function woocommerce_autogenerate() {
		$cookies = array();
		foreach ( $_COOKIE as $name => $val ) {
			$cookies[] = "$name=" . urlencode( is_array( $val ) ? serialize( $val ) : $val );
		}
		$cookies = implode( '; ', $cookies );

		$request_args = array(
			'sslverify' => apply_filters( 'https_local_ssl_verify', true ),
			'body'      => array(
				'action'      => 'woocommerce_update_api_key',
				'description' => 'OptinMonster API Read-Access (Auto-Generated)',
				'permissions' => 'read',
				'user'        => get_current_user_id(),
				'security'    => wp_create_nonce( 'update-api-key' ),
			),
			'headers'   => array(
				'cookie' => $cookies,
			),
		);
		$response = wp_remote_post( admin_url( 'admin-ajax.php' ), $request_args );
		$code     = wp_remote_retrieve_response_code( $response );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );

		if (
			200 === intval( $code )
			&& ! empty( $body->success )
			&& ! empty( $body->data->consumer_key )
			&& ! empty( $body->data->consumer_secret )
		) {

			return (array) $body->data;
		}

		return array();
	}

	/**
	 * Handles connecting WooCommerce when the connect button is clicked.
	 *
	 * @since 1.7.0
	 *
	 * @param array $data The data passed in via POST request.
	 *
	 * @return void
	 */
	protected function woocommerce_connect( $data ) {
		$woocommerce = new OMAPI_WooCommerce();
		$keys        = $woocommerce->validate_keys( $data );

		if ( isset( $keys['error'] ) ) {
			$this->errors['error'] = $keys['error'];
		} else {

			// Get the version of the REST API we should use. The
			// `v3` route wasn't added until WooCommerce 3.5.0.
			$api_version = OMAPI::woocommerce_version_compare( '3.5.0' )
				? 'v3'
				: 'v2';

			// Get current site url.
			$url = esc_url_raw( site_url() );

			// Make a connection request.
			$response = $woocommerce->connect(
				array(
					'consumerKey'    => $keys['consumer_key'],
					'consumerSecret' => $keys['consumer_secret'],
					'apiVersion'     => $api_version,
					'shop'           => $url,
					'name'           => esc_html( get_bloginfo( 'name' ) ),
				)
			);

			// Output an error or register a successful connection.
			if ( is_wp_error( $response ) ) {
				$this->errors['error'] = isset( $response->message )
					? $response->message
					: __( 'WooCommerce could not be connected to OptinMonster. The OptinMonster API returned with the following response: ', 'optin-monster-api' ) . $response->get_error_message();
			} else {

				// Get the shop hostname.
				// NOTE: Error suppression is used as prior to PHP 5.3.3, an
				// E_WARNING would be generated when URL parsing failed.
				$site = function_exists( 'wp_parse_url' )
					? wp_parse_url( $url )
					: @parse_url( $url );
				$host = isset( $site['host'] ) ? $site['host'] : '';

				// Set up the connected WooCommerce options.
				$option                = $this->base->get_option();
				$option['woocommerce'] = array(
					'api_version' => $api_version,
					'key_id'      => $keys['key_id'],
					'shop'        => $host,
				);

				// Save the option.
				$this->update_optin_monster_api_option( $option, $data, $this->view );
			}
		}
	}

	/**
	 * Handles disconnecting WooCommerce when the disconnect button is clicked.
	 *
	 * @since 1.7.0
	 *
	 * @param array $data The data passed in via POST request.
	 *
	 * @return void
	 */
	protected function woocommerce_disconnect( $data ) {
		$woocommerce = new OMAPI_WooCommerce();
		$response    = $woocommerce->disconnect();

		// Output an error or register a successful disconnection.
		if ( is_wp_error( $response ) ) {
			$this->errors['error'] = isset( $response->message )
				? $response->message
				: __( 'WooCommerce could not be disconnected from OptinMonster. The OptinMonster API returned with the following response: ', 'optin-monster-api' ) . $response->get_error_message();
		} else {
			$option = $this->base->get_option();

			unset( $option['woocommerce'] );

			// Save the option.
			$this->update_optin_monster_api_option( $option, $data, $this->view );
		}
	}

	/**
	 * Output any error messages.
	 *
	 * @since 1.0.0
	 */
	public function errors() {

		foreach ( $this->errors as $id => $message ) :
		?>
		<div class="<?php echo sanitize_html_class( $id, 'error' ); ?>"><p><?php echo $message; ?></p></div>
		<?php
		endforeach;

	}

	/**
	 * Output a save message.
	 *
	 * @since 1.0.0
	 */
	public function message() {

		?>
		<div class="updated"><p><?php _e( 'Your settings have been saved successfully.', 'optin-monster-api' ); ?></p></div>
		<?php

	}

}
