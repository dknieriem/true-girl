<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/12/2016
 * Time: 2:34 PM
 */

require_once dirname( __FILE__ ) . '/functions.php';

/**
 * add admin page
 */
add_filter( 'tve_dash_admin_product_menu', 'tvo_admin_menu' );
/**
 * enqueue admin scripts
 */
add_action( 'admin_enqueue_scripts', 'tvo_admin_enqueue_scripts' );

/**
 * add ovation product to dashboard
 */
add_filter( 'tve_dash_installed_products', 'tvo_add_to_dashboard' );

/**
 *  Hooks an action to the display testimonial function
 */
add_action( 'wp_ajax_testimonial_iframe', 'tvo_display_testimonial_iframe' );

/**
 * Add email notice for comments_testimonial creation
 */
add_action( 'admin_notices', 'tvo_notice' );
