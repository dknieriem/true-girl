<?php

use OTGS\Toolset\Common\ExceptionWithMessage;

/**
 * Represents a result of a single operation.
 *
 * This is a wrapper for easy handling of results of different types.
 * It can encapsulate a boolean, WP_Error, boolean + message, or an exception.
 *
 * It is supposed to work well with Toolset_Result_Set.
 *
 * @since 2.3
 */
class Toolset_Result implements \OTGS\Toolset\Common\Result\ResultInterface {

	/** @var bool */
	protected $is_error;

	/** @var bool|WP_Error|Exception What was passed as a result value. */
	protected $inner_result;

	/** @var string|null Display message, if one was provided. */
	protected $display_message;


	protected $code;


	/**
	 * Toolset_Result constructor.
	 *
	 * @param bool|WP_Error|Exception|Throwable $value Result value. For boolean, true determines a success, false
	 *     determines a failure. WP_Error and Exception are interpreted as failures.
	 * @param string|null $display_message Optional display message that will be used if a boolean result is
	 *     provided. If an exception is provided, it will be used as a prefix of the message from the exception.
	 * @param int|null $code Numeric code that can be set for easier programmatical recognition of the result.
	 *
	 * Some specific classes have a special handling:
	 * - If an ExceptionWithMessage is passed, it uses its specially stored error message to prevent xdebug from messing with it.
	 * - For ParseError, we extract a message together with a file and a line number where the error has occurred.
	 *
	 * @since 2.3
	 */
	public function __construct( $value, $display_message = null, $code = null ) {

		$this->inner_result = $value;
		$this->code = (int) $code;

		if( is_bool( $value ) ) {
			$this->is_error = ! $value;
			$this->display_message = ( is_string( $display_message ) ? $display_message : null );
		} else if( $value instanceof WP_Error ) {
			$this->is_error = true;
			$this->display_message = $value->get_error_message();
		} elseif( $value instanceof ExceptionWithMessage ) {
			$this->is_error = true;
			$this->display_message = $value->get_custom_message();
		} elseif( $value instanceof ParseError ) {
			$this->is_error = true;
			$this->display_message = sprintf( '%s in %s on line %d', $value->getMessage(), $value->getFile(), $value->getLine() );
		} elseif( $value instanceof Exception ) {
			$this->is_error = true;
			$this->display_message = (
				( is_string( $display_message ) ? $display_message . ': ' : '' )
				. $value->getMessage()
			);
		} elseif( $value instanceof Throwable ) {
			$this->is_error = true;
			$this->display_message = (
				( is_string( $display_message ) ? $display_message . ': ': '' )
				. $value->getMessage()
			);
		} else {
			throw new InvalidArgumentException( 'Unrecognized result value.' );
		}

	}


	public function is_error() { return $this->is_error; }


	public function is_success() { return ! $this->is_error; }


	public function has_message() { return ( null != $this->display_message ); }


	public function get_message() { return $this->display_message; }


	public function get_code() {
		return $this->code;
	}


	/**
	 * Returns the result as an associative array in a standard form.
	 * 
	 * That means, it will allways have the boolean element 'success' and
	 * a string 'message', if a display message is set.
	 * 
	 * @return array
	 * @since 2.3
	 */
	public function to_array() {
		$result = array( 'success' => $this->is_success() );
		if( $this->has_message() ) {
			$result['message'] = $this->get_message();
		}
		return $result;
	}


}