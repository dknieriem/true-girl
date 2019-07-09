<?php

defined( 'ABSPATH' ) or exit;

$factory = new MC4WP_Form_Notification_Factory();
$factory->add_hooks();

if( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	$admin = new MC4WP_Form_Notifications_Admin( __FILE__ );
	$admin->add_hooks();
}
