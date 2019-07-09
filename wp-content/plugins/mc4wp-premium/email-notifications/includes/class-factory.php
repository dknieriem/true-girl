<?php

/**
 * Class MC4WP_Form_Notification_Factory
 *
 * @ignore
 */
class MC4WP_Form_Notification_Factory {

	public function add_hooks() {
		add_filter( 'mc4wp_form_settings', array( $this, 'settings' ) );
		add_action( 'mc4wp_form_subscribed',array( $this, 'send_form_notification' ), 10, 4 );
		add_action( 'mc4wp_form_unsubscribed',array( $this, 'send_form_notification' ), 10, 2 );
	}

	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	public function settings( $settings ) {
		$defaults = array(
			'enabled' => 0,
			'subject' => 'New form submission - MailChimp for WordPress',
			'recipients' => get_bloginfo( 'admin_email' ),
			'message_body' => 'Form was submitted with the following data.' . "\r\n\r\n" . '[_ALL_]',
			'content_type' => 'text/html',
		);

		// make sure container is an array
		if( empty( $settings['email_notification'] ) ) {
			$settings['email_notification'] = array();
		}

		// merge with default settings
		$settings['email_notification'] = array_merge( $defaults, $settings['email_notification'] );

		return $settings;
	}

	/**
	 * @param MC4WP_Form $form
	 * @param string $email_address
	 * @param array $data
	 * @param MC4WP_MailChimp_Subscriber[] $map
	 * @return bool
	 */
	public function send_form_notification( MC4WP_Form $form, $email_address = '', $data = array(), $map = array() ) {

        $email_settings = $form->settings['email_notification'];
		if ( ! $email_settings['enabled'] ) {
			return false;
		}

		$email = new MC4WP_Email_Notification(
            $email_settings['recipients'],
            $email_settings['subject'],
            $email_settings['message_body'],
            $email_settings['content_type'],
			$form,
            $map
		);

		$email->send();

        // write info to log
        $this->get_log()->info( sprintf( 'Form %d > Sent email notification to %s', $form->ID, $email_settings['recipients'] ) );
	}

    /**
     * @return MC4WP_Debug_Log
     */
	private function get_log() {
        return mc4wp('log');
    }

}
