<?php
/**
 * Created by PhpStorm.
 * User: sala
 * Date: 10-Dec-15
 * Time: 15:15
 */

/**
 * the priority here must be lower than the one set from thrive-dashboard/version.php
 */
add_action( 'after_setup_theme', 'thrive_load_dash_version', 1 );
/**
 * Save current theme dashboard version
 */
function thrive_load_dash_version() {
	$_dash_path      = get_template_directory() . '/thrive-dashboard';
	$_dash_file_path = $_dash_path . '/version.php';

	if ( is_file( $_dash_file_path ) ) {
		$version                                  = require_once( $_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = $_dash_path . '/thrive-dashboard.php';

		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $_dash_path . '/thrive-dashboard.php',
			'folder' => 'focusblog',
			'from'   => 'themes'
		);
	}
}

add_filter( 'tve_dash_installed_products', 'thrive_add_to_dashboard' );

add_filter( 'tve_dash_features', 'thrive_add_dashboard_features' );

/**
 * Add theme to the dashboard
 *
 * @param $items
 *
 * @return array
 */
function thrive_add_to_dashboard( $items ) {
	include_once 'Theme_Product.php';

	$theme = new Theme_Product();

	$items[] = $theme;

	return $items;
}

/**
 * makes sure that all the sections needed by the theme are displayed under "THRIVE FEATURES" section
 *
 * @param array $features
 *
 * @return array
 */
function thrive_add_dashboard_features( $features = array() ) {
	$features['font_manager'] = true;

	return $features;
}

/**
 * Add menu pages but hide them
 *
 * @param array $menus
 *
 * @return array
 */
function thrive_add_admin_pages( $menus = array() ) {

	$menus['thrive_theme_admin_page_templates'] = array(
		'parent_slug' => null,
		'page_title'  => null,
		'menu_title'  => null,
		'capability'  => 'edit_theme_options',
		'menu_slug'   => 'thrive_admin_page_templates',
		'function'    => 'thrive_page_templates_admin_page',
	);
	$menus['thrive_theme_license_validation']   = array(
		'parent_slug' => null,
		'page_title'  => null,
		'menu_title'  => null,
		'capability'  => 'edit_theme_options',
		'menu_slug'   => 'thrive_license_validation',
		'function'    => 'thrive_license_validation',
	);
	$menus['thrive_theme_admin_options']        = array(
		'parent_slug' => 'tve_dash_section',
		'page_title'  => __( 'Thrive Options', 'thrive' ),
		'menu_title'  => __( 'Theme Options', 'thrive' ),
		'capability'  => 'edit_theme_options',
		'menu_slug'   => 'thrive_admin_options',
		'function'    => 'thrive_theme_options_render_page',
	);

	return $menus;
}

add_filter( 'tve_dash_admin_product_menu', 'thrive_add_admin_pages' );

/**
 * Check license status
 * @return bool
 */
function thrive_check_license() {
	return TVE_Dash_Product_LicenseManager::getInstance()->itemActivated( TVE_Dash_Product_LicenseManager::TAG_FOCUS );
}

add_action( 'init', 'thrive_add_license_notice' );
/*
 * Display top warning if the theme has not activated.
 */
function thrive_add_license_notice() {
	if ( ! thrive_check_license() ) {
		add_action( 'admin_notices', 'thrive_admin_notice' );
	}
}
