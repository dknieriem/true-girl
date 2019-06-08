<?php

namespace OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary;

use OTGS\Toolset\Types\Model\Post\Intermediary\Request;

/**
 * Class ResponseAssociationMissingData
 * @package OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary
 *
 * @since 3.0
 */
class ResponseAssociationMissingData implements IResponse {

	/**
	 * @param Request $request
	 * @param Result $result
	 *
	 * @return Result|null
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function response( Request $request, Result $result ) {
		$child_id = $request->getChildId();
		$parent_id = $request->getParentId();

		if( ! $request->getAssociation()
		    && ( empty( $parent_id ) || empty( $child_id ) )
		) {
			// no assocation and no parent and child selected
			$result->setMessage( 'Missing data.' );
			return $result;
		}

		return;
	}
}