<?php

/**
 * The "License" notice.
 *
 * @since        1.5.5
 * @version      1.0.2
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/admin
 */
final class Shortcodes_Ultimate_Addon_License_Notice extends Shortcodes_Ultimate_Addon_Notice {

	/**
	 * The ID of the add-on.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $addon_id   The ID of the add-on.
	 */
	private $addon_id;

	/**
	 * Array with screen IDs.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      mixed    $screens   Array with screen IDs.
	 */
	private $screens;

	/**
	 * The name of the option with license data.
	 *
	 * @since    1.5.5
	 * @access   protected
	 * @var      mixed    $license_option   The name of the option with license data.
	 */
	protected $license_option;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since  1.5.5
	 * @param string  $addon_id      The ID of the add-on.
	 * @param string  $template_file The full path to the notice template file.
	 */
	public function __construct( $addon_id, $template_file ) {

		parent::__construct( "{$addon_id}_license", $template_file );

		$this->defer_delay    = 3 * DAY_IN_SECONDS;
		$this->screens        = array( 'dashboard', 'plugins' );
		$this->addon_id       = $addon_id;
		$this->license_option = "su_option_{$addon_id}_license";

	}

	/**
	 * Display the notice.
	 *
	 * @since  1.5.5
	 */
	public function display_notice() {

		// Make sure this is the right screen
		if ( ! in_array( $this->get_current_screen_id(), $this->screens ) ) {
			return;
		}

		// Check user capability
		if ( ! $this->current_user_can_view() ) {
			return;
		}

		// Make sure the license key is not activated
		if ( $this->is_license_activated() ) {
			return;
		}

		// Make sure the notice is not dismissed or deferred
		if ( $this->is_dismissed() ) {
			return;
		}

		// Only show notice if the core plugin is installed and activated
		// Otherwise settings page won't be available
		if ( ! did_action( 'su/ready' ) ) {
			return;
		}

		// Display the notice
		$this->include_template();

	}

	/**
	 * Check if the license key is already activated.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return bool    True if license key exists, False if not.
	 */
	private function is_license_activated() {

		$key = get_option( $this->license_option, '' );

		return ! empty( $key );

	}

}
