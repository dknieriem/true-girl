<?php

namespace OTGS\Toolset\Common;

/**
 * Wrapper around core WordPress functions for easier access to post status information.
 *
 * @package OTGS\Toolset\Common
 */
class PostStatus {

	/**
	 * @param string[] $filter Array with filters to apply on the results. You can filter by properties of post statuses
	 *     like 'public' or 'publicly_queryable'.
	 * @param string $return slugs|names|objects. "names" will return display names indexed by slugs.
	 * @param string $compare and|or - comparison operator for individual $filter elements.
	 *
	 * @return array
	 */
	public function get_post_statuses( $filter, $return = 'slugs', $compare = 'and' ) {
		// Beware of get_post_stati() versus get_post_statuses()... omg why!
		$statuses = \get_post_stati( $filter, ( 'objects' === $return ? 'objects' : 'names' ), $compare );

		if( 'slugs' === $return ) {
			return array_keys( $statuses );
		}

		return $statuses;
	}


	/**
	 * Retrieve post statuses that are considered as "available" in the backend. Native ones fitting the
	 * description are publish, draft, pending and future. Further statuses can be added manually by
	 * the filter toolset_get_available_post_statuses.
	 *
	 * @return string[] An array of post status slugs.
	 */
	public function get_available_post_statuses() {
		$statuses = $this->get_post_statuses(
			array( 'protected' => true, 'public' => true, 'publicly_queryable' => true ),
			'slugs',
			'or'
		);

		/**
		 * toolset_get_available_post_statuses
		 *
		 * Manually adjust the array of post statuses that are considered "available".
		 *
		 * @param string[] $statuses An array of post status slugs.
		 * @return string[]
		 */
		$statuses = apply_filters( 'toolset_get_available_post_statuses', $statuses );

		return $statuses;
	}

}