<?php defined( 'ABSPATH' ) or exit; ?>

<input type="text" name="<?php echo esc_attr( $data['id'] ); ?>" id="<?php echo esc_attr( $data['id'] ); ?>" value="<?php echo esc_attr( $this->mask_license_key( get_option( $data['id'], '' ) ) ); ?>" class="regular-text" placeholder="XXXX-XXXX-XXXX-XXXX">

<?php if ( get_option( $data['id'] ) ) : ?>
	<code style="background:#46b450;color:#fff"><?php _ex( 'Active', 'License key is active', 'shortcodes-ultimate-maker' ); ?></code>
<?php else : ?>
	<code><?php _ex( 'Inactive', 'License key is not active', 'shortcodes-ultimate-maker' ); ?></code>
<?php endif; ?>

<p class="description">
	<?php _e( 'Enter license key to enable automatic updates', 'shortcodes-ultimate-maker' ); ?>.
	<a href="http://docs.getshortcodes.com/article/58-how-to-activate-license-key" target="_blank"><?php _e( 'How to activate license key', 'shortcodes-ultimate-maker' ); ?></a>.
</p>
