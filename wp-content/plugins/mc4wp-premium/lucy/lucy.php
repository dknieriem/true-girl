<?php

defined( 'ABSPATH' ) or exit;

/**
 * Enqueue Lucy (in plugin KB search)
 *
 * @param string $suffix
 */
function mc4wp_lucy_enqueue( $suffix ) {

	$assets_url = plugins_url( '/assets', __FILE__ );

	// build array of helpful data to include in support emails
	// TODO: Add license key?
	$data = array(
		'My website: ' . home_url(),
		'PHP v' . PHP_VERSION,
		'WordPress v' . $GLOBALS['wp_version'],
		'MailChimp for WordPress v' . MC4WP_VERSION,
		'Premium Bundle v' .MC4WP_PREMIUM_VERSION
	);

	$data = array_map( 'urlencode', $data );
	$data_string = implode( '%0A', $data ) . '%0A%0A';

	// script
	wp_enqueue_script( 'mc4wp-lucy', $assets_url . '/js/script'. $suffix .'.js', array( 'mc4wp-admin' ), MC4WP_PREMIUM_VERSION, true );
	wp_localize_script( 'mc4wp-lucy', 'lucy_config', array(
			'email_link' => sprintf( 'mailto:%s?body=%s', 'support@mc4wp.com', $data_string )
		)
	);

	// styles
	wp_enqueue_style( 'mc4wp-lucy', $assets_url . '/css/styles.css', array(), MC4WP_PREMIUM_VERSION );
}

add_action( 'mc4wp_admin_enqueue_assets', 'mc4wp_lucy_enqueue' );
