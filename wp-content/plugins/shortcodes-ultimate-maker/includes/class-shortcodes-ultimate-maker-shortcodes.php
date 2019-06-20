<?php

/**
 * The class responsible for registering and handling of custom shortcodes.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
final class Shortcodes_Ultimate_Maker_Shortcodes {

	/**
	 * The path to the main plugin file.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $plugin_file   The path to the main plugin file.
	 */
	private $plugin_file;

	/**
	 * The path to the plugin folder.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_path   The path to the plugin folder.
	 */
	private $plugin_path;

	/**
	 * The custom shortcodes data.
	 *
	 * @since  1.5.5
	 * @access private
	 * @var    mixed    $custom_shortcodes  The custom shortcodes data.
	 */
	private $custom_shortcodes;

	/**
	 * Custom shortcodes posts.
	 *
	 * @since  1.5.5
	 * @access private
	 * @var    mixed    $shortcode_posts  Custom shortcodes posts.
	 */
	private $shortcode_posts;

	/**
	 * Shortcodes prefix.
	 *
	 * @since  1.5.5
	 * @access private
	 * @var    string    $shortcode_prefix  Shortcodes prefix.
	 */
	private $shortcode_prefix;

	/**
	 * Custom shortcode callbacks collection.
	 *
	 * @since  1.5.5
	 * @access private
	 * @var    mixed    $shortcode_callbacks  Custom shortcode callbacks collection.
	 */
	private $shortcode_callbacks;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.5.5
	 */
	public function __construct( $plugin_file ) {

		$this->plugin_file         = $plugin_file;
		$this->plugin_path         = plugin_dir_path( $plugin_file );
		$this->custom_shortcodes   = array();
		$this->shortcode_posts     = array();
		$this->shortcode_prefix    = get_option( 'su_option_prefix' );
		$this->shortcode_callbacks = array();

	}

	/**
	 * Register custom shortcodes.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param mixed   $shortcodes Array with default shortcodes set.
	 * @return mixed              Modified array with custom shortcodes.
	 */
	public function register_custom_shortcodes( $shortcodes ) {

		foreach ( $this->get_custom_shortcodes() as $id => $data ) {
			$shortcodes[ $id ] = $data;
		}

		return $shortcodes;

	}

	/**
	 * Retrieve all the custom shortcodes data.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return mixed   Array with custom shortcodes data.
	 */
	private function get_custom_shortcodes() {

		if ( ! empty( $this->custom_shortcodes ) ) {
			return $this->custom_shortcodes;
		}

		foreach ( $this->get_shortcode_posts() as $post ) {

			$slug = get_post_meta( $post->ID, 'sumk_slug', true );

			if ( ! $this->is_valid_slug( $slug ) ) {
				continue;
			}

			$this->custom_shortcodes[ $slug ] = $this->get_custom_shortcode( $post->ID );
			$this->custom_shortcodes[ $slug ]['function'] = $this->get_shortcode_callback( $slug );

		}

		return $this->custom_shortcodes;

	}

	/**
	 * Retrieve custom shortcode posts.
	 *
	 * @since   1.5.5
	 * @access  private
	 * @return  mixed   Array with custom shortcodes posts data.
	 */
	private function get_shortcode_posts() {

		$args = array(
			'post_type'      => 'shortcodesultimate',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		return get_posts( $args );

	}

	/**
	 * Retrieve custom shortcode data.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param int     $post_id The ID of the shortcode post.
	 * @return mixed           Array with custom shortcode data.
	 */
	private function get_custom_shortcode( $post_id ) {

		$meta = get_post_custom( $post_id );

		$defaults = array(
			'id'        => 'shortcode_' . $post_id,
			'name'      => 'shortcode_' . $post_id,
			'desc'      => '',
			'type'      => 'single',
			'group'     => 'shortcode-creator',
			'icon'      => '',
			'image'     => '',
			'atts'      => $this->get_attributes( $post_id ),
			'is_custom' => true,
			'code'      => '',
			'code_type' => '',
			'css'       => '',
			'defaults'  => array(),
		);

		$shortcode = array();

		if ( isset( $meta['sumk_slug'][0] ) ) {
			$shortcode['id'] = $meta['sumk_slug'][0];
		}

		foreach ( array( 'name', 'desc', 'content', 'code', 'code_type', 'css' ) as $field ) {

			if ( isset( $meta[ 'sumk_' . $field ][0] ) ) {
				$shortcode[ $field ] = $meta[ 'sumk_' . $field ][0];
			}

		}

		if ( isset( $meta['sumk_icon'][0] ) ) {
			$shortcode['icon']  = $meta[ 'sumk_icon'][0];
			$shortcode['image'] = $meta[ 'sumk_icon'][0];
		}

		if (
			isset( $meta['sumk_code'][0] ) &&
			(
				strpos( base64_decode( $meta['sumk_code'][0] ), '{{content}}' ) !== false ||
				strpos( base64_decode( $meta['sumk_code'][0] ), '$content' ) !== false
			)
		) {
			$shortcode['type'] = 'wrap';
		}

		foreach ( $defaults['atts'] as $attribute ) {
			$shortcode['defaults'][ $attribute['slug'] ] = isset( $attribute['default'] ) ? $attribute['default'] : '';
		}

		return wp_parse_args( $shortcode, $defaults );

	}

	/**
	 * Retrieve the custom shortcode attributes.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param int     $post_id The ID of the shortcode post.
	 * @return mixed           Array with attributes data.
	 */
	private function get_attributes( $post_id ) {

		$meta = maybe_unserialize( get_post_meta( $post_id, 'sumk_attr', true ) );

		if ( ! is_array( $meta ) ) {
			return array();
		}

		$defaults = array(
			'type'    => 'text',
			'name'    => '',
			'desc'    => '',
			'default' => '',
			'min'     => 0,
			'max'     => 100,
			'step'    => 1,
		);

		$attributes = array();

		foreach ( $meta as $attribute ) {

			if ( empty( $attribute['slug'] ) || empty( $attribute['type'] ) ) {
				continue;
			}

			if ( empty( $attribute['name'] ) ) {
				$attribute['name'] = $attribute['slug'];
			}

			if ( $attribute['type'] === 'number' ) {
				$attribute['type'] = 'slider';
			}

			if ( $attribute['type'] === 'select' && ! empty( $attribute['options'] ) ) {

				$attribute['values'] = $this->parse_options_string( $attribute['options'] );

				unset( $attribute['options'] );

			}

			$attributes[ $attribute['slug'] ] = wp_parse_args( $attribute, $defaults );

		}

		return $attributes;

	}

	/**
	 * Retrieve shortcode callback function.
	 *
	 * Returns two different handler functions depending on the version of PHP.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param string  $id Shortcode ID.
	 * @return callable   Shortcode callback function.
	 */
	private function get_shortcode_callback( $id ) {

		if ( ! empty( $this->shortcode_callbacks[ $id ] ) ) {
			return $this->shortcode_callbacks[ $id ];
		}

		$version = version_compare( phpversion(), '5.4.0', '<' ) ? '5.2' : '5.4';

		/**
		 * 'include' used to isolate unsupported language constructions on different versions of PHP.
		 * Function create_function() will be used on PHP 5.2 and 5.3 (marked as deprecated in 7.2).
		 * Closures with $this reference will be used on PHP 5.4+.
		 *
		 * Tested on the following versions of PHP:
		 * 5.2.4, 5.2.17, 5.3.29, 5.4.45, 5.5.38, 5.6.20, 7.0.3, 7.1.4
		 */
		include $this->plugin_path . 'includes/shortcode-callback-' . $version . '.php';

		return $this->shortcode_callbacks[ $id ];

	}

	/**
	 * Remove shortcode prefix, if needed.
	 *
	 * Converts this 'su_super_shortcode' into this 'super_shortcode'.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param string  $tag Shortcode tag.
	 * @return string      Shortcode tag without prefix.
	 */
	private function maybe_remove_prefix( $tag ) {

		if ( empty( $this->shortcode_prefix ) ) {
			return $tag;
		}

		if ( strpos( $tag, $this->shortcode_prefix ) === 0 ) {
			$tag = substr( $tag, strlen( $this->shortcode_prefix ) );
		}

		return $tag;

	}

	/**
	 * Validate shortcode slug by comparing with sanitize_html_class() result.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param string  $slug Possible valid shortcode slug.
	 * @return boolean      True if slug is valid, False otherwise.
	 */
	private function is_valid_slug( $slug ) {
		return sanitize_html_class( $slug ) === $slug;
	}

	/**
	 * Convert key|Value strings into an array.
	 *
	 * Converts this:
	 * key1 | Value 1
	 * key2 | Value 2
	 *
	 * into this:
	 * array(
	 *   key1 => Value 1
	 *   key2 => Value 2
	 * )
	 *
	 * @since  1.5.5
	 * @access private
	 * @param string  $value Value string.
	 * @return mixed         Converted array.
	 */
	private function parse_options_string( $value ) {

		$array = array();

		$value = trim( $value, " \t\n" );
		$value = explode( "\n", $value );

		foreach ( $value as $line ) {

			$item = strpos( $line, '|' ) !== false ? explode( '|', $line ) : array( $line, $line );

			$item[0] = trim( sanitize_text_field( $item[0] ) );
			$item[1] = trim( sanitize_text_field( $item[1] ) );

			$array[ $item[0] ] = $item[1];

		}

		return $array;

	}

}
