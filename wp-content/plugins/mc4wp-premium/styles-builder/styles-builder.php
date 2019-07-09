<?php

defined('ABSPATH') or exit;

if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	$admin = new MC4WP_Styles_Builder_Admin(__FILE__);
	$admin->add_hooks();
}

$public = new MC4WP_Styles_Builder_Public(__FILE__);
$public->add_hooks();
