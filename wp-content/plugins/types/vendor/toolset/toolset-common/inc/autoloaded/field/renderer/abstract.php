<?php


abstract class Toolset_Field_Renderer_Abstract {

	/** @var null|Toolset_Field_Instance */
	protected $field = null;


	/**
	 * Toolset_Field_Renderer_Abstract constructor.
	 *
	 * @param Toolset_Field_Instance $field
	 */
	public function __construct( $field ) {
		// todo sanitize
		$this->field = $field;
	}


	/**
	 * @param bool $echo
	 *
	 * @return string|array
	 */
	public abstract function render( $echo = false );

}
