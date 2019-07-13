<?php
namespace TFNumbersOptions;

Class Layouts implements \TFNumbersOpsInterface {

  function init($prefix) {
     $section = $this->get_section($prefix);
     $options = $this->get_options();

     foreach ( $options as $values => $option ) {
       $option['id'] = $prefix . $option['id'];
       $option['name'] = sprintf('<span class="dashicons dashicons-edit"></span> %s', $option['name']);

       $section->add_field( $option );
     }
  }

  function get_section($prefix) {
    $section = new_cmb2_box( array(
        'id' => $prefix . 'side',
        'context'    => 'side',
        'priority'   => 'high',
        'title'      => esc_html__('More Options', 'tf_numbers'),
        'object_types' => array( 'tf_stats' )
     ) );

     return $section;
  }

  function get_options() {
    $options = array(
      array(
        'name' => 'Layout',
        'id'   => 'layout',
        'type' => 'radio',
        'options' => array(
            'n1'   => esc_html__( '1', 'tf_numbers' ),
            'n2'   => esc_html__( '2', 'tf_numbers' )
         ),
      )
    );

    return apply_filters( 'tf_layouts', $options );
  }
}
