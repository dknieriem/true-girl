<?php
/*
Plugin Name: Tour Dates
Plugin Description: Bandsintown's Tour Dates plugin makes it easy for artists to showcase their upcoming tour dates anywhere on their WordPress-powered blog or website. Easily display an automatically updated list of your tour dates to your fans using the widget, shortcode or template tag.
Plugin Author: Bandsintown.com
Author URI: http://www.bandsintown.com 
Version: 1.0.1
*/

//
// Bandsintown Plugin
//
class Bandsintown_JS_Plugin {
	
  function Bandsintown_JS_Plugin() {
	  if ( is_admin() ) {
      add_action('admin_menu', array($this, 'admin_menu'));
      add_action('admin_init', array($this, 'plugin_admin_init'));
		}
		else {
      add_action('wp_head', array($this, 'wp_head'));
		}
		
		// Shortcode
		add_shortcode('bandsintown_events', array($this, 'shortcode'));
		
		// Widget
		add_action('widgets_init', create_function('', 'return register_widget("Bandsintown_JS_Widget");'));
		
    $this->options = get_option('bitp_options');
	}
	
	// utility method for debugging
	function dump( $var ) { echo '<pre>'; var_dump($var); echo '</pre>'; }

	// Admin menu management.
	function admin_menu() {
	  add_options_page('Tour Dates', 'Tour Dates', 'manage_options', 'bandsintown-settings', array($this, 'settings'));
	}
	
	function wp_head() {
	  echo "\n<script type='text/javascript' src='http://www.bandsintown.com/javascripts/bit_widget.js'></script>\n";
	}
	
  // Manage plugin settings
  function settings() {
    ?>  
    <div>
    <div class="wrap" id="bandsintown_wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2>Tour Dates</h2>
    <form action="options.php" method="post">
    <?php 
      settings_fields('plugin_options');
      do_settings_sections('bitp');
    ?>
    <input name = "Submit" type="submit" class="button-primary" tabindex="1" value="<?php esc_attr_e('Save Settings'); ?>" />
    </form></div>
    <?php
  }
  // Register_settings
  function plugin_admin_init() { // whitelist options
    register_setting( 'plugin_options', 'bitp_options', array($this, 'options_validate'));
    add_settings_section('settings_section', 'General Settings', array($this, 'main_description'), 'bitp');
    add_settings_field('artist', '', array($this,'settings_inputs'), 'bitp', 'settings_section');
  }
  //Settings Description
  function main_description() {
  //description
  }
  //The Settings Inputs
  function settings_inputs() {
    $options = get_option('bitp_options');
    echo "<tr><p><label for='bitp_options[artist]'><strong>Artist</strong></label><br>";
    $artist = esc_attr( $options['artist'] );
    echo "<input id='bitp_options_artist' name='bitp_options[artist]' type='text' value='$artist' /><br>"; 
    echo "<p>
          <strong>Default CSS</strong>
          <br>
          You can use this section to review our default CSS rules and create your own custom rules
          to override the look and feel of the widget output.
          </p>
          <div style='padding:1em .5em;border:1px solid #ccc;background:#f5f5f5;color:#444;height:137px;overflow:auto;'><pre id='default-css'></pre></div>";
          $css = esc_attr( $options['custom_css'] );
          echo "<p>
          <strong>Custom CSS:</strong>
          <br>
          <textarea name='bitp_options[custom_css]' style='width: 100%;height:275px;' tabindex='1'> $css </textarea>
          </p>";
   echo '<script type="text/javascript" src="http://www.bandsintown.com/javascripts/bit_widget.js"></script>
        <script type="text/javascript">
        var widget = new BIT.Widget({});
        var default_css = widget.css();
        default_css = default_css.replace(/<\/?style[^>]*>/gi,"");
        default_css = default_css.replace(/}/g,"}\n");
        document.getElementById("default-css").innerHTML = default_css;
        </script>';
  }
  //Validation
  function options_validate($input) {
    return $input;
  }
  // [bandsintown] shortcode
  function shortcode( $atts, $content = null, $code = '' ) {
		return $this->template_tag($atts, false);
	}
	
	// actual processing of the template tag
	function template_tag( $params = array(), $echo = true ) {
		if ( !is_array( $params ) ) {
			$str = $params;
			$params = array();
			parse_str($str, $params);
		}
		if ( empty($params['artist']) ) {
			$params['artist'] = $this->options['artist'];
		}
		$output = "<script type='text/javascript'>var widget = new BIT.Widget({";
		if ( count($params) > 0 ) {
			$i = 0;
			foreach ( $params as $key => $val ) {
				$output .= "\"$key\": \"$val\", ";
			}
			if ( !isset($params['prefix']) ) {
				$output .= '"prefix": "wpjs", ';
			}
			$output = substr($output, 0, -2);
		}
		$output .= "});widget.insert_events();</script>";
		$options = get_option('bitp_options');
		if ( !empty($options['custom_css']) ) {
			$output .= '<style type="text/css">' . $options['custom_css'] . '</style>';
		}
		if ( $echo ) {
			echo $output;
		}
		else {
			return $output;
		}
	}
	
} // end Bandsintown_JS_Plugin

//
// Bandsintown Widget
//
class Bandsintown_JS_Widget extends WP_Widget {
	
	function Bandsintown_JS_Widget() {
		parent::WP_Widget(false, $name = 'Tour Dates');
	}
	
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if ( $title ) 
			echo $before_title . $title . $after_title;
		the_bandsintown_events(array( 
			'artist' => $instance['artist'], 
			'display_limit' => $instance['display_limit'],
			'force_narrow_layout' => true 
		));
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['artist'] = strip_tags(stripslashes($new_instance['artist']));
		$instance['display_limit'] = strip_tags(stripslashes($new_instance['display_limit']));
		return $instance;
	}
	
	function form( $instance ) {
		if ( empty($instance['artist']) ) {
			$options = get_option('bitp_options');
			$instance['artist'] = $options['artist'];
		}
		include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'widget-form.php';
	}
	
} // end Bandsintown JS Widget


global $bitp;
$bitp = new Bandsintown_JS_Plugin();

// template tag wrapper
function the_bandsintown_events( $params = array(), $echo = true ) {
	global $bitp;
	return $bitp->template_tag( $params, $echo );
}
