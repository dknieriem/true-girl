<?php

/**
 * Class MC4WP_Form_Widget_Enhancements
 *
 * @ignore
 * @access private
 */
class MC4WP_Form_Widget_Enhancements {

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_filter( 'mc4wp_form_widget_sanitize_settings', array( $this, 'sanitize_widget_settings' ), 10, 3 );
		add_action( 'mc4wp_form_widget_form', array( $this, 'widget_form' ), 10, 2 );
	}

	/**
	 * @param $new_settings
	 * @param $old_settings
	 * @param WP_Widget $widget
	 *
	 * @return mixed
	 */
	public function sanitize_widget_settings( $new_settings, $old_settings = array(), WP_Widget $widget = null ) {
		if( ! empty( $new_settings['form_id'] ) ) {
			$new_settings['form_id'] = (int) $new_settings['form_id'];
		}

		return $new_settings;
	}

	/**
	 * @param array $settings
	 * @param WP_Widget $widget
	 */
	public function widget_form( $settings, WP_Widget $widget ) {
		$forms = mc4wp_get_forms();

		?>
		<p>
			<label for="<?php echo $widget->get_field_id( 'form_id' ); ?>"><?php _e( 'Form:', 'mailchimp-for-wp' ); ?></label>
			<select class="widefat" name="<?php echo $widget->get_field_name( 'form_id' ); ?>" id="<?php echo $widget->get_field_id( 'form_id' ); ?>">
				<option value="0" disabled <?php selected( $settings['form_id'], 0 ); ?>><?php _e( 'Select the form to show' ,'mailchimp-for-wp' ); ?></option>
				<?php foreach( $forms as $f ) { ?>
					<option value="<?php echo esc_attr( $f->ID ); ?>" <?php selected( $settings['form_id'], $f->ID ); ?>><?php echo esc_html( $f->name ); ?></option>
				<?php } ?>
			</select>
		</p>

		<?php if( empty( $forms ) ) { ?>
			<p class="help"><?php printf( __( 'You don\'t have any sign-up forms. <a href="%s">Would you like to create one now?</a>' ,'mailchimp-for-wp' ), mc4wp_get_add_form_url() ); ?></p>
		<?php }
	}

}