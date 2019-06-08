<?php

use OTGS\Toolset\Types\Page\Extension\RelatedContent\DirectEditStatusFactory;

/**
 * Related Content. Elements related to a specific element.
 *
 * @since m2m
 */
abstract class Types_Viewmodel_Related_Content {


	/**
	 * Relationship
	 *
	 * @var Toolset_Relationship_Definition
	 * @since m2m
	 */
	protected $relationship;


	/**
	 * Role
	 *
	 * @var Toolset_Relationship_Role
	 * @since m2m
	 */
	protected $role;


	/**
	 * Role of the related element
	 *
	 * @var string
	 * @since m2m
	 */
	protected $related_element_role;


	/** @var Toolset_Constants */
	protected $constants;


	/**
	 * Query factory
	 *
	 * @var Toolset_Relationship_Query_Factory
	 */
	protected $query_factory;


	/** @var DirectEditStatusFactory */
	protected $direct_edit_status_factory;


	/**
	 * Constructor
	 *
	 * @param string|Toolset_Relationship_Role $role Relationship role.
	 * @param Toolset_Relationship_Definition $relationship Relationship type.
	 * @param Toolset_Constants|null $constants Constants handler.
	 * @param Toolset_Relationship_Query_Factory $query_factory_di For testing purposes.
	 * @param DirectEditStatusFactory|null $direct_edit_status_factory
	 */
	public function __construct(
		$role, $relationship, Toolset_Constants $constants = null,
		Toolset_Relationship_Query_Factory $query_factory_di = null,
		DirectEditStatusFactory $direct_edit_status_factory = null
	) {
		$this->role = Toolset_Relationship_Role::PARENT === $role
			? new Toolset_Relationship_Role_Parent()
			: new Toolset_Relationship_Role_Child();
		$this->relationship = $relationship;
		$this->constants = ( null === $constants ? new Toolset_Constants() : $constants );

		$this->related_element_role = Toolset_Relationship_Role::other( $this->role );
		$this->query_factory = $query_factory_di ? $query_factory_di : new Toolset_Relationship_Query_Factory();
		$this->direct_edit_status_factory = $direct_edit_status_factory ?: new DirectEditStatusFactory();
	}


	/**
	 * Returns the related content
	 *
	 * @return array Related content.
	 * @since m2m
	 */
	abstract public function get_related_content();


	/**
	 * Gets the related content as an array for using in the admin frontend for exporting to JSON format.
	 *
	 * @since m2m
	 */
	abstract public function get_related_content_array();


	/**
	 * Returns the association query
	 *
	 * @return Toolset_Association_Query_V2
	 * @since m2m
	 */
	protected function get_association_query() {
		return $this->query_factory->associations_v2();
	}



	/**
	 * Returns the relationship query
	 *
	 * @return Toolset_Relationship_Query_V2
	 * @since m2m
	 */
	protected function get_relationship_query() {
		return $this->query_factory->relationships_v2();
	}
}
