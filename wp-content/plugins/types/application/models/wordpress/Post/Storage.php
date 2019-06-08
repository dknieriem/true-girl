<?php

namespace OTGS\Toolset\Types\Wordpress\Post;

/**
 * Class Storage
 *
 * Gives some extra functioniality regarding loading/manipulating posts.
 *
 * @package OTGS\Toolset\Types\Wordpress\Post
 */
class Storage {

	/** @var \wpdb */
	private $wpdb;

	/**
	 * Storage constructor.
	 *
	 * @param \wpdb $wpdb
	 */
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Get post by id
	 *
	 * @param $id
	 *
	 * @return \WP_Post|null
	 */
	public function getPostById( $id ) {
		return get_post( $id );
	}

	/**
	 * Get post by GUID
	 * @param $guid
	 *
	 * @return \WP_Post|null
	 */
	public function getPostByGUID( $guid ) {
		$wpdb = $this->wpdb;

		// On creation WP uses &#038; for &, but on WP Export/Import & becomes &amp;
		$guid_amp = str_replace( '&#038;', '&amp;', $guid );
		$guid_038 = str_replace( '&amp;', '&#038;', $guid );
		$guid_no_html = html_entity_decode( $guid );

		$post_id = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT ID 
				FROM $wpdb->posts 
				WHERE guid LIKE %s 
				OR guid LIKE %s
				OR guid LIKE %s
				OR guid LIKE %s
				LIMIT 1",
				sanitize_text_field( $guid ),
				sanitize_text_field( $guid_amp ),
				sanitize_text_field( $guid_038 ),
				sanitize_text_field( $guid_no_html )
			)
		);

		if( ! $post_id ) {
			// no id by the guid found...
			return null;
		}

		$post = get_post( $post_id );

		if( ! $post ) {
			// this would be really strange after we successfully get the ID
			return null;
		}

		return $post;
	}

	/**
	 * Get post by title
	 *
	 * @param $title
	 * @param string $post_type
	 *
	 * @return null|\WP_Post
	 */
	public function getPostByTitle( $title, $post_type = 'page' ) {
		return get_page_by_title( $title, OBJECT, $post_type );
	}


	/**
	 * Wrapper for wp_delete_post()
	 *
	 * @param $post_id
	 */
	public function deletePostById( $post_id ) {
		wp_delete_post( $post_id );
	}
}