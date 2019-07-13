<?php
namespace TFNumbersOptions;

Class Numbers implements \TFNumbersOpsInterface {

  function init($prefix) {
     $section = $this->get_section($prefix);
     $group = $this->get_group($section, $prefix);
     $options = $this->get_options();

     foreach ( $options as $values => $option ) {
       $option['id'] = $prefix . $option['id'];
       $section->add_group_field( $group, $option );
     }

     // Hook for appending new fields to the elements
     do_action('tf_elements_reg_fields',$section, $group);
  }

  function get_section($prefix) {
    $section = new_cmb2_box( array(
        'id' => $prefix . 'stats_box',
        'title' => esc_html__('Random Numbers', 'tf_numbers'),
        'object_types' => array( 'tf_stats' )
     ) );

     return $section;
  }

  function get_group($section, $prefix) {
    $group = $section->add_field( array(
        'id'          => $prefix . 'stat',
        'type'        => 'group',
        'description' => esc_html__( 'Add/Remove New Random Number', 'tf_numbers' ),
        'options'     => array(
            'group_title'   => esc_html__( 'Random Numbers {#}', 'tf_numbers' ),
            'add_button'    => esc_html__( 'Add Another Random Number', 'tf_numbers' ),
            'remove_button' => esc_html__( 'Remove Random Number', 'tf_numbers' ),
            'sortable'      => true,
        ),
      )
    );

    return $group;
  }

  function get_options() {
    $options = array(
      array(
        'name' => '<span class="dashicons dashicons-visibility"></span> ' . esc_html__('Icon', 'tf_numbers'),
        'id'   => 'icon',
        'type' => 'text',
        'row_classes' => 'tf_icon'
      ),
      array(
        'name' => '<span class="dashicons dashicons dashicons-edit"></span> ' . esc_html__('Number', 'tf_numbers'),
        'id'   => 'number',
        'desc' => sprintf( '%s %s <a href="edit.php?post_type=tf_stats&page=tf-addons">%s</a>', esc_html__('Enter some number.', 'tf_numbers'), esc_html__( 'You can boost your numbers, include comma separator, and plenty of new features', 'tf_numbers'), esc_html__( 'Learn More', 'tf_numbers') ),
        'type' => 'text',
      ),
      array(
        'name' => '<span class="dashicons dashicons-star-empty"></span> ' . esc_html__('Dynamic Content', 'tf_numbers'),
        'id'   => 'dynamic_nmb',
        'type' => 'select',
        'options' => apply_filters( 'tf_numbers_dynamic_options', array(
            ''           => esc_html__( 'Use Custom Number Instead', 'tf_numbers' ),
            'articles'   => esc_html__( 'Number of Articles', 'tf_numbers' ),
            'categories' => esc_html__( 'Number of Categories', 'tf_numbers' ),
            'authors'    => esc_html__( 'Number of Authors', 'tf_numbers' ),
            'comments'   => esc_html__( 'Total Number Of Comments', 'tf_numbers' ),
         )),
      ),
      array(
        'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('Title', 'tf_numbers'),
        'id'   => 'title',
        'type' => 'text',
      ),
    );

    // custom fields filter
    $new_lines = apply_filters( 'tf_add_element', array() );
    $options = array_merge($options, $new_lines);

    return $options;
  }
}
