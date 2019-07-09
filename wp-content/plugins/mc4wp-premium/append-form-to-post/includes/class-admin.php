<?php

namespace MC4WP\Premium\AFTP;

use Exception;

class Admin {

	public function hook() {
		add_action( 'mc4wp_admin_form_after_behaviour_settings_rows', array( $this, 'show_setting' ), 10, 2 );
		add_action( 'mc4wp_save_form', array( $this, 'on_form_save' ) );
	}

	public function on_form_save( $form_id ) {
		try{
			$form = mc4wp_get_form( $form_id );
		} catch( \Exception $e ) {
			return;
		}

		$options = mc4wp_get_options();
		if( ! isset( $options['append_to_posts'] ) ) {
			$options['append_to_posts'] = array();
		}
		
		// set global option
		if( $form->settings['append_to_posts'] ) {
			$options['append_to_posts'][$form_id] = $form->settings['append_to_posts_category'];
		} else {
			unset($options['append_to_posts'][$form_id]);
		}

		update_option( 'mc4wp', $options );
	}

	/**
	 * @param            $opts
	 * @param MC4WP_Form $form
	 */
	public function show_setting( $opts, $form ) {
		?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Append to all posts?', 'mailchimp-for-wp' ); ?></th>
			<td>
				<label>
					<input type="radio" name="mc4wp_form[settings][append_to_posts]" value="1" <?php checked( ! empty( $opts['append_to_posts'] ), true ); ?> />&rlm;
					<?php _e( 'Yes, append this form to all posts in', 'mailchimp-for-wp' ); echo ' '; ?>
					<?php wp_dropdown_categories( array( 
						'hide_empty' => false, 
						'show_option_all' => __( 'All categories', 'mailchimp-for-wp' ),
						'name' => 'mc4wp_form[settings][append_to_posts_category]',
						'selected' => $opts['append_to_posts_category'],
					) ); ?>
				</label> <br />
				<label">
					<input type="radio" name="mc4wp_form[settings][append_to_posts]" value="0" <?php checked( $opts['append_to_posts'], 0 ); ?> />&rlm;
					<?php _e( 'No' ); ?>
				</label> &nbsp;
				<p class="help"><?php _e( 'Select "yes" if you want to automatically append this form to all posts (in a certain category).', 'mailchimp-for-wp' ); ?></p>
			</td>
		</tr>
		<?php
	}


}
