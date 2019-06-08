<?php

/**
 * Simple in-memory cache for association query results.
 *
 * @since 3.0.3
 */
class Toolset_Association_Query_Cache {


	/** @var Toolset_Association_Query_Cache */
	private static $instance;


	/** @var array Cache content. */
	private $cache = array();


	/**
	 * @return Toolset_Association_Query_Cache
	 */
	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->initialize();
		}

		return self::$instance;
	}


	public function initialize() {
		add_action( 'toolset_association_created', array( $this, 'flush' ) );
		add_action( 'toolset_association_deleted', array( $this, 'flush' ) );
	}


	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function push( $key, $value ) {
		$this->cache[ $key ] = $value;
	}


	/**
	 * @param string $key
	 * @param null|&bool $found
	 *
	 * @return mixed
	 */
	public function get( $key, &$found ) {
		if( ! array_key_exists( $key, $this->cache ) ) {
			$found = false;
			return null;
		}

		$found = true;
		return $this->cache[ $key ];
	}


	/**
	 * Delete all used cache records.
	 *
	 * @since Types 3.1.3
	 */
	public function flush() {
		$this->cache = array();
	}


}
