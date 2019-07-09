<?php

/**
 * Filter manager for Toolset Views.
 *
 * Initializes, stored and allows access to every query filter registered within Toolset Views.
 *
 * @since m2m
 */
class WPV_Filter_Manager {
	
	/**
	 * @var WPV_Filter_Manager|null
	 */
	protected static $instance = null;
	
	/**
	 * @var array
	 */
	protected $filters = array(
		Toolset_Element_Domain::POSTS => array(),
		Toolset_Element_Domain::TERMS => array(),
		Toolset_Element_Domain::USERS => array()
	);
	
	function __construct() {}

	/**
	 * Get the instance of this object.
	 *
	 * @since m2m
	 */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new WPV_Filter_Manager();
        }
        return self::$instance;
    }
	
	/**
	 * Initialize each domain filters.
	 *
	 * @since m2m
	 */
	public function initialize() {
		$this->initialize_post_filters();
		$this->initialize_term_filters();
		$this->initialize_user_filters();
	}
	
	/**
	 * Initialize post-related filters.
	 *
	 * @since m2m
	 */
	private function initialize_post_filters() {
		$this->filters[ Toolset_Element_Domain::POSTS ]['relationship'] = new WPV_Filter_Post_Relationship();
	}
	
	/**
	 * Initialize term-related filters.
	 *
	 * @since m2m
	 */
	private function initialize_term_filters() {
		
	}
	
	/**
	 * Initialize user-related filters.
	 *
	 * @since m2m
	 */
	private function initialize_user_filters() {
		
	}
	
	/**
	 * Get a filter per domain and slug.
	 *
	 * @since m2m
	 */
	public function get_filter( $domain, $slug ) {
		return ( isset( $this->filters[ $domain ][ $slug ] ) ) 
			? $this->filters[ $domain ][ $slug ]
			: null;
	}
	
}