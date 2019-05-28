<?php
/**
 * Handles database operations
 */

global $thodb;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class Tho_Db {
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
	 * @param $methodName
	 * @param $args
	 *
	 * @return mixed
	 */
	public function __call( $methodName, $args ) {
		return call_user_func_array( array( $this->wpdb, $methodName ), $args );
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
		$prefix = tho_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * Insert a new event in the log table and update the test items data
	 *
	 * @param $data
	 *
	 * @return int $log_id
	 */
	public function insert_event( $data ) {
		if ( empty( $data['date'] ) ) {
			$data['date'] = date( 'Y-m-d H:i:s' );
		}

		$this->wpdb->insert( tho_table_name( 'event_log' ), $data );
		$log_id = $this->wpdb->insert_id;

		if ( $log_id ) {
			$this->update_test_items_data( $data['variation'], $data['log_type'] );
		}

		return $log_id;
	}

	/**
	 * Update test items by increasing the views or engagements
	 *
	 * @param $variation_id int
	 * @param $field int|string THO_LOG_IMPRESSION|THO_LOG_ENGAGEMENT
	 * @param $value string value to be updated
	 *
	 * @return int
	 */
	public function update_test_items_data( $variation_id, $field, $value = '' ) {


		if ( $field == THO_LOG_IMPRESSION ) {
			$field = 'views';
			$value = $field . '+1';
		} elseif ( $field == THO_LOG_ENGAGEMENT ) {
			$field = 'engagements';
			$value = $field . '+1';
		}

		if ( empty( $field ) ) {
			return - 1;
		}

		$sql = "UPDATE " . tho_table_name( 'test_items' ) . " SET {$field}={$value} WHERE id = %d";

		return $this->wpdb->query( $this->prepare( $sql, array( $variation_id ) ) );
	}

	/**
	 * Get collection of all tests from DB filtered by params
	 *
	 * @param $filters array
	 * @param $single boolean return only one row from the db
	 *
	 * @return array
	 */
	public function get_tests( $filters = array(), $single = false ) {

		$params = array();
		$sql    = "SELECT ";

		if ( ! empty( $filters['status'] ) && $filters['status'] == THO_TEST_STATUS_COMPLETED ) {
			/* For completed tests, we select he title variation of the winner */
			$sql .= "items.variation_title AS winner_title, ";
		}

		$sql .= " tests.*, SUM(items.views) AS views, SUM(items.engagements) AS engagements
				FROM " . tho_table_name( 'tests' ) . " AS tests
		        JOIN " . tho_table_name( 'test_items' ) . " AS items ON items.test_id=tests.id
		        INNER JOIN " . $this->wpdb->posts . " AS post ON tests.post_id = post.id AND post.post_status = 'publish'
		        WHERE 1";

		if ( ! empty( $filters['test_id'] ) ) {
			$sql .= " AND tests.id = '%d'";
			$params[] = $filters['test_id'];
		}

		if ( ! empty( $filters['post_id'] ) ) {
			$sql .= " AND tests.post_id = '%d'";
			$params[] = $filters['post_id'];
		}

		if ( ! empty( $filters['status'] ) ) {
			$sql .= " AND tests.status = %d";
			$params[] = $filters['status'];

			if ( $filters['status'] == THO_TEST_STATUS_COMPLETED ) {
				/* For completed tests, we select he title variation of the winner */
				$sql .= " AND items.is_winner=1 ";
			}
		}

		if ( ! empty( $filters['start_date'] ) && ! empty( $filters['end_date'] ) ) {
			$sql .= " AND DATE(tests.`date_started`) BETWEEN  %s AND %s ";
			$params [] = $filters['start_date'] . ' 00:00:00';
			$params [] = $filters['end_date'] . ' 23:59:59';
		} elseif ( ! empty( $filters['start_date'] ) && empty( $filters['end_date'] ) ) {
			$sql .= " AND DATE(tests.`date_started`) <=  %s ";
			$params [] = $filters['start_date'] . ' 00:00:00';
		}

		$sql .= " GROUP BY tests.id ORDER BY tests.id DESC";

		if ( $single ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ) );
		} else {
			return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
		}
	}

	/**
	 * Select posts that have only completed tests
	 * @return array|null|object
	 */
	public function get_completed_test_posts() {

		$sql = "SELECT MAX(tests.id) AS id, tests.post_id, posts.post_title AS title, posts.post_type
				FROM " . tho_table_name( 'tests' ) . " AS tests
				INNER JOIN " . $this->wpdb->posts . " AS posts ON tests.post_id=posts.id AND posts.post_status = 'publish'
				WHERE tests.post_id NOT IN (SELECT post_id FROM " . tho_table_name( 'tests' ) . " WHERE status=%d)
				GROUP BY tests.post_id
				ORDER BY tests.id DESC";

		$params = array( THO_TEST_STATUS_ACTIVE );

		return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
	}

	/**
	 * Get test post variations
	 *
	 * @param $test_id
	 *
	 * @return array|null|object
	 */
	public function get_test_items( $test_id, $return_active_items = false ) {

		$params = array( $test_id );
		$where  = ' `test_id`=%d ';
		if ( $return_active_items ) {
			$where .= ' AND active = 1';
		}

		$sql = "SELECT * FROM " . tho_table_name( 'test_items' ) . " WHERE $where";

		return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
	}


	/**
	 * Get test post variation
	 *
	 * @param $test_id
	 *
	 * @return array|null|object
	 */
	public function get_test_item( $item_id ) {
		$sql = "SELECT * FROM " . tho_table_name( 'test_items' ) . " WHERE `id`=%d LIMIT 1";

		return $this->wpdb->get_row( $this->prepare( $sql, array( $item_id ) ) );
	}

	/**
	 * Saves a test in the database
	 *
	 * @param $model
	 *
	 * @return bool|int
	 */

	public function save_test( $model ) {

		/* make sure that we have an array */
		if ( is_object( $model ) ) {
			$model = get_object_vars( $model );
		}

		$_columns = array(
			'id',
			'post_id',
			'date_started',
			'date_completed',
			'config',
			'status'
		);

		foreach ( $model as $key => $data ) {
			if ( ! in_array( $key, $_columns ) ) {
				unset( $model[ $key ] );
			}
		}
		if ( ! empty( $model['id'] ) ) {
			$update_rows = $this->wpdb->update( tho_table_name( 'tests' ), $model, array( 'id' => $model['id'] ) );

			return $update_rows !== false;
		}

		$this->wpdb->insert( tho_table_name( 'tests' ), $model );
		$id = $this->wpdb->insert_id;

		return $id;
	}

	/**
	 * Saves a test item in the database
	 *
	 * @param $model
	 *
	 * @return bool|int
	 */

	public function save_test_item( $model ) {

		/* make sure that we have an array */
		if ( is_object( $model ) ) {
			$model = get_object_vars( $model );
		}

		$_columns = array(
			'id',
			'test_id',
			'variation_title',
			'is_control',
			'is_winner',
			'views',
			'engagements',
			'active',
			'stopped_date'
		);

		foreach ( $model as $key => $data ) {
			if ( ! in_array( $key, $_columns ) ) {
				unset( $model[ $key ] );
			}
		}
		if ( ! empty( $model['id'] ) ) {
			$update_rows = $this->wpdb->update( tho_table_name( 'test_items' ), $model, array( 'id' => $model['id'] ) );

			return $update_rows !== false;
		}

		$this->wpdb->insert( tho_table_name( 'test_items' ), $model );
		$id = $this->wpdb->insert_id;

		return $id;
	}

	/**
	 * Get all winner title variation from previous tests
	 *
	 * @param $post_id
	 *
	 * @return array|null|object
	 */
	public function get_winner_titles( $post_id ) {

		$sql = "SELECT tests.id AS test_id, items.variation_title AS title
				FROM " . tho_table_name( 'tests' ) . " tests
				JOIN " . tho_table_name( 'test_items' ) . " items  ON tests.id=items.test_id
			    INNER JOIN " . $this->wpdb->posts . " AS post ON tests.post_id = post.id AND post.post_status = 'publish'
				WHERE items.is_winner=1 AND tests.post_id=%d";

		return $this->wpdb->get_results( $this->prepare( $sql, array( $post_id ) ) );
	}


	/**
	 * Returns a count of log_types from a group in a time period
	 *
	 * @param $filter array of filters for the result
	 *
	 * @return array with number of conversions per group_id in a period of time
	 */
	public function get_chart_data_count_log_type( $filter ) {
		$date_interval = '';
		switch ( $filter['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`log`.`date`)," ", YEAR(`log`.`date`)) as date_interval';
				break;
			case 'week':
				$year          = "IF( WEEKOFYEAR(`log`.`date`) = 1 AND MONTH(`log`.`date`) = 12, 1 + YEAR(`log`.`date`), YEAR(`log`.`date`) )";
				$week          = "IF( WEEKOFYEAR(`log`.`date`) <= 9, CONCAT('0', WEEKOFYEAR(`log`.`date`)),  WEEKOFYEAR(`log`.`date`))";
				$date_interval = "CONCAT({$year},'W', {$week}) as date_interval";
				break;
			case 'day':
				$date_interval = 'DATE(`log`.`date`) as date_interval';
				break;
		}

		$sql = "SELECT IFNULL(COUNT( DISTINCT log.id ), 0) AS log_count,  post_id, engagement_type, log_type, {$date_interval} ";
		$sql .= " FROM " . tho_table_name( 'event_log' ) . " AS `log` ";
		$sql .= "  WHERE 1 ";

		$params = array();

		if ( ! empty( $filter['log_type'] ) ) {
			$sql .= "AND `log_type` = %d ";
			$params [] = $filter['log_type'];
		}

		if ( ! empty( $filter['post_id'] ) ) {
			$sql .= "AND `post_id` = %d ";
			$params [] = $filter['post_id'];
		}

		if ( ! empty( $filter['start-date'] ) && ! empty( $filter['end-date'] ) ) {
			$filter['end-date'] .= ' 23:59:59';

			$sql .= "AND `date` BETWEEN %s AND %s ";
			$params [] = $filter['start-date'];
			$params [] = $filter['end-date'];
		}

		if ( ! empty( $filter['engagement_type'] ) ) {
			$sql .= "AND `engagement_type` = %d ";
			$params [] = $filter['engagement_type'];
		}

		if ( isset( $filter['archived_log'] ) ) {
			$sql .= "AND `archived` = %d ";
			$params [] = $filter['archived_log'];
		}

		if ( ! empty( $filter['group_by'] ) && count( $filter['group_by'] ) > 0 ) {
			$sql .= 'GROUP BY ' . implode( ', ', $filter['group_by'] );
		}

		$sql .= ' ORDER BY ';
		if ( ! empty( $filter['order_by'] ) && ! empty( $filter['order_dir'] ) ) {
			$sql .= $filter['order_by'] . ' ' . $filter['order_dir'] . ', ';
		}
		$sql .= ' `log`.`date` DESC';

		return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
	}

	/**
	 * @param $filters
	 *
	 * @return array|null|object
	 */
	public function get_test_chart_data( $filters ) {
		$date_interval = '';
		switch ( $filters['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`log`.`date`)," ", YEAR(`log`.`date`)) as date_interval';
				break;
			case 'week':
				$year          = "IF( WEEKOFYEAR(`log`.`date`) = 1 AND MONTH(`log`.`date`) = 12, 1 + YEAR(`log`.`date`), YEAR(`log`.`date`) )";
				$week          = "IF( WEEKOFYEAR(`log`.`date`) <= 9, CONCAT('0', WEEKOFYEAR(`log`.`date`)),  WEEKOFYEAR(`log`.`date`))";
				$date_interval = "CONCAT({$year},'W', {$week}) as date_interval";
				break;
			default:
			case 'day':
				$date_interval = 'DATE(`log`.`date`) as date_interval';
				break;
		}

		$sql = "SELECT IFNULL(COUNT( DISTINCT log.id ), 0) AS log_count, variation, log_type, {$date_interval}
 				FROM " . tho_table_name( 'event_log' ) . " AS log
 				WHERE 1 ";

		$params = array();
		if ( ! empty( $filters['post_id'] ) ) {
			$sql .= "AND `post_id` = %d ";
			$params [] = $filters['post_id'];
		}

		if ( ! empty( $filters['start_date'] ) && ! empty( $filters['end_date'] ) ) {
			$sql .= "AND `date` BETWEEN %s AND %s ";
			$params [] = $filters['start_date'];
			$params [] = $filters['end_date'];
		}

		$sql .= ' GROUP BY variation, log_type, date_interval';

		return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
	}

	/**
	 * Returns all the statistic information needed for the meta box in the post view
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_post_test_statistics( $post_id ) {

		$query  = "SELECT log.log_type, log.engagement_type, IFNULL(COUNT( DISTINCT log.id ), 0) AS s FROM " . tho_table_name( 'event_log' ) . " log
		 INNER JOIN " . tho_table_name( 'tests' ) . " test ON log.post_id = test.post_id
		 WHERE log.post_id = %s  AND test.status = %s AND test.date_started <= log.date
		 GROUP BY log.log_type, log.engagement_type";
		$params = array( $post_id, THO_TEST_STATUS_ACTIVE );
		$result = $this->wpdb->get_results( $this->prepare( $query, $params ) );

		return $result;
	}

	/**
	 * Custom function for getting WP posts so we won't have to build filters and other things
	 *
	 * @param $filters
	 * @param $count
	 *
	 * @return array|null|object
	 */
	public function get_posts( $filters, $count = false ) {

		$select = "*";
		if ( $filters['select'] && is_array( $filters['select'] ) ) {
			$select = implode( ', ', $filters['select'] );
		}

		$sql = "SELECT " . $select . " FROM " . $this->wpdb->posts . " WHERE 1 ";

		$params = array();

		if ( ! empty( $filters['post_types'] ) ) {
			$placeholder = array_fill( 1, count( $filters['post_types'] ), "'%s'" );
			$sql .= " AND `post_type` IN (" . implode( ",", $placeholder ) . ")";
		} else {
			$sql .= " AND `post_type` IN ('')";
		}
		$params = $filters['post_types'];

		if ( ! empty( $filters['post_status'] ) ) {
			$sql .= " AND `post_status` = '%s' ";
			$params [] = $filters['post_status'];
		}

		if ( ! empty( $filters['exclude'] ) ) {
			$placeholder = array_fill( 1, count( $filters['exclude'] ), "%d" );

			$sql .= " AND `ID` NOT IN (" . implode( ",", $placeholder ) . ")";
			$params = array_merge( $params, $filters['exclude'] );

		}

		if ( ! empty( $filters['search_by'] ) ) {
			$sql .= " AND `post_title` LIKE '%s' ";
			$params [] = "%" . $filters['search_by'] . "%";
		}

		$sql .= " ORDER BY `ID` DESC ";

		if ( ! $count && ! empty( $filters['per_page'] ) && ! empty( $filters['page'] ) ) {
			$start = absint( ( $filters['page'] - 1 ) * $filters['per_page'] );

			$sql .= " LIMIT %d, %d";
			$params [] = $start;
			$params [] = $filters['per_page'];

		}

		$posts = $this->wpdb->get_results( $this->prepare( $sql, $params ) );

		if ( $count ) {
			return $this->wpdb->num_rows;
		} else {
			return $posts;
		}

	}

	/**
	 * Delete test
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function delete_test( $id ) {
		return $this->wpdb->delete( tho_table_name( 'tests' ), array( 'id' => $id ) ) && $this->delete_test_variations( array( 'test_id' => $id ) );
	}

	/**
	 * Remove test items
	 *
	 * @param $filters
	 *
	 * @return array|int|null|object
	 */
	private function delete_test_variations( $filters ) {

		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
		}

		if ( ! empty( $filters['test_id'] ) ) {
			$params ['test_id'] = $filters['test_id'];
		}

		if ( empty( $params ) ) {
			/* we need at least one parameter so we won't empty the table by mistake */
			return 0;
		} else {
			return $this->wpdb->delete( tho_table_name( 'test_items' ), $params );
		}
	}

	/**
	 * Remove log data
	 *
	 * @param $filters
	 *
	 * @return array|int|null|object
	 */
	public function delete_log_data( $filters ) {

		$params = array();

		if ( ! empty( $filters['variation'] ) ) {
			$params ['variation'] = $filters['variation'];
		}

		if ( ! empty( $filters['post_id'] ) ) {
			$params ['post_id'] = $filters['post_id'];
		}

		if ( ! empty( $params ) ) {
			/* we need at least one parameter so we won't empty the table by mistake */
			return $this->wpdb->delete( tho_table_name( 'event_log' ), $params );
		}
	}

}

$thodb = new Tho_Db();