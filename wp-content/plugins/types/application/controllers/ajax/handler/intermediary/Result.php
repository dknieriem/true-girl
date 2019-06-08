<?php


namespace OTGS\Toolset\Types\Controller\Ajax\Handler\Intermediary;


class Result implements \JsonSerializable {
	const RESULT_SUCCESS = 'success';
	const RESULT_CONFLICT = 'conflict';
	const RESULT_SYSTEM_ERROR = 'system error';
	const RESULT_DOM_ERROR = 'dom error';

	/**
	 * One of the defined results
	 * @var string
	 */
	private $result = self::RESULT_SUCCESS;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var id
	 */
	private $conflict_id;

	/**
	 * @var string
	 */
	private $conflict_url;

	/**
	 * Make private properties visible on json seralize.
	 *
	 * @return array|mixed
	 */
	public function jsonSerialize() {
		return get_object_vars( $this );
	}

	/**
	 * @param  $result
	 *
	 * @return Result
	 */
	public function setResult( $result ) {
		switch( $result ) {
			case self::RESULT_SUCCESS:
			case self::RESULT_CONFLICT:
			case self::RESULT_SYSTEM_ERROR:
				$this->result = $result;
				break;
			default:
				$this->result = self::RESULT_SYSTEM_ERROR;
		}

		$this->setDefaultMessage();

		return $this;
}

	/**
	 * @param string $message
	 *
	 * @return Result
	 */
	public function setMessage( $message ) {
		if( is_string( $message ) || is_numeric( $message ) ) {
			$this->message = $message;
		}

		// check for default message
		$this->setDefaultMessage();

		return $this;
	}

	/**
	 * Will be triggered when trying to apply an empty message
	 * or when the setResult() is used.
	 */
	private function setDefaultMessage() {
		if( ! empty( $this->message ) ) {
			// only use default message if there is no message set
			return;
		}

		// only for errors
		switch( $this->result ) {
			case self::RESULT_DOM_ERROR:
				$this->message = __( 'Request failed. Please reload the page and try again.', 'wpcf' );
				break;
			case self::RESULT_SYSTEM_ERROR:
				$this->message = __( 'System Error. Please contact our support.', 'wpcf' );
				break;
		}
	}

	/**
	 * @param id $conflict_id
	 *
	 * @return Result
	 */
	public function setConflictId( $conflict_id ) {
		$this->conflict_id = $conflict_id;

		return $this;
	}

	/**
	 * @param string $conflict_url
	 *
	 * @return Result
	 */
	public function setConflictUrl( $conflict_url ) {
		$this->conflict_url = $conflict_url;

		return $this;
}
}