<?php

/**
 * The "Install Core" notice.
 *
 * @since        1.5.8
 * @version      1.0.1
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/admin
 */
final class Shortcodes_Ultimate_Addon_Core_Notice extends Shortcodes_Ultimate_Addon_Notice {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since  1.5.8
	 * @param string  $addon_id      The ID of the add-on.
	 * @param string  $template_file The full path to the notice template file.
	 */
	public function __construct( $addon_id, $template_file ) {
		parent::__construct( "{$addon_id}_core", $template_file );
	}

	/**
	 * Display the notice.
	 *
	 * @since  1.5.8
	 */
	public function display_notice() {

		// Display notice only at Plugins screen
		if ( 'plugins' !== $this->get_current_screen_id() ) {
			return;
		}

		// Check user capability
		if ( ! $this->current_user_can_view() ) {
			return;
		}

		// Display notice only if core plugin isn't installed
		if ( did_action( 'su/ready' ) ) {
			return;
		}

		// Make sure the notice is not dismissed or deferred
		if ( $this->is_dismissed() ) {
			return;
		}

		$this->include_template();

	}

}
