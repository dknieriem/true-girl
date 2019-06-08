<?php

namespace OTGS\Toolset\Types\PostType\Part;

/**
 * Abstract APostTypePart
 *
 * @package OTGS\Toolset\Types\PostType\Part
 *
 * @since 3.2
 */
abstract class APostTypePart implements IPostTypePart {

	/**
	 * Unique slug of the CPT
	 * @var string
	 */
	private $slug;

	/**
	 * @param $slug
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $slug ) {
		if( ! is_string( $slug ) && ! is_integer( $slug ) ) {
			throw new \InvalidArgumentException( 'slug must be a string or integer' );
		}

		$this->slug = $slug;
	}

	/**
	 * The slug of the CPT the Part belongs to.
	 *
	 * @return mixed
	 */
	public function get_cpt_slug() {
		return $this->slug;
	}
}