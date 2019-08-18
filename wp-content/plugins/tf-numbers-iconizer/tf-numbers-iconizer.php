<?php
/**
  * Plugin Name: TF Random Numbers Iconizer Addon
  * Plugin URI: http://themeflection.com/plug/number-counter-animation-wordpress-plugin/
  * Version: 1.0.2
  * Author: Aleksej Vukomanovic
  * Author URI: http://themeflection.com
  * Description: Random numbers plugin addon that let's you include your images and set cusotm width and height of your images and also includes icons search bar that will let you to search trhought default icons and your images
  * Text Domain: TF
  * Domain Path: /languages
  * License: GPL
  */
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;

  define( 'TF_ICONIZER_NAME', 'iconizer tf numbers addon' );

  //define plugin directory path
  if( !defined( 'TF_NUMB_ICONIZER_DIR' ) )
    define( 'TF_NUMB_ICONIZER_DIR', plugin_dir_url( __FILE__ ) );
 
  require_once 'setup.php';

 function tf_iconizer_run() {
   if( class_exists( 'TF_Numbers' ) ) {
      add_filter( 'tf_custom_icons', 'tf_brdjokal' );
      add_filter( 'tf_icons_tabs', 'tf_custom_tabs' );
      add_filter('tf_numbers_icon_search', 'tf_add_search' );
      add_filter('tf_add_options', 'tf_icons_ops' );
      add_filter( 'tf_custom_styles', 'tf_apply_image_ops' );  
      add_action( 'tf_license_row', 'tf_iconizer_license', 10, 2 );   
    }
 }

 function tf_iconizer_updater() {
 
   $license_key = trim( get_option( 'tf_iconizer_license_key' ) );

     if ( class_exists('TF_Numbers_Addons_Updater') ) {
         $edd_updater = new TF_Numbers_Addons_Updater(TF_STORE_URL, __FILE__, array(
                 'version' => '1.0.2',
                 'license' => $license_key,
                 'item_name' => TF_ICONIZER_NAME,
                 'author' => 'Aleksej Vukomanovic'
             )
         );
     }
 
 }

 add_action( 'plugins_loaded', 'tf_iconizer_run' );
 add_action( 'admin_init', 'tf_iconizer_updater', 0 );
add_action('admin_init', 'tf_numbers_bundle_iconizer_init' );

function tf_numbers_bundle_iconizer_init() {
    if( !is_tf_numbers_active() ) {

        add_action( 'admin_notices', 'tf_numbers_admin_notice__error' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        };
    }
}

if( ! function_exists('is_tf_numbers_active') ) {
    function is_tf_numbers_active() {
        return is_plugin_active('tf-numbers-number-counter-animaton/tf-random_numbers.php');
    }
}

if( ! function_exists('tf_numbers_admin_notice__error') ) {
    function tf_numbers_admin_notice__error()  {
        $class = 'notice notice-error';
        $message = __('You Need to have base TF Numbers plugin active before attempting to activate TF Numbers addon.', 'rolo-slider-lc');

        printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
    }
}