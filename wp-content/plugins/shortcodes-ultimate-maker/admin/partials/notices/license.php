<?php defined( 'ABSPATH' ) or exit; ?>

<div class="notice notice-warning shortcodes-ultimate-maker-notice-license">
	<p><strong><?php _e( 'Shortcodes Ultimate: Shortcode Creator', 'shortcodes-ultimate-maker' ); ?></strong></p>
	<p><?php _e( 'Activate your license key to enable automatic updates.', 'shortcodes-ultimate-maker' ); ?></p>
	<p class="shortcodes-ultimate-maker-notice-license-actions">
		<a href="<?php echo add_query_arg( 'page', 'shortcodes-ultimate-settings', admin_url( 'admin.php' ) ); ?>#<?php echo $this->license_option; ?>"><strong><?php _e( 'Enter license key', 'shortcodes-ultimate-maker' ); ?></strong></a>
		<a href="<?php echo esc_url( $this->get_dismiss_link( true ) ); ?>"><?php _e( 'Remind me later', 'shortcodes-ultimate-maker' ); ?></a>
		<a href="<?php echo esc_url( $this->get_dismiss_link() ); ?>"><?php _e( 'Dismiss', 'shortcodes-ultimate-maker' ); ?></a>
	</p>
</div>

<style>
	.shortcodes-ultimate-maker-notice-license {
		position: relative;
	}
	.shortcodes-ultimate-maker-notice-license-actions a {
		text-decoration: none;
	}
	.shortcodes-ultimate-maker-notice-license-actions a + a {
		margin-left: 20px;
	}
</style>
