<?php defined( 'ABSPATH' ) or exit; ?>

<input type="text" name="sumk_slug" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'sumk_slug', true ) ); ?>" id="sum-tag-name" class="sum-tag-name regular-text" placeholder="new_shortcode" required>
<p class="description sum-validation-failed-message"><?php printf( __( 'Invalid shortcode tag name. Please use only allowed characters: %s', 'shortcodes-ultimate-maker' ), '<code><nobr>a-z, 0-9, _</nobr></code>' ); ?></p>
<p class="description sum-validation-required-message"><?php _e( 'Shortcode tag name could not be empty.', 'shortcodes-ultimate-maker' ); ?></p>
<p class="description"><?php _e( 'Short name of the shortcode.', 'shortcodes-ultimate-maker' ); ?></p>
<p class="description"><?php printf( __( 'This name will be used at insertion of shortcode into post editor. You can use only Latin letters in lower case %s, digits %s, and underscores %s in this field.', 'shortcodes-ultimate-maker' ), '<code>[a..z]</code>', '<code>[0..9]</code>', '<code>_</code>' ); ?></p>
<p class="description"><?php printf( __( 'Example: use %s as shortcode tag name and you will create shortcode %s.', 'shortcodes-ultimate-maker' ), '<code>my_shortcode</code>', '<code><nobr>[su_my_shortcode]</nobr></code>' ); ?></p>
