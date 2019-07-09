<?php

defined( 'ABSPATH' ) or exit;

$custom_color_theme = new MC4WP_Custom_Color_Theme( __FILE__ );
$custom_color_theme->add_hooks();

if( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	$custom_color_theme_admin = new MC4WP_Custom_Color_Theme_Admin( __FILE__ );
	$custom_color_theme_admin->add_hooks();
}
