<?php

namespace OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary;

use OTGS\Toolset\Types\Model\Post\Intermediary\Request;

/**
 * Class ResponseAssociationDelete
 * @package OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary
 *
 * @since 3.0
 */
class ResponseAssociationDelete implements IResponse {

	/**
	 * @var \Toolset_Association_Persistence
	 */
	private $association_persistence;

	/**
	 * ResponseAssociationSave constructor.
	 *
	 * @param \Toolset_Association_Persistence $association_persistence
	 */
	public function __construct( \Toolset_Association_Persistence $association_persistence ) {
		$this->association_persistence = $association_persistence;
	}

	/**
	 * @param Request $request
	 * @param Result $result
	 *
	 * @return Result|null
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function response( Request $request, Result $result ) {
		if( ! $association = $request->getAssociation() ) {
			// no association to delete
			return;
		}

		$child_id = $request->getChildId();
		$parent_id = $request->getParentId();
		if( ! empty( $child_id ) && ! empty( $parent_id ) ) {
			// parent and child are set
			return;
		}

		/* we have an association, but the user deselected parent or child -> delete association */
		add_filter( 'toolset_deleting_association_intermediary_post', function( $return, $post_id ) use( $association ) {
			if( $association->get_intermediary_id() == $post_id ) {
				// do not delete intermediary id
				return false;
			}

			// do nothing
			return $return;
		}, 10, 2 );

		$this->association_persistence->delete_association( $association );

		// success
		$result->setMessage( 'Association deleted.' );
		return $result;
	}
}