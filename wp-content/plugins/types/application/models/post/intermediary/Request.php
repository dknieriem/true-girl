<?php

namespace OTGS\Toolset\Types\Model\Post\Intermediary;

class Request {

	/**
	 * @var \Toolset_Element_Factory
	 */
	private $element_factory;

	/**
	 * @var \Toolset_Post_Type_Repository
	 */
	private $post_type_repository;

	/**
	 * @var \Toolset_Association_Query_V2
	 */
	private $_association_query;

	/**
	 * @var \Toolset_Relationship_Query_V2
	 */
	private $relationship_query;

	/**
	 * @var \Toolset_Relationship_Role_Parent
	 */
	private $role_parent;

	/**
	 * @var \Toolset_Relationship_Role_Child
	 */
	private $role_child;

	/**
	 * @var \Toolset_Relationship_Role_Intermediary
	 */
	private $role_intermediary;

	/**
	 * @var $intermediary_id
	 */
	private $intermediary_id;

	/**
	 * @var string $post_type_slug
	 */
	private $post_type_slug;

	/**
	 * @var int $parent_id
	 */
	private $parent_id;

	/**
	 * @var int $child_id
	 */
	private $child_id;

	/**
	 * @var \IToolset_Post
	 */
	private $_intermediary_post;

	/**
	 * @var \IToolset_Post_Type
	 */
	private $_intermediary_type;

	/**
	 * @var \IToolset_Association
	 */
	private $_association;

	/**
	 * @var \IToolset_Association
	 */
	private $_association_conflict;

	/**
	 * @var \IToolset_Relationship_Definition
	 */
	private $_relationship_definition;

	/**
	 * Request constructor.
	 *
	 * @param \Toolset_Element_Factory $element_factory
	 * @param \Toolset_Post_Type_Repository $post_type_repository
	 * @param \Toolset_Association_Query_V2 $association_query
	 * @param \Toolset_Relationship_Query_V2 $relationship_query
	 * @param \Toolset_Relationship_Role_Parent $role_parent
	 * @param \Toolset_Relationship_Role_Child $role_child
	 * @param \Toolset_Relationship_Role_Intermediary $role_intermediary
	 */
	public function __construct(
		\Toolset_Element_Factory $element_factory,
		\Toolset_Post_Type_Repository $post_type_repository,
		\Toolset_Association_Query_V2 $association_query,
		\Toolset_Relationship_Query_V2 $relationship_query,
		\Toolset_Relationship_Role_Parent $role_parent,
		\Toolset_Relationship_Role_Child $role_child,
		\Toolset_Relationship_Role_Intermediary $role_intermediary

	) {
		$this->element_factory      = $element_factory;
		$this->post_type_repository = $post_type_repository;
		$this->_association_query   = $association_query;
		$this->relationship_query   = $relationship_query;
		$this->role_parent          = $role_parent;
		$this->role_child           = $role_child;
		$this->role_intermediary    = $role_intermediary;
	}

	/**
	 * @param mixed $intermediary_id
	 *
	 * @return Request
	 */
	public function setIntermediaryId( $intermediary_id ) {
		if( is_int( $intermediary_id ) || is_numeric( $intermediary_id ) ) {
			$this->intermediary_id = $intermediary_id;
		}


		return $this;
	}

	/**
	 * @param string $post_type_slug
	 *
	 * @return Request
	 */
	public function setPostTypeSlug( $post_type_slug ) {
		if( is_string( $post_type_slug ) ) {
			$this->post_type_slug = $post_type_slug;
		}

		return $this;
	}

	/**
	 * @param int $parent_id
	 *
	 * @return Request
	 */
	public function setParentId( $parent_id ) {
		if( is_int( $parent_id ) || is_numeric( $parent_id ) ) {
			$this->parent_id = $parent_id;
		}

		return $this;
	}

	/**
	 * @param int $child_id
	 *
	 * @return Request
	 */
	public function setChildId( $child_id ) {
		if( is_int( $child_id ) || is_numeric( $child_id ) ) {
			$this->child_id = $child_id;
		}

		return $this;
	}

	/**
	 * @return \IToolset_Post
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function getIntermediaryPost() {
		if( $this->_intermediary_post !== null ) {
			return $this->_intermediary_post;
		}

		if( $this->intermediary_id !== null ) {
			$this->_intermediary_post = $this->element_factory->get_post( $this->intermediary_id );
		}

		return $this->_intermediary_post;
	}

	/**
	 * @return \IToolset_Post_Type
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function getIntermediaryType() {
		if( $this->_intermediary_type !== null ) {
			return $this->_intermediary_type;
		}

		if( $this->post_type_slug !== null ) {
			// 1. priority is the users defined slug
			$_intermediary_type = $this->post_type_repository->get( $this->post_type_slug );
			$this->post_type_slug = null;
		} elseif( $intermediary_post = $this->getIntermediaryPost() ) {
			// 2. priority use self::getIntermediaryElement
			$_intermediary_type = $this->post_type_repository->get( $intermediary_post->get_type() );
		}

		if( isset( $_intermediary_type )
		    && $_intermediary_type instanceof \IToolset_Post_Type
			&& $_intermediary_type->is_intermediary() ) {
			$this->_intermediary_type = $_intermediary_type;
		}

		return $this->_intermediary_type;
	}

	/**
	 * @return \IToolset_Association
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function getAssociation() {
		if( $this->_association !== null ) {
			return $this->_association;
		}

		if( ! $intermediary_post = $this->getIntermediaryPost() ) {
			return;
		}

		if( ! $relationship = $this->getRelationshipDefinition() ) {
			return;
		}

		$association_query = $this->getAssociationQuery();

		$results = $association_query
			->add( $association_query->element( $intermediary_post, $this->role_intermediary ) )
			->add( $association_query->relationship( $relationship ) )
			->do_not_add_default_conditions()
			->limit( 1 )
			->get_results();

		if ( count( $results ) === 1 ) {
			$this->_association = reset( $results );
		}

		return $this->_association;
	}

	/**
	 * @return \IToolset_Association
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function getPossibleAssociationConflict() {
		if( $this->_association_conflict !== null ) {
			return $this->_association_conflict;
		}

		if( $this->child_id === null || $this->parent_id === null || ! $this->getRelationshipDefinition() ) {
			return;
		}

		if( ! $child_element = $this->element_factory->get_post( $this->child_id ) ) {
			return;
		}

		if( ! $parent_element = $this->element_factory->get_post( $this->parent_id ) ) {
			return;
		}

		if( $this->intermediary_id !== null ) {
			$this->_intermediary_post = $this->element_factory->get_post( $this->intermediary_id );
		}

		$association_query = $this->getAssociationQuery();

		$results = $association_query
			->add( $association_query->element( $parent_element, $this->role_parent ) )
			->add( $association_query->element( $child_element, $this->role_child ) )
			->add( $association_query->relationship( $this->getRelationshipDefinition() ) )
			->limit( 1 )
			->get_results();

		if ( count( $results ) === 1 ) {
			$this->_association_conflict = reset( $results );
		}

		return $this->_association_conflict;
	}

	/**
	 * @return \IToolset_Relationship_Definition
	 * @throws \Toolset_Element_Exception_Element_Doesnt_Exist
	 */
	public function getRelationshipDefinition() {
		if( $this->_relationship_definition !== null ) {
			return $this->_relationship_definition;
		}

		if( ! $_intermediary_type = $this->getIntermediaryType() ) {
			return;
		}

		// load relationship
		$results = $this->relationship_query
			->add( $this->relationship_query->intermediary_type( $_intermediary_type->get_slug() ) )
			->get_results();

		if ( count( $results ) === 1 ) {
			$this->_relationship_definition = reset( $results );
		}

		return $this->_relationship_definition;
	}

	/**
	 * @return int
	 */
	public function getChildId() {
		return $this->child_id;
	}

	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}

	/**
	 * Toolset_Association_Query_V2 cannot be reused, so we have to clone it
	 * AND we canot simply use "clone" as deeper nested objects are not cloned
	 * and they are also not allowed to be reused.
	 *
	 * @return \Toolset_Association_Query_V2
	 */
	private function getAssociationQuery() {
		$association_query_class = get_class( $this->_association_query );

		/** @var \Toolset_Association_Query_V2 $association_query */
		return new $association_query_class();
	}
}
