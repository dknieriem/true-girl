<?php


class MC4WP_Ecommerce_Helper {

	/**
	 * @var WPDB
	 */
	private $db;

	/**
	 * @var int
	 */
	private $order_count;

	/**
	 * MC4WP_Ecommerce_Helper constructor.
	 */
	public function __construct() {
		$this->db = $GLOBALS['wpdb'];
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public function get_untracked_order_ids( $offset = 0, $limit = 1000 ) {
		$sql = $this->get_untracked_order_sql();
		$sql .= " ORDER BY p.ID DESC LIMIT %d, %d";
		$query = $this->db->prepare( $sql, MC4WP_Ecommerce::META_KEY, $offset, $limit );
		$results = $this->db->get_col( $query );
		return $results;
	}

	/**
	 * @return int
	 */
	public function get_untracked_order_count() {
		if( ! is_null( $this->order_count ) ) {
			return $this->order_count;
		}

		$sql = $this->get_untracked_order_sql( 'COUNT(DISTINCT p.ID)' );
		$query = $this->db->prepare( $sql, MC4WP_Ecommerce::META_KEY );
		return (int) $this->db->get_var( $query );
	}

	/**
	 * Fetches post ID's for both WooCommerce and Easy Digital Downloads orders
	 *
	 * @param string $columns The columns to select
	 *
	 * @return string
	 */
	private function get_untracked_order_sql( $columns = 'DISTINCT p.ID' ) {

		$post_types = array( 'edd_payment', 'shop_order' );
		$order_statuses = array( 'wc-completed', 'publish' );

		/**
		 * Filters the order statuses to send to MailChimp
		 *
		 * @param array $order_statuses
		 */
		$order_statuses = apply_filters( 'mc4wp_ecommerce360_order_statuses', $order_statuses );

		// generate the SQL string (unprepared)
		$sql = "SELECT {$columns} FROM {$this->db->posts} p";
		$sql .= " LEFT JOIN {$this->db->postmeta} pm ON pm.post_id = p.ID";
		$sql .= sprintf( " WHERE p.post_type IN ( %s )", "'" . join( "', '", $this->db->_escape( $post_types ) ) . "'" );
		$sql .= sprintf( " AND p.post_status IN ( %s )", "'" . join( "', '", $this->db->_escape( $order_statuses ) ) . "'" );

		// Meta key to store whether order was tracked should not exist
		$sql .= " AND NOT EXISTS (";
		$sql .= " SELECT meta_key FROM {$this->db->postmeta} pm2";
		$sql .= " WHERE pm2.meta_key = %s";
		$sql .= " AND pm2.post_id = p.ID";
		$sql .= ")";

		// Email meta (or associated user account) should be set
		$sql .= " AND ( pm.meta_key = '_billing_email' OR pm.meta_key = '_edd_payment_user_email' OR pm.meta_key = '_customer_user' )";
 		$sql .= " AND pm.meta_value != ''";

		return $sql;
	}

}