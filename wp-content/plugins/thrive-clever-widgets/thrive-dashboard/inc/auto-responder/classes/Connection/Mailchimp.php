<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 02.04.2015
 * Time: 15:33
 */
class Thrive_Dash_List_Connection_Mailchimp extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 * @return String
	 */
	public static function getType() {
		return 'autoresponder';
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return 'Mailchimp';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function outputSetupForm() {
		$this->_directFormHtml( 'mailchimp' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function readCredentials() {
		$key = ! empty( $_POST['connection']['key'] ) ? $_POST['connection']['key'] : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid Mailchimp key', TVE_DASH_TRANSLATE_DOMAIN ) );
		}

		$this->setCredentials( $_POST['connection'] );

		$result = $this->testConnection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to Mailchimp using the provided key (<strong>%s</strong>)', TVE_DASH_TRANSLATE_DOMAIN ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'Mailchimp connected successfully', TVE_DASH_TRANSLATE_DOMAIN ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function testConnection() {
		$mc = $this->getApi();
		/**
		 * just try getting a list as a connection test
		 */

		try {
			$mc->lists->getList();
		} catch ( Thrive_Dash_Api_Mailchimp_Error $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function _apiInstance() {
		return new Thrive_Dash_Api_Mailchimp( $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _getLists() {
		/** @var Thrive_Dash_Api_Mailchimp $api */
		$api = $this->getApi();

		try {
			$lists = array();

			$raw = $api->lists->getList( array(), 0, 100 );
			if ( empty( $raw['total'] ) || empty( $raw['data'] ) ) {
				return array();
			}
			foreach ( $raw['data'] as $item ) {
				$lists [] = array(
					'id'          => $item['id'],
					'name'        => $item['name'],
					'group_count' => $item['stats']['grouping_count']
				);
			}

			return $lists;
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage() . ' ' . __( "Please re-check your API connection details.", TVE_DASH_TRANSLATE_DOMAIN );

			return false;
		}
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	protected function _getGroups( $params ) {

		$groupings = '';

		$api   = $this->getApi();
		$lists = $api->lists->getList( array(), 0, 100 );

		if ( empty( $params['list_id'] ) && ! empty( $lists ) ) {
			$params['list_id'] = $lists['data'][0]['id'];
		}

		foreach ( $lists['data'] as $list ) {
			if ( $list['id'] == $params['list_id'] && $list['stats']['group_count'] > 0 ) {
				$groupings = $api->lists->interestGroupings( $params['list_id'] );
			}
		}
		
		if ( isset( $params['grouping_id'] ) ) {
			foreach ( $groupings as $grouping ) {
				if ( $grouping['id'] == $params['grouping_id'] ) {
					$groupings = $grouping;
				}
			}
		}

		return $groupings;
	}


	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|string true for success or string error message for failure
	 */
	public function addSubscriber( $list_identifier, $arguments ) {
		list( $first_name, $last_name ) = $this->_getNameParts( $arguments['name'] );

		$double_optin = isset( $arguments['mailchimp_optin'] ) && $arguments['mailchimp_optin'] == 's' ? false : true;

		/** @var Thrive_Dash_Api_Mailchimp $api */
		$api = $this->getApi();

		$merge_tags = array(
			'FNAME' => $first_name,
			'LNAME' => $last_name,
			'NAME'  => $arguments['name']
		);

		if ( isset( $arguments['mailchimp_groupin'] ) && $arguments['mailchimp_groupin'] != 0 && ! empty( $arguments['mailchimp_group'] ) ) {
			$group_ids             = explode( ',', $arguments['mailchimp_group'] );
			$params['list_id']     = $list_identifier;
			$params['grouping_id'] = $arguments['mailchimp_groupin'];
			$grouping              = $this->_getGroups( $params );

			foreach ( $grouping['groups'] as $group ) {
				if ( in_array( $group['id'], $group_ids ) ) {
					$groups[] = $group['name'];
				}
			}

			$merge_tags['groupings'] = array(
				array(
					'id'     => $arguments['mailchimp_groupin'],
					'groups' => $groups,
				)
			);
		}

		if ( isset( $arguments['phone'] ) ) {
			$merge_vars = $this->getCustomFields( $list_identifier );
			foreach ( $merge_vars as $item ) {
				if ( $item['field_type'] == 'phone' ) {
					$merge_tags[ $item['name'] ] = $arguments['phone'];
					$merge_tags[ $item['tag'] ]  = $arguments['phone'];
				}
			}
		}

		$member = $api->lists->memberInfo( $list_identifier, array( array( 'email' => $arguments['email'] ) ) );

		if($member['error_count'] == 1 || $member['data'][0]['status'] == 'unsubscribed') {
			try {
				$api->lists->subscribe(
					$list_identifier,
					array( 'email' => $arguments['email'] ),
					$merge_tags,
					'html',
					$double_optin,
					true
				);

				return true;
			} catch ( Thrive_Dash_Api_Mailchimp_Error $e ) {
				return $e->getMessage() ? $e->getMessage() : __( 'Unknown Mailchimp Error', TVE_DASH_TRANSLATE_DOMAIN );
			} catch ( Exception $e ) {
				return $e->getMessage() ? $e->getMessage() : __( 'Unknown Error', TVE_DASH_TRANSLATE_DOMAIN );
			}
		} else {

			$existing_groups = array();
			if(isset($member['data'][0]['merges']['GROUPINGS'])) {
				foreach($member['data'][0]['merges']['GROUPINGS'] as $grouping) {
					foreach($grouping['groups'] as $group) {
						if($group['interested'] == true) {
							$grouping_id = $grouping['id'];
							$existing_groups[$grouping_id]['id'] = $grouping_id;
							$existing_groups[$grouping_id]['groups'][] = $group['name'];
						}
					}
				}
			}


			$merge_tags['groupings'] = array_merge($merge_tags['groupings'], $existing_groups);

			try {
				$api->lists->updateMember(
					$list_identifier,
					array( 'email' => $arguments['email'] ),
					$merge_tags,
					false
				);

				return true;
			} catch ( Thrive_Dash_Api_Mailchimp_Error $e ) {
				return $e->getMessage() ? $e->getMessage() : __( 'Unknown Mailchimp Error', TVE_DASH_TRANSLATE_DOMAIN );
			} catch ( Exception $e ) {
				return $e->getMessage() ? $e->getMessage() : __( 'Unknown Error', TVE_DASH_TRANSLATE_DOMAIN );
			}
		}


	}

	/**
	 * Allow the user to choose whether to have a single or a double optin for the form being edited
	 * It will hold the latest selected value in a cookie so that the user is presented by default with the same option selected the next time he edits such a form
	 *
	 * @param array $params
	 */
	public function renderExtraEditorSettings( $params = array() ) {
		$params['optin'] = empty( $params['optin'] ) ? ( isset( $_COOKIE['tve_api_mailchimp_optin'] ) ? $_COOKIE['tve_api_mailchimp_optin'] : 'd' ) : $params['optin'];
		setcookie( 'tve_api_mailchimp_optin', $params['optin'], strtotime( '+6 months' ), '/' );
		$groups           = $this->_getGroups( $params );
		$params['groups'] = $groups;
		$this->_directFormHtml( 'mailchimp/api-groups', $params );
		$this->_directFormHtml( 'mailchimp/optin-type', $params );
	}

	/**
	 * @param $list
	 *
	 * @return mixed
	 */
	public function getCustomFields( $list ) {
		/** @var Thrive_Dash_Api_Mailchimp $api */
		$api = $this->getApi();

		$merge_vars = $api->lists->mergeVars( array( $list ) );
		if ( ! isset( $merge_vars['data'] ) || ! isset( $merge_vars['data'][0] ) ) {
			return array();
		}

		$list = $merge_vars['data'][0];
		if ( ! isset( $list['merge_vars'] ) ) {
			return array();
		}

		return $list['merge_vars'];
	}

	/**
	 * Return the connection email merge tag
	 * @return String
	 */
	public static function getEmailMergeTag() {
		return '*|EMAIL|*';
	}

}
