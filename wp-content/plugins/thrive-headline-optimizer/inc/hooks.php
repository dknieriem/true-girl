<?php
/**
 * Use this file to declare hooks - actions and filters
 */

/**
 * Load Dashboard version check
 */
add_action( 'plugins_loaded', 'tho_load_dash_version' );

if ( is_admin() ) {
	add_filter( 'tve_dash_installed_products', 'tho_add_to_dashboard' );
} else {
	/*
	 * Enqueue frontend scripts
	 */
	add_action( 'wp_enqueue_scripts', 'tho_enqueue_scripts' );
	add_action( 'wp_print_footer_scripts', 'tho_print_footer_scripts' );

	/**
	 * Mark the end of content with an element so we can detect it for scroll
	 */
	add_filter( 'the_content', 'tho_filter_end_content' );
}

/**
 * Load text domain used for translations
 */
add_action( 'init', 'tho_load_plugin_textdomain' );

/**
 * check for update at init because dashboard loads the required classes at plugins_loaded
 */
add_action( 'init', 'tho_update_checker' );

/**
 * Load REST Routes
 */
add_action( 'rest_api_init', 'tho_create_initial_rest_routes' );

/**
 * Add triggers for logging impressions and engagements
 */
add_filter( 'the_title', 'tho_add_title_variations', PHP_INT_MAX, 2 );
add_filter( 'woocommerce_product_title', 'tho_add_woocommerce_title_variation', PHP_INT_MAX, 2 );

/**
 * filter woocommerce breadcrumbs so we can modify the title
 */
add_filter( 'woocommerce_breadcrumb_defaults', 'tho_change_woo_breadcrumb' );

/**
 * change woocommerce breadcrumb template at a low priority so it can be overwritten by others
 */
add_filter( 'woocommerce_locate_template', 'tho_woocommerce_locate_template', 1, 3 );

/**
 * filter products title with the_title
 */
add_filter( 'add_to_cart_fragments', 'tho_filter_product_title', 1, 1 );

/**
 * Hooks the inconclusive tests action on wordpress initialization
 */
add_action( 'admin_init', 'tho_get_inconclusive_tests' );

/**
 * Hooks the notfication manager trigger types
 */
add_filter( 'td_nm_trigger_types', 'tho_filter_nm_trigger_types' );
