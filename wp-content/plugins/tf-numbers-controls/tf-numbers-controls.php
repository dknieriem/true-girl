<?php
/**
  * Plugin Name: TF Random Numbers Controls Addon
  * Plugin URI: http://themeflection.com/plug/number-counter-animation-wordpress-plugin/
  * Version: 1.2.2
  * Author: Aleksej Vukomanovic
  * Author URI: http://themeflection.com
  * Description: Random numbers plugin addon that includes counter speed selection and support for .(dot) separator (example: 10.000) and unlocks 4 more layouts.
  * Text Domain: TF
  * Domain Path: /languages
  * License: GPL
  */
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;

  if( ! defined('TF_CONTROLS_DIR') ) define('TF_CONTROLS_DIR', plugin_dir_url( __FILE__ )  );

  define( 'TF_CONTROLLER_NAME', 'Controller Addon' );

  require_once 'setup.php';

 function tf_controls_run() {
   if( class_exists( 'TF_Numbers' ) ) {
      add_action( 'wp_enqueue_scripts', 'tf_redeclare_scripts' );
      add_action( 'admin_enqueue_scripts', 'tf_admin_script' );
      add_action( 'tf_elements_reg_fields', 'tf_add_element_sub', 10, 2 );
      add_filter('tf_addon_options', 'tf_add_controls' );
      add_filter('tf_layouts', 'tf_more_layouts' );
      add_filter( 'tf_addon_styles', 'tf_apply_new_ops' );
      add_action( 'tf_license_row', 'tf_controller_license', 10, 2 );
    }
 }

 function tf_controller_updater() {

   $license_key = trim( get_option( 'tf_controller_license_key' ) );

     if ( class_exists('TF_Numbers_Addons_Updater') ) {
         $edd_updater = new TF_Numbers_Addons_Updater(TF_STORE_URL, __FILE__, array(
                 'version' => '1.2.2',
                 'license' => $license_key,
                 'item_name' => TF_CONTROLLER_NAME,
                 'author' => 'Aleksej Vukomanovic'
             )
         );
     }

 }

 add_action( 'plugins_loaded', 'tf_controls_run' );
 add_action( 'admin_init', 'tf_controller_updater', 0 );
add_action('admin_init', 'tf_numbers_bundle_controls_init' );

function tf_numbers_bundle_controls_init() {
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
