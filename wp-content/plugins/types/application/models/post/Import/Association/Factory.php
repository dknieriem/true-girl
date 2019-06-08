<?php

namespace OTGS\Toolset\Types\Post\Import\Association;

use OTGS\Toolset\Types\Post\Import\Association;

/**
 * Class Factory
 * @package OTGS\Toolset\Types\Post\Import\Association
 *
 * @since 3.0
 */
class Factory {

	/**
	 * @param $meta_post_id
	 * @param $meta_key
	 * @param $meta_association_string
	 * @param \WP_Post $child
	 * @param \Toolset_Relationship_Definition|null $relationship
	 * @param null $relationship_slug
	 * @param \WP_Post|null $parent
	 * @param null $parent_guid
	 * @param \WP_Post|null $intermediary
	 * @param null $intermediary_guid
	 * @param \IToolset_Association|null $association
	 *
	 * @return Association
	 */
	public function createAssociation(
		$meta_post_id,
		$meta_key,
		$meta_association_string,
		\WP_Post $child = null,
		\Toolset_Relationship_Definition $relationship = null,
		$relationship_slug = null,
		\WP_Post $parent = null,
		$parent_guid = null,
		\WP_Post $intermediary = null,
		$intermediary_guid = null,
		\IToolset_Association $association = null
	) {
		$associationImport = new Association();

		// Set Meta data of Association
		$associationImport->setMetaPostId( $meta_post_id );
		$associationImport->setMetaKey( $meta_key );
		$associationImport->setMetaAssociationString( $meta_association_string );

		// Child
		if( $child ){
			$associationImport->setChild( $child );
		} else {
			// no child... system error
			$associationImport->setHasMissingData( true );
		}

		// Relationship
		if( $relationship ) {
			$associationImport->setRelationship( $relationship );
		} elseif( $relationship_slug ) {
			// relationshiop could not be found by the given relationship slug
			$associationImport->setRelationship( $relationship_slug );
			$associationImport->setHasMissingData( true );
		} else {
			// no relationship slug... system error
			$associationImport->setHasMissingData( true );
		}

		// Parent
		if( $parent ){
			$associationImport->setParent( $parent );
		} elseif( $parent_guid ) {
			// parent could not be found by the GUID
			$associationImport->setParent( $parent_guid );
			$associationImport->setHasMissingData( true );
		} else {
			// no parent... system error
			$associationImport->setHasMissingData( true );
		}

		// Intermediary
		if( $intermediary ) {
			$associationImport->setIntermediary( $intermediary );
		} elseif( $intermediary_guid ) {
			// there should be an intermediary but it could be found by the GUID
			$associationImport->setIntermediary( $intermediary_guid );
			$associationImport->setHasMissingData( true );
		}

		if( $association ) {
			// association is already imported
			$associationImport->setIsAlreadyImported( true );
		}

		return $associationImport;
	}
}