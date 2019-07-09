<?php

/**
 * Class MC4WP_Ecommerce_Admin
 *
 * @ignore
 */
class MC4WP_Ecommerce_Admin {

	/**
	 * @var MC4WP_Plugin
	 */
	protected $plugin;

	/**
	 * @var boolean
	 */
	protected $enabled;

	/**
	 * MC4WP_Ecommerce_Admin constructor.
	 *
	 * @param MC4WP_Plugin $plugin
	 * @param boolean $enabled
	 */
	public function __construct( MC4WP_Plugin $plugin, $enabled = false ) {
		$this->plugin = $plugin;
		$this->enabled = $enabled;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		# Pages
		add_action( 'mc4wp_admin_other_settings', array( $this, 'show_settings_page' ) );
        add_action( 'admin_notices', array( $this, 'show_api_v3_notice' ) );

		# Add notice when eCommerce is enabled
		add_action( 'mc4wp_save_settings', array( $this, 'maybe_show_notice' ), 10, 2 );

        # Listen to form for enabling e-commerce v3
        add_action( 'mc4wp_admin_enable_ecommerce_v3', array( $this, 'enable_ecommerce_v3' ), 90 );

		# AJAX hooks
		add_action( 'wp_ajax_mc4wp_ecommerce_add_untracked_orders', array( $this, 'add_untracked_orders' ) );
		add_action( 'wp_ajax_mc4wp_ecommerce_get_untracked_orders_count', array( $this, 'get_untracked_orders_count' ) );

		# Hook into regular form submit (non-AJAX)
		add_action( 'mc4wp_admin_ecommerce_add_untracked_orders', array( $this, 'add_untracked_orders' ) );

		add_action( 'admin_menu', array( $this, 'register_hidden_pages' ) );

		if( $this->enabled ) {
			// add new WooCommerce order action to manually add / delete order from MailChimp
			add_filter( 'woocommerce_order_actions', array( $this, 'add_woocommerce_order_action' ) );
			add_action( 'woocommerce_order_action_mailchimp_ecommerce', array( $this, 'run_woocommerce_order_action' ) );
		}
	}

	/**
	 * @param array $actions
	 * @return array
	 */
	public function add_woocommerce_order_action( $actions ) {
		global $theorder;
		$tracked = !! get_post_meta( $theorder->id, '_mc4wp_ecommerce_tracked', true );
		$actions['mailchimp_ecommerce'] = $tracked ? __( 'Delete from MailChimp', 'mailchimp-for-wp' ) : __( 'Add to MailChimp', 'mailchimp-for-wp' );
		return $actions;
	}

	/**
	 * @param WC_Order $order
	 */
	public function run_woocommerce_order_action( $order ) {
		$tracked = !! get_post_meta( $order->id, '_mc4wp_ecommerce_tracked', true );
		$ecommerce = $this->get_ecommerce();

		if( $tracked ) {
			$ecommerce->delete_order( $order->id );
		} else {
			$ecommerce->add_woocommerce_order( $order->id );
		}
	}

	/**
	 * Registers menu page without a parent item.
	 */
	public function register_hidden_pages() {
		add_submenu_page( null, 'Record orders', '', 'manage_options', 'mailchimp-for-wp-ecommerce', array( $this, 'show_track_orders_page' ) );
	}

	/**
	 * Register menu pages
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function add_menu_item( array $items ) {

		$item = array(
			'title' => __( 'MailChimp E-Commerce', 'mc4wp-ecommerce' ),
			'text' => __( 'E-Commerce', 'mc4wp-ecommerce' ),
			'slug' => 'ecommerce',
			'callback' => array( $this, 'show_settings_page' )
		);


		$items[] = $item;

		return $items;
	}

	/**
	 * @param array $opts
	 */
	public function show_settings_page( $opts ) {
		require __DIR__ . '/views/settings.php';
	}

	/**
	 * Shows the wizard
	 */
	public function show_track_orders_page() {
		$helper = new MC4WP_Ecommerce_Helper();
		$untracked_order_count = $helper->get_untracked_order_count();

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'mc4wp-ecommerce-admin', $this->plugin->url( '/assets/js/admin' . $suffix . '.js' ), array(), $this->plugin->version(), true );
		wp_localize_script( 'mc4wp-ecommerce-admin', 'mc4wp_ecommerce', array(
			'untracked_order_count' => $untracked_order_count
		));

		require __DIR__ . '/views/track-previous-orders.php';
	}

	/**
	 * Starts adding untracked orders to MailChimp. This can take a while..
	 */
	public function add_untracked_orders() {

		// don't lock session (because we poll for progress)
		@session_write_close();

		// no time limit
		@set_time_limit(0);

		$helper = new MC4WP_Ecommerce_Helper();
		$ecommerce = $this->get_ecommerce();

		$offset = isset( $_REQUEST['offset'] ) ? (int) $_REQUEST['offset'] : 0;
		$limit = isset( $_REQUEST['limit'] ) ? (int) $_REQUEST['limit'] : 100;

        // clear tracking cookies for now.
        unset( $_COOKIE['mc_cid'] );
        unset( $_COOKIE['mc_eid'] );

		// loop through order id's
		$order_ids = $helper->get_untracked_order_ids( $offset, $limit );
		foreach( $order_ids as $order_id ) {
			$success = $ecommerce->add_order( $order_id );
		}

		// respond to request
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->get_untracked_orders_count();
		}
	}

	/**
	 * Gets the number of untracked orders and outputs it
	 */
	public function get_untracked_orders_count() {
		@session_write_close();
		$helper = new MC4WP_Ecommerce_Helper();
		$count = $helper->get_untracked_order_count();
		echo (string) $count;
		exit;
	}

	/**
	 * This asks the user to record previous orders after eCommerce360 tracking was enabled
	 *
	 * @param array $settings
	 * @param array $old_settings
	 */
	public function maybe_show_notice( $settings, $old_settings ) {
		if( $settings['ecommerce'] && ! $old_settings['ecommerce'] ) {
			$text = __( 'You just enabled eCommerce360 - do you want to <a href="%s">add all past orders to MailChimp</a>?', 'mc4wp-ecommerce' );
			$this->get_admin_messages()->flash( sprintf( $text, admin_url( 'admin.php?page=mailchimp-for-wp-ecommerce' ) ) );
		}
	}

    /**
     * Disable old e-commerce v2, enable v3 & go to wizard.
     */
	public function enable_ecommerce_v3() {
        // disable old option
        $options = get_option( 'mc4wp', array() );
        $options['ecommerce'] = 0;
        update_option( 'mc4wp', $options );

        // remove old meta key from all orders
        delete_post_meta_by_key( '_mc4wp_ecommerce_tracked' );

        // enable new option
        update_option( 'mc4wp_ecommerce', array( 'enable_object_tracking' => 1 ) );

        // redirect to wizard
        wp_redirect( admin_url('admin.php?page=mailchimp-for-wp-ecommerce' ) );
        exit;
    }

    /**
     * Show notice asking user to switch e-commerce over to API v3
     */
	public function show_api_v3_notice() {
	    global $pagenow;

	    // only show on mailchimp for wordpress pages & plugins overview page
	    if( ( ! isset( $_GET['page'] ) || stripos( $_GET['page'], 'mailchimp-for-wp' ) === false ) && $pagenow !== 'plugins.php' ) {
	        return;
        }

	    // only show if running core v4.0 (for API v3 classes)
	    if( version_compare( MC4WP_VERSION, '4.0', '<' ) ) {
	        return;
        }

        $settings = mc4wp_get_options();

        // this means switch was made already or not using ecommerce
        if( empty( $settings['ecommerce'] ) ) {
            return;
        }

        if( class_exists( 'WooCommerce' ) ) {
            echo '<div class="notice notice-warning">';
            echo '<p><strong>MailChimp for WordPress: Upgrade your e-commerce integration</strong></p>';
            echo '<p>You are on MailChimp\'s older API for your e-commerce integration. Would you like to switch to the new API now?</p>';
            echo '<p>' . sprintf( 'Please <a href="%s">read this for more information about the new & improved e-commerce integration</a>.', 'https://mc4wp.com/kb/upgrading-ecommerce-api-v3/') . '</p>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="_mc4wp_action" value="enable_ecommerce_v3" />';
            echo '<p><input type="submit" class="button" value="Switch to e-commerce API v3" /></p>';
            echo '</form>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-warning">';
            echo '<p><strong>MailChimp for WordPress: Important news about your e-commerce integration</strong></p>';
            echo '<p>You are currently still using MailChimp\'s older API for your e-commerce integration. This API will close down at the end of 2016.</p>';
            echo '<p>';
            echo 'Because we are focusing our efforts on WooCommerce, we are not developing an Easy Digital Downloads integration for MailChimp\'s newer API. ';
            echo sprintf( 'You can keep using the current integration for now, but please <a href="%s">switch to EDD\'s own MailChimp integration</a> before January 1, 2017.', 'https://easydigitaldownloads.com/downloads/mail-chimp/?ref=4676&campaign=mc4wp-api-v3' );
            echo '</p>';
            echo '</div>';
        }
    }

	/**
	 * @return MC4WP_Ecommerce
	 */
	private function get_ecommerce() {
		return mc4wp('ecommerce');
	}

	/**
	 * @return MC4WP_Admin_Messages
	 */
	private function get_admin_messages() {
		return mc4wp('admin.messages');
	}
}