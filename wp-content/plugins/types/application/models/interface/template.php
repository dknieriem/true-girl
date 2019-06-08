<?php

/**
 * Interface Types_Interface_Template
 *
 * @since 2.3
 */
interface Types_Interface_Template {
	/**
	 * @param $file
	 * @param $data
	 *
	 * @return string
	 */
	public function render( $file, $data );


	/**
	 * Renders a hidden dialog box
	 *
	 * @param $id
	 * @param $template_path
	 *
	 * @return mixed
	 */
	public function prepare_dialog( $id, $template_path );
}