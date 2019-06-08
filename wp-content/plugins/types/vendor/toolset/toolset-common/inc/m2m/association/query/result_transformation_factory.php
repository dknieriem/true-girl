<?php

/**
 * Factory for the IToolset_Association_Query_Result_Transformation classes.
 *
 * @since 3.0.9
 */
class Toolset_Association_Query_Result_Transformation_Factory {


	/**
	 * @return Toolset_Association_Query_Result_Transformation_Association_Instance
	 */
	public function association_instance() {
		return new Toolset_Association_Query_Result_Transformation_Association_Instance();
	}


	/**
	 * @return Toolset_Association_Query_Result_Transformation_Association_Uid
	 */
	public function association_uids() {
		return new Toolset_Association_Query_Result_Transformation_Association_Uid();
	}


	/**
	 * @param Toolset_Association_Query_V2 $associaton_query Query instance where the transformation class will be used.
	 *
	 * @return Toolset_Association_Query_Result_Transformation_Element_Per_Role
	 */
	public function element_per_role( Toolset_Association_Query_V2 $associaton_query ) {
		return new Toolset_Association_Query_Result_Transformation_Element_Per_Role( $this, $associaton_query );
	}


	/**
	 * @param IToolset_Relationship_Role $role
	 *
	 * @return Toolset_Association_Query_Result_Transformation_Element_Id
	 */
	public function element_ids( IToolset_Relationship_Role $role ) {
		return new Toolset_Association_Query_Result_Transformation_Element_Id( $role );
	}


	/**
	 * @param IToolset_Relationship_Role $role
	 *
	 * @return Toolset_Association_Query_Result_Transformation_Element_Instance
	 */
	public function element_instances( IToolset_Relationship_Role $role ) {
		return new Toolset_Association_Query_Result_Transformation_Element_Instance( $role );
	}

}