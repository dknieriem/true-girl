<?php

/**
 * The class responsible for plugin upgrade procedures.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
final class Shortcodes_Ultimate_Maker_Upgrade {

	/**
	 * The path to the main plugin file.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $plugin_file   The path to the main plugin file.
	 */
	private $plugin_file;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $current_version   The current version of the plugin.
	 */
	private $current_version;

	/**
	 * Name of the option with plugin version.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $version_option   Name of the option with plugin version.
	 */
	private $version_option;

	/**
	 * The previous saved version.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $saved_version   The previous saved version.
	 */
	private $saved_version;

	/**
	 * Define the functionality of the updater.
	 *
	 * @since   1.5.5
	 * @param string  $plugin_file    The path to the main plugin file.
	 * @param string  $plugin_version The current version of the plugin.
	 */
	public function __construct( $plugin_file, $plugin_version ) {

		$this->plugin_file     = $plugin_file;
		$this->current_version = $plugin_version;
		$this->version_option  = 'su_option_shortcode-creator_version';
		$this->saved_version   = get_option( $this->version_option, 0 );

	}

	/**
	 * Run upgrade procedures.
	 *
	 * @since  1.5.5
	 */
	public function upgrade() {

		if ( ! $this->is_version_changed() ) {
			return;
		}

		if ( $this->is_previous_version_less_than( '1.5.6' ) ) {
			$this->upgrade_to_1_5_6();
		}

		$this->update_version();

	}

	/**
	 * Conditional check if previous version of the plugin less than passed one.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return boolean True if previous version of the plugin less than passed one, False otherwise.
	 */
	private function is_previous_version_less_than( $version ) {
		return version_compare( $this->saved_version, $version, '<' );
	}

	/**
	 * Conditional check if plugin was updated.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return boolean True if plugin was updated, False otherwise.
	 */
	private function is_version_changed() {
		return $this->is_previous_version_less_than( $this->current_version );
	}

	/**
	 * Save current version number.
	 *
	 * @since  1.5.5
	 * @access private
	 */
	private function update_version() {
		update_option( $this->version_option, $this->current_version );
	}

	/**
	 * Upgrade to 1.5.6.
	 *
	 * 1. Modify custom shortcodes.
	 *  1.1. Replace old png icons with FontAwesome icons.
	 *  1.2. Convert base64-encoded attributes into arrays.
	 *  1.3. Encode shortcode code into base64.
	 *  1.4. Trash old demo shortcodes.
	 *  1.5. Add post meta with plugin version.
	 * 2. Delete 'sumk_demo_imported' option.
	 * 3. Add 'su_option_shortcode-creator_license' option.
	 *
	 * @access  private
	 * @since   1.5.6
	 */
	private function upgrade_to_1_5_6() {

		/**
		 * 1. Modify custom shortcodes.
		 */
		$shortcode_posts = get_posts( array(
				'post_type'      => 'shortcodesultimate',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			) );

		foreach ( $shortcode_posts as $shortcode_post ) {

			$post_id = $shortcode_post->ID;
			$plugin_version = get_post_meta( $post_id, 'sumk_plugin_version', true );

			// Skip updated shortcodes
			if ( version_compare( $plugin_version, '1.5.6', '>=' ) ) {
				continue;
			}

			/**
			 * 1.1. Replace old png icons with FontAwesome icons.
			 */
			$icon = get_post_meta( $post_id, 'sumk_icon', true );

			// /assets/images/default-icon.png
			if ( strpos( $icon, '/assets/images/default-icon.png' ) !== false ) {

				if ( ! add_post_meta( $post_id, 'sumk_icon', 'cog', true ) ) {
					update_post_meta( $post_id, 'sumk_icon', 'cog' );
				}

			}

			// /assets/images/demo-N.png
			elseif (
				strpos( $icon, '/assets/images/demo-1.png' ) !== false ||
				strpos( $icon, '/assets/images/demo-2.png' ) !== false ||
				strpos( $icon, '/assets/images/demo-3.png' ) !== false
			) {

				if ( ! add_post_meta( $post_id, 'sumk_icon', 'cogs', true ) ) {
					update_post_meta( $post_id, 'sumk_icon', 'cogs' );
				}

			}


			/**
			 * 1.2. Convert base64-encoded attributes into array.
			 */
			$attributes = get_post_meta( $post_id, 'sumk_attr', false );

			foreach ( $attributes as $value ) {

				$prev_value = $value;

				if ( is_string( $value ) ) {

					$base64_decoded = base64_decode( $value, true );

					if ( $base64_decoded !== false ) {
						$value = $base64_decoded;
					}

				}

				$value = maybe_unserialize( $value );

				if ( is_array( $value ) && empty( $value ) ) {

					delete_post_meta( $post_id, 'sumk_attr', $prev_value );
					continue;

				}

				if ( ! add_post_meta( $post_id, 'sumk_attr', $value, true ) ) {
					update_post_meta( $post_id, 'sumk_attr', $value, $prev_value );
				}

			}


			/**
			 * 1.3. Encode shortcode code into base64.
			 */
			$code = get_post_meta( $post_id, 'sumk_code', true );

			if ( base64_decode( $code, true ) === false ) {

				$code = stripslashes( $code );
				$code = htmlspecialchars_decode( $code, ENT_QUOTES );
				$code = base64_encode( $code );

				if ( ! add_post_meta( $post_id, 'sumk_code', $code, true ) ) {
					update_post_meta( $post_id, 'sumk_code', $code );
				}

			}


			/**
			 * 1.4. Trash old demo shortcodes.
			 */
			$demos = array(
				__( 'Demo shortcode #1: HTML code example', 'shortcodes-ultimate-maker' ),
				__( 'Demo shortcode #2: PHP return code example', 'shortcodes-ultimate-maker' ),
				__( 'Demo shortcode #3: PHP echo code example', 'shortcodes-ultimate-maker' ),
			);

			if ( in_array( $shortcode_post->post_title, $demos ) ) {
				wp_trash_post( $post_id );
			}


			/**
			 * 1.5. Add post meta with plugin version.
			 */
			if ( ! add_post_meta( $post_id, 'sumk_plugin_version', $this->current_version, true ) ) {
				update_post_meta( $post_id, 'sumk_plugin_version', $this->current_version );
			}

		}

		/**
		 * 2. Delete 'sumk_demo_imported' option.
		 */
		delete_option( 'sumk_demo_imported' );

		/**
		 * 3. Add 'su_option_shortcode-creator_license' option.
		 */
		if ( get_option( 'su_option_shortcode-creator_license', 0 ) === 0 ) {
			add_option( 'su_option_shortcode-creator_license', '', '', false );
		}

	}

}
