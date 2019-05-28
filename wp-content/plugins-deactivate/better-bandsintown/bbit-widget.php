<?php

add_action( 'wp_head', function(){
    wp_enqueue_style('widget', plugins_url('themes/widget.css', __FILE__ ));
    wp_enqueue_style('widget-dark', plugins_url('themes/widget-dark.css', __FILE__ ));
    wp_enqueue_style('widget-light', plugins_url('themes/widget-light.css', __FILE__ ));
});

add_action('wp_enqueue_scripts', function(){
    wp_enqueue_script('bit-initializer', 'http://www.bandsintown.com/javascripts/bit_widget.js');
});

add_action( 'widgets_init', function(){
     register_widget( 'BBIT_Widget' );
});

class BBIT_Widget Extends WP_Widget {
    /**
    * Sets up the widgets name etc
    */
    public function __construct() {
        parent::__construct(
            'bbit-widget', // Base ID
            __( 'Better Bandsintown', 'text_domain' ), // Name
            array( 'description' => __( 'Better Bandsintown Widget', 'bbit-widget' ), ) // Args
        );
    }

    /**
    * Outputs the content of the widget
    *
    * @param array $args
    * @param array $instance
    */
    public function widget( $args, $instance ) {
        // Retrieve values
        if( $instance) {
             $bandname = esc_attr($instance['bandname']);
             $theme = esc_attr($instance['theme']);
        } else {
             $bandname = '';
             $theme = '';
        }
        
        // Get $select value
        if ( $theme == 'Dark' ) {
            $css_class = "bbit-widget-dark";
        } else { // Light or some bullshit value
            $css_class = "bbit-widget-light";
        }
        
        echo $args['before_widget'];
        include ('bbit-widget-view.php');
        echo $args['after_widget'];
}

    /**
    * Outputs the options form on admin
    *
    * @param array $instance The widget options
    */
    public function form( $instance ) {
        // Check values
        if( $instance) {
             $bandname = esc_attr($instance['bandname']);
             $theme = esc_attr($instance['theme']);

        } else {
             $bandname = '';
             $theme = '';
        }
        
        include ('bbit-widget-form.php');
    }

    /**
    * Processing widget options on save
    *
    * @param array $new_instance The new options
    * @param array $old_instance The previous options
    */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['bandname'] = strip_tags($new_instance['bandname']);
        $instance['theme'] = strip_tags($new_instance['theme']);
        return $instance;
    }
}