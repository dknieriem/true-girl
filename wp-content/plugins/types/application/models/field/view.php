<?php

/**
 * Class Types_Field_View
 *
 * @since 2.3
 */
class Types_Field_View {

	/**
	 * @var Types_Field_Interface
	 */
	private $field;

	/**
	 * @var Types_Interface_Template
	 */
	private $template;

	/**
	 * Types_Field_View constructor.
	 *
	 * @param Types_Interface_Value $field
	 * @param Types_Interface_Template $template
	 */
	public function __construct( Types_Interface_Value $field, Types_Interface_Template $template ) {
		$this->field = $field;
		$this->template = $template;
	}

	/**
	 * Returns a rendered field by using $template_path as template file
	 *
	 * @param $template_path
	 *
	 * @return string The rendered templated
	 */
	public function render( $template_path = false ) {
		$output = '';
		if( ! $template_path ) {
			// no template file used
			$output = $this->field->get_value();
		} else {
			// render field using a template file
			$output = $this->template->render(
				$template_path,
				array(
					'field' => $this->field
				)
			);
		}

		if( method_exists( $this->field, 'is_raw_output' ) && $this->field->is_raw_output() ) {
			// do not render shortcodes if "raw" output is wanted
			return $output;
		}

		return do_shortcode( $output );
	}
}
