<?php

/**
 * Interface Types_Wordpress_Media_Interface
 *
 * @since 2.3
 */
interface Types_Wordpress_Media_Interface {
	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	public function get_attachment_id_by_url( $url );

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get_attachment_by_id( $id );

	/**
	 * @param null $size
	 *
	 * @return mixed
	 */
	public function get_addtional_image_sizes( $size = null );
}