<?php

/**
 * Class WPV_Shortcode_Factory
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Factory {
	
	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	private $attr_item_chain;
	
	public function __construct(
		Toolset_Shortcode_Attr_Item_M2M $attr_item_chain
	) {
		$this->attr_item_chain = $attr_item_chain;
	}

	/**
	 * @param $shortcode
	 *
	 * @return false|WPV_Shortcode_Interface_View
	 *
	 * @since 2.5.0
	 */
	public function get_shortcode( $shortcode ) {
		switch( $shortcode ) {
			case WPV_Shortcode_Post_Link::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Link( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Link_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Title::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Title( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Title_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Url::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Url( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Url_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Body::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Body( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Body_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Excerpt::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Excerpt( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Excerpt_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Date::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Date( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Date_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Author::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Author( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Author_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Featured_Image::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Featured_Image( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Featured_Image_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Id::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Id( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Id_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Slug::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Slug( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Slug_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Type::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Type( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Type_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Format::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Format( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Format_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Status::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Status( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Status_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Comments_Number::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Comments_Number( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Comments_Number_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Class::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Class( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Class_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Edit_Link::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Edit_Link( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Edit_Link_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Menu_Order::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Menu_Order( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Menu_Order_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Field::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Field( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Field_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Field_Iterator::SHORTCODE_NAME_ALIAS:
				$shortcode_object = new WPV_Shortcode_Post_Field_Iterator( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Field_Iterator_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Next_Link::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Next_Link( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Next_Link_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Previous_Link::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Previous_Link( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Previous_Link_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Post_Taxonomy::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_Post_Taxonomy( $this->attr_item_chain );
				$shortcode_gui = new WPV_Shortcode_Post_Taxonomy_GUI();
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			
			
			case WPV_Shortcode_Control_Post_Relationship::SHORTCODE_NAME:
			case WPV_Shortcode_Control_Post_Relationship::SHORTCODE_NAME_ALIAS:
				$shortcode_object = new WPV_Shortcode_Control_Post_Relationship( $shortcode );
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			case WPV_Shortcode_Control_Post_Ancestor::SHORTCODE_NAME: 
			case WPV_Shortcode_Control_Post_Ancestor::SHORTCODE_NAME_ALIAS;
				if ( apply_filters( 'toolset_is_m2m_enabled', false ) ) {
					$shortcode_object = new WPV_Shortcode_Control_Post_Ancestor_From_M2m( $shortcode );
				} else {
					$shortcode_object = new WPV_Shortcode_Control_Post_Ancestor_From_Postmeta( $shortcode );
				}
				return new WPV_Shortcode_Base_View( $shortcode_object );
				break;
			
			case WPV_Shortcode_WPML_Conditional::SHORTCODE_NAME:
				$shortcode_object = new WPV_Shortcode_WPML_Conditional();
				if ( $shortcode_object->condition_is_met() ) {
					$shortcode_gui = new WPV_Shortcode_WPML_Conditional_GUI();
					return new WPV_Shortcode_Base_View( $shortcode_object );
				}
				return new WPV_Shortcode_Base_View( new WPV_Shortcode_Empty() );
		}

		return false;
	}
}