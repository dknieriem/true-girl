<?php

/**
 * Class MC4WP_Custom_Color_Theme
 *
 * @ignore
 */
class MC4WP_Custom_Color_Theme {

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
		add_filter( 'mc4wp_form_settings', array( $this, 'add_settings' ) );
		add_action( 'mc4wp_output_form', array( $this, 'print_css' ) );
	}

	/**
	 * @param $settings
	 *
	 * @return array
	 */
	public function add_settings( $settings ) {
		$defaults = array(
			'custom_theme_color' => '#1af'
		);

		$settings = array_merge( $defaults, $settings );
		return $settings;
	}

	/**
	 * Print CSS
	 *
	 * @param MC4WP_Form $form
	 * @return bool|void
	 */
	public function print_css( $form ) {

		if( $form->settings['css'] !== 'form-theme-custom-color' || empty( $form->settings['custom_theme_color'] ) ) {
			return false;
		}

		$color = $form->settings['custom_theme_color'];
		$rgb_color = new MC4WP_RGB_Color( $color );

		$darker_color = $rgb_color->darken( 10 );
		$darkest_color =  $rgb_color->darken( 20 );
		$font_color =  $rgb_color->lightness() > 50 ? 'black' : 'white';
		$this->print_css_template( $form->ID, $color, $darker_color, $darkest_color, $font_color );
	}

	/**
	 * @param int $form_id
	 * @param string $color
	 * @param string $darker_color
	 * @param string $darkest_color
	 * @param string $font_color
	 */
	public function print_css_template( $form_id, $color, $darker_color, $darkest_color, $font_color = 'white' ) {
		echo '<style type="text/css">';
		include dirname( $this->plugin_file ) . '/views/custom-css.php';
		echo '</style>';
	}


}
