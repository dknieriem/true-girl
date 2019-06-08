<?php

use OTGS\Toolset\Common\M2M\PotentialAssociation as potentialAssociation;

/**
 * Factory for IToolset_Potentional_Association_Query.
 *
 * Detects the target domain and returns the proper factory instance.
 *
 * @since m2m
 */
class Toolset_Potential_Association_Query_Factory {

	/**
	 * @param IToolset_Relationship_Definition $for_relationship
	 * @param IToolset_Relationship_Role $for_role
	 * @param IToolset_Element $for_element
	 * @param array $args
	 *
	 * @return IToolset_Potential_Association_Query
	 * @throws RuntimeException
	 */
	public function create(
		IToolset_Relationship_Definition $for_relationship,
		IToolset_Relationship_Role $for_role,
		IToolset_Element $for_element,
		$args = array()
	) {
		$target_domain = $for_relationship->get_element_type( $for_role->get_name() )->get_domain();

		switch( $target_domain ) {
			case Toolset_Element_Domain::POSTS:
				return new Toolset_Potential_Association_Query_Posts(
					$for_relationship, $for_role, $for_element, $args,
					null, null, $this
				);
				break;
			default:
				throw new RuntimeException( 'Not implemented.' );
		}
	}


	public function create_join_manager(
		IToolset_Relationship_Definition $relationship,
		IToolset_Relationship_Role_Parent_Child $target_role,
		IToolset_Element $for_element
	) {
		return new potentialAssociation\JoinManager(
			$relationship, $target_role, $for_element
		);
	}
}