<?php

namespace MC4WP\Premium\AFTP;

use Exception;

class Plugin {

	private $settings;

	/**
	* @param array $settings
	*/ 
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function hook() {
		add_filter( 'mc4wp_settings', array( $this, 'global_settings' ) );
		add_filter( 'mc4wp_form_settings', array( $this, 'form_settings' ) );
		add_filter( 'the_content', array( $this, 'maybe_append_form' ) );
	}

	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	public function global_settings( $settings ) {
		$defaults = array(
			'append_to_posts' => array(),
		);
		$settings = array_merge( $defaults, $settings );
		return $settings;
	}

	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	public function form_settings( $settings ) {
		$defaults = array(
			'append_to_posts' => 0,
			'append_to_posts_category' => '',
		);
		$settings = array_merge( $defaults, $settings );
		return $settings;
	}

	/**
	* @var string $content
	*/ 
	public function maybe_append_form( $content ) {
		if( empty( $this->settings['append_to_posts'] ) ) {
			return $content;
		}

		if( ! is_singular( 'post' ) ) {
			return $content;
		}

		foreach( $this->settings['append_to_posts'] as $form_id => $category ) {
			// "0" corresponds to the "all categories" option
			if( $category === "0" || has_category( $category ) ) {

				// get form to make sure it still exists
				try{
					$form = mc4wp_get_form( $form_id );
				} catch( Exception $e ) {
					continue; // form was deleted
				}

				// add form shortcode to this post
				$content .= "\n" . sprintf( ' [mc4wp_form id="%d"]', $form_id );

				// stop looking for other forms if we have a match
				break;
			}
		}

		return $content;
	}	
}	
