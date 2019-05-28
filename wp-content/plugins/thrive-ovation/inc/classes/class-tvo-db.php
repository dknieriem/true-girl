<?php
/**
 * Handles database operations
 */

global $tvodb;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class Tvo_Db {
	/**
	 * @var $wpdb wpdb
	 */
	protected $wpdb = null;

	/**
	 * class constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * forward the call to the $wpdb object
	 *
	 * @param $method_name
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call( $method_name, $args ) {
		return call_user_func_array( array( $this->wpdb, $method_name ), $args );
	}

	/**
	 * unserialize fields from an array
	 *
	 * @param array $array where to search the fields
	 * @param array $fields fields to be unserialized
	 *
	 * @return array the modified array containing the unserialized fields
	 */
	protected function _unserialize_fields( $array, $fields = array() ) {

		foreach ( $fields as $field ) {
			if ( ! isset( $array[ $field ] ) ) {
				continue;
			}
			/* the serialized fields should be trigger_config and tcb_fields */
			$array[ $field ] = empty( $array[ $field ] ) ? array() : unserialize( $array[ $field ] );
			$array[ $field ] = wp_unslash( $array[ $field ] );

			/* extra checks to ensure we'll have consistency */
			if ( ! is_array( $array[ $field ] ) ) {
				$array[ $field ] = array();
			}
		}

		return $array;
	}

	/**
	 *
	 * replace table names in form of {table_name} with the prefixed version
	 *
	 * @param $sql
	 * @param $params
	 *
	 * @return false|null|string
	 */
	public function prepare( $sql, $params ) {
		$prefix = tvo_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * get activity logs
	 *
	 * @param post_id , offset, limit
	 *
	 * @return array/null
	 */
	public function get_activity_log( $post_id, $offset = 0, $limit = 4 ) {

		$sql = 'SELECT * FROM ' . tvo_table_name( 'activity_log' ) . ' WHERE `post_id` = %d ORDER BY `date` DESC LIMIT %d OFFSET %d';

		$items = $this->wpdb->get_results( $this->prepare( $sql, array( $post_id, $limit, $offset ) ), ARRAY_A );

		if ( empty( $items ) ) {
			return null;
		}
		foreach ( $items as $i => $item ) {
			$item        = $this->_unserialize_fields( $item, array( 'activity_data' ) );
			$items[ $i ] = $item;
		}

		return $items;
	}

	/**
	 * get sent email count
	 *
	 * @param post_id
	 *
	 * @return array/null
	 */
	public function get_send_email_count( $post_id ) {

		$sql = 'SELECT count(*) AS count FROM ' . tvo_table_name( 'activity_log' ) . ' WHERE `post_id` = %d AND `activity_type` = %s ';

		$count = $this->wpdb->get_results( $this->prepare( $sql, array( $post_id, TVO_LOG_EMAIL_SENT ) ) );

		if ( empty( $count[0] ) ) {
			return null;
		}
		$count = $count[0];

		return $count->count;
	}

	/**
	 * custom population of activity logs
	 *
	 * @param $post_id
	 * @param string $type
	 * @param array $activity_variables
	 */
	public function populate_activity_log( $post_id, $type = '', $activity_variables = array() ) {
		$defaults = array(
			'user_id' => get_current_user_id(),
		);

		$activity_data = array_merge( $defaults, $activity_variables );

		$data = array(
			'post_id'       => $post_id,
			'activity_type' => $type,
			'date'          => date_i18n( 'Y-m-d H:i:s' ),
			'activity_data' => serialize( $activity_data ),
		);

		$this->wpdb->insert( tvo_table_name( 'activity_log' ), $data );
	}

	/**
	 * count activity logs
	 *
	 * @param post_id
	 *
	 * @return int
	 */
	public function count_logs( $post_id ) {
		$sql = 'SELECT COUNT( e.id ) AS `entry_count` FROM `' . tvo_table_name( 'activity_log' ) . '` AS `e` WHERE `post_id` = %d ';

		return $this->wpdb->get_results( $this->prepare( $sql, array( $post_id ) ), ARRAY_A );
	}
}

$tvodb = new Tvo_Db();
