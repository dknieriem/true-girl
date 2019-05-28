<?php

require_once dirname( __FILE__ ) . '/functions.php';


/**
 * plugin init
 */
add_action( 'init', 'tho_plugin_init' );

/**
 * add admin page
 */
add_filter( 'tve_dash_admin_product_menu', 'tho_admin_menu' );

/**
 * enqueue admin scripts
 */
add_action( 'admin_enqueue_scripts', 'tho_admin_enqueue_scripts' );

/**
 * Add error notice for duplicate variations
 */
add_action( 'admin_notices', 'tho_error_notice' );

/*
 * Load the add variation view to the post view
 */
add_action( 'edit_form_after_title', 'tho_variation_template' );

/*
 * Hooks the save post action to the variation functionality
 */
add_action( 'save_post', 'tho_save_post_variation', 10, 3 );

/*
 * Adds the headline meta box
 */
add_action( 'add_meta_boxes', 'tho_add_headline_meta_box' );

/**
 * add test icon in admin post/page list display
 */
add_filter( 'post_row_actions', 'tho_add_post_test_icon', 10, 2 );
add_filter( 'page_row_actions', 'tho_add_post_test_icon', 10, 2 );

/**
 * Filter post types for where to display the meta box
 */
add_filter( 'tho_custom_post_type_meta_box_filter', 'tho_add_post_types_meta_filter', 1, 1 );

/**
 * Prevent page from browser caching
 */
add_action( 'admin_head', 'tho_admin_cache_meta' );