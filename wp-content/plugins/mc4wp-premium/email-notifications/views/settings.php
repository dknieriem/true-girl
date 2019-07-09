<?php defined( 'ABSPATH' ) or exit; ?>
<h2><?php echo __( 'Email Notifications', 'mailchimp-for-wp' ); ?></h2>

<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Enable?', 'mailchimp-for-wp' ); ?>
		</th>
		<td>
			<label><input type="radio" name="mc4wp_form[settings][email_notification][enabled]" value="1" <?php checked( $opts['enabled'], 1 ); ?> /> <?php _e( 'Yes' ); ?></label> &nbsp;
			<label><input type="radio" name="mc4wp_form[settings][email_notification][enabled]" value="0" <?php checked( $opts['enabled'], 0 ); ?>  /> <?php _e( 'No' ); ?></label>
			<p class="help"><?php _e( 'Tick "yes" to send an email whenever this form is successfully used.', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>

	<?php $config = array( 'element' => 'mc4wp_form[settings][email_notification][enabled]', 'value' => 1, 'hide' => false ); ?>
	<tbody data-showif="<?php echo esc_attr( json_encode( $config ) ); ?>">

		<!-- To -->
		<tr valign="top">
			<th scope="row">
				<label for="mc4wp_form_email_notification_recipients">
					<?php _e( 'To', 'mailchimp-for-wp' ); ?>
				</label>
			</th>
			<td>
				<input type="text" class="widefat" name="mc4wp_form[settings][email_notification][recipients]" value="<?php echo esc_attr( $opts['recipients'] ); ?>" id="mc4wp_form_email_notification_recipients" />
				<p class="help"><?php _e( 'Separate multiple email addresses with a comma.', 'mailchimp-for-wp' ); ?></p>
			</td>
		</tr>

		<!-- Subject -->
		<tr valign="top">
			<th scope="row">
				<label for="mc4wp_form_email_notification_subject">
					<?php _e( 'Subject', 'mailchimp-for-wp' ); ?>
				</label>
			</th>
			<td>
				<input type="text" class="widefat" name="mc4wp_form[settings][email_notification][subject]" value="<?php echo esc_attr( $opts['subject'] ); ?>" id="mc4wp_form_email_notification_subject" />
			</td>
		</tr>

		<!-- Message Body -->
		<tr valign="top">
			<th scope="row">
				<label for="mc4wp_form_email_notification_message_body">
					<?php _e( 'Message Body', 'mailchimp-for-wp' ); ?>
				</label>

				<p style="font-weight: normal;"><?php _e( 'Available tags:' ,'mailchimp-for-wp' ); ?></p>
				<p id="email-message-template-tags" style="margin-top: 20px; font-weight: normal;"></p>
			</th>
			<td>
				<p>
					<textarea class="widefat" rows="<?php echo ( substr_count( $opts['message_body'], PHP_EOL ) + 8 ); ?>" name="mc4wp_form[settings][email_notification][message_body]" id="mc4wp_form_email_notification_message_body"><?php echo esc_textarea( $opts['message_body'] ); ?></textarea>
				</p>

				<p>
					<label>
						<input type="hidden" name="mc4wp_form[settings][email_notification][content_type]" value="text/plain" />
						<input type="checkbox" name="mc4wp_form[settings][email_notification][content_type]" value="text/html" <?php checked( $opts['content_type'], 'text/html' ); ?> >
						<?php _e( 'Use HTML content type?', 'mailchimp-for-wp' ); ?>
					</label>
				</p>

			</td>
		</tr>



	</tbody>
</table>

<?php submit_button(); ?>
