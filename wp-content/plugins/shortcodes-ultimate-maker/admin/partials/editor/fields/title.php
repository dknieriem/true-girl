<?php defined( 'ABSPATH' ) or exit; ?>

<input type="text" name="sumk_name" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'sumk_name', true ) ); ?>" id="sum-title" class="regular-text" placeholder="<?php esc_attr_e( 'New shortcode', 'shortcodes-ultimate-maker' ); ?>" autofocus required>
<p class="description"><?php _e( 'Full name of the shortcode.', 'shortcodes-ultimate-maker' ); ?></p>
<p class="description"><?php _e( 'This name will be shown in shortcode generator (in Insert Shortcode window). You can use any text in this field. It is not recommended to use long names; one-two words will be enough.', 'shortcodes-ultimate-maker' ); ?></p>
