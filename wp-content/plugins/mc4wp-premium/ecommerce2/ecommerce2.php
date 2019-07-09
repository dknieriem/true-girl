<?php

defined( 'ABSPATH' ) or exit;

// Check dependencies (either WooCommerce or Easy Digital Downloads)
if( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
    return;
}

// load commonly used classes
require_once __DIR__ . '/includes/class-ecommerce.php';
require_once __DIR__ . '/includes/class-helper.php';
require_once __DIR__ . '/includes/class-tracker.php';
require_once __DIR__ . '/includes/class-worker.php';

// register setting
add_filter( 'mc4wp_settings', function( $options ) {
	$defaults = array(
		'ecommerce' => 0
	);

	return array_merge( $defaults, $options );
});

// setup objects
$plugin = new MC4WP_Plugin( __FILE__, MC4WP_PREMIUM_VERSION );
$opts = mc4wp_get_options();
$enabled = $opts['ecommerce'];

// setup admin stuffs?
if( is_admin() ) {
	require_once __DIR__ . '/includes/class-admin.php';
	$admin = new MC4WP_Ecommerce_Admin( $plugin, $enabled );
	$admin->add_hooks();
}

// are we enabled?

if( $enabled ) {

	// setup tracker
	$tracker = new MC4WP_Ecommerce_Tracker();
	$tracker->hook();

	// setup ecommerce instance
	$mc4wp              = mc4wp();
	$mc4wp['ecommerce'] = $ecommerce = new MC4WP_Ecommerce( $tracker );

	// setup queue
	$queue = new MC4WP_Queue( 'mc4wp_ecommerce_queue' );

	// setup worker (adds items to queue)
	$worker = new MC4WP_Ecommerce_Worker( $queue, $ecommerce );
	$worker->hook();

	// put in work when doing cron, work hard!
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		// don't work before "init", WooCommerce can't handle that.
		add_action( 'init', array( $worker, 'work' ) );
	}

	// register command when running cli
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once __DIR__ . '/includes/class-command.php';
		WP_CLI::add_command( 'mc4wp-ecommerce', 'MC4WP_Ecommerce_Command' );
	}
}