<?php defined( 'ABSPATH' ) or exit; ?>

<textarea name="sumk_content" id="sum-content" cols="30" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Default content', 'shortcodes-ultimate-maker' ); ?>"><?php echo esc_textarea( get_post_meta( get_the_ID(), 'sumk_content', true ) ); ?></textarea>
<p class="description"><?php printf( __( 'This text will be used as default content of the shortcode.', 'shortcodes-ultimate-maker' ) ); ?></p>
<p class="description"><?php printf( __( 'Shortcode content is a text placed between opening and closing tags of the shortcode. Leave this field empty if your shortcode does not imply use of any content and closing tag.', 'shortcodes-ultimate-maker' ) ); ?></p>
<p class="description"><?php printf( __( 'Example of shortcode with content: %s', 'shortcodes-ultimate-maker' ), sprintf( '<code><nobr>[my_shortcode] %s [/my_shortcode]</nobr></code>', __( 'Default content', 'shortcodes-ultimate-maker' ) ) ); ?></p>
