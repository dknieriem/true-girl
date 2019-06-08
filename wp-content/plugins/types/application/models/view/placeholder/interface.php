<?php

/**
 * Interface Types_View_Placeholder_Interface
 *
 * @since 2.3
 */
interface Types_View_Placeholder_Interface {
	/**
	 * @param $string
	 * @param null $object
	 *
	 * @return mixed
	 */
	public function replace( $string, $object = null );
}