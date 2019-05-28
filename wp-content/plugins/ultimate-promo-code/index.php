<?php

	/*

		Plugin Name: Ultimate Promo Code

		Plugin URI: http://cmshelplive.com/

		Description: Ultimate Promo Code allows creating multiple promo codes for downloading files from your WordPress site. You can limit the total number of uses per code, start and expiry dates and track download numbers with time tracking. Code can be pasted on any page using following short code format: [UlitmatePromo id="1" promolabel="Enter your promo code" emaillabel="Enter your email"]. promolabel and emaillabel are optional and can be used if you want to custom labels for both fields. If you want to keep default labels, just use the format [UlitmatePromo id="1"]

		Version: 1.1

		Author: Vincent Andrew

		Author URI: https://profiles.wordpress.org/cmshelplive/

		License: GPL2

	*/

ob_start();

register_activation_hook ( __FILE__, 'activate_ultimate_promo_code' );



function ultimate_promo_code_scripts() 
{
    wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style( 'calendar.css', plugin_dir_url(__FILE__) . 'calendar.css');
	wp_enqueue_style( 'chosen.css', plugin_dir_url(__FILE__) . 'chosen.css');
	wp_enqueue_script( 'chosen.jquery.js',  plugin_dir_url(__FILE__) . 'chosen.jquery.js');
	wp_enqueue_script( 'prism.js',  plugin_dir_url(__FILE__) . 'docsupport/prism.js' );

}

add_action( 'admin_init', 'ultimate_promo_code_scripts' );

function activate_ultimate_promo_code()
{
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;

	$ultimate_promocode = $wpdb->prefix. "ultimate_promo_code";

	$ultimate_promohits = $wpdb->prefix. "ultimate_promo_hits";

	$sqlcreate = "CREATE TABLE IF NOT EXISTS $ultimate_promocode(

			id int NOT NULL AUTO_INCREMENT,

			PRIMARY KEY(id),

			name varchar(255),

			limit_uses int,

			download_url varchar(255),

			email varchar(255),

			start_date DATETIME,

			end_date DATETIME,

			status int		

		)";

    dbDelta($sqlcreate);

	$sqlcreate = "CREATE TABLE IF NOT EXISTS $ultimate_promohits(

		   id int NOT NULL AUTO_INCREMENT,

		   PRIMARY KEY(id),

		  `promo_code` VARCHAR(255),

		  `name` VARCHAR(255),

		  `created_date` DATETIME,

		  `email` VARCHAR(255)	

		)";

		dbDelta($sqlcreate);

}

add_action('admin_menu', 'ultimate_promo_code_menu');

function ultimate_promo_code_menu()
{
	add_menu_page("Ultimate Promo Code","Ultimate Promo Code","manage_options","Ultimate_Promo_codes","Ultimate_Promo_codes",plugins_url('/icon.png', __FILE__));

	add_submenu_page("Ultimate_Promo_codes","Statistics","Statistics","manage_options","Ultimate_Promo_Code_hits","Ultimate_Promo_Code_hits");

	add_submenu_page("","New Promo Code","New Promo Code","manage_options","Ultimate_Promo_Code","Ultimate_Promo_Code");
	add_submenu_page("","export user list","export user list","manage_options","Ultimate_Promo_Hits_export","Ultimate_Promo_Hits_export");
}

function Ultimate_Promo_codes()
{

	include 'ultimate_promo_codes.php';

}

function Ultimate_Promo_Code()
{

	include 'ultimate_promo_code.php';

}

function Ultimate_Promo_Code_edit()
{

	echo 'promo code edit';	

}

function Ultimate_Promo_Code_hits()
{

	include 'ultimate_promo_code_hits.php';

}

function Ultimate_Promo_Hits_export()
{
	include 'ultimate_promo_hits_export.php';		
}


function Ultimate_promo_stats($code)
{

	global $wpdb;

	$table = $wpdb->prefix. "ultimate_promo_hits";

	$values = $code;

	$sql = "SELECT COUNT(*) FROM $table WHERE binary `promo_code` = %s";

	$ssql = $wpdb->prepare($sql, $values);

	$n = $wpdb->get_var($ssql);

	return $n;

}

function Ultimate_promo_hit($code, $name, $email) 
{

    global $wpdb;

    $tn = $wpdb->prefix . 'ultimate_promo_hits';

    $ts = date('Y-m-d H:i:s');
	
    $sql = "INSERT INTO $tn (promo_code, name, created_date, email)VALUES(%s, %s, %s, %s)";

    $ssql = $wpdb->prepare($sql, array($code, $name, $ts, $email));
	
    $res = $wpdb->query($ssql);
}

function Ultimate_promo_validEmail($email) {
    $val = filter_var($email, FILTER_VALIDATE_EMAIL);
    return($val !== false);

}

function Ultimate_promo_form($atts)
{ 
	 include 'ultimate_promo_form.php';
}

add_shortcode( 'UlitmatePromo', 'Ultimate_promo_form' );

add_action('wp_ajax_check_code', 'UPC_check_code');

add_action('wp_ajax_nopriv_check_code', 'UPC_check_code');

function UPC_check_code()
{
	global $wpdb;
	include('check_code.php');die;
}
?>