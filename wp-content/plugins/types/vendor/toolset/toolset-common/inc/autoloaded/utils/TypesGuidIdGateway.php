<?php

namespace OTGS\Toolset\Common\Utils;

/**
 * Class TypesGuidIdGateway
 * This class makes sure the user can work without caring about Types is active or not.
 *
 * @package OTGS\Toolset\Common\Utils
 * @since Types 3.3
 */
class TypesGuidIdGateway {
	/** @var \WPCF_Guid_Id|false if Types is not active */
	private static $_wpcf_guid_id;

	/**
	 * Tries to get instance of \WPCF_Guid_Id.
	 * The result will be stored in the static $_wpcf_guid_id to make sure it only needs to be fetched once.
	 *
	 * @return false|\WPCF_Guid_Id
	 */
	private function get_wpcf_guid_id() {
		if ( null !== self::$_wpcf_guid_id ) {
			return self::$_wpcf_guid_id;
		}

		// Let's assume Types is not providing WPCF_Guid_Id until we know better.
		self::$_wpcf_guid_id = false;

		// \WPCF_Guid_Id not available -> check if types WPCF_EMBEDDED_INC_ABSPATH isset and load the image.php library
		// (it should already being loaded by Types at this point... just to be save for some future refactoring)
		if ( ! class_exists( '\WPCF_Guid_Id' )
			&& defined( 'WPCF_EMBEDDED_INC_ABSPATH' )
			&& file_exists( WPCF_EMBEDDED_INC_ABSPATH . '/fields/image.php' ) ) {
			require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/image.php';
		}

		// Another class exist check, just for the case the class did not exist and the file include also failed.
		if ( class_exists( '\WPCF_Guid_Id' ) ) {
			$wpcf_guid_id = \WPCF_Guid_Id::get_instance();
			if (
				method_exists( $wpcf_guid_id, 'get_id_by_guid' )
				&& method_exists( $wpcf_guid_id, 'insert' )
			) {
				// All required methods available.
				self::$_wpcf_guid_id = $wpcf_guid_id;
			}
		}

		// Return WPCF_Guid_Id instance OR false if class does not exist or does not provide the used methods anymore.
		return self::$_wpcf_guid_id;
	}

	/**
	 * Get post (attachment) id by url
	 *
	 * @param string $guid GUID of the post.
	 *
	 * @return bool|string|null
	 */
	public function get_id_by_guid( $guid ) {
		$wpcf_guid_id = $this->get_wpcf_guid_id();

		if ( ! $wpcf_guid_id ) {
			return false;
		}

		return $wpcf_guid_id->get_id_by_guid( $guid );
	}

	/**
	 * Insert/Update attachment url and id
	 *
	 * @param string $guid The GUID of the post.
	 * @param int    $post_id The ID of the post.
	 *
	 * @return bool|void
	 */
	public function insert( $guid, $post_id ) {
		$wpcf_guid_id = $this->get_wpcf_guid_id();

		if ( ! $wpcf_guid_id ) {
			return;
		}

		return $wpcf_guid_id->insert( $guid, $post_id );
	}
}
