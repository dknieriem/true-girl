<?php

function tf_collect_numbers(){
   $args = array(
     'post_type' => 'tf_stats',
     'posts_per_page' => -1
   );
   $result = array();
   $stats = get_posts($args);
   foreach( $stats as $stat ): 
     $result[$stat->post_name] = $stat->post_name;
   endforeach; 

   return $result;
} 

if(!function_exists('tf_numbers_vc_shortcode')) {
   function tf_numbers_vc_shortcode() {
      vc_map(array(
         'name' => 'TF Numbers',
         'base' => 'tf_numbers',
         'category' => 'Content',
         "class" => "",
         'icon' => 'dashicons dashicons-slides tf_numbers',
         'params' => array(
            array(
               'type' => 'dropdown',
               'holder' => 'div',
               'heading' => 'Include TF Numbers',
               'param_name' => 'name',
               'description' => 'Select the numbers section you want to include.',
               'value' => tf_collect_numbers()
            ),
         )
      ));
   }

   add_action('vc_before_init', 'tf_numbers_vc_shortcode');
}