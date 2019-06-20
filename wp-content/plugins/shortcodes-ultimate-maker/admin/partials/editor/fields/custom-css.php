<?php defined( 'ABSPATH' ) or exit; ?>

<div id="sum-custom-css" class="sum-custom-css">

	<div id="sum-custom-css-editor" class="sum-custom-css-editor hidden"></div>

	<textarea name="sumk_css" id="sum-custom-css-value" rows="15" class="large-text"><?php echo esc_textarea( base64_decode( get_post_meta( get_the_ID(), 'sumk_css', true ) ) ); ?></textarea>

	<p class="description"><?php _e( 'This CSS code will be displayed on the site right after shortcode. If you will insert multiple shortcodes, CSS code will be displayed only once.', 'shortcodes-ultimate-maker' ); ?></p>

</div>
