<?php
/*
 * Plugin Name: Facebook Events Importer PRO
 * Plugin URI: http://wpfbevents.com/
 * Description: Import Facebook events like a Pro. 
 * Version: Pro
 * Author: <a href="http://volk.io">Volk</a>
 * Author URI: http://volk.io/
 * License: GPL2
 /*  Copyright 2015  Volk  (email : media@volk.io)

    This program is free software; You can modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

 register_activation_hook(__FILE__,'fbe_pro_install'); 
 register_deactivation_hook( __FILE__, 'fbe_pro_remove' );


 function fbe_pro_install() {
 update_option('facebook_events_pro_version','Active');
 update_option('facebook_events_pro_deal','remove');
    global $wpdb;

    $the_wpfbevents_page_title = 'Facebook Events';
    $the_wpfbevents_page_name = 'facebook-events';

    // the menu entry...
    delete_option("facebook_events_pro_page_title");
    add_option("facebook_events_pro_page_title", $the_wpfbevents_page_title, '', 'yes');
    // the slug...
    delete_option("facebook_events_pro_page_name");
    add_option("facebook_events_pro_page_name", $the_wpfbevents_page_name, '', 'yes');
    // the id...
    delete_option("fbe_pro_page_id");
    add_option("fbe_pro_page_id", '0', '', 'yes');

    $the_wpfbevents_page = get_page_by_title( $the_wpfbevents_page_title );

    if ( ! $the_wpfbevents_page ) {

        // Create post object
        $event_page = array(
        'post_title' => $the_wpfbevents_page_title,
        'post_content' => "Facebook Events",
        'post_status' =>'publish',
        'post_type' => 'page',
        'page_template'  => ''
        );

        $the_wpfbevents_page_id = wp_insert_post( $event_page);
    }
    else {

        $the_wpfbevents_page_id = $the_wpfbevents_page->ID;
        $the_wpfbevents_page->post_status = 'publish';
        $the_wpfbevents_page_id = wp_update_post( $the_wpfbevents_page );

    }

    delete_option( 'fbe_pro_page_id' );
    add_option( 'fbe_pro_page_id', $the_wpfbevents_page_id );

}

   function fbe_pro_remove() {
    update_option('facebook_events_pro_deal','add');
    update_option('pro_msg_shown','no');
    update_option('facebook_events_pro_version','');
    global $wpdb;

    $the_wpfbevents_page_title = get_option( "facebook_events_pro_page_title" );
    $the_wpfbevents_page_name = get_option( "facebook_events_pro_page_name" );

    $the_wpfbevents_page_id = get_option( 'fbe_pro_page_id' );
    if( $the_wpfbevents_page_id ) {

        wp_delete_post( $the_wpfbevents_page_id,true ); 

    }


    delete_option("facebook_events_pro_page_title");
    delete_option("facebook_events_pro_page_name");
    delete_option("fbe_pro_page_id");

}


  add_action( 'admin_notices', 'fbe_pro_admin_notice' ); 
  function fbe_pro_admin_notice() {
    
            $fbeFree = get_option("facebook_events_free");  if($fbeFree == "installed"){?>

                <div class="updated" style="padding:20px">
                   <b>YOU'RE AWESOME!</b></a> Thank you for installing <b>Facebook Events Importer PRO</b> &mdash; <i>enjoy!</i>
                </div>
             
            <?php update_option('pro_msg_shown','yes');
            }else{
            ?>
            <div class="error" style="padding:30px">
               <b>Hey!</b></a> You need to have <b>Facebook Events Importer</b> installed to use <b>PRO features</b>. No big deal, it's free, <a href="http://wpfbevents.com/">get it here</a>.
            </div>
            <?php
            update_option('pro_msg_shown','no');
        }
    }   
function hide_fbepro_update_notice() {
 if (get_option('pro_msg_shown') == "yes"){  remove_action( 'admin_notices', 'fbe_pro_admin_notice' ); } 
}
add_action( 'admin_head', 'hide_fbepro_update_notice');

?>