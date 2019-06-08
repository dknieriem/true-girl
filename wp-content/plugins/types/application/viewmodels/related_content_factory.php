<?php

/**
 * Factory for viewmodels of related content, for the purposes of the Edit pages.
 *
 * @since m2m
 */
class Types_Viewmodel_Related_Content_Factory {


	/**
	 * For a given field domain, return the appropriate related content factory instance.
	 *
	 * @param string                          $role Relationship element role.
	 * @param Toolset_Relationship_Definition $relationship The relationship.
	 *
	 * @return Types_Viewmodel_Related_Content
	 * @throws RuntimeException When the domains is incorrect.
	 * @since m2m
	 */
	public static function get_model_by_relationship( $role, $relationship ) {
		// TODO in a future must admin different domains.
		switch ( $relationship->get_domain( $role ) ) {
			case Toolset_Field_Utils::DOMAIN_POSTS:
				return new Types_Viewmodel_Related_Content_Post( $role, $relationship );
			default:
				throw new RuntimeException( 'Not implemented.' );
		}
	}
}
