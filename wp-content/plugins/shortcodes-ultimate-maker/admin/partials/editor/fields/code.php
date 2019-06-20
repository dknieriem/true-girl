<?php defined( 'ABSPATH' ) or exit; ?>

<div id="sum-code" class="sum-code">

	<div id="sum-code-variables" class="sum-code-variables">
		<p class="description"><strong><?php _e( 'Available variables', 'shortcodes-ultimate-maker' ); ?></strong></p>
		<ul id="sum-code-variables-list" class="sum-code-variables-list"></ul>
		<p class="description"><?php _e( 'You can use these variables in your code. Variables will be replaced with shortcode content and attribute values. Click a variable to insert it into code editor.', 'shortcodes-ultimate-maker' ); ?></p>
	</div>

	<div id="sum-code-editor" class="sum-code-editor hidden"></div>

	<textarea name="sumk_code" id="sum-code-value" rows="15" class="large-text"><?php echo esc_textarea( base64_decode( get_post_meta( get_the_ID(), 'sumk_code', true ) ) ); ?></textarea>

	<p class="description"><?php printf( __( 'This code will be used to generate the shortcode output. %sLearn more about code editor%s.', 'shortcodes-ultimate-maker' ), '<a href="http://docs.getshortcodes.com/article/61-custom-shortcode#Code_types" target="_blank"><nobr>', '</nobr></a>' ); ?></p>

</div>
