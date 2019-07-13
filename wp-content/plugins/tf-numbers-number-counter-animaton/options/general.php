<?php
namespace TFNumbersOptions;

Class General implements \TFNumbersOpsInterface {

  function init($prefix) {
     $section = $this->get_section($prefix);
     $options = $this->get_options();

     foreach ( $options as $values => $option ) {
       $option['id'] = $prefix . $option['id'];
       $section->add_field( $option );
     }
  }

  function get_section($prefix) {
    $section = new_cmb2_box( array(
        'id'           => $prefix . 'stats_bg',
        'context'      => 'normal',
        'priority'     => 'core',
        'title'        => esc_html__('General Options', 'tf_numbers'),
        'object_types' => array( 'tf_stats' )
     ) );

     return $section;
  }

  function get_options() {
    $options = array(
      // Depricated option in favour of js solution
      // array(
      //   'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Start counting immediately after page load', 'tf_numbers'),
      //   'id'   => 'cmo',
      //   'desc' => esc_html__('If checked this option will disable scrolling trigger, you will not need to scroll down to the numbers section to start counting, instead it will be triggered immediatelly after page is loaded.', 'tf_numbers'),
      //   'type' => 'checkbox'
      // ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Background Image', 'tf_numbers'),
        'id'   => 'bg',
        'type' => 'file',
      ),
      array(
          'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Background Color', 'tf_numbers'),
          'id'   => 'bgc',
          'type' => 'colorpicker',
      ),
      array(
          'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Use Transparent Background', 'tf_numbers'),
          'id'   => 'bgct',
          'type' => 'checkbox',
      ),
      array(
          'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Section Title Color', 'tf_numbers'),
          'id'   => 'tc',
          'type' => 'colorpicker',
      ),
      array(
          'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Section Title Vertical Margin', 'tf_numbers'),
          'id'   => 'tvm',
          'type' => 'text',
          'desc' => esc_html__('Add value that will be applied to vertical margin. Value will be applied in em.', 'tf_numbers')
      )
    );

    return $options;
  }
}
