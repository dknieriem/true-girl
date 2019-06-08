<?php

namespace OTGS\Toolset\Common\Utils;

/**
 * Attachment-related utilities.
 *
 * @since Types 3.3
 */
class Attachments {

	/** @var TypesGuidIdGateway */
	private $types_guid_id_gateway;

	/**
	 * Attachments constructor.
	 *
	 * @param TypesGuidIdGateway $types_guid_id_proxy
	 */
	public function __construct( TypesGuidIdGateway $types_guid_id_proxy ) {
		$this->types_guid_id_gateway = $types_guid_id_proxy;
	}

	/**
	 * Return an ID of an attachment by searching the database with the file URL.
	 *
	 * First checks to see if the $url is pointing to a file that exists in
	 * the wp-content directory. If so, then we search the database for a
	 * partial match consisting of the remaining path AFTER the wp-content
	 * directory. Finally, if a match is found the attachment ID will be
	 * returned.
	 *
	 * Taken from:
	 *
	 * @link http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
	 *
	 * @param string $url URL of the file.
	 *
	 * @return int|null Attachment ID if it exists.
	 * @since 2.2.9
	 * @since Types 3.3 extracted to a separate class.
	 */
	public function get_attachment_id_by_url( $url ) {
		// Split the $url into two parts with the wp-content directory as the separator.
		$parsed_url = explode( wp_parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

		// Get the host of the current site and the host of the $url, ignoring www.
		$this_host = str_ireplace( 'www.', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$file_host = str_ireplace( 'www.', '', wp_parse_url( $url, PHP_URL_HOST ) );

		// Return nothing if there aren't any $url parts or if the current host and $url host do not match.
		$attachment_path = toolset_getarr( $parsed_url, 1 );
		if ( ! isset( $attachment_path ) || empty( $attachment_path ) || ( $this_host !== $file_host ) ) {
			return null;
		}

		// try to fetch id by using our toolset_post_guid_id table
		$post_id = $this->types_guid_id_gateway->get_id_by_guid( $url );
		if ( $post_id ) {
			return $post_id;
		}

		global $wpdb;

		// Check for the guid with the exact url
		// (will match in most cases and is way faster than partial match search)
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$query = $wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE guid = %s LIMIT 1",
			$url
		);

		if ( $attachment_id = $wpdb->get_var( $query ) ) {
			// attachment id found
			$this->types_guid_id_gateway->insert( $url, $attachment_id );
			return (int) $attachment_id;
		}

		// No match for the full $url. Checking for any attachment GUID with a partial path match.
		// Example: /uploads/2013/05/test-image.jpg
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$query = $wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid LIKE %s LIMIT 1",
			'%' . $attachment_path
		);

		if ( $attachment_id = $wpdb->get_var( $query ) ) {
			// attachment id found
			$this->types_guid_id_gateway->insert( $url, $attachment_id );
			return (int) $attachment_id;
		}

		return null;
	}

}
