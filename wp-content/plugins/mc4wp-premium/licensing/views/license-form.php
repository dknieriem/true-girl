
<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th><?php _e( 'License Key', 'mailchimp-for-wp' ); ?></th>
			<td>
				<input size="40" name="mc4wp_license_key" placeholder="<?php esc_attr_e( 'Enter your license key..', 'mailchimp-for-wp' ); ?>" value="<?php echo esc_attr( $license->key ); ?>" <?php if( $license->activated ) { echo 'readonly'; } ?> />
				<input class="button" type="submit" name="action" value="<?php echo ( $license->activated ? 'deactivate' : 'activate' ); ?>" />
				<p class="help">
					<?php echo sprintf( __( 'The license key received when purchasing MailChimp for WordPress Premium. <a href="%s">You can find it here</a>.', 'mailchimp-for-wp' ), 'https://account.mc4wp.com/' ); ?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th><?php _e( 'License Status', 'mailchimp-for-wp' ); ?></th>
			<td>
				<?php
				if( $license->activated ) { ?>
					<p><span class="status positive"><?php _e( 'ACTIVE', 'mailchimp-for-wp' ); ?></span> - <?php _e( 'you are receiving plugin updates', 'mailchimp-for-wp' ); ?></p>
				<?php } else { ?>
					<p><span class="status negative"><?php _e( 'INACTIVE', 'mailchimp-for-wp' ); ?></span> - <?php _e( 'you are <strong>not</strong> receiving plugin updates right now', 'mailchimp-for-wp' ); ?></p>
				<?php } ?>
			</td>
		</tr>
	</table>

	<p>
		<input type="submit" class="button button-primary" name="action" value="<?php _e( 'Save Changes' ); ?>" />
	</p>

	<input type="hidden" name="_mc4wp_action" value="save_license" />
</form>
