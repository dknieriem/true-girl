<?php

/**
 * The class responsible for demo shortcodes.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
final class Shortcodes_Ultimate_Maker_Demos {

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $plugin_version   The current version of the plugin.
	 */
	private $plugin_version;

	/**
	 * Name of the option with 'demo created' flag.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $created_option   Name of the option with 'demo created' flag.
	 */
	private $created_option;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.5.5
	 * @param string  $plugin_version The current version of the plugin.
	 */
	public function __construct( $plugin_version ) {

		$this->plugin_version = $plugin_version;
		$this->created_option = 'su_option_shortcode-creator_demos_created';

	}

	/**
	 * Create demo shortcodes.
	 *
	 * @since  1.5.5
	 */
	public function create() {

		if ( get_option( $this->created_option ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || $screen->id !== 'edit-shortcodesultimate' ) {
			return;
		}

		foreach ( $this->get_shortcodes() as $shortcode ) {

			$post_id = wp_insert_post( $shortcode );

			foreach ( $shortcode['meta'] as $key => $value ) {
				add_post_meta( $post_id, $key, $value );
			}

		}

		add_option( $this->created_option, true, '', false );

	}

	/**
	 * Retrieve demo shortcodes.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return mixed   Array with demo shortcodes data.
	 */
	private function get_shortcodes() {

		$shortcodes = array();

		/**
		 * Demo shortcode 1, HTML code.
		 */
		$shortcodes[] = array(
			'post_title'  => sprintf( __( 'Demo shortcode %s', 'shortcodes-ultimate-maker' ), '1' ),
			'post_type'   => 'shortcodesultimate',
			'post_status' => 'publish',
			'post_date'   => date( 'Y-m-d H:i:02' ),
			'meta'        => array(
				'sumk_icon'           => 'asterisk',
				'sumk_plugin_version' => $this->plugin_version,
				'sumk_name'           => sprintf( __( 'Demo shortcode %s', 'shortcodes-ultimate-maker' ), '1' ),
				'sumk_slug'           => 'custom_shortcode_1',
				'sumk_desc'           => __( 'This shortcode written with simple HTML code and uses three different attributes', 'shortcodes-ultimate-maker' ),
				'sumk_content'        => __( 'Colorful text', 'shortcodes-ultimate-maker' ),
				'sumk_code_type'      => 'html',
				'sumk_code'           => base64_encode( "<div style=\"background: {{background}}\" class=\"su-maker-demo-1\">\n\t<span style=\"color: {{color}}\">{{content}}</span><br>\n\t<img src=\"{{image}}\">\n</div>" ),
				'sumk_attr'           => array(
					array(
						'slug'    => 'background',
						'default' => '#E6E6FA',
						'type'    => 'color',
						'name'    => __( 'Background color', 'shortcodes-ultimate-maker' ),
						'desc'    => __( 'Select background color', 'shortcodes-ultimate-maker' ),
						'min'     => '0',
						'max'     => '100',
						'step'    => '1',
						'options' => sprintf(
							'option1|%1$s 1%2$soption2|%1$s 2',
							__( 'Option', 'shortcodes-ultimate-maker' ),
							"\n"
						),
					),
					array(
						'slug'    => 'color',
						'default' => 'red',
						'type'    => 'select',
						'name'    => __( 'Text color', 'shortcodes-ultimate-maker' ),
						'desc'    => __( 'Select text color', 'shortcodes-ultimate-maker' ),
						'min'     => '0',
						'max'     => '100',
						'step'    => '1',
						'options' => sprintf(
							'red|%1$s%4$sgreen|%2$s%4$sblue|%3$s',
							__( 'Red', 'shortcodes-ultimate-maker' ),
							__( 'Green', 'shortcodes-ultimate-maker' ),
							__( 'Blue', 'shortcodes-ultimate-maker' ),
							"\n"
						),
					),
					array(
						'slug'    => 'image',
						'default' => 'http://lorempixel.com/200/100/',
						'type'    => 'upload',
						'name'    => __( 'Image', 'shortcodes-ultimate-maker' ),
						'desc'    => __( 'Select an image', 'shortcodes-ultimate-maker' ),
						'min'     => '0',
						'max'     => '100',
						'step'    => '1',
						'options' => sprintf(
							'option1|%1$s 1%2$soption2|%1$s 2',
							__( 'Option 1', 'shortcodes-ultimate-maker' ),
							"\n"
						),
					),
				),
			),
		);

		/**
		 * Demo shortcode 2, PHP code.
		 */
		$shortcodes[] = array(
			'post_title'  => sprintf( __( 'Demo shortcode %s', 'shortcodes-ultimate-maker' ), '2' ),
			'post_type'   => 'shortcodesultimate',
			'post_status' => 'publish',
			'post_date'   => date( 'Y-m-d H:i:01' ),
			'meta'        => array(
				'sumk_icon'           => 'asterisk',
				'sumk_plugin_version' => $this->plugin_version,
				'sumk_name'           => sprintf( __( 'Demo shortcode %s', 'shortcodes-ultimate-maker' ), '2' ),
				'sumk_slug'           => 'custom_shortcode_2',
				'sumk_desc'           => __( 'This shortcode uses PHP code to create greeting message for current user', 'shortcodes-ultimate-maker' ),
				'sumk_content'        => '',
				'sumk_code_type'      => 'php_echo',
				'sumk_code'           => base64_encode( sprintf(
						'// %3$s%1$sif ( ! is_user_logged_in() ) {%1$s%2$sreturn;%1$s}%1$s%1$s// %4$s%1$s$current_user = wp_get_current_user();%1$s%1$s// %5$s%1$secho \'<div class="su-maker-demo-2" style="background: \' . $background . \'; padding: \' . $padding . \'px;">\';%1$secho \'%6$s, \' . $current_user->user_firstname;%1$secho \'</div>\';%1$s',
						"\n",
						"\t",
						__( 'Do nothing if user is NOT logged in', 'shortcodes-ultimate-maker' ),
						__( 'Retrieve current user information', 'shortcodes-ultimate-maker' ),
						__( 'Display the message', 'shortcodes-ultimate-maker' ),
						__( 'Hi', 'shortcodes-ultimate-maker' )
					) ),
				'sumk_css'            => base64_encode( sprintf(
						'.su-maker-demo-2 {%1$s%2$smargin: 1em 0;%1$s%2$sborder: 1px solid #555;%1$s%2$scolor: #333;%1$s}%1$s',
						"\n",
						"\t"
					) ),
				'sumk_attr'           => array(
					array(
						'slug'    => 'background',
						'default' => '#E6E6FA',
						'type'    => 'color',
						'name'    => __( 'Background color', 'shortcodes-ultimate-maker' ),
						'desc'    => __( 'Select background color', 'shortcodes-ultimate-maker' ),
						'min'     => '0',
						'max'     => '100',
						'step'    => '1',
						'options' => sprintf(
							'option1|%1$s 1%2$soption2|%1$s 2',
							__( 'Option', 'shortcodes-ultimate-maker' ),
							"\n"
						),
					),
					array(
						'slug'    => 'padding',
						'default' => '15',
						'type'    => 'number',
						'name'    => __( 'Block padding', 'shortcodes-ultimate-maker' ),
						'desc'    => __( 'Adjust block padding', 'shortcodes-ultimate-maker' ),
						'min'     => '0',
						'max'     => '50',
						'step'    => '5',
						'options' => sprintf(
							'option1|%1$s 1%2$soption2|%1$s 2',
							__( 'Option', 'shortcodes-ultimate-maker' ),
							"\n"
						),
					),
				),
			),
		);

		return apply_filters( 'su/maker/demo_shortcodes', $shortcodes );

	}

}
