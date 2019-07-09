<?php

defined('ABSPATH') or exit;

// make sure WooCommerce is installed & activated.
if (!class_exists('WooCommerce')) {
	return;
}

define('MC4WP_ECOMMERCE_VERSION', '1.0');
require_once __DIR__ . '/includes/class-ecommerce.php';
require_once __DIR__ . '/includes/class-helper.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/class-worker.php';
require_once __DIR__ . '/includes/class-object-observer.php';
require_once __DIR__ . '/includes/class-cart-observer.php';


// load settings
$settings = mc4wp_ecommerce_get_settings();

// register ecommerce & tracker in service container (for lazy loading)
$mc4wp = mc4wp();
$mc4wp['ecommerce.options'] = $settings;
$mc4wp['ecommerce.tracker'] = function () use ($settings) {
	require_once __DIR__ . '/includes/class-tracker.php';
	return new MC4WP_Ecommerce_Tracker(__FILE__, $settings);
};
$mc4wp['ecommerce.transformer'] = function () use ($mc4wp, $settings) {
	require_once __DIR__ . '/includes/interface-transformer.php';

	if (!defined('WOOCOMMERCE_VERSION') || version_compare(WOOCOMMERCE_VERSION, '3.0', '<')) {
		require_once __DIR__ . '/includes/class-transformer-wc2.php';
		return new MC4WP_Ecommerce_Object_Transformer_WC2($settings, $mc4wp['ecommerce.tracker']);
	}

	require_once __DIR__ . '/includes/class-transformer-wc3.php';
	return new MC4WP_Ecommerce_Object_Transformer_WC3($settings, $mc4wp['ecommerce.tracker']);
};
$mc4wp['ecommerce'] = function () use ($mc4wp, $settings) {
	return new MC4WP_Ecommerce( $settings['store_id'], $mc4wp['ecommerce.transformer']);
};

$mc4wp['ecommerce.queue'] = function () {
	return new MC4WP_Queue('mc4wp_ecommerce_queue');
};

$mc4wp['ecommerce.worker'] = function() use($mc4wp) {
	return new MC4WP_Ecommerce_Worker($mc4wp['ecommerce'], $mc4wp['ecommerce.queue']);
};

// enable queue & worker if e-commerce is enabled in settings
if ($settings['enable_object_tracking']) {
	add_filter('cron_schedules', '_mc4wp_ecommerce_cron_schedules');

	/** @var MC4WP_Ecommerce_Tracker */
	$mc4wp['ecommerce.tracker']->hook();

	// setup worker (processes items from queue)
	$mc4wp['ecommerce.worker']->hook();

	// setup object observer  (adds jobs to queue)
	$object_observer = new MC4WP_Ecommerce_Object_Observer($mc4wp['ecommerce'], $mc4wp['ecommerce.queue']);
	$object_observer->hook();

	// setup cart observer (adds jobs to queue)
	if ($settings['enable_cart_tracking']) {
		$cart_observer = new MC4WP_Ecommerce_Cart_Observer(__FILE__, $mc4wp['ecommerce'], $mc4wp['ecommerce.queue'], $mc4wp['ecommerce.transformer'] );
		$cart_observer->hook();
	}
}

// setup admin stuffs?
if (is_admin()) {
	if (defined('DOING_AJAX') && DOING_AJAX) {
		require_once __DIR__ . '/includes/class-ajax.php';
		$ajax = new MC4WP_Ecommerce_Admin_Ajax();
		$ajax->hook();
	} else {
		require_once __DIR__ . '/includes/class-admin.php';
		require_once __DIR__ . '/includes/class-object-count.php';

		$admin = new MC4WP_Ecommerce_Admin(__FILE__, $mc4wp['ecommerce.queue'], $settings);
		$admin->add_hooks();
	}
} 

// register command when running cli
if (defined('WP_CLI') && WP_CLI) {
	require_once __DIR__ . '/includes/class-command.php';
	WP_CLI::add_command('mc4wp-ecommerce', 'MC4WP_Ecommerce_Command');
}


