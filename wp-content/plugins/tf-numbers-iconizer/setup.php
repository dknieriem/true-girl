<?php

  
    function tf_add_search() {
       $srch = '<span id="icn-srch"><input type="text" placeholder="Search Icons" /><i class="fa fa-search"></i></span>';
       return $srch;
    }

    function tf_custom_tabs(){
        return array('<li class="active">Font-Awesome</li>','<li>Image Library</li>');
    }

    function tf_brdjokal() {
       $dirname = plugin_dir_path(__FILE__ )."images/";  
       $images = glob($dirname."*.{jpg,png,gif,bmp,jpeg}", GLOB_BRACE); 
       $list = '';
       foreach($images as $image) {
         $name = basename($image);
         $list .= '<img src="'.TF_NUMB_ICONIZER_DIR.'images/'.basename($image).'" class="'.$name.'" data-id="'.$name.'" />';
       }

       return $list;
    }

    function tf_icons_ops(){
      $ops = array(
          0  => array(
              'name'  => 'Image Width',
              'id'    => 'wdh',
              'type'  => 'text',
              'desc' => 'Add px, em or %'
          ),
          1 => array(
              'name'  => 'Image Height',
              'id'    => 'hgh',
              'type'  => 'text',
              'desc' => 'Add px, em or %'
          )
        
        );

       return $ops;
    }

    function tf_apply_image_ops() {
        $image_style =  array(
            0 => array(
                'selector' => 'span img',
                'values' => array( 
                    0 => array(
                      'property' => 'width',
                      'id' => 'wdh'
                    ),
                    1 => array(
                      'property' => 'height',
                      'id' => 'hgh'
                    )
                )
             )
        );

         return $image_style;
    }

    function tf_iconizer_license() {
        $license  = get_option( 'tf_iconizer_license_key' );
        $status   = get_option( 'tf_iconizer_license_status' );
      ?>
      <tr valign="top"> 
            <th scope="row" valign="top">
              <?php _e('Iconizer License Key'); ?>
            </th>
            <td>
              <input id="tf_iconizer_license_key" name="tf_iconizer_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
              <label class="description" for="tf_iconizer_license_key"><?php _e('Enter your license key'); ?></label>
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
                  <?php endif; ?>
                <?php } ?>
              </td>
           </tr>
           <tr style="height: 20px"></tr>
          <?php }
    }

  ?>