<?php

  function tf_redeclare_scripts(){
      wp_dequeue_script('tf_numbers');
      wp_enqueue_script( 'tf_numbers-controls', TF_CONTROLS_DIR . 'assets/js/tf_numbers.js', array('jquery'), '1.0', true );
  }

  function tf_admin_script() {
      wp_dequeue_script('tf-admin-js');
      wp_enqueue_script( 'tf-admin-controls', TF_CONTROLS_DIR . 'assets/js/admin.js', array('jquery'), '1.0', true );
      wp_localize_script( 'tf-admin-controls', 'url', array(
            'path' => TF_NUMBERS_DIR.'assets/images/'
      ) );
  }

  add_action( 'cmb2_render_text_range', 'cmb2_render_callback_for_text_email', 10, 5 );

  function cmb2_render_callback_for_text_email( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
     echo $field_type_object->input( array( 'type' => 'range' ) );
  }

  function tf_more_layouts() {
     $layouts = array(
         0 =>  array(
           'name' => 'Layout',
            'id'   => 'layout',
            'type' => 'radio',
            'options' => array(
                'n1' => __( '1', 'cmb2' ),
                'n2'   => __( '2', 'cmb2' ),
                'n3'   => __( '3', 'cmb2' ),
                'n4'   => __( '4', 'cmb2' ),
                'n5'   => __( '5', 'cmb2' ),
                'n6'   => __( '6', 'cmb2' ),
                'n7'   => __( '7', 'cmb2' ),
                'n8'   => __( '8', 'cmb2' ),
                'n9'   => __( '9', 'cmb2' ),
             ),
         )
      );
     return $layouts;
  }

  function tf_add_controls() {
     $ops = array(
        0 => array(
            'name'  => 'Change Speed',
            'id'    => 'sp',
            'type'  => 'text_range',
            'default'   => 50,
            'desc' => 'Move more to the right for slower speed, or to the left for faster counting speed.',
        ),
        1 => array(
            'name'  => 'Use comma "," separator between numbers',
            'id'    => 'cm',
            'desc' => 'If checked this option will format your numbers with comma separator. You do not need to add it, just type regular number in number field.',
            'type' => 'checkbox'
        ),
        3 => array(
            'name'  => 'Numbers Section Vertical Padding',
            'id'    => 'pdn',
            'desc' => 'Add px, em, or % with value.',
            'type' => 'text'
        ),
        4 => array(
            'name'  => 'Numbers Section Vertical Margin',
            'id'    => 'mrg',
            'desc' => 'Add px, em, or % with value.',
            'type' => 'text'
        ),
        5 => array(
            'name'  => 'Numbers Section Title',
            'id'    => 'hst',
            'type' => 'select',
            'options' => array(
                'block' => __( 'Show', 'cmb2' ),
                'none'   => __( 'Hide', 'cmb2' ),
             ),
        ),
        6 => array(
            'name'  => 'Numbers Subtitle Font Size',
            'id'    => 'subf',
            'type'  => 'text',
            'desc' => 'Add px, em, or % with value.',
            'default' => '13px'
        ),
        7 => array(
            'name'  => 'Numbers Subtitle Distance From Title',
            'id'    => 'subt',
            'type'  => 'text',
            'desc' => 'Add px, em, or % with value.',
            'default' => '10px'
        ),
        8 => array(
            'name'  => 'Numbers Subtitle Color',
            'id'    => 'subc',
            'type'  => 'colorpicker',
        ),
     );
     return $ops;
    }

    function tf_apply_new_ops() {
        $style =  array(
            0 => array(
                'selector' => '',
                'values' => array(
                    0 => array(
                      'property' => 'padding-top',
                      'id' => 'pdn'
                    ),
                    1 => array(
                      'property' => 'padding-bottom',
                      'id' => 'pdn'
                    ),
                    2 => array(
                      'property' => 'margin-top',
                      'id' => 'mrg'
                    ),
                    3 => array(
                      'property' => 'margin-bottom',
                      'id' => 'mrg'
                    )
                )
            ),
            1 => array(
              'selector' => 'h3',
              'values' => array(
                  0 => array(
                    'property' => 'display',
                    'id' => 'hst'
                  )
               )
            ),
            2 => array(
              'selector' => '.stat .count-subtitle',
              'values' => array(
                  0 => array(
                    'property' => 'font-size',
                    'id' => 'subf'
                  ),
                  1 => array(
                    'property' => 'color',
                    'id' => 'subc'
                  ),
                  2 => array(
                    'property' => 'margin-top',
                    'id' => 'subt'
                  )
               )
            )
        );

        return $style;
    }

    /**
    *
    * @since 1.0.3
    */
    function tf_add_element_sub($el, $el_group)
    {
       $el->add_group_field( $el_group, array(
           'name'  => '<span class="dashicons dashicons dashicons-edit"></span> Subtitle',
           'id'    => '_tf_subt',
           'type'  => 'text',
           'desc'  => 'Duration in ms (milliseconds) of selected intro animation.'
       ));
    }

    function tf_controller_license() {
        $license  = get_option( 'tf_controller_license_key' );
        $status   = get_option( 'tf_controller_license_status' );
      ?>
      <tr valign="top">
            <th scope="row" valign="top">
              <?php _e('Controller License Key'); ?>
            </th>
            <td>
              <input id="tf_controller_license_key" name="tf_controller_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
              <label class="description" for="tf_controller_license_key"><?php _e('Enter your license key'); ?></label>
            </td>
          </tr>
          <?php if( false !== $license ) { ?>
           <tr valign="top">
              <th scope="row" valign="top">
                <?php _e('License Status'); ?>
              </th>
              <td>
                <?php if( $status !== false && $status == 'valid' ) { ?>
                  <span style="color:green;line-height: 26px;"><?php _e('Active'); ?></span>
                  <?php } else {
                  if( $status !== false && $status == 'expired' ): ?>
                  <span style="color:crimson;line-height: 26px;"><?php _e('Expired'); ?></span>
                  <?php elseif( $status !== false && $status == 'invalid' ): ?>
                  <span style="color:crimson;line-height: 26px;"><?php _e('Invalid License'); ?></span>
                  <?php endif; //wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
                <?php } ?>
              </td>
           </tr>
           <tr style="height: 20px"></tr>
          <?php }
    }
