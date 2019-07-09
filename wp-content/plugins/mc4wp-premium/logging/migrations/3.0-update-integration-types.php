<?php

defined( 'ABSPATH' ) or exit;

global $wpdb;
$table_name = $wpdb->prefix . 'mc4wp_log';

$changes = array(
	'comment_form'          => 'wp-comment-form',
	'comment'               => 'wp-comment-form',
	'registration'          => 'wp-registration-form',
	'registration_form'     => 'wp-registration-form',
	'contact_form_7'        => 'contact-form-7',
	'cf7'                   => 'cf7',
	'edd_checkout'          => 'easy-digital-downloads',
	'woocommerce_checkout'  => 'woocommerce',
	'events_manager'        => 'events-manager',
	'buddypress_form'       => 'buddypress',
	'general'               => 'custom',
	'other_form'            => 'custom',
	'other'                 => 'custom',
	'form'                  => 'mc4wp-form',
	'sign-up-form'          => 'mc4wp-form',
	'mailchimp-top-bar'     => 'mc4wp-top-bar'
);

foreach( $changes as $old => $new ) {
	$wpdb->query( "UPDATE `{$table_name}` SET `type` = '{$new}' WHERE `type` = '{$old}'" );
}

