<?php defined( 'ABSPATH' ) or exit; ?>

<textarea name="sumk_desc" id="sum-description" cols="30" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Shortcode description', 'shortcodes-ultimate-maker' ); ?>"><?php echo esc_textarea( get_post_meta( get_the_ID(), 'sumk_desc', true ) ); ?></textarea>
<p class="description"><?php _e( 'This text will be used as a pop-up tip at selection of shortcode in Insert Shortcode window and in shortcode settings window.', 'shortcodes-ultimate-maker' ); ?></p>
