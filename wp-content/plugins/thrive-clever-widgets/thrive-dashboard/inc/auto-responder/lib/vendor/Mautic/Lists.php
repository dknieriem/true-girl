<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     MIT http://opensource.org/licenses/MIT
 */

/**
 * Lists Context
 */
class Thrive_Dash_Api_Mautic_Lists extends Thrive_Dash_Api_Mautic_Api {

	/**
	 * {@inheritdoc}
	 */
	protected $endpoint = 'segments';

	/**
	 * Add a lead to the list
	 *
	 * @param int $id List ID
	 * @param int $leadId Lead ID
	 *
	 * @return array|mixed
	 */
	public function addLead( $id, $leadId ) {
		return $this->makeRequest( $this->endpoint . '/' . $id . '/contact/add/' . $leadId, array(), 'POST' );
	}


	/**
	 * Remove a lead from the list
	 *
	 * @param int $id List ID
	 * @param int $leadId Lead ID
	 *
	 * @return array|mixed
	 */
	public function removeLead( $id, $leadId ) {
		return $this->makeRequest( $this->endpoint . '/' . $id . '/contact/remove/' . $leadId, array(), 'POST' );
	}
}
