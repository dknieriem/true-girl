<?php
namespace TFNumbersOptions;

Class NumbersStyle implements \TFNumbersOpsInterface {

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
      'id'          => $prefix . 'stats_stle',
      'context'     => 'normal',
      'priority'    => 'core',
      'title'       => esc_html__('Numbers Style', 'tf_numbers'),
      'object_types' => array( 'tf_stats' )
     ) );

     return $section;
  }

  function get_options() {
    $options = array(
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Icons Color', 'tf_numbers'),
        'id'   => 'ic',
        'type' => 'colorpicker',
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Numbers Color', 'tf_numbers'),
        'id'   => 'nc',
        'type' => 'colorpicker',
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Numbers Title Color', 'tf_numbers'),
        'id'   => 'ctc',
        'type' => 'colorpicker',
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Numbers Size', 'tf_numbers'),
        'id'   => 'nbs',
        'type' => 'text',
        'desc' => esc_html__('Add value that will be applied to numbers size. Value will be applied in em.', 'tf_numbers'),
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Titles Size', 'tf_numbers'),
        'id'   => 'tts',
        'type' => 'text',
        'desc' => esc_html__('Add value that will be applied to titles size. Value will be applied in em.', 'tf_numbers')
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Icons Size', 'tf_numbers'),
        'id'   => 'ics',
        'type' => 'text',
        'desc' => esc_html__('Add value that will be applied to icons size. Value will be applied in em.', 'tf_numbers')
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Section Border', 'tf_numbers'),
        'id'   => 'border',
        'type' => 'text',
        'desc' => esc_html__('Add border around the numbers section. You can insert css for the border, eq 1px solid #333', 'tf_numbers')
      ),
    );

    return $options;
  }
}
