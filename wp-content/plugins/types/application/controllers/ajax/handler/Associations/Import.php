<?php

/**
 * Class Types_Ajax_Handler_Associations_Import
 *
 * No namespace possible, need to refactor our Ajax Handler
 *
 * @since 3.0
 */
class Types_Ajax_Handler_Associations_Import extends \Toolset_Ajax_Handler_Abstract {

	/**
	 * Processes the Ajax call
	 *
	 * @param array $arguments Original action arguments.
	 *
	 * @return void
	 */
	function process_call( $arguments ) {
		$this->get_ajax_manager()->ajax_begin(
			array( 'nonce' => \Types_Ajax::CALLBACK_ASSOCIATIONS_IMPORT ) );

		// Action
		$action = sanitize_text_field( toolset_getpost( 'associations_import_action' ) );

		// route action
		$this->route( $action );
	}

	/**
	 * Route calls via ajax
	 *
	 * @param $action
	 */
	private function route( $action ) {
		switch( $action ) {
			case 'getAssociations':
				return $this->getAssociations();
			case 'importAssociations':
				return $this->importAssociations();
			case 'deleteExistingAssociationsFromImportList':
				return $this->deleteExistingAssociationsFromImportList();
			case 'deleteBrokenAssociationsFromImportList':
				return $this->deleteBrokenAssociationsFromImportList();
		}
	}

	/**
	 * On page load this function is called to load associations
	 */
	private function getAssociations(){
		$limit = sanitize_text_field( toolset_getpost( 'associations_import_limit', null ) );
		$offset = sanitize_text_field( toolset_getpost( 'associations_import_offset', null ) );

		global $wpdb;

		$association_repository = new \OTGS\Toolset\Common\M2M\Association\Repository(
			new Toolset_Relationship_Query_Factory(),
			new Toolset_Relationship_Role_Parent(),
			new Toolset_Relationship_Role_Child(),
			new Toolset_Relationship_Role_Intermediary(),
			new Toolset_Element_Domain()
		);

		$associations = new \OTGS\Toolset\Types\Post\Import\Associations(
			new \OTGS\Toolset\Types\Post\Meta\Associations(),
			new \OTGS\Toolset\Types\Wordpress\Post\Storage( $wpdb ),
			new \OTGS\Toolset\Types\Wordpress\Postmeta\Storage( $wpdb ),
			\Toolset_Relationship_Definition_Repository::get_instance(),
			$association_repository,
			new \OTGS\Toolset\Types\Post\Import\Association\Factory()
		);

		$associations->loadAssociationsByChunks( $limit, $offset );

		$this->get_ajax_manager()->ajax_finish( array(
			'associations' => $associations->getAssociations()
		) );
	}

	/**
	 * The import process
	 */
	private function importAssociations() {
		// delete the notice for available associations
		$this->deleteNoticeAboutAssociationsAvailable();

		// return preparation
		$result = array(
			'success' => 0,
			'error' => 0
		);

		// associations to import
		$associations = html_entity_decode( stripslashes( toolset_getpost( 'associations_import', null ) ) );
		$associations = json_decode(  $associations, true );

		if( empty( $associations ) ) {
			$this->get_ajax_manager()->ajax_finish( $result );
		}

		// we have associations
		global $wpdb;
		$storage_postmeta = new \OTGS\Toolset\Types\Wordpress\Postmeta\Storage( $wpdb );
		$postmeta_associations = new \OTGS\Toolset\Types\Post\Meta\Associations();

		foreach( $associations as $association ) {
			try{
				$imported = Toolset_Relationship_Database_Operations::create_association(
					sanitize_text_field( $association['relationship']['slug'] ),
					sanitize_text_field( $association['parent']['id'] ),
					sanitize_text_field( $association['child']['id'] ),
					sanitize_text_field( $association['intermediary']['id'] )
				);

				if( $imported instanceof Toolset_Result ) {
					$result['error']++;
				} else {
					$relationship = $imported->get_definition();
					$origin = $relationship->get_origin();
					if( $origin instanceof Toolset_Relationship_Origin_Post_Reference_Field ) {
						update_post_meta(
							$imported->get_element_id( new Toolset_Relationship_Role_Child() ),
							'wpcf-' . $relationship->get_slug(),
							$imported->get_element_id( new Toolset_Relationship_Role_Parent() )
						);
					}

					$result['success']++;
					$storage_postmeta->deleteStringFromPostMeta(
						sanitize_text_field( $association['meta']['postId'] ),
						sanitize_text_field( $association['meta']['key'] ),
						sanitize_text_field( $association['meta']['associationString'] ),
						$postmeta_associations::BETWEEN_MULTIPLE_ASSOCIATIONS
					);
				}

			} catch( Exception $e ) {
				$result['error']++;
			}
		}

		$this->get_ajax_manager()->ajax_finish( $result );
	}

	/**
	 * This will just remove all associations postmeta which has no empty value.
	 */
	private function deleteExistingAssociationsFromImportList() {
		// delete the notice for available associations
		$this->deleteNoticeAboutAssociationsAvailable();

		global $wpdb;

		$meta_associations = new \OTGS\Toolset\Types\Post\Meta\Associations();
		$meta_storage = new \OTGS\Toolset\Types\Wordpress\Postmeta\Storage( $wpdb );

		$postmeta = $meta_associations->getKeyWithWildcardForMysql();
		$deleted_rows = $meta_storage->deleteEmptyPostMetaByKey( $postmeta );

		$this->get_ajax_manager()->ajax_finish( array( 'deleted' => $deleted_rows ) );
	}

	/**
	 * Deletes all broken associations.
	 * This runs after the import process and only includes broken associations, which were broken BEFORE the import.
	 * If the very very edge case happens that an association breaks on the import process it's not affected by this,
	 * so the user can check the issue and decided to delete or fix it.
	 */
	private function deleteBrokenAssociationsFromImportList() {
		// delete the notice for available associations
		$this->deleteNoticeAboutAssociationsAvailable();

		$associations = toolset_getpost( 'associations_to_delete', null );

		if( empty( $associations ) ) {
			return;
		}

		global $wpdb;
		$storage_postmeta = new \OTGS\Toolset\Types\Wordpress\Postmeta\Storage( $wpdb );
		$postmeta_associations = new \OTGS\Toolset\Types\Post\Meta\Associations();

		foreach( $associations as $association ) {
			$storage_postmeta->deleteStringFromPostMeta(
				sanitize_text_field( $association['meta']['postId'] ),
				sanitize_text_field( $association['meta']['key'] ),
				sanitize_text_field( $association['meta']['associationString'] ),
				$postmeta_associations::BETWEEN_MULTIPLE_ASSOCIATIONS
			);
		}
	}

	/**
	 * This removes the wp_option that there are associations available.
	 * It doesn't hurt to remove it, even if not all associations are imported.
	 */
	private function deleteNoticeAboutAssociationsAvailable() {
		// delete notice about associations available
		$option = new \OTGS\Toolset\Types\Wordpress\Option\Associations\ImportAvailable();
		$option->deleteOption();

	}
}
