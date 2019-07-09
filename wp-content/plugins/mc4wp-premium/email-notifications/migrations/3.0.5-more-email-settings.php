<?php

defined( 'ABSPATH' ) or exit;

// find forms
$posts = get_posts(
	array(
		'post_type' => 'mc4wp-form',
		'post_status' => 'publish',
		'numberposts' => -1
	)
);

// loop through forms
foreach( $posts as $post ) {

	// get form options from post meta directly
	$options = (array) get_post_meta( $post->ID, '_mc4wp_settings', true );

	$email_notification_options = array();

	// transfer email settings to new format
	if( isset( $options['send_email_copy'] ) ) {
		$email_notification_options['enabled'] = $options['send_email_copy'];
		unset( $options['send_email_copy'] );
	}

	if( ! empty( $options['email_copy_receiver'] ) ) {
		$email_notification_options['recipients'] = $options['email_copy_receiver'];
		unset( $options['email_copy_receiver'] );
	}

	// push into options array
	$options['email_notification'] = $email_notification_options;

	// update post meta
	update_post_meta( $post->ID, '_mc4wp_settings', $options );
}

