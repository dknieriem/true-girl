<?php
/**
 * Utils class.
 *
 * @since 1.3.6
 *
 * @package OMAPI
 * @author  Justin Sternberg
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utils class.
 *
 * @since 1.3.6
 */
class OMAPI_Utils {

	/**
	 * Determines if given type is an inline type.
	 *
	 * @since  1.3.6
	 *
	 * @param  string $type Type to check
	 *
	 * @return boolean
	 */
	public static function is_inline_type( $type ) {
		return 'post' === $type || 'inline' === $type;
	}

	public static function item_in_field( $item, $fields, $field ) {
		return $item
			&& is_array( $fields )
			&& ! empty( $fields[ $field ] )
			&& in_array( $item, (array) $fields[ $field ] );
	}

	public static function field_not_empty_array( $fields, $field ) {
		if ( empty( $fields[ $field ] ) ) {
			return false;
		}

		$values = array_values( (array) $fields[ $field ] );
		$values = array_filter( $values );

		return ! empty( $values ) ? $values : false;
	}

	/**
	 * WordPress utility functions.
	 */

	public static function is_front_or_search() {
		return is_front_page() || is_home() || is_search();
	}

	public static function is_term_archive( $term_id, $taxonomy ) {
		if ( ! $term_id ) {
			return false;
		}
		return 'post_tag' === $taxonomy && is_tag( $term_id ) || is_tax( $taxonomy, $term_id );
	}

}
