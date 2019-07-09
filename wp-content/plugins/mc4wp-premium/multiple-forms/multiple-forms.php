<?php

defined( 'ABSPATH' ) or exit;

$widget_enhancements = new MC4WP_Form_Widget_Enhancements();
$widget_enhancements->add_hooks();

if( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	$admin = new MC4WP_Multiple_Forms_Admin( __FILE__ );
	$admin->add_hooks();
}
