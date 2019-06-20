<?php defined( 'ABSPATH' ) or exit; ?>

<div id="sum-attributes" class="sum-attributes">
	<input type="hidden" name="sumk_attr" value="<?php echo esc_attr( $this->get_attributes_field_value() ); ?>" id="sum-attributes-value">
</div>

<p class="description"><?php _e( 'Attributes are shortcode settings. Using the attributes you can, for example, create two identical shortcodes of different colours or sizes. Created attributes will be available at insertion of a custom shortcode to post editor, in Insert Shortcode window.', 'shortcodes-ultimate-maker' ); ?></p>
<p class="description"><?php _e( 'You can reorder attributes by dragging and dropping them.', 'shortcodes-ultimate-maker' ); ?></p>
<p class="description"><a href="http://docs.getshortcodes.com/article/63-attributes-of-custom-shortcodes" target="_blank"><?php _e( 'Learn more about attributes', 'shortcodes-ultimate-maker' ); ?></a>.</p>
