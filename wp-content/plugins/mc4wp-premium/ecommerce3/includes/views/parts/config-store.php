<!-- Store configuration -->
<h3><?php _e( 'Store settings', 'mc4wp-ecommerce' ); ?></h3>

<form method="post">
    <input type="hidden" name="_mc4wp_action" value="save_ecommerce_settings" />
    <input type="hidden" name="_redirect_to" value="<?php echo add_query_arg( array( 'wizard' => '2' ) ); ?>" />
    <?php wp_nonce_field( 'save_ecommerce_settings' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">
                <label><?php _e( 'Name', 'mc4wp-ecommerce' ); ?></label>
            </th>
            <td>
                <input class="regular-text" name="mc4wp_ecommerce[store][name]" value="<?php echo esc_attr( $settings['store']['name'] ); ?>" required />
                <div class="help"><?php _e( 'The name of your store.', 'mc4wp-ecommerce' ); ?></div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label><?php _e( 'List', 'mc4wp-ecommerce' ); ?></label>
            </th>
            <td>
                <select name="mc4wp_ecommerce[store][list_id]" required <?php if( ! empty( $disable_list_select  ) ) { echo 'disabled'; } ?>>
                    <option value="" readonly><?php _e( 'Select a MailChimp list', 'mc4wp-ecommerce' ); ?></option>
                    <?php foreach( $lists as $list ) { ?>
                        <option value="<?php echo esc_attr( $list->id ); ?>" <?php selected( $settings['store']['list_id'], $list->id ); ?>><?php echo esc_html( $list->name ); ?></option>
                    <?php } ?>
                </select>
                <div class="help">
                    <?php _e( 'The MailChimp list associated with your store.', 'mc4wp-ecommerce' ); ?>
                    <?php if(  ! empty( $disable_list_select ) ) { echo sprintf( ' <span><a href="%s">' . __( 'How to change this?', 'mc4wp-ecommerce' ) . '</a></span>', 'https://mc4wp.com/kb/change-mailchimp-ecommerce-list/' ); } ?>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label><?php _e( 'Currency', 'mc4wp-ecommerce' ); ?></label>
            </th>
            <td>
                <select name="mc4wp_ecommerce[store][currency_code]" required>
                    <option readonly><?php _e( 'Select a currency', 'mc4wp-ecommerce' ); ?></option>
                    <?php foreach( get_woocommerce_currencies() as $code => $label ) { ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $settings['store']['currency_code'], $code ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php } ?>
                </select>
                <div class="help"><?php _e( 'The currency that your store accepts.', 'mc4wp-ecommerce' ); ?></div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label><?php _e( 'Enable automations?', 'mc4wp-ecommerce' ); ?></label>
            </th>
            <td>
                <label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[store][is_syncing]" value="0" <?php checked( $settings['store']['is_syncing'], 0 ); ?> />&rlm; <?php _e( 'Yes' ); ?></label>
                <label class="choice-wrap"><input type="radio" name="mc4wp_ecommerce[store][is_syncing]" value="1" <?php checked( $settings['store']['is_syncing'], 1 ); ?> />&rlm; <?php _e( 'No' ); ?></label>
                <div class="help"><?php _e( 'Should adding orders to MailChimp possibly trigger e-commerce automations?', 'mc4wp-ecommerce' ); ?></div>
            </td>
        </tr>
    </table>

    <?php submit_button( __( 'Save store', 'mc4wp-ecommerce' ) ); ?>

</form>

<!-- / Store configuration -->