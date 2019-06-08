<?php

/**
 * Types_Helper_Twig
 *
 * @since 2.0
 */
class Types_Helper_Twig implements Types_Interface_Template {

	/**
	 * @var Twig_Environment
	 */
	private $twig;


	/**
	 * Types_Helper_Twig constructor.
	 *
	 * @param string[] $additional_namespaces Twig namespaces to add, "namespace => absolute path" value pairs.
	 * @param Toolset_Common_Bootstrap|null $toolset_common_bootstrap_di
	 * @param Toolset_Gui_Base|null $toolset_gui_base_di
	 *
	 * @since m2m Implemented possibility to add other Twig namespaces.
	 */
	public function __construct(
		array $additional_namespaces = array(),
		Toolset_Common_Bootstrap $toolset_common_bootstrap_di = null,
		Toolset_Gui_Base $toolset_gui_base_di = null
	) {

		// Ensure that we have Twig ready
		$tcb = ( null === $toolset_common_bootstrap_di ? Toolset_Common_Bootstrap::get_instance() : $toolset_common_bootstrap_di );
		$tcb->register_gui_base();

		$gui_base = ( null === $toolset_gui_base_di ? Toolset_Gui_Base::get_instance() : $toolset_gui_base_di );
		$gui_base->init();

		$namespaces = array_merge( array( 'types' => TYPES_ABSPATH . '/application/views' ), $additional_namespaces );

		$this->twig = $gui_base->create_twig_environment( $namespaces );
	}


	public function render( $file, $data ) {
		return $this->twig->render( "@types$file", $data );
	}


	/**
	 * Return the underlying Twig environment.
	 *
	 * @since m2m
	 */
	public function get_environment() {
		return $this->twig;
	}

	/**
	 * Alias for Toolset_Twig_Dialog_Box::construct()
	 *
	 * @param $id
	 * @param $template_path
	 * @param array $template_values
	 *
	 * @return Toolset_Twig_Dialog_Box
	 */
	public function prepare_dialog( $id, $template_path, $template_values = array() ) {
		$twig_factory = new Toolset_Twig_Dialog_Box_Factory();

		$twig_factory->create(
			$id,
			$this->twig,
			$template_values,
			"@types$template_path"
		);
	}
}