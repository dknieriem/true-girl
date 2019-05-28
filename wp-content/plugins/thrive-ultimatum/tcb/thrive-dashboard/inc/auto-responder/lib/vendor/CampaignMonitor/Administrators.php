<?php

/**
 * Class to access the administrator resources from the create send API.
 * This class includes functions to add and remove administrators,
 * along with getting details for a single administrator
 * @author pauld
 *
 */
class Thrive_Dash_Api_CampaignMonitor_Administrators extends Thrive_Dash_Api_CampaignMonitor_Base {

	/**
	 * The base route of the people resource.
	 * @var string
	 * @access private
	 */
	var $_admins_base_route;

	/**
	 * Constructor.
	 *
	 * @param $auth_details array Authentication details to use for API calls.
	 *        This array must take one of the following forms:
	 *        If using OAuth to authenticate:
	 *        array(
	 *          'access_token' => 'your access token',
	 *          'refresh_token' => 'your refresh token')
	 *
	 *        Or if using an API key:
	 *        array('api_key' => 'your api key')
	 * @param $protocol string The protocol to use for requests (http|https)
	 * @param $debug_level int The level of debugging required Thrive_Dash_Api_CampaignMonitor_Log_NONE | Thrive_Dash_Api_CampaignMonitor_Log_ERROR | Thrive_Dash_Api_CampaignMonitor_Log_WARNING | Thrive_Dash_Api_CampaignMonitor_Log_VERBOSE
	 * @param $host string The host to send API requests to. There is no need to change this
	 * @param $log Thrive_Dash_Api_CampaignMonitor_Log The logger to use. Used for dependency injection
	 * @param $serialiser The serialiser to use. Used for dependency injection
	 * @param $transport The transport to use. Used for dependency injection
	 *
	 * @access public
	 */
	function __construct(
		$auth_details,
		$protocol = 'http',
		$debug_level = Thrive_Dash_Api_CampaignMonitor_Log_NONE,
		$host = 'api.createsend.com',
		$log = null,
		$serialiser = null,
		$transport = null
	) {

		parent::__construct( $auth_details, $protocol, $debug_level, $host, $log, $serialiser, $transport );
		$this->_admins_base_route = $this->_base_route . 'admins';
	}

	/**
	 * Adds a new administrator to the current account
	 *
	 * @param array $admin The administrator details to use during creation.
	 *     This array should be of the form
	 *     array (
	 *         'EmailAddress' => The new administrator email address
	 *         'Name' => The name of the new administrator
	 *     )
	 *
	 * @access public
	 * @return Thrive_Dash_Api_CampaignMonitor_Result A successful response will be empty
	 */
	function add( $admin ) {
		return $this->post_request( $this->_admins_base_route . '.json', $admin );
	}

	/**
	 * Updates details for an existing administrator associated with the current account
	 *
	 * @param string $email The email address of the administrator to be updated
	 * @param array $admin The updated administrator details to use for the update.
	 *     This array should be of the form
	 *     array (
	 *         'EmailAddress' => The new email address
	 *         'Name' => The updated name of the administrator
	 *     )
	 *
	 * @access public
	 * @return Thrive_Dash_Api_CampaignMonitor_Result A successful response will be empty
	 */
	function update( $email, $admin ) {
		return $this->put_request( $this->_admins_base_route . '.json?email=' . urlencode( $email ), $admin );
	}

	/**
	 * Gets the details for a specific administrator
	 * @access public
	 * @return Thrive_Dash_Api_CampaignMonitor_Result A successful response will be an object of the form
	 * {
	 *     'EmailAddress' => The email address of the administrator
	 *     'Name' => The name of the administrator
	 *     'Status' => The status of the administrator
	 *     )
	 * }
	 */
	function get( $email ) {
		return $this->get_request( $this->_admins_base_route . '.json?email=' . urlencode( $email ) );
	}


	/**
	 * deletes the given administrator from the current account
	 *
	 * @param string $email The email address of the administrator to delete
	 *
	 * @access public
	 * @return Thrive_Dash_Api_CampaignMonitor_Result A successful response will be empty
	 */
	function delete( $email ) {
		return $this->delete_request( $this->_admins_base_route . '.json?email=' . urlencode( $email ) );
	}
}