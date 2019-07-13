<?php

if( !class_exists( 'TF_Numbers_Notice' ) )
{
  class TF_Numbers_Notice
  {

    protected static function hooks()
    {
         //enqueue front-end scripts and styles 
        if( is_admin() ) {
         add_action( 'init', array( 'TF_Numbers_Notice', 'check_promo' ) );
         add_action( 'admin_footer', array( 'TF_Numbers_Notice', 'tf_admin_js' ) );
         add_action( 'wp_ajax_tf_num_dissm', array( 'TF_Numbers_Notice', 'tf_num_dissm' ) );
       }
    }

    /**
    * Check for promotions
    *
    * @since 1.1.3
    */
    public static function check_promo()
    {
      if( ini_get('allow_url_fopen') ) 
      { 
        $dated = get_option('tf_promo');
        if( !$dated ) { 
           self::fetch_json();
        } else {
          $day = date('d'); 
          $pass = (int)$dated['expires']+7;
          if( (int)$dated['expires'] > 23 ) $pass = 30;
          if( 1 == $day && 30 == $pass || 10 == $day && 30 == $pass ) $pass = 1;
          if( '1' == $dated['expired'] && $day > $pass ) { 
             delete_option('tf_promo');
          } else if( $day > $dated['expires'] ) { 
             $new_val = array_merge( $dated, array('expired' => '1', 'hidden' => '1') );
             update_option('tf_promo', $new_val); 
          }

          self::notificationCheck('');
        }
      }
    }

    /**
    * Fetch json
    *
    * @since 1.1.3
    */
    public static function fetch_json()
    { 
      $json = file_get_contents('http://themeflection.com/plugins/wordpress/tf-numbers/promo.json');
      if( $json ) {
        $obj = json_decode($json,true); 
        self::notificationCheck($obj);
      }
    }

    /**
    * Check for notice
    *
    * @since  1.1.3
    */
    public static function notificationCheck($json) { 
        $cached = get_option('tf_promo');
        if($json) $data = $json;
        else $data = $cached;
        $expires = $data['expires'];
        $day = date('d'); 
        $month = date('m'); 
        $cache = array(
           'expires' => $expires,
           'content' => $data['content'],
           'month' => $data['month'],
           'hidden' => '0',
           'expired' => '0'
        );
        if( !$cached && $json ) update_option('tf_promo', $cache); 
        if( $cached && (int) $data['expires'] == $day && '1' == $cached['hidden'] && strpos($data['content'], 'Today is your last chance' ) == false ) {
           $cached['hidden'] = '0'; 
           $data['content'] = "<code>Today is your last chance.</code><strong>Grab this offer before it expires today</strong>. ".$data['content'];
             $new_val = array_merge( $cached, array('content' => "<code>Today is your last chance.</code><strong>Grab this offer before it expires today</strong>. ".$data['content']) );
             update_option('tf_promo', $new_val); 
        } 
        if( !$cached && $month == $data['month'] || $cached && '1' != $cached['hidden'] && isset($cached['expired']) && '0' == $cached['expired'] )
          self::notification($data['content']);
    }

    /**
    * Show Notice
    *
    * @since  1.1.3
    */
    public static function notification($msg) {
      if( $msg ) {
        $class = 'notice notice-info';
        $message = $msg;
    
        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
      }
    }
    
    /**
    * Notificcation Dismiss
    *
    * @since  1.1.3
    */
    public static function tf_num_dissm()
    {
       $data = get_option('tf_promo');
       $new_val = array_merge( $data, array('hidden' => '1') );
       update_option('tf_promo', $new_val); 
       
       wp_die();
    }
    
    /**
    * Admin JS
    *
    * @since  1.1.3
    */
    public static function tf_admin_js()
    {
      ?>
        <script type="text/javascript">
        jQuery('#tf-dism').on('click', dismissN);

        function dismissN() {
          var $this = jQuery(this);
          jQuery.ajax({
             url: <?php echo '"'.admin_url( 'admin-ajax.php' ).'"'; ?>,
             data: {
                'action' : 'tf_num_dissm'
             },
             success: function(data){
               $this.closest('.notice').remove(); 
             }
          });
        }
        </script>
      <?php
    }

     public static function init()
     {
      self::hooks();
     }
  }
}