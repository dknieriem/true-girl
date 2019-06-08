<?php

/**
 * Returns the HTML container (using Twig) with the fields edit inputs
 *
 * @since m2m
 */
class Types_Viewmodel_Fields_Edit_Container {


	/**
	 * Twig environment
	 *
	 * @var Twig_Environment
	 * @since m2m
	 */
	private $twig;


	/**
	 * Twig context
	 *
	 * @var string
	 * @since m2m
	 */
	private $context;


	/**
	 * Fields array
	 *
	 * @var Toolset_Field_Definition[] Fields definitions.
	 * @since m2m
	 */
	private $fields;


	/**
	 * Template name
	 *
	 * @var string
	 * @since m2m
	 */
	private $template_name;


	/**
	 * Viewmodel for getting formatted data
	 *
	 * @var Types_Viewmodel_Field_Input
	 * @since m2m
	 */
	protected $viewmodel;


	/**
	 * Constructor
	 *
	 * @param Toolset_Field_Definition[]  $fields Array of fields.
	 * @param Twig_Environment            $twig Twig environment.
	 * @param array                      $context Initial Twig context.
	 * @param string                      $template_name Template path.
	 * @param Types_Viewmodel_Field_Input $viewmodel Viewmodel for getting formatted data.
	 */
	public function __construct( $fields, $twig, $context, $template_name, $viewmodel = null ) {
		$this->fields = toolset_ensarr( $fields );
		$this->twig = $twig;
		$this->context = toolset_ensarr( $context );
		$this->context = wp_parse_args( $this->context, array(
			'id' => 'field-container-' . wp_rand( 1, 1000 ),
		) );
		$this->template_name = $template_name;
		$this->viewmodel = $viewmodel;
	}


	/**
	 * Gets the viewmodel
	 *
	 * @return Types_Viewmodel_Field_Input
	 * @since m2m
	 */
	private function get_view_model() {
		if ( $this->viewmodel ) {
			return $this->viewmodel;
		}
		return new Types_Viewmodel_Field_Input( $this->fields );
	}


	/**
	 * Returns the HTML cointainer for the fields inputs
	 * Renders the output using Twig
	 *
	 * @return string
	 * @since m2m
	 */
	public function to_html() {
		$viewmodel = $this->get_view_model();
		$context = array_merge( $this->context, array(
			'fields' => $viewmodel->get_fields_data( Toolset_Field_Renderer_Purpose::INPUT ),
			'wpnonce' => $this->context['nonce'],
			'nonce' => $this->context['nonce'],
		) );
		return $this->twig->render( $this->template_name, $context );
	}
}
