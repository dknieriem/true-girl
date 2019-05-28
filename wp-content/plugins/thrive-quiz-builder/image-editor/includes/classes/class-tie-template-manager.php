<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Template_Manager {

	private $_templates = null;

	private function _get_templates() {

		$templates      = array();
		$templates_path = tie()->path( '/includes/templates/images/' );
		$system_dirs    = array(
			'.',
			'..'
		);

		$handle = opendir( $templates_path );
		while ( false !== ( $entry = readdir( $handle ) ) ) {
			if ( ! in_array( $entry, $system_dirs ) && is_dir( $templates_path . $entry ) ) {
				$templates[ $entry ] = array();
			}
		}
		closedir( $handle );

		ksort($templates);

		foreach ( $templates as $key => $config ) {
			$config_path = $templates_path . $key . '/_config.php';

			if ( ! is_file( $config_path ) ) {
				continue;
			}


			$tpl_config = include( $templates_path . $key . '/_config.php' );

			/**
			 * set content
			 */
			$content_file_path = $templates_path . $key . '/content.phtml';
			if ( is_file( $content_file_path ) ) {//form template directory
				$content = include( $content_file_path );

			} else if ( isset( $tpl_config['content'] ) ) {//from custom config
				$content = include( $templates_path . $key . '/' . $tpl_config['content'] );

			} else { //from blank template
				$blank_content_file_path = $templates_path . 'blank/content.phtml';
				$content                 = is_file( $blank_content_file_path ) ? include( $blank_content_file_path ) : '';
			}

			/**
			 * set thumb
			 */
			$thumb_file_path = $templates_path . $key . '/thumb.jpg';
			if ( is_file( $thumb_file_path ) ) {
				$tpl_config['thumb'] = tie()->url( 'includes/templates/images/' . $key . '/thumb.jpg' );

			} else if ( isset( $tpl_config['thumb'] ) ) {
				$tpl_config['thumb'] = tie()->url( 'includes/templates/images/' . $key . '/' . $tpl_config['thumb'] );
			}

			/**
			 * set bg image
			 */
			$bg_file_path = $templates_path . $key . '/bg.jpg';
			if ( is_file( $bg_file_path ) ) {
				$tpl_config['settings']['background_image']['url'] = tie()->url( 'includes/templates/images/' . $key . '/bg.jpg' );

			} else if ( isset( $tpl_config['settings']['bg_image_url'] ) ) {
				$tpl_config['settings']['background_image']['url'] = tie()->url( 'includes/templates/images/' . $key . '/' . $tpl_config ['settings'] ['bg_image_url'] );
			}

			$tpl_config['content']              = $content;
			$tpl_config['settings']['template'] = $key;

			$templates[ $key ] = $tpl_config;
		}

		$tpls = array();
		foreach ( $templates as $key => $config ) {
			$config['key'] = $key;
			$tpls[]        = $config;
		}

		return $tpls;
	}

	public function get_templates() {
		if ( $this->_templates === null ) {
			$this->_templates = $this->_get_templates();
		}

		return $this->_templates;
	}

	public function get_template( $template_key ) {
		if ( ! $this->template_exists( $template_key ) ) {
			return null;
		}

		$found = array();
		foreach ( $this->get_templates() as $template ) {
			if ( $template['key'] === $template_key ) {
				$found = $template;
				break;
			}
		}

		return $found;
	}

	public function template_exists( $template_name ) {
		foreach ( $this->get_templates() as $template ) {
			if ( $template['key'] === $template_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Read the template settings from template config file
	 * Initialize a new TIE_Image and update its settings
	 *
	 * @param $image_id
	 * @param $template_key
	 *
	 * @return bool
	 */
	public function set_template( $image_id, $template_key ) {
		$template = $this->get_template( $template_key );

		$image = new TIE_Image( $image_id );

		$image->save_content( $template['content'] );

		return $image->get_settings()->save( $template['settings'] );
	}
}
