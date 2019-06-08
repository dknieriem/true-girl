<?php

namespace OTGS\Toolset\Types\Page\Extension\RelatedContent;


/**
 * Factory for Types_Page_Extension_Related_Content_Direct_Edit_Status to allow dependency injection and unit testing.
 *
 * @package OTGS\Toolset\Types\Page\Extension\RelatedContent
 * @since 3.1.1
 */
class DirectEditStatusFactory {


	/**
	 * @param int|\IToolset_Association $association
	 * @param null|int $user_id
	 *
	 * @return \Types_Page_Extension_Related_Content_Direct_Edit_Status
	 */
	public function create( $association, $user_id = null ) {
		return new \Types_Page_Extension_Related_Content_Direct_Edit_Status( $association, $user_id );
	}


}