<?php

/**
 * Interface Types_Field_Mapper_Interface
 *
 * @since 2.3
 */
interface Types_Field_Mapper_Interface {
	/**
	 * @param $id
	 * @param $id_post
	 *
	 * @return Types_Field_Abstract
	 */
	public function find_by_id( $id, $id_post );
}