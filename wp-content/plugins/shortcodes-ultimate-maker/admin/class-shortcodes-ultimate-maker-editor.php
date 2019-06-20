<?php

/**
 * The class responsible for shortcode editor (metabox).
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/admin
 */
final class Shortcodes_Ultimate_Maker_Editor {

	/**
	 * The path to the main plugin file.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_file   The path to the main plugin file.
	 */
	private $plugin_file;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_version   The current version of the plugin.
	 */
	private $plugin_version;

	/**
	 * The path to the plugin folder.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string      $plugin_path   The path to the plugin folder.
	 */
	private $plugin_path;

	/**
	 * The URL of the plugin folder.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $plugin_url    The URL of the plugin folder.
	 */
	private $plugin_url;

	/**
	 * The list of shortcode fields and their defaults.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      mixed    $editor_fields    The list of shortcode fields and their defaults.
	 */
	private $editor_fields;

	/**
	 * Default attribute settings.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      mixed    $attribute_defaults    Default attribute settings.
	 */
	private $attribute_defaults;

	/**
	 * Attribute field types.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      mixed    $attribute_field_types    Attribute field types.
	 */
	private $attribute_field_types;

	/**
	 * Setup class properties.
	 *
	 * @since   1.5.5
	 * @param string  $plugin_file    The path to the main plugin file.
	 * @param string  $plugin_version The current version of the plugin.
	 */
	public function __construct( $plugin_file, $plugin_version ) {

		$this->plugin_file        = $plugin_file;
		$this->plugin_version     = $plugin_version;
		$this->plugin_path        = plugin_dir_path( $plugin_file );
		$this->plugin_url         = plugin_dir_url( $plugin_file );
		$this->editor_fields      = array();
		$this->attribute_defaults = array(
			'slug'    => '',
			'default' => '',
			'type'    => 'text',
			'name'    => '',
			'desc'    => '',
			'min'     => 0,
			'max'     => 100,
			'step'    => 1,
			'options' => '',
		);

	}

	/**
	 * Callback to register metabox.
	 *
	 * @since 1.5.5
	 */
	public function add_meta_box() {

		add_meta_box(
			'shortcodes-ultimate-maker-editor',
			' ',
			array( $this, 'display_metabox' ),
			'shortcodesultimate',
			'normal',
			'high',
			array()
		);

	}

	/**
	 * Callback to display metabox content.
	 *
	 * @since 1.5.5
	 * @param WP_Post $post WP_Post instance.
	 */
	public function display_metabox( $post ) {
		$this->the_template( 'editor', $post );
	}

	/**
	 * Enqueue scripts and stylesheets.
	 *
	 * @since  1.5.5
	 */
	public function enqueue_assets() {

		if ( ! $this->is_editor_page() ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'shortcodes-ultimate-maker-editor',
			$this->plugin_url . 'admin/css/editor.css',
			array(),
			$this->plugin_version,
			'all'
		);

		wp_enqueue_script(
			'shortcodes-ultimate-maker-editor',
			$this->plugin_url . 'admin/js/editor.js',
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-mouse',
				'jquery-ui-sortable',
				'ace',
			),
			$this->plugin_version,
			true
		);

		wp_localize_script(
			'shortcodes-ultimate-maker-editor',
			'ShortcodesUltimateMakerEditorData',
			array(
				'icons' => Su_Data::icons(),
				'attributes' => array(
					'defaults' => $this->attribute_defaults,
					'types'    => $this->get_attribute_field_types(),
				),
				'l10n' => array(
					'Editor' => array(
						'createShortcode' => __( 'Create shortcode', 'shortcodes-ultimate-maker' ),
						'updateShortcode' => __( 'Update shortcode', 'shortcodes-ultimate-maker' ),
					),
					'Icon' => array(
						'selectIcon'        => __( 'Select icon', 'shortcodes-ultimate-maker' ),
						'close'             => __( 'Close', 'shortcodes-ultimate-maker' ),
						'useCustomImage'    => __( 'Use custom image', 'shortcodes-ultimate-maker' ),
						'selectCustomImage' => __( 'Select custom image', 'shortcodes-ultimate-maker' ),
						'useSelectedImage'  => __( 'Use selected image', 'shortcodes-ultimate-maker' ),
					),
					'Attributes' => array(
						'general' => array(
							'label'   => __( 'Field label', 'shortcodes-ultimate-maker' ),
							'type'    => __( 'Field type', 'shortcodes-ultimate-maker' ),
							'default' => __( 'Default value', 'shortcodes-ultimate-maker' ),
							'none'    => __( 'No attribues yet.', 'shortcodes-ultimate-maker' ),
							'add'     => __( 'Add attribute', 'shortcodes-ultimate-maker' ),
							'option'  => __( 'Option', 'shortcodes-ultimate-maker' ),
						),
						'item' => array(
							'edit'           => __( 'Edit', 'shortcodes-ultimate-maker' ),
							'close'          => __( 'Close', 'shortcodes-ultimate-maker' ),
							'delete'         => __( 'Delete', 'shortcodes-ultimate-maker' ),
							'deleted'        => __( 'Attribute will be deleted.', 'shortcodes-ultimate-maker' ),
							'restore'        => __( 'Restore', 'shortcodes-ultimate-maker' ),
							'label'          => __( 'Field label', 'shortcodes-ultimate-maker' ),
							'labelDesc'      => sprintf( __( 'This text will be used as field label in the Insert Shortcode window. %sLearn more%s.', 'shortcodes-ultimate-maker' ), '<a href="http://docs.getshortcodes.com/article/63-attributes-of-custom-shortcodes#Field_name_and_description" target="_blank">', '</a>' ),
							'noLabel'        => _x( 'none', 'Field name not set', 'shortcodes-ultimate-maker' ),
							'newName'        => __( 'Attribute %s', 'shortcodes-ultimate-maker' ),
							'name'           => __( 'Attribute name', 'shortcodes-ultimate-maker' ),
							'invalidName'    => sprintf( __( 'Invalid attribute name. Please use only allowed characters: %s', 'shortcodes-ultimate-maker' ), '<code><nobr>a-z, 0-9, _</nobr></code>' ),
							'emptyName'      => __( 'Attribute name could not be empty.', 'shortcodes-ultimate-maker' ),
							'nameDesc1'      => sprintf( __( 'This name will be used at insertion of shortcode into post editor. You can use only Latin letters in lower case %s, digits %s, and underscores %s in this field.', 'shortcodes-ultimate-maker' ), '<code>[a..z]</code>', '<code>[0..9]</code>', '<code>_</code>' ),
							'nameDesc2'      => __( 'Also, this name will be used as name of a variable in the code editor below.', 'shortcodes-ultimate-maker' ),
							'nameDesc3'      => sprintf( __( 'Example: use %s as attribute name and you will create the following shortcode: %s', 'shortcodes-ultimate-maker' ), '<code>style</code>', '<code><nobr>[shortcode style=""]</nobr></code>' ),
							'default'        => __( 'Default value', 'shortcodes-ultimate-maker' ),
							'defaultDesc'    => __( 'This text will be used as attribute value, unless any other value is specified at insertion of shortcode into page editor.', 'shortcodes-ultimate-maker' ),
							'noDefault'      => _x( 'none', 'Default field value not set', 'shortcodes-ultimate-maker' ),
							'type'           => __( 'Field type', 'shortcodes-ultimate-maker' ),
							'typeDesc'       => sprintf( __( 'This setting indicates the field type which will be used in shortcode generator at insertion of shortcode to page editor. %sLearn more%s.', 'shortcodes-ultimate-maker' ), '<a href="http://docs.getshortcodes.com/article/63-attributes-of-custom-shortcodes#Field_types" target="_blank">', '</a>' ),
							'desc'           => __( 'Field description', 'shortcodes-ultimate-maker' ),
							'descDesc'       => sprintf( __( 'This text will be used as field description in the Insert Shortcode window. %sLearn more%s.', 'shortcodes-ultimate-maker' ), '<a href="http://docs.getshortcodes.com/article/63-attributes-of-custom-shortcodes#Field_name_and_description" target="_blank">', '</a>' ),
							'options'        => __( 'Dropdown options', 'shortcodes-ultimate-maker' ),
							'optionsDesc'    => sprintf( __( 'This text will be used to create dropdown options list. Each option must be placed on a separate line. Option values and labels must be separated with pipe symbol %s. %sLearn more%s.', 'shortcodes-ultimate-maker' ), '<code>|</code>', '<a href="http://docs.getshortcodes.com/article/63-attributes-of-custom-shortcodes#Field_type_dropdown" target="_blank">', '</a>' ),
							'min'            => __( 'Minimum value', 'shortcodes-ultimate-maker' ),
							'max'            => __( 'Maximum value', 'shortcodes-ultimate-maker' ),
							'step'           => __( 'Step size', 'shortcodes-ultimate-maker' ),
							'closeAttribute' => __( 'Close attribute', 'shortcodes-ultimate-maker' ),
						),
					),
					'Code' => array(
						'fullscreen' => __( 'Toggle fullscreen', 'shortcodes-ultimate-maker' ),
					),
				),
			)
		);

	}

	/**
	 * Retrieve the list of shortcode fields and their defaults.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return mixed Array with field names and default values.
	 */
	private function get_fields() {

		if ( ! empty( $this->editor_fields ) ) {
			return $this->editor_fields;
		}

		$this->editor_fields = apply_filters( 'su/maker/admin/fields', array(
				'title' => array(
					'id'      => 'title',
					'title'   => __( 'Shortcode title', 'shortcodes-ultimate-maker' ),
					'default' => '',
				),
				'description' => array(
					'id'      => 'description',
					'title'   => __( 'Shortcode description', 'shortcodes-ultimate-maker' ),
					'default' => '',
				),
				'tag-name' => array(
					'id'      => 'tag-name',
					'title'   => __( 'Shortcode tag name', 'shortcodes-ultimate-maker' ),
					'default' => '',
				),
				'content' => array(
					'id'      => 'content',
					'title'   => __( 'Default content', 'shortcodes-ultimate-maker' ),
					'default' => __( 'Default content', 'shortcodes-ultimate-maker' ),
				),
				'icon' => array(
					'id'      => 'icon',
					'title'   => __( 'Icon', 'shortcodes-ultimate-maker' ),
					'default' => 'icon:cog',
				),
				'attributes' => array(
					'id'      => 'attributes',
					'title'   => __( 'Attributes', 'shortcodes-ultimate-maker' ),
					'default' => array(),
				),
				'type' => array(
					'id'      => 'type',
					'title'   => __( 'Editor mode', 'shortcodes-ultimate-maker' ),
					'default' => 'html',
				),
				'code' => array(
					'id'      => 'code',
					'title'   => __( 'Shortcode code', 'shortcodes-ultimate-maker' ),
					'default' => '',
				),
				'custom-css' => array(
					'id'      => 'custom-css',
					'title'   => __( 'Custom CSS', 'shortcodes-ultimate-maker' ),
					'default' => '',
				),
			) );

		return $this->editor_fields;

	}

	/**
	 * Save shortcode data.
	 *
	 * @since  1.5.5
	 * @param int     $post_id Post ID.
	 */
	public function save_post( $post_id ) {

		$capability = apply_filters( 'su/maker/capability', 'manage_options' );

		/**
		 * Check user capability.
		 */
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		/**
		 * Verify nonce.
		 */
		if (
			! isset( $_POST['sumk_nonce'] ) ||
			! wp_verify_nonce( $_POST['sumk_nonce'], 'save' )
		) {
			return;
		}

		/**
		 * Save post and shortcode titles.
		 */
		if ( isset( $_POST['sumk_name'] ) ) {

			$name = sanitize_text_field( $_POST['sumk_name'] );

			remove_action( 'save_post_shortcodesultimate', array( $this, 'save_post' ) );

			wp_update_post( array(
					'ID'         => $post_id,
					'post_title' => $name,
				) );

			add_action( 'save_post_shortcodesultimate', array( $this, 'save_post' ) );

			if ( ! add_post_meta( $post_id, 'sumk_name', $name, true ) ) {
				update_post_meta( $post_id, 'sumk_name', $name );
			}

		}

		/**
		 * Save shortcode tag name.
		 */
		if ( isset( $_POST['sumk_slug'] ) ) {

			$slug = strtolower( $_POST['sumk_slug'] );
			$slug = preg_replace( '/[^a-z0-9_]/', '', $slug );
			$slug = trim( $slug, '_' );

			if ( empty( $slug ) ) {
				$slug = 'custom_shortcode_' . $post_id;
			}

			if ( ! add_post_meta( $post_id, 'sumk_slug', $slug, true ) ) {
				update_post_meta( $post_id, 'sumk_slug', $slug );
			}

		}

		/**
		 * Save shortcode description.
		 */
		if ( isset( $_POST['sumk_desc'] ) ) {

			$desc = wp_strip_all_tags( $_POST['sumk_desc'], true );

			if ( ! add_post_meta( $post_id, 'sumk_desc', $desc, true ) ) {
				update_post_meta( $post_id, 'sumk_desc', $desc );
			}

		}

		/**
		 * Save default content of the shortcode.
		 */
		if ( isset( $_POST['sumk_content'] ) ) {

			if ( ! add_post_meta( $post_id, 'sumk_content', $_POST['sumk_content'], true ) ) {
				update_post_meta( $post_id, 'sumk_content', $_POST['sumk_content'] );
			}

		}

		/**
		 * Save shortcode icon.
		 */
		if ( isset( $_POST['sumk_icon'] ) ) {

			$icon = wp_strip_all_tags( $_POST['sumk_icon'], true );

			if ( ! add_post_meta( $post_id, 'sumk_icon', $icon, true ) ) {
				update_post_meta( $post_id, 'sumk_icon', $icon );
			}

		}

		/**
		 * Save attributes data.
		 */
		if ( isset( $_POST['sumk_attr'] ) ) {

			$attributes = json_decode( stripslashes( $_POST['sumk_attr'] ), true );

			$attributes = $this->validate_attributes( $attributes );

			if ( ! add_post_meta( $post_id, 'sumk_attr', $attributes, true ) ) {
				update_post_meta( $post_id, 'sumk_attr', $attributes );
			}

		}

		/**
		 * Save code editor mode and shortcode code.
		 */
		if ( isset( $_POST['sumk_code_type'] ) && isset( $_POST['sumk_code'] ) ) {

			/**
			 * Save code editor mode.
			 */
			$code_type = preg_replace( '/[^a-z_]/', '', $_POST['sumk_code_type'] );
			$code_types = array_keys( $this->get_code_types( true ) );

			if ( ! in_array( $code_type, $code_types ) ) {
				$code_type = $code_types[0];
			}

			if ( ! add_post_meta( $post_id, 'sumk_code_type', $code_type, true ) ) {
				update_post_meta( $post_id, 'sumk_code_type', $code_type );
			}

			/**
			 * Save shortcode code.
			 */
			$code = stripslashes( $_POST['sumk_code'] );

			// Encode into base64 to avoid issues with PHP 5.2 create_function().
			$code = base64_encode( $code );

			if ( ! add_post_meta( $post_id, 'sumk_code', $code, true ) ) {
				update_post_meta( $post_id, 'sumk_code', $code );
			}

		}

		/**
		 * Save custom CSS code.
		 */
		if ( isset( $_POST['sumk_css'] ) ) {

			$custom_css = stripslashes( $_POST['sumk_css'] );
			$custom_css = wp_strip_all_tags( $custom_css );
			$custom_css = base64_encode( $custom_css );

			if ( ! add_post_meta( $post_id, 'sumk_css', $custom_css, true ) ) {
				update_post_meta( $post_id, 'sumk_css', $custom_css );
			}

		}

		/**
		 * Save plugin version.
		 */
		$plugin_version = get_option( 'su_option_maker_version' );

		if ( ! add_post_meta( $post_id, 'sumk_plugin_version', $plugin_version, true ) ) {
			update_post_meta( $post_id, 'sumk_plugin_version', $plugin_version );
		}

	}

	/**
	 * Conditional tag to check that current page contains the editor.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return boolean True if the editor is displayed on current page, False if not.
	 */
	private function is_editor_page() {

		$screen = get_current_screen();

		if ( empty( $screen->base ) || empty( $screen->post_type ) ) {
			return false;
		}

		if ( $screen->base !== 'post' ) {
			return false;
		}

		if ( $screen->post_type !== 'shortcodesultimate' ) {
			return false;
		}

		return true;

	}

	/**
	 * Utility function to get specified template by it's name.
	 *
	 * @since 1.5.5
	 * @access private
	 * @param string  $name Template name (without extension).
	 * @param mixed   $data Template data to be passed to the template.
	 * @return string           Template content.
	 */
	private function get_template( $name, $data = null ) {

		// Sanitize name
		$name = preg_replace( '/[^A-Za-z0-9\/_-]/', '', $name );

		// Trim slashes
		$name = trim( $name, '/' );

		// The full template path
		$template = $this->plugin_path . 'admin/partials/editor/' . $name . '.php';

		// Look for a specified file
		if ( file_exists( $template ) ) {

			ob_start();
			include $template;
			$output = ob_get_contents();
			ob_end_clean();

		}

		return ( isset( $output ) ) ? $output : '';

	}

	/**
	 * Utility function to display specified template by it's name.
	 *
	 * @since 1.5.5
	 * @access private
	 * @param string  $name Template name (without extension).
	 * @param mixed   $data Template data to be passed to the template.
	 */
	private function the_template( $name, $data = null ) {
		echo $this->get_template( $name, $data );
	}

	/**
	 * Retrieve current value of the icon field.
	 *
	 * @since  1.5.5
	 * @return string Current value of the icon field.
	 */
	private function get_icon_field_value() {

		$icon = get_post_meta( get_the_ID(), 'sumk_icon', true );

		// Default icon
		if ( ! $icon ) {
			return 'cog';
		}

		// <img> icon
		if ( strpos( $icon, '/' ) !== false ) {
			return esc_url( $icon );
		}

		// FA icon
		return preg_replace( '/[^a-z0-9-]/', '', $icon );

	}

	/**
	 * Retrieve current value of the attributes field.
	 *
	 * @since  1.5.5
	 * @return string Current value of the attributes field.
	 */
	private function get_attributes_field_value() {

		$attributes = get_post_meta( get_the_ID(), 'sumk_attr', true );

		$attributes = maybe_unserialize( $attributes );

		if ( empty( $attributes ) ) {
			$attributes = array();
		}

		$attributes = json_encode( $attributes );

		return $attributes;

	}

	/**
	 * Retrieve the list of available code types.
	 *
	 * @since  1.5.5
	 * @param bool    $deprecated Include deprecated code types or not.
	 * @return mixed              The list of available code types.
	 */
	private function get_code_types( $deprecated = false ) {

		$types = array(
			'html' => array(
				'id'    => 'html',
				'title' => __(
					'HTML code - simple mode, perfect for beginners',
					'shortcodes-ultimate-maker'
				),
			),
			'php_echo' => array(
				'id'    => 'php_echo',
				'title' => __( 'PHP code - advanced mode', 'shortcodes-ultimate-maker' ),
			),
		);

		if ( $deprecated ) {

			$types['php_return'] = array(
				'id'       => 'php_return',
				'title'    => __( 'PHP return - deprecated', 'shortcodes-ultimate-maker' ),
			);

		}

		return $types;

	}

	/**
	 * Validate attributes data.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param mixed   $attributes Array with dirty data.
	 * @return mixed              Array with valid data, or empty array if all attributes are invalid.
	 */
	private function validate_attributes( $attributes ) {

		$valid = array();

		if ( empty( $attributes ) || ! is_array( $attributes ) ) {
			return array();
		}

		foreach ( $attributes as $attribute ) {

			$attribute = $this->validate_attribute( $attribute );

			if ( ! $attribute ) {
				continue;
			}

			$valid[] = $attribute;

		}

		return $valid;

	}

	/**
	 * Validate single attribute.
	 *
	 * @since  1.5.5
	 * @access private
	 * @param mixed   $attribute Array with attribute data.
	 * @return mixed            Array with valid data, or False if attribute data is invalid.
	 */
	private function validate_attribute( $attribute ) {

		$defaults = $this->attribute_defaults;
		$attribute = wp_parse_args( $attribute, $defaults );

		/**
		 * Validate attribute name.
		 */
		$attribute['slug'] = preg_replace( '/[^a-z0-9_]/', '', $attribute['slug'] );
		$attribute['slug'] = trim( $attribute['slug'], '_' );

		if ( empty( $attribute['slug'] ) ) {
			return false;
		}

		/**
		 * Validate field label.
		 *
		 * Note: we have valid attribute name (slug) at this point.
		 */
		$attribute['name'] = sanitize_text_field( $attribute['name'] );

		if ( empty( $attribute['name'] ) ) {
			$attribute['name'] = $attribute['slug'];
		}

		/**
		 * Validate field type.
		 */
		if ( ! array_key_exists( $attribute['type'], $this->get_attribute_field_types() ) ) {
			$attribute['type'] = $defaults['type'];
		}

		/**
		 * Validate description.
		 *
		 * Trim whispaces and line breaks.
		 */
		if ( ! empty( $attribute['desc'] ) ) {
			$attribute['desc'] = trim( $attribute['desc'] );
		}

		/**
		 * Validate dropdown options.
		 */
		if ( ! empty( $attribute['options'] ) ) {
			$attribute['options'] = $this->validate_dropdown_options( $attribute['options'] );
		}

		/**
		 * Validate min, max and step values.
		 */
		if ( ! is_numeric( $attribute['min'] ) ) {
			$attribute['min'] = $defaults['min'];
		}

		if ( ! is_numeric( $attribute['max'] ) ) {
			$attribute['max'] = $defaults['max'];
		}

		if ( ! is_numeric( $attribute['step'] ) ) {
			$attribute['step'] = $defaults['step'];
		}

		/**
		 * Set default value for switch field type.
		 */
		if (
			$attribute['type'] === 'bool' &&
			! in_array( $attribute['default'], array( 'yes', 'no' ) )
		) {
			$attribute['default'] = 'yes';
		}

		/**
		 * Set default value for number field type.
		 */
		if (
			$attribute['type'] === 'number' &&
			! is_numeric( $attribute['default'] )
		) {
			$attribute['default'] = $attribute['min'];
		}

		return $attribute;

	}

	/**
	 * Validate dropdown options string.
	 *
	 * Output format:
	 * option1|Option 1
	 * option2|Option 2
	 *
	 * @since  1.5.5
	 * @access private
	 * @param string  $options Multi-line string with dropdown options.
	 * @return string          Sanitized string.
	 */
	private function validate_dropdown_options( $options ) {

		$valid_options = array();

		foreach ( explode( PHP_EOL, $options ) as $line ) {

			$option = explode( '|', $line );

			if ( count( $option ) === 1 ) {
				$option[1] = $option[0];
			}

			$value = sanitize_text_field( $option[0] );
			$label = sanitize_text_field( $option[1] );

			if ( empty( $value ) ) {
				continue;
			}

			$valid_options[] = sprintf(
				'%s|%s',
				$value,
				$label
			);

		}

		return implode( PHP_EOL, $valid_options );

	}

	/**
	 * Retrieve attribute field types.
	 *
	 * @since  1.5.5
	 * @access private
	 * @return mixed   Array with field types and their labels.
	 */
	private function get_attribute_field_types() {

		if ( ! empty( $this->attribute_field_types ) ) {
			return $this->attribute_field_types;
		}

		$this->attribute_field_types = array(
			'text'         => __( 'Text', 'shortcodes-ultimate-maker' ),
			'number'       => __( 'Number', 'shortcodes-ultimate-maker' ),
			'color'        => __( 'Color', 'shortcodes-ultimate-maker' ),
			'select'       => __( 'Dropdown', 'shortcodes-ultimate-maker' ),
			'bool'         => __( 'Switch', 'shortcodes-ultimate-maker' ),
			'icon'         => __( 'Icon', 'shortcodes-ultimate-maker' ),
			'upload'       => __( 'Media library', 'shortcodes-ultimate-maker' ),
			'image_source' => __( 'Image source', 'shortcodes-ultimate-maker' ),
		);

		return $this->attribute_field_types;

	}

}
