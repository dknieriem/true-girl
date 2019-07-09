<?php

defined( 'ABSPATH' ) or exit;

/**
 * @since 3.3
 * @return array
 */
function mc4wp_ecommerce_get_settings() {
    $options = get_option( 'mc4wp_ecommerce', array() );
    $options = is_array( $options ) ? $options : array();
    $defaults = array(
        'enable_object_tracking' => 0,
        'enable_cart_tracking' => 0,
        'load_mcjs_script' => 0,
        'include_all_order_statuses' => 0,
        'store_id' => '',
        'store' => array(
            'list_id' => '',
            'name' => get_bloginfo( 'name' ),
            'currency_code' => get_woocommerce_currency(),
            'is_syncing' => 1,
        ),
        'mcjs_url' => '',
        'last_updated' => null,
    );

    // merge saved options with defaults
    $options = array_merge( $defaults, $options );
    $options['store'] = array_merge( $defaults['store'], $options['store'] );

     // fill store_id dynamically if it's empty
    if( empty( $options['store_id'] ) ) {
        $options['store_id'] = (string) md5( get_option( 'siteurl', '' ) );
    }

    // backwards compat for moved mcjs_url prop
    if( empty( $options['mcjs_url'] ) && ! empty( $options['store']['mcjs_url'] ) ) {
        $options['mcjs_url'] = $options['store']['mcjs_url'];
        unset( $options['store']['mcjs_url'] );
    }

    /**
     * Filters the options array
     *
     * @param array $options
     */
    $options = apply_filters( 'mc4wp_ecommerce_options', $options );

    return $options;
}

/**
 * @since 3.3.2
 *
 * @param array $new_settings
 * @return array $settings
 */
function mc4wp_ecommerce_update_settings( array $new_settings ) {
    $old_settings = mc4wp_ecommerce_get_settings();
    $settings = array_replace_recursive( $old_settings, $new_settings );
    update_option( 'mc4wp_ecommerce', $settings );
    return $settings;
}

/**
 * Gets which order statuses should be stored in MailChimp.
 *
 * @private
 * @since 3.3
 * @return array
 */
function mc4wp_ecommerce_get_order_statuses() {
    $order_statuses = array( 'wc-completed', 'wc-processing' );

    // include non-completed orders when setting is enabled (for Order Notifications, mostly)
    $opts = mc4wp_ecommerce_get_settings();
    if( $opts['include_all_order_statuses'] ) {
        $order_statuses = array_merge( $order_statuses, array( 'wc-pending', 'wc-cancelled', 'wc-on-hold', 'wc-refunded', 'wc-failed') );
    }

    /**
     * Filters the order statuses to send to MailChimp
     *
     * @param array $order_statuses
     * @since 3.3
     */
    $order_statuses = apply_filters( 'mc4wp_ecommerce_order_statuses', $order_statuses );

    /**
     * @deprecated Use mc4wp_ecommerce_order_statuses instead.
     * @ignore
     */
    $order_statuses = apply_filters( 'mc4wp_ecommerce360_order_statuses', $order_statuses );

    return $order_statuses;
}

/**
 * @param array $schedules
 * @return array
 */
function _mc4wp_ecommerce_cron_schedules( $schedules ) {
    $schedules['every5minutes'] = array(
        'interval' => 60 * 5,
        'display' => __( 'Every 5 minutes', 'mc4wp-ecommerce' ),
    );
    $schedules['every3minutes'] = array(
        'interval' => 60 * 3,
        'display' => __( 'Every 3 minutes', 'mc4wp-ecommerce' ),
	);
	$schedules['every-minute'] = array(
		'interval' => 60,
		'display' => __( 'Every minute', 'mc4wp-ecommerce' ),
	);
    return $schedules;
}

/**
 * Schedule e-commerce events with WP Cron.
 */
function _mc4wp_ecommerce_schedule_events() {
    /**
    * Allows you to disable the WP Cron schedule for processing the queue.
    *
    * To be used when you process the queue over WP CLI using `wp mc4wp-ecommerce process-queue`
    */
    if( ! apply_filters( 'mc4wp_ecommerce_schedule_process_queue_event', true ) ) {
        return;
    }

    $expected_next = time() + 60;
    $event_name = 'mc4wp_ecommerce_process_queue';
    
    $actual_next = wp_next_scheduled( $event_name );

    if( ! $actual_next || $actual_next > $expected_next ) {
        wp_schedule_event( $expected_next, 'every-minute', $event_name );
    }
}
