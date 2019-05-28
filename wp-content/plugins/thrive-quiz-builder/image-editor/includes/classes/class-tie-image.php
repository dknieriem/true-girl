<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-image-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Image {

	private $post_id = null;

	/**
	 * @var TIE_Image_Settings
	 */
	private $settings;

	private $post_parent;

	/**
	 * TIE_Image constructor.
	 *
	 * @param WP_Post|int $post
	 */
	public function __construct( $post ) {

		if ( $post instanceof WP_Post ) {
			$this->post_id     = $post->ID;
			$this->post_parent = $post->post_parent;
		} else {
			$this->post_id = intval( $post );
		}
	}

	public function save_content( $content ) {
		if ( ! $this->_can_save() ) {
			return false;
		}

		update_post_meta( $this->post_id, 'tie_image_content', $content );

		return true;
	}

	/**
	 * Saves the html canvas in meta
	 *
	 * @param $html_canvas
	 *
	 * @return bool
	 */
	public function save_html_canvas( $html_canvas ) {
		if ( ! $this->_can_save() ) {
			return false;
		}

		$html_canvas    = preg_replace( '@\>\s{1,}\<@', '><', $html_canvas );// remove spaces between tags
		$replace_params = array(
			'tie-canvas-overlay',
			'tie-canvas',
			'ui-droppable',
			'tie-element-actions',
			'tie-element',
			'ui-draggable',
			'tie-editable',
			'mce-content-body',
			'mce_0'
		);
		$html_canvas    = str_replace( $replace_params, '', $html_canvas );

		update_post_meta( $this->post_id, 'tie_html_canvas_content', $html_canvas );

		return true;
	}

	/**
	 * Returns the image URL stored in WordPress uploads thrive-quiz-builder folder
	 *
	 * @return null|string
	 */
	public function get_image_url() {
		if ( ! $this->post_parent ) {
			$post              = get_post( $this->post_id );
			$this->post_parent = $post->post_parent;
		}

		$upload_dir = wp_upload_dir();
		$file_path  = $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $this->post_parent . '.png';

		if ( is_file( $file_path ) ) {
			$file_url = $upload_dir['baseurl'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER . '/' . $this->post_parent . '.png' . '?' . rand();

			return $file_url;
		}

		return null;
	}

	/**
	 * Generates and saves the image to WordPress uploads thrive-quiz-builder folder
	 *
	 * @param $file_content
	 */
	public function save_file( $file_content ) {
		//todo: secure this saving
		$post      = get_post( $this->post_id );
		$img       = str_replace( 'data:image/png;base64,', '', $file_content );
		$file_data = base64_decode( $img );


		$old_umask = umask( 0 );

		/*Upload to WordPress uploads*/
		$upload_dir = wp_upload_dir();
		$base       = $upload_dir['basedir'] . '/' . Thrive_Quiz_Builder::UPLOAD_DIR_CUSTOM_FOLDER;

		if ( ! is_dir( $base ) ) {
			mkdir( $base, 0777 );
		}

		$file_name = $base . '/' . $post->post_parent . '.png';

		$update_results_pages = false;
		if ( ! is_file( $file_name ) ) {
			$update_results_pages = true;
		}

		@unlink( $file_name );
		file_put_contents( $file_name, $file_data );

		umask( $old_umask );

		if ( $update_results_pages ) {
			$image_url = $this->get_image_url();
			do_action( 'tqb_update_social_share_badge_url', $post->post_parent, $image_url, null );
		}
	}

	public function get_content() {
		return get_post_meta( $this->post_id, 'tie_image_content', true );
	}

	public function get_html_canvas_content() {
		return get_post_meta( $this->post_id, 'tie_html_canvas_content', true );
	}

	/**
	 * @return TIE_Image_Settings
	 */
	public function get_settings() {
		if ( ! ( $this->settings instanceof TIE_Image_Settings ) ) {
			$this->settings = new TIE_Image_Settings( $this->post_id );
		}

		return $this->settings;
	}

	public function get_canvas_style() {
		$style = 'background-repeat: no-repeat;';
		if ( ! ( $this->get_settings() instanceof TIE_Image_Settings ) ) {
			return $style;
		}
		$style .= "width: {$this->get_settings()->get_data('size/width')}px;";
		$style .= "height: {$this->get_settings()->get_data('size/height')}px;";

		if ( $this->get_settings()->get_data( 'background_image/url' ) !== 'none' ) {
			$style .= "background-image: url('{$this->get_settings()->get_data('background_image/url')}');";
		}

		$style .= "background-size: {$this->get_settings()->get_data('background_image/size')};";
		$style .= "background-position: {$this->get_settings()->get_data('background_image/position')};";

		return $style;
	}

	public function get_overlay_style() {
		$style = 'height: 100%;';
		if ( ! ( $this->get_settings() instanceof TIE_Image_Settings ) ) {
			return $style;
		}
		$_opacity = intval( $this->get_settings()->get_data( 'overlay/opacity' ) );
		if ( $_opacity > 1 ) {
			$_opacity = $_opacity / 100;
		}
		$style .= "background-color: {$this->get_settings()->get_data('overlay/bg_color')};";
		$style .= "opacity: {$_opacity};";

		return $style;
	}

	public function print_fonts() {
		$fonts = $this->get_settings()->get_data( 'fonts' );

		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $font_name => $font_url ) {
				printf( '<link rel="stylesheet" class="tie-loaded-google-fonts" data-family="%s" type="text/css" href="%s">' . "\n", $font_name, $font_url );
			}
		}
	}

	private function _can_save() {
		return is_numeric( $this->post_id ) && $this->post_id > 0;
	}
}
