<?php

/**
 * Get field groups based on query arguments.
 *
 * @param array $args Following arguments are supported.
 *     - 'domain' (string): The only mandatory argument, must be 'posts'|'users'|'terms'.
 *     - 'types_search': String for extended search.
 *     - 'is_active' (bool): If defined, only active/inactive field groups will be returned.
 *     - 'assigned_to_post_type' string: For post field groups only, filter results by being assinged to a particular
 *     post type.
 *     - 'purpose' (string): See Toolset_Field_Group::get_purpose() for information about this argument.
 *        Default is Toolset_Field_Group::PURPOSE_GENERIC. Special value '*' will return groups of all purposes.
 *
 * @return \OTGS\Toolset\Common\PublicAPI\CustomFieldGroup[]
 * @since 3.4 (Types 3.3)
 * @throws \InvalidArgumentException On invalid input.
 */
function toolset_get_field_groups( $args ) {

	if ( ! apply_filters( 'types_is_active', false ) ) {
		return array();
	}

	$domain = toolset_getarr( $args, 'domain' );
	if ( ! in_array( $domain, Toolset_Element_Domain::all(), true ) ) {
		throw new \InvalidArgumentException( 'Invalid field group domain.' );
	}

	$field_group_factory = Toolset_Field_Group_Factory::get_factory_by_domain( $domain );
	$field_groups = $field_group_factory->query_groups( $args );

	return $field_groups;
}


/**
 * Retrieve a particular field group by its slug.
 *
 * @param string $group_slug Slug of the field group.
 * @param string $domain Domain of the field group, 'posts'|'users'|'terms'.
 *
 * @since 3.4 (Types 3.3)
 * @return \OTGS\Toolset\Common\PublicAPI\CustomFieldGroup|null
 * @throws \InvalidArgumentException On invalid input.
 */
function toolset_get_field_group( $group_slug, $domain ) {

	if ( ! apply_filters( 'types_is_active', false ) ) {
		return null;
	}

	if ( ! in_array( $domain, Toolset_Element_Domain::all(), true ) ) {
		throw new \InvalidArgumentException( 'Invalid field group domain.' );
	}

	$field_group_factory = Toolset_Field_Group_Factory::get_factory_by_domain( $domain );

	return $field_group_factory->load_field_group( $group_slug );

}
