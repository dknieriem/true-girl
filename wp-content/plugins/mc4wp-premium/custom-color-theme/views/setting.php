<?php

defined( 'ABSPATH' ) or exit;

$config = array( 'element' => 'mc4wp_form[settings][css]', 'value' => 'form-theme-custom-color' );
?>
<tr data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">
	<th><label for="mc4wp-custom-color-input"><?php _e( 'Select Color', 'mailchimp-for-wp' ); ?></label></th>
	<td>
		<input id="mc4wp-custom-color-input" name="mc4wp_form[settings][custom_theme_color]" type="text" class="color-field" value="<?php echo esc_attr( $opts['custom_theme_color'] ); ?>" />
	</td>
</tr>