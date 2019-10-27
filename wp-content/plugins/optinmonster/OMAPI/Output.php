<?php
/**
 * Output class.
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
 * Output class.
 *
 * @since 1.0.0
 */
class OMAPI_Output {

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
	 * Holds the meta fields used for checking output statuses.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Flag for determining if localized JS variable is output.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $localized = false;

	/**
	 * Flag for determining if localized JS variable is output.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $data_output = false;

	/**
	 * Holds JS slugs for maybe parsing shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $slugs = array();

	/**
	 * Holds shortcode output.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $shortcodes = array();

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		add_filter( 'optinmonster_pre_campaign_should_output', array( $this, 'enqueue_helper_js_if_applicable' ), 999, 2 );

		// If no credentials have been provided, do nothing.
		if ( ! $this->base->get_api_credentials() ) {
			return;
		}

		// Load actions and filters.
		add_action( 'wp_enqueue_scripts', array( $this, 'api_script' ) );
		add_action( 'wp_footer', array( $this, 'localize' ), 9999 );
		add_action( 'wp_footer', array( $this, 'display_rules_data' ), 9999 );
		add_action( 'wp_footer', array( $this, 'maybe_parse_shortcodes' ), 11 );

		// Maybe load OptinMonster.
		$this->maybe_load_optinmonster();

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set() {

		self::$instance = $this;
		$this->base     = OMAPI::get_instance();

		$rules = new OMAPI_Rules();

		// Keep these around for back-compat.
		$this->fields   = $rules->fields;

	}

	/**
	 * Enqueues the OptinMonster API script.
	 *
	 * @since 1.0.0
	 */
	public function api_script() {

		wp_enqueue_script( $this->base->plugin_slug . '-api-script', OPTINMONSTER_APIJS_URL, array( 'jquery' ), null );

		if ( version_compare( get_bloginfo( 'version' ), '4.1.0', '>=' ) ) {
			add_filter( 'script_loader_tag', array( $this, 'filter_api_script' ), 10, 2 );
		} else {
			add_filter( 'clean_url', array( $this, 'filter_api_url' ) );
		}

	}

	/**
	 * Filters the API script tag to add a custom ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag    The HTML script output.
	 * @param string $handle The script handle to target.
	 * @return string $tag   Amended HTML script with our ID attribute appended.
	 */
	public function filter_api_script( $tag, $handle ) {

		// If the handle is not ours, do nothing.
		if ( $this->base->plugin_slug . '-api-script' !== $handle ) {
			return $tag;
		}

		// Adjust the output to add our custom script ID.
		return str_replace( ' src', ' data-cfasync="false" id="omapi-script" async="async" src', $tag );

	}

	/**
	 * Filters the API script tag to add a custom ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url  The URL to filter.
	 * @return string $url Amended URL with our ID attribute appended.
	 */
	public function filter_api_url( $url ) {

		// If the handle is not ours, do nothing.
		if ( false === strpos( $url, str_replace( 'https://', '', OPTINMONSTER_APIJS_URL ) ) ) {
			return $url;
		}

		// Adjust the URL to add our custom script ID.
		return "$url' async='async' id='omapi-script";

	}

	/**
	 * Set the default query arg filter for OptinMonster.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $bool Whether or not to alter the query arg filter.
	 * @return bool      True or false based on query arg detection.
	 */
	public function query_filter( $bool ) {

		// If "omhide" is set, the query filter exists.
		if ( isset( $_GET['omhide'] ) && $_GET['omhide'] ) {
			return true;
		}

		return $bool;

	}

	/**
	 * Conditionally loads the OptinMonster optin based on the query filter detection.
	 *
	 * @since 1.0.0
	 */
	public function maybe_load_optinmonster() {

		// If a URL suffix is set to not load optinmonster, don't do anything.
		if ( apply_filters( 'optin_monster_api_query_filter', false ) ) {
			// Default the global cookie to 30 days.
			$global_cookie = 30;
			$global_cookie = apply_filters( 'optin_monster_query_cookie', $global_cookie ); // Deprecated.
			$global_cookie = apply_filters( 'optin_monster_api_query_cookie', $global_cookie );
			if ( $global_cookie ) {
				setcookie( 'om-global-cookie', 1, time() + 3600 * 24 * (int) $global_cookie, COOKIEPATH, COOKIE_DOMAIN, false );
			}

			return;
		}

		// Add the hook to allow OptinMonster to process.
		add_action( 'pre_get_posts', array( $this, 'load_optinmonster_inline' ), 9999 );
		add_action( 'wp_footer', array( $this, 'load_optinmonster' ) );

	}

	/**
	 * Loads an inline optin form (sidebar and after post) by checking against the current query.
	 *
	 * @since 1.0.0
	 *
	 * @param object $query The current main WP query object.
	 */
	public function load_optinmonster_inline( $query ) {

		// If we are not on the main query or if in an rss feed, do nothing.
		if ( ! $query->is_main_query() || $query->is_feed() ) {
			return;
		}

		$priority = apply_filters( 'optin_monster_post_priority', 999 ); // Deprecated.
		$priority = apply_filters( 'optin_monster_api_post_priority', 999 );
		add_filter( 'the_content', array( $this, 'load_optinmonster_inline_content' ), $priority );

	}

	/**
	 * Filters the content to output an optin form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content  The current HTML string of main content.
	 * @return string $content Amended content with possibly an optin.
	 */
	public function load_optinmonster_inline_content( $content ) {

		global $post;

		// If the global $post is not set or the post status is not published, return early.
		if ( empty( $post ) || isset( $post->ID ) && 'publish' !== get_post_status( $post->ID ) ) {
			   return $content;
		}

		// Don't do anything for excerpts.
		// This prevents the optin accidentally being output when get_the_excerpt() or wp_trim_excerpt() is
		// called by a theme or plugin, and there is no excerpt, meaning they call the_content and break us.
		global $wp_current_filter;

		if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
			return $content;
		}

		if ( in_array( 'wp_trim_excerpt', (array) $wp_current_filter ) ) {
			return $content;
		}

		// Prepare variables.
		$post_id = self::current_id();
		$optins = $this->base->get_optins();

		// If no optins are found, return early.
		if ( ! $optins ) {
			return $content;
		}

		// Loop through each optin and optionally output it on the site.
		foreach ( $optins as $optin ) {
			if ( OMAPI_Rules::check_inline( $optin, $post_id, true ) ) {
				$this->set_slug( $optin );

				// Prepare the optin campaign.
				$content .= $this->prepare_campaign( $optin );
			}
		}

		// Return the content.
		return $content;

	}

	/**
	 * Possibly loads an optin on a page.
	 *
	 * @since 1.0.0
	 */
	public function load_optinmonster() {

		// Prepare variables.
		$post_id = self::current_id();
		$optins  = $this->base->get_optins();
		$init    = array();

		// If no optins are found, return early.
		if ( ! $optins ) {
			return;
		}

		// Loop through each optin and optionally output it on the site.
		foreach ( $optins as $optin ) {
			$rules = new OMAPI_Rules( $optin, $post_id );

			if ( $rules->should_output() ) {
				$this->set_slug( $optin );

				// Prepare the optin campaign.
				$init[ $optin->post_name ] = $this->prepare_campaign( $optin );
				continue;
			}

			$fields = $rules->field_values;

			// Allow devs to filter the final output for more granular control over optin targeting.
			// Devs should return the value for the slug key as false if the conditions are not met.
			$init = apply_filters( 'optinmonster_output', $init ); // Deprecated.
			$init = apply_filters( 'optin_monster_output', $init, $optin, $fields, $post_id ); // Deprecated.
			$init = apply_filters( 'optin_monster_api_output', $init, $optin, $fields, $post_id );
		}

		// Run a final filter for all items.
		$init = apply_filters( 'optin_monster_api_final_output', $init, $post_id );

		// If the init code is empty, do nothing.
		if ( empty( $init ) ) {
			return;
		}

		// Load the optins.
		foreach ( (array) $init as $optin ) {
			if ( $optin ) {
				echo $optin;
			}
		}

	}

	/**
	 * Sets the slug for possibly parsing shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @param object $optin The optin object.
	 */
	public function set_slug( $optin ) {
		$slug = str_replace( '-', '_', $optin->post_name );

		// Set the slug.
		$this->slugs[ $slug ] = array(
			'slug'     => $slug,
			'mailpoet' => (bool) get_post_meta( $optin->ID, '_omapi_mailpoet', true ),
		);

		// Maybe set shortcode.
		if ( get_post_meta( $optin->ID, '_omapi_shortcode', true ) ) {
			$this->shortcodes[] = get_post_meta( $optin->ID, '_omapi_shortcode_output', true );
		}

		if ( get_post_meta( $optin->ID, '_omapi_mailpoet', true ) ) {
			$this->wp_helper();
		}

		return $this;
	}

	/**
	 * Maybe outputs the JS variables to parse shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function maybe_parse_shortcodes() {

		// If no slugs have been set, do nothing.
		if ( empty( $this->slugs ) ) {
			return;
		}

		// Loop through any shortcodes and output them.
		foreach ( $this->shortcodes as $shortcode_string ) {
			if ( empty( $shortcode_string ) ) {
				continue;
			}

			if ( strpos( $shortcode_string, '|||' ) !== false ) {
				$all_shortcode = explode( '|||', $shortcode_string );
			} else { // Backwards compat.
				$all_shortcode = explode( ',', $shortcode_string );
			}

			foreach ( $all_shortcode as $shortcode ) {
				if ( empty( $shortcode ) ) {
					continue;
				}

				echo '<div style="position:absolute;overflow:hidden;clip:rect(0 0 0 0);height:1px;width:1px;margin:-1px;padding:0;border:0">';
					echo '<div class="omapi-shortcode-helper">' . html_entity_decode( $shortcode, ENT_COMPAT ) . '</div>';
					echo '<div class="omapi-shortcode-parsed">' . do_shortcode( html_entity_decode( $shortcode, ENT_COMPAT ) ) . '</div>';
				echo '</div>';
			}
		}

		// Output the JS variables to signify shortcode parsing is needed.
		?>
		<script type="text/javascript"><?php foreach ( $this->slugs as $slug => $data ) { echo 'var ' . $slug . '_shortcode = true;'; } ?></script>
		<?php

	}

	/**
	 * Possibly localizes a JS variable for output use.
	 *
	 * @since 1.0.0
	 */
	public function localize() {

		// If no slugs have been set, do nothing.
		if ( empty( $this->slugs ) ) {
			return;
		}

		// If already localized, do nothing.
		if ( $this->localized ) {
			return;
		}

		// Set flag to true.
		$this->localized = true;

		// Output JS variable.
		?>
		<script type="text/javascript">var omapi_localized = { ajax: '<?php echo esc_url_raw( add_query_arg( 'optin-monster-ajax-route', true, admin_url( 'admin-ajax.php' ) ) ); ?>', nonce: '<?php echo wp_create_nonce( 'omapi' ); ?>', slugs: <?php echo json_encode( $this->slugs ); ?> };</script>
		<?php
	}

	/**
	 * Enqueues the WP helper script for storing local optins.
	 *
	 * @since 1.0.0
	 */
	public function wp_helper() {
		// Only try to use the MailPoet integration if it is active.
		if ( $this->base->is_mailpoet_active() ) {
			wp_enqueue_script(
				$this->base->plugin_slug . '-wp-helper',
				plugins_url( 'assets/js/helper.js', OMAPI_FILE ),
				array( 'jquery'),
				$this->base->version . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : '' ),
				true
			);
		}
	}

	/**
	 * Outputs a JS variable, in the footer of the site, with information about
	 * the current page, and the terms in use for the display rules.
	 *
	 * @since 1.6.5
	 *
	 * @return void
	 */
	public function display_rules_data() {
		global $wp_query;

		// If already localized, do nothing.
		if ( $this->data_output ) {
			return;
		}

		// Set flag to true.
		$this->data_output = true;

		$tax_terms    = array();
		$object       = get_queried_object();
		$object_id    = self::current_id();
		$object_class = is_object( $object ) ? get_class( $object ) : '';
		$object_type  = '';
		$object_key   = '';
		$post         = null;
		if ( 'WP_Post' === $object_class ) {
			$post        = $object;
			$object_type = 'post';
			$object_key  = $object->post_type;
		} elseif ( 'WP_Term' === $object_class ) {
			$object_type = 'term';
			$object_key  = $object->taxonomy;
		}

		// Get the current object's terms, if applicable. Defaults to public taxonomies only.
		if ( ! empty( $post->ID ) && is_singular() || ( $wp_query->is_category() || $wp_query->is_tag() || $wp_query->is_tax() ) ) {

			// Should we only check public taxonomies?
			$only_public = apply_filters( 'optinmonster_only_check_public_taxonomies', true, $post );
			$taxonomies  = get_object_taxonomies( $post, false );

			if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {

					// Private ones should remain private and not output in the JSON blob.
					if ( $only_public && ! $taxonomy->public ) {
						continue;
					}

					$terms = get_the_terms( $post, $taxonomy->name );
					if ( ! empty( $terms ) && is_array( $terms ) ) {
						$tax_terms = array_merge( $tax_terms, wp_list_pluck( $terms, 'term_id' ) );
					}
				}

				$tax_terms = wp_parse_id_list( $tax_terms );
			}
		}

		$output = array(
			'wc_cart'     => $this->woocommerce_cart(),
			'object_id'   => $object_id,
			'object_key'  => $object_key,
			'object_type' => $object_type,
			'term_ids'    => $tax_terms,
		);
		$output = function_exists( 'wp_json_encode' )
			? wp_json_encode( $output )
			: json_encode( $output );

		// Output JS variable.
		?>
		<script type="text/javascript">var omapi_data = <?php echo $output; // XSS: okay. ?>;</script>
		<?php
	}

	/**
	 * Prepare the optin campaign html.
	 *
	 * @since  1.5.0
	 *
	 * @param  object  $optin The option post object.
	 *
	 * @return string         The optin campaign html.
	 */
	public function prepare_campaign( $optin ) {
		return isset( $optin->post_content ) && ! empty( $optin->post_content )
			? trim( html_entity_decode( stripslashes( $optin->post_content ), ENT_QUOTES ), '\'' )
			: '';
	}

	/**
	 * Enqueues the WP helper script if relevant optin fields are found.
	 *
	 * @since  1.5.0
	 *
	 * @param  bool  $should_output Whether it should output.
	 * @param  OMAPI_Rules $rules   OMAPI_Rules object
	 *
	 * @return array
	 */
	public function enqueue_helper_js_if_applicable( $should_output, $rules ) {

		// Check to see if we need to load the WP API helper script.
		if ( $should_output && ! $rules->field_empty( 'mailpoet' ) ) {
			$this->wp_helper();
		}

		return $should_output;
	}

	/**
	 * Get the current page/post's post id.
	 *
	 * @since  1.6.9
	 *
	 * @return int
	 */
	public static function current_id() {
		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			if ( 'page' == get_option( 'show_on_front' ) ) {
				$post_id = get_option( 'page_for_posts' );
			}
		}

		return $post_id;
	}

	/**
	 * AJAX callback for returning WooCommerce cart information.
	 *
	 * @since 1.7.0
	 *
	 * @return array An array of WooCommerce cart data.
	 */
	public function woocommerce_cart() {
		// Bail if WooCommerce isn't currently active.
		if ( ! OMAPI::is_woocommerce_active() ) {
			return array();
		}

		// Check WooCommerce is version 3.0.0 or greater.
		if ( ! OMAPI::woocommerce_version_compare( '3.0.0' ) ) {
			return array();
		}

		// Calculate the cart totals.
		WC()->cart->calculate_totals();

		// Get initial cart data.
		$cart               = WC()->cart->get_totals();
		$cart['cart_items'] = WC()->cart->get_cart();

		// Set the currency data.
		$currencies       = get_woocommerce_currencies();
		$currency_code    = get_woocommerce_currency();
		$cart['currency'] = array(
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
		return $cart;
	}
}
