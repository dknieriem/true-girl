<?php
class TFNumbersAdditionalOps {

  function init($prefix) {
    //custom options
    $section = new_cmb2_box( array(
        'id'           => $prefix . 'addons',
        'context'      => 'normal',
        'priority'     => 'core',
        'title'        => esc_html__('Addon Options', 'tf_numbers'),
        'object_types' => array( 'tf_stats' )
     ) );

     $this->depricated_support($section, $prefix);

     if (
       !defined('TF_WOO_STATS_NAME') &&
       !defined('TF_CONTROLLER_NAME') &&
       !defined('TF_BUNDLE_NAME') &&
       !defined('TF_ICONIZER_NAME') &&
       !defined('TF_CURRENCIES_NAME') &&
       !defined('TF_INCREMENTER_NAME') &&
       !defined('TF_ANIMATOR_NAME') &&
       !defined('TF_PARALLAX_NAME')
     ) {
         $section->add_field( array(
           'name' => '<span class="dashicons dashicons-edit"></span> ' . esc_html__('You do not have any addons active yet', 'tf_numbers'),
           'id'   => '_no_addons',
           'type' => 'label',
         ) );
     }

    // Hook for appending new option fields
    do_action('tf_add_option', $section);
  }

  function depricated_support($section, $prefix) {
    // fallback for older versions

    $new_ops = array();
    $new_ops = apply_filters( 'tf_add_options', $new_ops );
    if( !empty($new_ops) && is_array( $new_ops ) ) {
      foreach( $new_ops as $op )
      {
        $op['id'] = $prefix . $op['id'];
        $section->add_field( $op );
      }
    }

    //addon backdoor ! do not modify this
    $addon = apply_filters( 'tf_addon_options', array() );
    if( !empty($addon) && is_array( $addon ) ) {
      foreach( $addon as $op )
      {
        $op['id'] = $prefix . $op['id'];
        $section->add_field( $op );
      }
    }
  }
}
