<?php

/**
 * Class Types_Shortcode_Factory
 *
 * @since 2.3
 */
class Types_Shortcode_Factory {

	/**
	 * @param $shortcode
	 *
	 * @return false|Types_Shortcode_Types_View
	 */
	public function get_shortcode( $shortcode ) {
		switch( $shortcode ) {
			case Types_Shortcode_Types::SHORTCODE_NAME:
				$relationship_service = new Toolset_Relationship_Service();
				$attr_item_chain = new Toolset_Shortcode_Attr_Item_M2M(
					new Toolset_Shortcode_Attr_Item_Legacy(
						new Toolset_Shortcode_Attr_Item_Id(),
						$relationship_service
					),
					$relationship_service
				);

				return new Types_Shortcode_Types_View(
					new Types_Shortcode_Types(
						new Toolset_Shortcode_Attr_Field(),
						$attr_item_chain
					)
				);
		}

		return false;
	}
}