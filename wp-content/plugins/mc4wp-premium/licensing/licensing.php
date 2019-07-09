<?php

if( is_admin() || (  defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	$api_url = 'https://account.mc4wp.com/api/v2';
	$plugin_slug = 'mc4wp-premium';
	$plugin_file = MC4WP_PREMIUM_PLUGIN_FILE;
	$plugin_version = MC4WP_PREMIUM_VERSION;

	$admin = new MC4WP\Licensing\Admin( $plugin_slug, $plugin_file, $plugin_version, $api_url );
	$admin->add_hooks();
}


