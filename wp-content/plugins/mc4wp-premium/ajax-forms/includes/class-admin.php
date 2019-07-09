<?php

class MC4WP_AJAX_Forms_Admin {

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'mc4wp_admin_form_after_behaviour_settings_rows', array( $this, 'show_setting' ), 10, 2 );
	}

	/**
	 * @param            $opts
	 * @param MC4WP_Form $form
	 */
	public function show_setting( $opts, $form ) {
		?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Enable AJAX form submission?', 'mailchimp-for-wp' ); ?></th>
			<td>
				<label>
					<input type="radio" name="mc4wp_form[settings][ajax]" value="1" <?php checked( $opts['ajax'], 1 ); ?> />&rlm;
					<?php _e( 'Yes' ); ?>
				</label> &nbsp;
				<label>
					<input type="radio" name="mc4wp_form[settings][ajax]" value="0" <?php checked( $opts['ajax'], 0 ); ?> />&rlm;
					<?php _e( 'No' ); ?>
				</label> &nbsp;
				<p class="help"><?php _e( 'Select "yes" if you want to use AJAX (JavaScript) to submit forms.', 'mailchimp-for-wp' ); ?></p>
			</td>
		</tr>
		<?php
	}

}
