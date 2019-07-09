<?php

class MC4WP_Custom_Color_Theme_Admin {

	private $plugin_file;

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_filter( 'mc4wp_admin_form_css_options', array( $this, 'add_theme_option' ) );
		add_action( 'mc4wp_admin_form_after_appearance_settings_rows', array( $this, 'add_custom_color_option' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_footer' ), 99 );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Adds an option to the CSS dropdown.
	 *
	 * @param array $opts
	 * @return array
	 */
	function add_theme_option( $opts ) {
		$opts['form-theme-custom-color'] = __( 'Custom Colored Theme', 'mailchimp-for-wp' );
		return $opts;
	}

	/**
	 * Adds the color option row
	 *
	 * @param $opts
	 */
	function add_custom_color_option( $opts ) {
		include dirname( $this->plugin_file ) . '/views/setting.php';
	}

	/**
	 * Instantiate the WP Color Picker on our fields
	 */
	function admin_footer() {
		?>
		<script type="text/javascript">
			window.jQuery('.color-field').wpColorPicker();
		</script>
	<?php
	}

}
