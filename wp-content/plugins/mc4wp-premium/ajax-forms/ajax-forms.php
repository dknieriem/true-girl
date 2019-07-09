<?php

defined('ABSPATH') or exit;

// main functionality
require_once dirname(__FILE__) . '/includes/class-ajax-forms.php';
$ajax_forms = new MC4WP_AJAX_Forms( __FILE__ );
$ajax_forms->add_hooks();

if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	require_once dirname(__FILE__) . '/includes/class-admin.php';
	$admin = new MC4WP_AJAX_Forms_Admin();
	$admin->add_hooks();
}
