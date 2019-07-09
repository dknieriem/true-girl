<?php

namespace MC4WP\Premium\AFTP;

$settings = mc4wp_get_options();

require __DIR__ . '/includes/class-plugin.php';
$plugin = new Plugin( $settings );
$plugin->hook();

if( is_admin() ) {
	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	} else {
		require __DIR__ . '/includes/class-admin.php';
		$admin = new Admin();
		$admin->hook();
	}
}
