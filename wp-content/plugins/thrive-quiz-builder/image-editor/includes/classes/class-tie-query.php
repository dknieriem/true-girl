<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TIE_Query {

	public $query_vars = array();

	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		$this->init_query_vars();
	}

	public function init_query_vars() {
		$this->query_vars = array(
			Thrive_Image_Editor::EDITOR_FLAG => Thrive_Image_Editor::EDITOR_FLAG,
		);
	}

	public function add_query_vars( $vars ) {
		foreach ( $this->query_vars as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;
	}

	public function get_query_vars() {
		return $this->query_vars;
	}

	public function get_var( $key ) {

		if ( ! in_array( $key, $this->query_vars ) ) {
			return null;
		}

		global $wp;

		return isset( $wp->query_vars[ $key ] ) ? $wp->query_vars[ $key ] : null;
	}
}
