<?php

namespace MC4WP\Licensing;

use Exception;

class ApiException extends Exception {

	/**
	 * @var object
	 */
	protected $data;

	/**
	 * API_Exception constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param object $data
	 */
	public function __construct( $message, $code, $data ) {
		parent::__construct( $message, $code );

		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getData() {
		return $this->data;
	}

	/**
	* @return string
	 */
	public function getApiMessage() {
		return $this->data->message;
	}

	/**
	 * @return string
	 */ 
	public function getApiCode() {
		return $this->data->code;
	}

}
