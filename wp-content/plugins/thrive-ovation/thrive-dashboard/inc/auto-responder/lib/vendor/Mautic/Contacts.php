<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

/**
 * Contacts Context
 */
class Thrive_Dash_Api_Mautic_Contacts extends Thrive_Dash_Api_Mautic_Api {
	/**
	 * {@inheritdoc}
	 */
	protected $endpoint = 'contacts';

	/**
	 * Get a list of users available as contact owners
	 *
	 * @return array|mixed
	 */
	public function getOwners() {
		return $this->makeRequest( $this->endpoint . '/list/owners' );
	}

	/**
	 * Get a list of custom fields
	 *
	 * @return array|mixed
	 */
	public function getFieldList() {
		return $this->makeRequest( $this->endpoint . '/list/fields' );
	}

	/**
	 * Get a list of contact segments
	 *
	 * @return array|mixed
	 */
	public function getSegments() {
		return $this->makeRequest( $this->endpoint . '/list/segments' );
	}

	/**
	 * Get a list of a contact's notes
	 *
	 * @param int $id Contact ID
	 * @param string $search
	 * @param int $start
	 * @param int $limit
	 * @param string $orderBy
	 * @param string $orderByDir
	 *
	 * @return array|mixed
	 */
	public function getContactNotes( $id, $search = '', $start = 0, $limit = 0, $orderBy = '', $orderByDir = 'ASC' ) {
		$parameters = array();

		$args = array( 'search', 'start', 'limit', 'orderBy', 'orderByDir' );

		foreach ( $args as $arg ) {
			if ( ! empty( $$arg ) ) {
				$parameters[ $arg ] = $$arg;
			}
		}

		return $this->makeRequest( $this->endpoint . '/' . $id . '/notes', $parameters );
	}

	/**
	 * Get a segment of smart segments the lead is in
	 *
	 * @param $id
	 *
	 * @return array|mixed
	 */
	public function getContactSegments( $id ) {
		return $this->makeRequest( $this->endpoint . '/' . $id . '/segments' );
	}

	/**
	 * Get a segment of campaigns the lead is in
	 *
	 * @param $id
	 *
	 * @return array|mixed
	 */
	public function getContactCampaigns( $id ) {
		return $this->makeRequest( $this->endpoint . '/' . $id . '/campaigns' );
	}
}
