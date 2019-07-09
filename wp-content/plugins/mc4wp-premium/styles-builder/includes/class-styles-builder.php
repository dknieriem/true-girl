<?php

class MC4WP_Styles_Builder {

	/**
	 * Array with all available CSS fields, their default value and their type
	 *
	 * @var array
	 */
	protected $fields = array(
		'form_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'form_background_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'form_background_image' => array(
			'default' => '',
			'type' => 'text'
		),
		'form_background_repeat' => array(
			'default' => 'repeat',
			'type' => 'text'
		),
		'form_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'form_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'form_border_width' => array(
			'default' => '',
			'type' => 'int'
		),
		'form_padding' => array(
			'default' => '',
			'type' => 'int'
		),
		'form_text_align' => array(
			'default' => '',
			'type' => 'text'
		),
		'form_font_size' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'labels_font_style' => array(
			'default' => '',
			'type' => 'text'
		),
		'labels_font_size' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_display' => array(
			'default' => '',
			'type' => 'text'
		),
		'labels_vertical_margin' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_horizontal_margin' => array(
			'default' => '',
			'type' => 'int'
		),
		'labels_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'fields_border_radius' => array(
			'default' => '',
			'type' => 'int'
		),
		'fields_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'fields_focus_outline_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'fields_border_width' => array(
			'default' => '',
			'type' => 'int'
		),
		'fields_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'fields_height' => array(
			'default' => '',
			'type' => 'int'
		),
		'fields_display' => array(
			'default' => '',
			'type' => 'text'
		),
		'buttons_background_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_hover_background_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_font_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_font_size' => array(
			'default' => '',
			'type' => 'int'
		),
		'buttons_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_hover_border_color' => array(
			'default' => '',
			'type' => 'color'
		),
		'buttons_border_radius' => array(
			'default' => '',
			'type' => 'int'
		),
		'buttons_border_width' => array(
			'default' => '',
			'type' => 'int'
		),
		'buttons_width' => array(
			'default' => '',
			'type' => 'px'
		),
		'buttons_height' => array(
			'default' => '',
			'type' => 'int'
		),
		'messages_font_color_error' => array(
			'default' => '',
			'type' => 'color'
		),
        'messages_font_color_notice' => array(
            'default' => '',
            'type' => 'color'
        ),
		'messages_font_color_success' => array(
			'default' => '',
			'type' => 'color'
		),
		'selector_prefix' => array(
			'default' => '',
			'type' => 'selector'
		),
		'manual' => array(
			'default' => '',
			'type' => 'text'
		),
	);

	/**
	 * @var array
	 */
	protected $default_form_styles = array();

	/**
	 * @var array
	 */
	protected $styles = array();

	/**
	 * @var MC4WP_Admin_Messages
	 */
	protected $messages;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->default_form_styles = $this->load_default_form_styles();
		$this->styles = $this->load_styles();
		$this->messages = mc4wp( 'admin.messages' );
	}

	/**
	 * @param $styles
	 *
	 * @return MC4WP_Styles_Builder
	 */
	public function build( $styles ) {
		// clean-up styles array
		$this->clean();

		// sanitize submitted styles
		$this->sanitize( $styles );

		// listen for user-triggered actions (delete, copy, ..)
		$this->act();

		// re-bundle
		$this->create_bundle();

		// return all styles (for WP options API)
		return $this->styles;
	}

	/**
	 * Act on user-triggered actions
	 */
	protected function act() {
		$form_id = (int) $_POST['form_id'];
        if( empty( $form_id ) ) {
            return;
        }

		// was delete button clicked?
		if( isset( $_POST['_mc4wp_delete_form_styles'] ) ) {
			$form_id_to_delete = absint( $_POST['_mc4wp_delete_form_styles'] );
			$this->delete_form_styles( $form_id_to_delete );
		} elseif( isset( $_POST['_mc4wp_copy_form_styles'] ) ) {
			$this->copy_form_styles( $_POST['copy_from_form_id'], $form_id );
		}

		// recreate stylesheet (with new values)
		if( ! defined( 'MC4WP_DOING_UPGRADE' ) ) {
			$this->delete_stylesheet( $form_id );
			$this->build_stylesheet( $form_id );
		}

	}

	/**
	 * @param int $form_id
	 *
	 * @return bool
	 */
	public function delete_form_styles( $form_id ) {
		if( isset( $this->styles['form-' . $form_id ] ) ) {
			unset( $this->styles['form-' . $form_id ] );
			return true;
		}

		return false;
	}

	/**
	 * @param int $from_id
	 * @param int $to_id
	 * @return bool
	 */
	public function copy_form_styles( $from_id, $to_id ) {
		if( isset( $this->styles['form-' . $from_id ] ) ) {
			$this->styles['form-' . $to_id ] = $this->styles['form-' . $from_id ];
			return true;
		}

		return false;
	}

	/**
	 * Get the default theme settings
	 *
	 * @return array
	 */
	protected function load_default_form_styles() {
		$default_form_styles = array();

		foreach( $this->fields as $key => $field ) {
			$default_form_styles[ $key ] = $field['default'];
		}

		return $default_form_styles;
	}

	/**
	 * Get all form themes, merged with defaults
	 *
	 * @return array
	 */
	protected function load_styles() {

		$all_styles = (array) get_option( 'mc4wp_form_styles', array() );

		if( empty( $all_styles ) ) {
			return array();
		}

		// merge all theme settings with the defaults array
		foreach( $all_styles as $form_id => $form_styles ) {
			$all_styles[ $form_id ] = array_merge( $this->default_form_styles, $form_styles );
		}

		// return merged array
		return $all_styles;
	}

	/**
	 * Get saved CSS values from option
	 *
	 * @param int $form_id
	 *
	 * @return array
	 */
	public function get_form_styles( $form_id = 0 ) {
		$form_styles = ( isset( $this->styles[ 'form-' . $form_id ] ) ) ? $this->styles[ 'form-' . $form_id ] : $this->default_form_styles;
		return $form_styles;
	}

	/**
	 * Clean complete $styles array, remove deleted forms..
	 *
	 * @return array
	 */
	protected function clean() {
		// clean-up existing form styles
		foreach( $this->styles as $form_id => $form_styles ) {
			// skip these styles if form no longer exists
			$form = get_post( substr( $form_id, 5 ) );
			if( ! $form instanceof WP_Post || $form->post_status !== 'publish' ) {
				unset( $this->styles[ $form_id ] );
			}
		}

		return $this->styles;
	}

	/**
	 * Validate the given CSS values according to their type
	 *
	 * @param $dirty_form_styles
	 *
	 * @return mixed
	 */
	protected function sanitize( $dirty_form_styles = array() ) {

		// start sanitizing new form styles
		foreach( $dirty_form_styles as $form_id => $new_form_styles ) {

			// start with empty array of styles
			$sanitized_form_styles = array();

			foreach( $new_form_styles as $key => $value ) {

				// skip field if it's not a valid field
				if( ! isset( $this->fields[ $key ] ) ) {
					continue;
				}

				// add field value to css array
				$sanitized_form_styles[ $key ] = $value;

				// skip if field is empty or has its default value
				if( '' === $value || $value === $this->fields[$key]['default'] ) {
					continue;
				}

				// sanitize field since it's not default
				$type = $this->fields[ $key ]['type'];
				$value = call_user_func( array( $this, 'sanitize_' . $type ), $value );

				// save css value
				$sanitized_form_styles[ $key ] = $value;
			}

			// save sanitized styles in array with all styles
			$this->styles[ $form_id ] = $sanitized_form_styles;
		}

		return $this->styles;
	}

	/**
	 * Sanitize color values
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function sanitize_color( $value ) {
		// make sure colors start with #
		return '#' . ltrim( trim( $value ), '#' );
	}

	/**
	 * Sanitize pixel value
	 *
	 * @param $value
	 *
	 * @return mixed|string
	 */
	public function sanitize_px( $value ) {
		// make sure px and % end with 'px' or '%'
		$value = str_replace( ' ', '', strtolower( $value ) );

		if( substr( $value, -1 ) !== '%' && substr( $value, -2 ) !== 'px') {
			$value = floatval( $value ) . 'px';
		}

		return $value;
	}

	/**
	 * Sanitize integer value
	 *
	 * @param $value
	 *
	 * @return int
	 */
	public function sanitize_int( $value ) {
		return intval( $value );
	}

	/**
	 * Sanitize CSS selector value
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function sanitize_selector( $value ) {
		return trim( $value ) . ' ';
	}

	/**
	 * Sanitize text value
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function sanitize_text( $value ) {
		return trim( $value );
	}

	/**
	 * Delete all stylesheets from the WP uploads dir
	 *
	 * @param int $form_id
	 * @return bool
	 */
	protected function delete_stylesheet( $form_id = 0 ) {

		$dir = $this->get_stylesheets_dir();

		// if form id not given, delete all .css files in directory
		if( ! $form_id ) {
			$stylesheets = glob( $dir . '/*.css');

			if( is_array( $stylesheets ) ) {
				// unlink all stylesheets
				array_map( 'unlink', $stylesheets );
			}

			return true;
		}

		// only unlink if file exists
        $filename = $dir . sprintf( '/form-%d.css', $form_id );
        if( file_exists( $filename ) ) {
            unlink( $filename );
        }

		return true;
	}

	/**
	 * Build file with given CSS values
	 *
	 * @param int $form_id
	 * @return bool
	 */
	protected function build_stylesheet( $form_id ) {

		$css_string = $this->get_css_string( $form_id );
		$dir = $this->get_stylesheets_dir();
		$filename = sprintf( '/form-%d.css', $form_id );
		$file = $dir . $filename;

		// try to create stylesheets dir with proper permissions
		if( ! file_exists( $dir ) ) {
			@mkdir( $dir, 0755 );
		}

		@chmod( $dir, 0755 );
		@chmod( $file, 0755 );

		// write css string to file
		$handle = fopen( $file, 'w' );
		$success = false;

		// show error when opening file for writing fails
		if( is_resource( $handle ) ) {
			$success = fwrite( $handle, $css_string );
			fclose( $handle );
		}

		// success should be > 0 now
		if( ! $success ) {
			$this->messages->flash( sprintf( 'Error writing stylesheet. File <code>%s</code> is not writable, please check your file permissions.', $file ), 'error' );
			return false;
		}

		// create url
		$url = $this->get_stylesheets_url( $filename );
		$message = '<strong>' . sprintf( __( '<a href="%s">CSS stylesheet</a> created.', 'mailchimp-for-wp'  ), $url ) . '</strong>';

		// check if stylesheet is being loaded for this form, otherwise show notice.
		$form = mc4wp_get_form( $form_id );
		if( $form->settings['css'] !== 'styles-builder' ) {
			$message .= '<br /><br />' . sprintf( __( 'Please note that you need to <a href="%s">select "Use Styles Builder" in the form appearance settings</a> if you want to use these styles.', 'mailchimp-for-wp' ), mc4wp_get_edit_form_url( $form_id, 'appearance' ) );
		}

		// add "back to form" link in notice
		$message .= '<br /><br />' . sprintf( '<a href="%s"> &laquo; ' . __( 'Back to form', 'mailchimp-for-wp' ) .'</a>', mc4wp_get_edit_form_url( $form_id ) );

		// show notice
		$this->messages->flash( $message, 'success' );
		return true;
	}

	/**
	 * Turns array of CSS values into CSS stylesheet string
	 *
	 * @return string
	 */
	protected function get_css_string( $form_id ) {

		// Build CSS String
		$css_string = '';
		ob_start();

		$form_styles = $this->styles[ 'form-' . $form_id ];
		$form_selector = '.mc4wp-form-' . $form_id;

		// Build CSS styles for this form
		extract( $form_styles );
		include dirname( __FILE__ ) . '/../views/css-styles.php';

		// get output buffer
		$css_string = ob_get_contents();
		ob_end_clean();

		return $css_string;
	}

	/**
	 * Checks whether a custom CSS rule value was set for this element
	 *
	 * @param $form_id
	 * @param $element_name
	 *
	 * @return bool
	 */
	public function form_has_rules_for_element( $form_id, $element_name ) {

		if( ! isset( $this->styles[ 'form-' . $form_id ] ) ) {
			return false;
		}

		// loop through all form styles
		foreach( $this->styles[ 'form-' . $form_id ] as $rule_name => $rule_value ) {

			// is this a rule for the given element?
			if( strpos( $rule_name, $element_name ) === 0 ) {

				// is this rule filled with a value?
				if( ! empty( $rule_value ) ) {
					return true;
				}
			}
		}

		// no filled rules for this element found
		return false;
	}

	/**
	 * @param $rule
	 * @param $value
	 */
	public function maybe_echo( $rule, $value ) {
		if( ! empty( $value ) ) {
			printf( $rule, $value );
		}
	}

	/**
	 * @return array
	 */
	public function get_enabled_form_ids() {
		// find all forms where "css" is set to "styles-builder"
		$forms = mc4wp_get_forms(array(
			'post_status' => array( 'publish', 'draft', 'pending', 'future' ),
		));
		$enabled_forms = array();

		foreach( $forms as $form ) {
			if( $form->settings['css'] === 'styles-builder' ) {
				$enabled_forms[] = $form->ID;
			}
		}

		return $enabled_forms;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function get_stylesheets_dir( $path = '' ) {
		$upload = wp_upload_dir();
		$dir = $upload['basedir'] . '/mc4wp-stylesheets';

		if( ! empty( $path ) ) {
			$dir .= '/' . trim( $path, '/' );
		}

		return $dir;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function get_stylesheets_url( $path = '' ) {
		$upload = wp_upload_dir( null, false );
		$url = $upload['baseurl'] . '/mc4wp-stylesheets';

		if( ! empty( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		return $url;
	}

	/**
	 * Bundle all activated stylesheets into a single "bundle.css" file.
	 */
	public function create_bundle() {

		// bail if none of the forms have Styles Builder styles enabled
		$enabled_form_ids = $this->get_enabled_form_ids();
		if( empty( $enabled_form_ids ) ) {
			return;
		}

		// find all stylesheet files created by Styles Builder
		$stylesheet_files = $this->get_stylesheet_files( $enabled_form_ids );
        if( empty( $stylesheet_files ) ) {
            return;
        }

        $dir = $this->get_stylesheets_dir();
		$filename = $dir . '/bundle.css';
		@chmod( $filename, 0755 );
		$handle = fopen( $filename, 'w' );

		// show error when opening file for writing fails
		if( ! is_resource( $handle ) || ! fwrite( $handle, '/* bundled styles */' . PHP_EOL ) ) {
			$message = sprintf( 'File <code>%s</code> is not writable. Please set the correct file permissions or manually include the styles on your site using a plugin like %s.', $filename, '<a href="https://wordpress.org/plugins/simple-custom-css/">Simple Custom CSS' );
			$message .= '<br /><br /><a href="#" onclick="this.nextSibling.style.display = \'\';">' . __( 'Show CSS', 'mailchimp-for-wp' ) . '</a>';

			$css_string = esc_html( join( PHP_EOL, array_map( 'file_get_contents', $stylesheet_files ) ) );
			$message .= '<textarea readonly style="display:none; width: 100%; min-height: 200px; margin-top: 20px; font-size: 13px; font-weight: normal; font-family: Monospace, Courier, consola;">'. $css_string .'</textarea><strong>';

			$this->messages->flash( $message, 'error' );
			return;
		}
		
		// write stylesheet files to bundle file
		foreach( $stylesheet_files as $stylesheet_file ) {
			$content = file_get_contents( $stylesheet_file );
			fwrite( $handle, $content );
			fwrite( $handle, PHP_EOL . PHP_EOL );
		}

		fclose( $handle );

		// store version as option (for cache busting)
		update_option( 'mc4wp_forms_styles_builder_version', time(), false );
	}


	/**
	 * @param array $form_ids
	 *
	 * @return array
	 */
	public function get_stylesheet_files( array $form_ids = array() ) {
		$dir = $this->get_stylesheets_dir();

		$stylesheet_files = array();
		foreach( $form_ids as $form_id ) {
			$stylesheet_file = $dir . '/' . sprintf( 'form-%d.css', $form_id );
			if( file_exists( $stylesheet_file ) ) {
				$stylesheet_files[] = $stylesheet_file;
			}
		}

		return $stylesheet_files;
	}

}