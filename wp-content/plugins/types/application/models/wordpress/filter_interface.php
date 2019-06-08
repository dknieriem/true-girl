<?php

/**
 * Interface Types_Wordpress_Filter_Interface
 *
 * @since 2.3
 */
interface Types_Wordpress_Filter_Interface {
	/**
	 * @param $tag
	 *
	 * @return mixed
	 */
	public function filter_state_store( $tag );

	/**
	 * @param $tag
	 *
	 * @return mixed
	 */
	public function filter_state_restore( $tag );

	/**
	 * @param $content
	 *
	 * @return mixed
	 */
	public function filter_wysiwyg( $content );
}