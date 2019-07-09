<?php
defined( 'ABSPATH' ) or exit;


?>
<div class="metabox-holder">
	<div class="postbox">
		<h3 style="margin-top: 0;"><span><?php _e( 'Delete all log items', 'mailchimp-for-wp' ); ?></span></h3>
		<div class="inside">
			<form method="POST" onsubmit="return confirm('Are you sure?');">
				<input type="hidden" name="_mc4wp_action" value="log_empty" />
				<p>
					<?php _e( 'Use the following button to <strong>clear all of your log items at once</strong>.', 'mailchimp-for-wp' ); ?>
				</p>
				<p style="margin-bottom: 0">
					<input type="submit" class="button" value="<?php esc_attr_e( 'Empty Log', 'mailchimp-for-wp' ); ?>" />
				</p>
			</form>
		</div><!-- .inside -->
	</div>

	<div class="postbox">
		<h3 style="margin-top: 0;"><span><?php _e( 'Automatically delete log items', 'mailchimp-for-wp' ); ?></span></h3>
		<div class="inside">
			<form method="POST">
				<input type="hidden" name="_mc4wp_action" value="log_set_purge_schedule" />
				<p>
					<?php _e( 'Log items which are older than the specified number of days below will be automatically deleted.', 'mailchimp-for-wp' ); ?>
				</p>

				<p>
					<label><strong><?php _e( 'Days', 'mailchimp-for-wp' ); ?></strong></label><br />
					<input name="log_purge_days" type="number" step="1" value="<?php echo esc_attr( $options['log_purge_days'] ); ?>" required />
				</p>

				<p style="margin-bottom: 0">
					<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'mailchimp-for-wp' ); ?>" />
				</p>
			</form>
		</div><!-- .inside -->
	</div>
</div>
