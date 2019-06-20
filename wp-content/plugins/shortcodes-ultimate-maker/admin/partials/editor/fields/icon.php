<?php defined( 'ABSPATH' ) or exit; ?>

<div id="sum-icon" class="sum-icon">
	<input type="hidden" name="sumk_icon" value="<?php echo esc_attr( $this->get_icon_field_value() ); ?>" id="sum-icon-value">
</div>

<p class="description"><?php _e( 'This icon will be shown in shortcode list in Insert Shortcode window.', 'shortcodes-ultimate-maker' ); ?></p>
