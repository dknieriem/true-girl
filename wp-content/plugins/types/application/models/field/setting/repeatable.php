<?php

/**
 * Class Types_Field_Setting_Repeatable
 *
 * @since 2.3
 */
class Types_Field_Setting_Repeatable implements Types_Field_Setting_Bool_Interface {

	private $repeatable = false;

	/**
	 * Types_Field_Setting_Repeatable constructor.
	 *
	 * @param $data
	 */
	public function __construct( $data ) {
		if( ! isset( $data['repeatable'] ) ) {
			return;
		}

		$this->set_repeatable( $data['repeatable'] );
	}

	/**
	 * @param string|bool|int $repeatable
	 */
	private function set_repeatable( $repeatable ) {
		if( ! is_string( $repeatable ) && ! is_bool( $repeatable ) && ! is_int( $repeatable ) ) {
			return;
		}

		$this->repeatable = ! $repeatable
			? false
			: true;
	}

	/**
	 * @return bool
	 */
	public function is_true() {
		return $this->repeatable;
	}
}