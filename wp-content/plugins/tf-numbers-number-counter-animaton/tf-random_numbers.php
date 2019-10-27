<?php
/**
  * Plugin Name: TF Random Numbers
  * Plugin URI: http://themeflection.com/plug/number-counter-animation-wordpress-plugin/
  * Version: 1.7.2
  * Author: Themeflection
  * Author URI: http://themeflection.com
  * Description: Random numbers plugin for WordPress
  * Text Domain: tf-numbers
  * Domain Path: /languages
  * License: GPL
  */
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;

  //define version
  if( !defined( 'TF_NUMBERS_VERSION' ) )
    define( 'TF_NUMBERS_VERSION', '1.7.2' );
  //define translation string
  if( !defined( 'TF_NUMBERS_STRING' ) )
    define( 'TF_NUMBERS_STRING', 'tf_numbers' );
  //define plugin directory path
  if( !defined( 'TF_NUMBERS_DIR' ) )
    define( 'TF_NUMBERS_DIR', plugin_dir_url( __FILE__ ) );
  // define store URL
  if( !defined('TF_STORE_URL') )
    define( 'TF_STORE_URL', 'http://themeflection.com' );
  if( !defined('TF_NUMBERS_BASE') ) {
      define('TF_NUMBERS_BASE', plugin_basename( __FILE__ ) );
  }

  load_plugin_textdomain( 'tf_numbers', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

  if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/cmb2/init.php';
  } elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/CMB2/init.php';
  }

  add_filter('plugin_row_meta',  'tf_pluign_links', 10, 2);

  // source: http://goo.gl/iAiPPI
  function tf_pluign_links ($links, $file) {
     $base = plugin_basename(__FILE__);
     if ($file == $base) {
           $links[] = '<a href="http://themeflection.com/forums/">' .'<i class="dashicons dashicons-sos"></i>'. __('Support') . '</a>';
           $links[] = '<a href="http://themeflection.com/docs/tf-numbers/">' .'<i class="dashicons dashicons-book"></i>'. __('Documentation') . '</a>';
           $links[] = '<a href="admin.php?edit.php?post_type=tf_stats&page=tf-addons">' .'<i class="dashicons dashicons-archive"></i>'. __('Addons', 'tf-numbers') . '</a>';
     }
     return $links;
 }


  require_once 'options/init.php';
  require_once 'inc/sections.php';
  require_once 'inc/setup.php';
  require_once 'inc/pages/init.php';
  require_once 'inc/shortcode.php';
  require_once 'inc/license.php';
  require_once 'inc/update.php';

  //initialize
  TF_Numbers::init();
  if( is_admin() ) $pages = new TF_Pages();
  $shortcode = new TF_Numbers_Shortcode;
