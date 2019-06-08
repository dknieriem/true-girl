<?php

/**
 * Class Types_Field_Service
 *
 * All dependencies for fields should use this service to the job.
 *
 * @since 2.3
 */
class Types_Field_Service {
	/**
	 * @var Types_Field_Factory_Interface[]
	 */
	private $factories = array();


	/**
	 * Constructor
	 *
	 * @param bool $load_form_validation
	 *
	 * @since 2.3
	 */
	public function __construct( $load_form_validation = true ) {
		// Not used later, it is only needed for script loading.
		// Instanced only one time for every field.
		// Only for admin.
		if ( is_admin() && $load_form_validation ) {
			Types_Field_Validation_Form::get_instance( 'post' );
		}
	}


	/**
	 * Get field object by $id (slug of the field)
	 * and the $post_id (id of the post the field belongs to)
	 *
	 * @param Types_Field_Gateway_Interface $gateway
	 * @param $id
	 * @param $id_post_user_term
	 *
	 * @return Types_Field_Abstract
	 */
	public function get_field( Types_Field_Gateway_Interface $gateway, $id, $id_post_user_term ) {
		try {
			$field_data = $gateway->get_field_by_id( $id, $id_post_user_term );
			$field = $this->get_factory( $field_data['type'] )->get_mapper( $gateway )->find_by_id( $id, $id_post_user_term );
		} catch ( Exception $e ) {
			return false;
		}

		return $field;
	}

	/**
	 * Get field frontend view using $field object and user params.
	 * The $user_params are passed to the view object.
	 *
	 * @param Types_Field_Interface $field
	 * @param $user_params
	 *
	 * @return mixed
	 */
	public function get_field_view_frontend( Types_Field_Interface $field, $user_params ) {
		$factory = $this->get_factory( $field->get_type() );

		return $factory->get_view_frontend( $field, $user_params );
	}

	/**
	 * Returns the rendered frontend output of a field by using a $post object and $field_id.
	 *
	 * @param Types_Field_Gateway_Interface $gateway
	 * @param WP_Post|WP_User|WP_Term $belongs_to_post_user_term
	 * @param $field_id
	 * @param array $user_params
	 * @param null $content
	 *
	 * @return string
	 */
	public function render_frontend(
		Types_Field_Gateway_Interface $gateway,
		$belongs_to_post_user_term,
		$field_id,
		$user_params = array(),
		$content = null
	) {
		if( ! is_object( $belongs_to_post_user_term )
		    ||
		    (
		    	! property_exists( $belongs_to_post_user_term, 'ID' )
				&& ! property_exists( $belongs_to_post_user_term, 'term_id' )
		    )
		) {
			// no valid object
			return '';
		}

		$belongs_to_id = property_exists( $belongs_to_post_user_term, 'ID' )
			? $belongs_to_post_user_term->ID
			: $belongs_to_post_user_term->term_id;

		$field = $this->get_field( $gateway, $field_id, $belongs_to_id );

		if ( ! $field ) {
			// no field found
			return '';
		}

		if ( $content !== null ) {
			$user_params['content'] = $content;
		}

		/**
		 * Filter 'types_field_shortcode_parameters' to modify user_params
		 *
		 * @since 1.x
		 * @deprecated 2.3 Not used by any of our plugins nor is it documented / mentioned anyhwere
		 */
		$user_params = apply_filters(
			'types_field_shortcode_parameters',
			$user_params,    // the params given by the shortcode
			$field->to_array(),          // object of Types_Field_Interface
			$belongs_to_post_user_term, // object of WP_Post|WP_User|WP_Taxonomy
			null             // @deprecated, previously $meta_id
		);

		try {
			$field_view = $this->get_field_view_frontend( $field, $user_params );
		} catch( Exception $e ) {
			error_log( $e->getMessage() );
			return '';
		}

		$view   = new Types_Field_View( $field_view, new Types_Helper_Twig() );
		// output of the field value, add template path if needed, e.g. ->render( '/field/frontend.twig' );
		$output = $view->render();

		if ( ! is_string( $output ) && ! is_int( $output ) ) {
			return '';
		}

		return $output;
	}

	/**
	 * Get the field factory by using the field type.
	 *
	 * @param $type
	 *
	 * @return Types_Field_Factory_Interface
	 * @throws Exception
	 */
	private function get_factory( $type ) {
		if ( isset( $this->factories[ $type ] ) ) {
			// we can use the same instance for all fields of the same type
			return $this->factories[ $type ];
		}

		switch ( $type ) {
			case 'single-line':
			case 'textfield':
				$this->factories[ $type ] = new Types_Field_Type_Single_Line_Factory();
				break;
			case 'audio':
				$this->factories[ $type ] = new Types_Field_Type_Audio_Factory();
				break;
			case 'checkboxes':
				$this->factories[ $type ] = new Types_Field_Type_Checkboxes_Factory();
				break;
			case 'checkbox':
				$this->factories[ $type ] = new Types_Field_Type_Checkbox_Factory();
				break;
			case 'colorpicker':
				$this->factories[ $type ] = new Types_Field_Type_Colorpicker_Factory();
				break;
			case 'date':
				$this->factories[ $type ] = new Types_Field_Type_Date_Factory();
				break;
			case 'email':
				$this->factories[ $type ] = new Types_Field_Type_Email_Factory();
				break;
			case 'embedded-media':
			case 'embed':
				$this->factories[ $type ] = new Types_Field_Type_Embedded_Media_Factory();
				break;
			case 'file':
				$this->factories[ $type ] = new Types_Field_Type_File_Factory();
				break;
			case 'image':
				$this->factories[ $type ] = new Types_Field_Type_Image_Factory();
				break;
			case 'number':
			case 'numeric':
				$this->factories[ $type ] = new Types_Field_Type_Number_Factory();
				break;
			case 'phone':
				$this->factories[ $type ] = new Types_Field_Type_Phone_Factory();
				break;
			case 'radio':
				$this->factories[ $type ] = new Types_Field_Type_Radio_Factory();
				break;
			case 'select':
				$this->factories[ $type ] = new Types_Field_Type_Select_Factory();
				break;
			case 'skype':
				$this->factories[ $type ] = new Types_Field_Type_Skype_Factory();
				break;
			case 'textarea':
			case 'multiple-lines':
				$this->factories[ $type ] = new Types_Field_Type_Multiple_Lines_Factory();
				break;
			case 'url':
				$this->factories[ $type ] = new Types_Field_Type_Url_Factory();
				break;
			case 'video':
				$this->factories[ $type ] = new Types_Field_Type_Video_Factory();
				break;
			case 'wysiwyg':
				$this->factories[ $type ] = new Types_Field_Type_Wysiwyg_Factory();
				break;
			case 'post':
				$this->factories[ $type ] = new Types_Field_Type_Post_Factory();
				break;
			default:
				/*
				This is for all fields which are not ported to the new structure.
				Note: all native Types fields are ported but for example Toolset Maps "Address" field not
				*/
				if ( isset( $this->factories['legacy'] ) ) {
					return $this->factories['legacy'];
				}

				return $this->factories['legacy'] = new Types_Field_Type_Legacy_Factory();
				break;
		}

		return $this->factories[ $type ];
	}
}
