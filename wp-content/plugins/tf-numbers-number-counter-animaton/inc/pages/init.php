<?php
/*
 Custom dashboard that will be opened on install, or update
*/

class TF_Pages {
 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
 
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() { 
	
		add_action('admin_menu', array( $this,'tf_register_menu') );
		add_action('activated_plugin', array( &$this,'tf_redirect') );
		$active = get_option('tf-activated');
		if( !isset( $active ) || '' === $active ) {
  			add_action( 'activated_plugin', array( $this,'tf_redirect' ) );
  			update_option('tf-activated', 'active');
		}
 
	} // end constructor
 
	
	function tf_register_menu() {
		add_submenu_page( 'edit.php?post_type=tf_stats', 'About', 'About', 'read', 'tf-dashboard', array( $this,'tf_dashboard') );
		add_submenu_page( 'edit.php?post_type=tf_stats', 'Addons', 'Addons', 'read', 'tf-addons', array( $this,'tf_addons') );
	}

	 function tf_redirect($plugin) {
		 if( $plugin == TF_NUMBERS_BASE ) {
			 exit(wp_redirect(admin_url('edit.php?post_type=tf_stats&page=tf-dashboard')));
		 }
	 }
	
	function tf_dashboard() {
		include_once( 'dashboard.php'  );
	}

	function tf_addons() {
		include_once( 'addons.php'  );
	}

}