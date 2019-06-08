<?php

use OTGS\Toolset\Types\Field\Group\ViewmodelFactory;
use OTGS\Toolset\Types\Field\Group\ViewmodelInterface;

/**
 * Handle action with field definitions on the Custom Fields page.
 *
 * @since 2.3
 */
final class Types_Ajax_Handler_Custom_Fields_Action extends Toolset_Ajax_Handler_Abstract {

	/**
	 * Check fields.
	 *
	 * For testing purposes, it is a mess to test it because static methods
	 *
	 * @param boolean
	 */
	private $check_fields;

	/**
	 * Types_Ajax_Handler_Relationships_Action constructor.
	 *
	 * Includes dependency injection arguments which can be used only with mocks.
	 * One of the reasons is that in normal execution, the m2m API would not have been initialized yet.
	 *
	 * @param Types_Ajax $ajax_manager Ajas manager.
	 * @param boolean    $check_fields_di Testing purposes.
	 */
	public function __construct(
		Types_Ajax $ajax_manager,
		$check_fields_di = true
	) {
		parent::__construct( $ajax_manager );

		$this->check_fields = $check_fields_di && apply_filters( 'toolset_is_m2m_enabled', false );
	}


	/**
	 * Process the Ajax call
	 *
	 * @since 2.3
	 * @param array $arguments List of POST arguments.
	 */
	public function process_call( $arguments ) {
		$errors = array();
		$results = array();

		// Gets the AJAX manager.
		$ajax_managager = $this->get_am();

		$ajax_managager->ajax_begin( array(
			'nonce' => $ajax_managager->get_action_js_name( Types_Ajax::CALLBACK_CUSTOM_FIELDS_ACTION ),
		) );

		// Reads and validate input.
		$field_action = sanitize_text_field( toolset_getpost( 'field_action' ) );
		// Reads and validate domain.
		$domain = sanitize_text_field( toolset_getpost( 'domain' ) );
		// Checks if it is a valid domain.
		if ( ! in_array( $domain, Toolset_Field_Utils::get_domains(), true ) ) {
			$errors[] = new WP_Error( 0, sprintf(
				// translators: Placeholder represents the list of valid domains.
				__( 'Invalid field domain provided. Expected one of those values: %s', 'wpcf' ),
				implode( ', ', Toolset_Field_Utils::get_domains() )
			) );
		}
		// array of values, will be sanitized when processed.
		$fields = toolset_getpost( 'fields' );

		if ( ! is_array( $fields ) || empty( $fields ) ) {
			$ajax_managager->ajax_finish( array(
				'message' => __( 'No field groups have been selected.', 'wpcf' ),
			), false );
		}

		// Processes fields one by one.
		foreach ( $fields as $field ) {

			$result = $this->single_custom_field_action( $field_action, $field, $domain );

			if ( is_array( $result ) ) {
				// Array of errors.
				$errors = array_merge( $errors, $result );
			} elseif ( $result instanceof WP_Error ) {
				// Single error.
				$errors[] = $result;
			} elseif ( false === $result ) {
				// This should not happen...!
				$errors[] = new WP_Error( 0, __( 'An unexpected error happened while processing the request.', 'wpcf' ) );
			} else {
				// Success.
				// Save the field definition model as a result if we got a whole definition.
				if ( $result instanceof ViewmodelInterface ) {
					$result = $result->to_json();
				}

				$results[ toolset_getarr( $result, 'slug' ) ] = $result;
			}
		}

		$data = array(
			'results' => $results,
		);
		$is_success = empty( $errors );

		if ( ! $is_success ) {
			$error_messages = array();

			/**
			 * List of errors.
			 *
			 * @var WP_Error $error
			 */
			foreach ( $errors as $error ) {
				$error_messages[] = $error->get_error_message();
			}
			$data['messages'] = $error_messages;
		}

		$ajax_managager->ajax_finish( $data, $is_success );

	}


	/**
	 * Handles a single custom field group.
	 *
	 * @since 2.3
	 * @param string $action_name Name of the action executed by the user.
	 * @param array  $field Field definition model passed from JS.
	 * @param string $domain A valid domain: posts, users, terms.
	 * @return bool|mixed|null|WP_Error|WP_Error[]|ViewmodelInterface An error, array of errors, boolean indicating
	 * success or a result value to be passed back to JS.
	 */
	private function single_custom_field_action( $action_name, $field, $domain ) {
		$group_id = intval( sanitize_text_field( toolset_getarr( $field, 'groupId' ) ) );
		switch ( $action_name ) {

			case 'delete_group':
				return $this->delete_group( $group_id, $domain );
			// Toggle is useful for single actions, due to knockout its easier to
			// have one action than changing it using ko.
			case 'toggle_active':
				return $this->toggle_active( $group_id, $domain );

			case 'activate_group':
				return $this->toggle_active( $group_id, $domain, true );

			case 'deactivate_group':
				return $this->toggle_active( $group_id, $domain, false );

			default:
				return new WP_Error( 42, __( 'Invalid action name.', 'wpcf' ) );
		}
	}


	/**
	 * Checks if the post and the domain are correct.
	 *
	 * @since 2.3
	 *
	 * @param int $group_id ID of the group (post->ID).
	 * @param string $domain A valid domain: posts, users, terms.
	 *
	 * @return WP_Post|WP_Error|ViewmodelInterface WP_Post for success, WP_Error on error.
	 */
	private function check_post_domain( $group_id, $domain ) {
		$field_group_class = Toolset_Field_Group_Factory::get_factory_by_domain( $domain );

		// Checks if the user has permissions.
		switch ( $domain ) {
			case Toolset_Element_Domain::POSTS:
				if ( ! WPCF_Roles::user_can_create( 'custom-field' ) ) {
					return new WP_Error( 42, __( 'You do not have permissions for that.', 'wpcf' ) );
				}
				break;
			case Toolset_Element_Domain::USERS:
				if ( ! WPCF_Roles::user_can_create( 'user-meta-field' ) ) {
					return new WP_Error( 42, __( 'You do not have permissions for that.', 'wpcf' ) );
				}
				break;
			case Toolset_Element_Domain::TERMS:
				if ( ! WPCF_Roles::user_can_create( 'term-field' ) ) {
					return new WP_Error( 42, __( 'You do not have permissions for that.', 'wpcf' ) );
				}
				break;
		}

		$field_group = $field_group_class->load_field_group( $group_id );
		$viewmodel_factory = new ViewmodelFactory();
		$field_group_viewmodel = $viewmodel_factory->create_viewmodel( $field_group );

		return $field_group_viewmodel;
	}


	/**
	 * Deletes a custom fields group.
	 *
	 * @since 2.3
	 * @param int	$group_id ID of the group (post->ID).
	 * @param string $domain A valid domain: posts, users, terms.
	 * @return bool|WP_Error True for success, false or WP_Error on error.
	 */
	public function delete_group( $group_id, $domain ) {
		/** @var \OTGS\Toolset\Common\Auryn\Injector $dic */
		if( ! $dic = apply_filters( 'toolset_dic', false ) ) {
			return new WP_Error( 42, __( 'Technical error. Please contact our support.', 'wpcf' ) );
		}

		if( ! $field_group_post = get_post( $group_id ) ) {
			return new WP_Error( 42, __( 'Technical error. Please contact our support.', 'wpcf' ) );
		}

		try {
			switch ( $domain ) {
				case Toolset_Field_Utils::DOMAIN_POSTS:
					/** @var \OTGS\Toolset\Types\Controller\Field\Group\Post\Deletion $deletion_controller */
					$deletion_controller = $dic->make( '\OTGS\Toolset\Types\Controller\Field\Group\Post\Deletion' );
					$group = new Toolset_Field_Group_Post( $field_group_post );
					break;
				case Toolset_Field_Utils::DOMAIN_USERS:
					$deletion_controller = $dic->make( '\OTGS\Toolset\Types\Controller\Field\Group\User\Deletion' );
					$group = new Toolset_Field_Group_User( $field_group_post );
					break;
				case Toolset_Field_Utils::DOMAIN_TERMS:
					$deletion_controller = $dic->make( '\OTGS\Toolset\Types\Controller\Field\Group\Term\Deletion' );
					$group = new Toolset_Field_Group_Term( $field_group_post );
					break;
				default:
					return new WP_Error( 42, __( 'Technical error.  Please contact our support.', 'wpcf' ) );
			}

			$deletion_controller->delete( $group );

		} catch( \OTGS\Toolset\Types\Access\Exception $e ) {
			// User has no access
			return new WP_Error( 42, __( 'You do not have permissions for that.', 'wpcf' ) );

		} catch( Exception $e ) {
			// Something else went wrong (probably class resolving).
			return new WP_Error( 42, __( 'Technical error. Please contact our support.', 'wpcf' ) );
		}

		return true;
	}


	/**
	 * Toggles active.
	 *
	 * 'Active' uses post_status: publish = activete, draft = deactivate
	 *
	 * @since 2.3
	 * @param int	 $group_id ID of the group (post->ID).
	 * @param string  $domain A valid domain: posts, users, terms.
	 * @param boolean $status If setted, the value will be saved, if no it will be
	 *												the logical negation.
	 * @return ViewmodelInterface|WP_Error Field group viewmodel for success, false or WP_Error on error.
	 */
	public function toggle_active( $group_id, $domain, $status = null ) {
		$group = $this->check_post_domain( $group_id, $domain );
		if ( is_wp_error( $group ) ) {
			return $group;
		}

		$is_active = $group->is_active();
		$group->is_active( null !== $status? $status : ! $is_active );

		return $this->check_post_domain( $group_id, $domain );
	}


}
