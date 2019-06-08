<?php

namespace OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary;

use OTGS\Toolset\Types\Model\Post\Intermediary\Request;


/**
 * Class ResponseAssociationConflict
 * @package OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary
 *
 * @since 3.0
 */
class ResponseAssociationSave implements IResponse {

	/**
	 * @var \Toolset_Association_Persistence
	 */
	private $association_persistence;

	/**
	 * @var \Toolset_Association_Factory
	 */
	private $association_factory;


	/**
	 * ResponseAssociationSave constructor.
	 *
	 * @param \Toolset_Association_Persistence $association_persistence
	 * @param \Toolset_Association_Factory $association_factory
	 */
	public function __construct(
		\Toolset_Association_Persistence $association_persistence,
		\Toolset_Association_Factory $association_factory ) {
		$this->association_persistence = $association_persistence;
		$this->association_factory = $association_factory;
	}

	/**
	 * @param Request $request
	 * @param Result $result
	 *
	 * @return Result
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function response( Request $request, Result $result ) {
		if( ! $request->getParentId() || ! $request->getChildId() ) {
			// no association without parent and child
			return;
		}

		if( ! $relationship = $request->getRelationshipDefinition() ) {
			// should not happen, probably the DOM is invalid
			$result->setResult( $result::RESULT_DOM_ERROR );
			return $result;
		}


		if( $prev_association = $request->getAssociation() ) {
			// delete previous association
			add_filter( 'toolset_deleting_association_intermediary_post', function( $return, $post_id ) use( $prev_association ) {
				if( $prev_association->get_intermediary_id() == $post_id ) {
					// do not delete intermediary id
					return false;
				}

				// do nothing
				return $return;
			}, 10, 2 );

			$this->association_persistence->delete_association( $prev_association );
		}

		$intermediary = $request->getIntermediaryPost();

		$new_association = $this->association_factory
		                        ->create(
		                        	$relationship,
			                        $request->getParentId(),
			                        $request->getChildId(),
			                        $intermediary->get_id()
		                        );

		$this->association_persistence->insert_association( $new_association );

		$result->setMessage( 'New association stored.' );
		return $result;
	}
}