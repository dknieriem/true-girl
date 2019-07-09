<?php


class MC4WP_Ecommerce_Helper {

	/**
	* @var WPDB
	*/
	private $db;

	/**
	* MC4WP_Ecommerce_Helper constructor.
	*/
	public function __construct() {
		$this->db = $GLOBALS['wpdb'];
	}

	public function get_order_ids() {
		$query = $this->get_order_query( 'p.id', false );
		return $this->db->get_col( $query );
	}

	public function get_tracked_order_ids() {
		$query = $this->get_order_query( 'p.id', true );
		return $this->db->get_col( $query );
	}

	public function get_product_ids() {
		$query = $this->get_product_query( 'p.id' );
		return $this->db->get_col( $query );
	}

	public function get_tracked_product_ids() {
		$query = $this->get_product_query( 'p.id', true );
		return $this->db->get_col( $query );
	}

	/**
	* @param string $select
	* @param bool $untracked_only
	*
	* @return string
	*/
	private function get_product_query( $select = 'p.*' , $tracked_only = false) {
		$query = "SELECT %s
		FROM {$this->db->posts}	p
		WHERE p.post_type = 'product'
		AND p.post_status IN('publish', 'draft', 'private', 'trash')";

		$query = sprintf( $query, $select ) . ' ';

		if( $tracked_only ) {
			$query .= sprintf( " AND p.id IN ( SELECT post_id FROM {$this->db->postmeta} WHERE p.id = post_id AND meta_key = '%s' )", MC4WP_Ecommerce::META_KEY );
		}

		// order by descending product ID so we start with newest orders first
		if( strpos( $select, 'COUNT' ) === false ) {
			$query .= " ORDER BY p.id DESC";
		}

		return $query;
	}

	/**
	* @param string $select
	* @param bool $tracked_only
	*
	* @return string
	*/
	private function get_order_query( $select = 'p.*', $tracked_only = false ) {
		$query = "
		SELECT %s
		FROM {$this->db->posts}	p
		WHERE p.post_type = 'shop_order'
		AND p.post_status IN( %s )
		AND p.id IN ( SELECT pm.post_id FROM {$this->db->postmeta} pm WHERE pm.post_id = p.id AND ( pm.meta_key = '_billing_email' OR pm.meta_key = 'billing_email' OR pm.meta_key = '_customer_user' ) AND pm.meta_value != '' )";

		// IMPORTANT: not all orders have a _billing_email meta value.

		// add IN clause for order statuses
		$order_statuses = mc4wp_ecommerce_get_order_statuses();
		$query = sprintf( $query, $select . ' ',  "'" . join( "', '", $this->db->_escape( $order_statuses ) ) . "'" );

		if( $tracked_only ) {
			$query .= sprintf( " AND p.id IN ( SELECT post_id FROM {$this->db->postmeta} WHERE meta_key = '%s' )", MC4WP_Ecommerce::META_KEY );
		}

		// order by descending order ID so we start with newest orders first
		if( strpos( $select, 'COUNT' ) === false ) {
			$query .= " ORDER BY p.id DESC";
		}

		return $query;
	}

	/**
	* @param string $email_address
	*
	* @return float
	* @see wc_get_customer_total_spent
	*/
	public function get_total_spent_for_email( $email_address ) {

		// use WooCommmerce method when this is a registered customer
		// please note that this uses the WooCommerce registered order types for "reports"
		$user = get_user_by( 'email', $email_address );
		if( $user instanceof WP_User && in_array( 'customer', $user->roles ) ) {
			return floatval( wc_get_customer_total_spent( $user->ID ) );
		}

		$order_statuses = mc4wp_ecommerce_get_order_statuses();
		$in = join( "', '", $this->db->_escape( $order_statuses ) );

		$query = "SELECT SUM(meta2.meta_value)
		FROM {$this->db->posts} as posts
		LEFT JOIN {$this->db->postmeta} AS meta ON posts.ID = meta.post_id AND ( meta.meta_key = '_billing_email' OR meta.meta_key = 'billing_email' )
		LEFT JOIN {$this->db->postmeta} AS meta2 ON posts.ID = meta2.post_id AND meta2.meta_key = '_order_total'
		WHERE   meta.meta_value     = %s
		AND     posts.post_type     = 'shop_order'
		AND     posts.post_status   IN( '{$in}' )
		";

		$query = $this->db->prepare( $query, $email_address );

		$result = $this->db->get_var( $query );
		return floatval( $result );
	}

	/**
	* @param string $email_address
	*
	* @return int
	* @see wc_get_customer_order_count
	*/
	public function get_order_count_for_email( $email_address ) {

		// use WooCommmerce method when this is a registered customer
		// please note that this uses the WooCommerce registered order types for "reports"
		$user = get_user_by( 'email', $email_address );
		if( $user instanceof WP_User && in_array( 'customer', $user->roles ) ) {
			return intval( wc_get_customer_order_count( $user->ID ) );
		}

		$order_statuses = mc4wp_ecommerce_get_order_statuses();
		$in = join( "', '", $this->db->_escape( $order_statuses ) );

		$query = "SELECT COUNT(DISTINCT(posts.id))
		FROM {$this->db->posts} as posts
		LEFT JOIN {$this->db->postmeta} AS meta ON posts.ID = meta.post_id  
		WHERE meta.meta_key = '_billing_email' AND meta.meta_value     = %s
		AND posts.post_type     = 'shop_order'
		AND posts.post_status   IN( '{$in}' )
		";

		$query = $this->db->prepare( $query, $email_address );
		$result = $this->db->get_var( $query );
		return intval( $result );
	}



}
