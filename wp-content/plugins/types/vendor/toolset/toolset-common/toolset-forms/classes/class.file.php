<?php

/**
 *
 *
 */
require_once 'class.textfield.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_File extends WPToolset_Field_Textfield {

	protected $_validation = array( 'required' );

	/**
	 * @var int The ID of the current post to attach files to
	 */
	private $parent_id = 0;

	//protected $_defaults = array('filename' => '', 'button_style' => 'btn2');

	public function init() {
		WPToolset_Field_File::file_enqueue_scripts();
		$this->set_current_parent_id();
		$this->set_placeholder_as_attribute();
	}

	public static function file_enqueue_scripts() {
		wp_register_script(
			'wptoolset-field-file',
			WPTOOLSET_FORMS_RELPATH . '/js/file-wp35.js',
			array(
				Toolset_Assets_Manager::SCRIPT_TOOLSET_MEDIA_FIELD_PROTOTYPE,
			),
			WPTOOLSET_FORMS_VERSION,
			true
		);

		if ( ! wp_script_is( 'wptoolset-field-file', 'enqueued' ) ) {
			wp_enqueue_script( 'wptoolset-field-file' );
		}

		// Note: we check whether the current_screen action has been fired because sometimes
		// some plugins might perform somethign that loads this field before get_current_screen() is defined.
		// This happens with an image widget module of Jetpack, for example.
		if (
			Toolset_Utils::is_real_admin()
			&& did_action( 'current_screen' ) > 0
		) {
			$screen = get_current_screen();
			if ( isset( $screen->parent_base ) && 'users' == $screen->parent_base ) {
				wp_enqueue_media();
			}

			if ( isset( $screen->post_type ) && isset( $screen->base ) && 'post' == $screen->base ) {
				global $post;
				if ( is_object( $post ) ) {
					wp_enqueue_media( array( 'post' => $post->ID ) );
				}
			}
		}
	}

	/**
	 * Calculate the current post ID so media instances are binded to the right parent.
	 *
	 * @since 3.3.9
	 */
	private function set_current_parent_id() {
		if (
			Toolset_Utils::is_real_admin()
			&& did_action( 'current_screen' ) > 0
		) {
			$screen = get_current_screen();

			if ( isset( $screen->post_type ) && isset( $screen->base ) && 'post' == $screen->base ) {
				global $post;
				if ( is_object( $post ) ) {
					$this->parent_id = $post->ID;
				}
			}
		}
	}

	public function enqueueStyles() {

	}

	/**
	 *
	 * @global object $wpdb
	 *
	 */
	public function metaform() {
		$value = $this->getValue();
		$type = $this->getType();
		$form = array();
		$preview = '';
		$wpml_action = $this->getWPMLAction();

		// Get attachment by guid
		if ( ! empty( $value ) && is_string( $value ) ) {
			global $wpdb;
			$attachment_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid=%s", $value
				)
			);
		}

		// Set preview
		if ( ! empty( $attachment_id ) ) {
			$attributes = array();
			$full = wp_get_attachment_image_src( $attachment_id, 'full' );
			if ( ! empty( $full ) ) {
				$attributes['data-full-src'] = esc_attr( $full[0] );
			}
			$preview = wp_get_attachment_image( $attachment_id, 'thumbnail', false, $attributes );
		} else {
			// If external image set preview
			while( is_array( $value ) ) {
				// repeatable file field
				$value = reset( $value );
			}
			$file_path = parse_url( $value );
			if ( $file_path && isset( $file_path['path'] ) ) {
				$file = pathinfo( $file_path['path'] );
			} else {
				$file = pathinfo( $value );
			}
			if (
				isset( $file['extension'] ) && in_array( strtolower( $file['extension'] ), array(
					'jpg',
					'jpeg',
					'gif',
					'png',
				) )
			) {
				$preview = '<img alt="" src="' . $value . '" />';
			}
		}

		$wpcf_wpml_condition = defined( 'WPML_TM_VERSION' ) &&
			intval( $wpml_action ) === 1 &&
			function_exists( 'wpcf_wpml_post_is_original' ) &&
			function_exists( 'wpcf_wpml_have_original' ) &&
			! wpcf_wpml_post_is_original() &&
			wpcf_wpml_have_original();

		$button_status = '';
		if (
			Toolset_Utils::is_real_admin() &&
			$wpcf_wpml_condition
		) {
			$button_status = ' disabled="disabled"';
		}

		$meta_data = array(
			'metakey' => $this->getName(),
			'title' => $this->getTitle(),
			'parent' => $this->parent_id,
			'type' => $type,
			'multiple' => $this->isRepetitive(),
			'preview' => '',
			'select_label' => $this->get_select_label(),
			'edit_label' => $this->get_edit_label(),
		);

		$button = sprintf(
			'<button class="js-wpt-file-upload js-toolset-media-field-trigger button button-small button-secondary" data-meta=\'%s\' data-wpt-type="%s" %s>%s</button>',
			esc_attr( wp_json_encode( $meta_data ) ),
			$type,
			$button_status,
			( empty( $value ) ? $this->get_select_label() : $this->get_edit_label() )
		);

		// Set form
		$form[] = array(
			'#type' => 'markup',
			'#markup' => '<div class="js-wpt-file-preview wpt-file-preview">' . $preview . '</div>',
		);

		$form[] = array(
			'#type' => 'textfield',
			'#name' => $this->getName(),
			'#title' => $this->getTitle(),
			'#description' => $this->getDescription(),
			'#value' => $value,
			'#suffix' => '&nbsp;' . $button,
			'#validate' => $this->getValidationData(),
			'#repetitive' => $this->isRepetitive(),
			'#attributes' => $this->getAttr(),
			'wpml_action' => $wpml_action,
		);

		return $form;
	}

	/**
	 * Get the default label for the Media Manager button when selecting a value.
	 *
	 * @return string
	 *
	 * @since 3.3
	 */
	protected function get_select_label() {
		if ( $this->isRepetitive() ) {
			return __( 'Select file(s)', 'wpv-views' );
		} else {
			return __( 'Select file', 'wpv-views' );
		}
	}

	/**
	 * Get the default label for the Media Manager button when editing a value.
	 *
	 * @return string
	 *
	 * @since 3.3
	 */
	protected function get_edit_label() {
		return __( 'Replace file', 'wpv-views' );
	}

}
