<?php
/**
 * Handles database operations
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/22/2016
 * Time: 5:16 PM
 */

global $tqbdb;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class TQB_Database {
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
		$prefix = tqb_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * save a variation into the database
	 *
	 * @param array $model
	 *
	 * @return int
	 */
	public function save_variation( $model = array() ) {

		$_columns = array(
			'id',
			'quiz_id',
			'date_added',
			'date_modified',
			'page_id',
			'parent_id',
			'is_control',
			'post_status',
			'post_title',
			'cache_impressions',
			'cache_optins',
			'cache_optins_conversions',
			'cache_social_shares',
			'cache_social_shares_conversions',
			'tcb_fields',
			'content',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['tcb_fields'] ) ) {
			$data['tcb_fields'] = serialize( $data['tcb_fields'] );
		} else {
			unset( $data['tcb_fields'] );
		}

		if ( ! empty( $data['content'] ) ) {
			$data['content'] = wp_unslash( $data['content'] );
		}

		if ( ! empty( $data['id'] ) ) {
			$data['date_modified'] = date( 'Y-m-d H:i:s' );
			$update_rows           = $this->wpdb->update( tqb_table_name( 'variations' ), $data, array( 'id' => $data['id'] ) );
			if ( $update_rows !== false ) {
				return $data['id'];
			}

			return $update_rows;
		}

		$this->wpdb->insert( tqb_table_name( 'variations' ), $data );
		$variation_id = $this->wpdb->insert_id;

		return $variation_id;
	}

	/**
	 * Get the running test items
	 *
	 * @param array $filters
	 * @param $return_type
	 *
	 * @return array|null|object
	 */
	public function get_test_items( $filters = array(), $return_type = ARRAY_A ) {

		$sql = 'SELECT * FROM ' . tqb_table_name( 'tests_items' ) . ' WHERE 1 ';

		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$sql .= ' AND id = %s';
			$params [] = $filters['id'];
		}

		if ( ! empty( $filters['test_id'] ) ) {
			$sql .= ' AND test_id = %d';
			$params [] = $filters['test_id'];
		}

		if ( ! empty( $filters['is_control'] ) ) {
			$sql .= ' AND is_control = %s';
			$params [] = $filters['is_control'];
		}

		if ( ! empty( $filters['is_winner'] ) ) {
			$sql .= ' AND is_winner = %s';
			$params [] = $filters['is_winner'];
		}

		if ( isset( $filters['active'] ) ) {
			$sql .= ' AND active = %d';
			$params [] = $filters['active'];
		}

		if ( ! empty( $filters['id'] ) ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
		}
		$sql .= ' ORDER BY id ASC';
		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );

		return $models;
	}

	/**
	 * Gets the test for checking the auto win settings
	 *
	 * @param array $filters
	 * @param bool $single
	 * @param string $return_type
	 *
	 * @return array|null|object|void
	 */
	public function get_tests( $filters = array(), $single = false, $return_type = ARRAY_A ) {
		$params = array();

		$query = 'SELECT tests.*, SUM(items.impressions) as impressions, SUM(items.optins_conversions) as optins_conversions, SUM(items.social_shares_conversions) as social_shares_conversions 
			FROM ' . tqb_table_name( 'tests' ) . ' AS tests 
			INNER JOIN ' . tqb_table_name( 'tests_items' ) . ' AS items ON tests.id = items.test_id 
			WHERE 1 ';

		if ( ! empty( $filters['test_id'] ) ) {
			$query .= " AND tests.id = '%d'";
			$params[] = $filters['test_id'];
		}

		if ( isset( $filters['status'] ) ) {
			$query .= " AND tests.status = '%d'";
			$params[] = $filters['status'];
		}

		/*Fetch only the active items*/
		$query .= " AND items.active = '%d'";
		$params[] = 1;

		$query .= ' GROUP BY tests.id ORDER BY tests.id DESC';

		if ( $single ) {
			return $this->wpdb->get_row( $this->prepare( $query, $params ), $return_type );
		} else {
			return $this->wpdb->get_results( $this->prepare( $query, $params ), $return_type );
		}
	}

	/**
	 *
	 * Gets the quiz page variations
	 *
	 * @param array $filters
	 * @param string $return_type
	 *
	 * @return array|null|object|void
	 */
	public function get_page_variations( $filters = array(), $return_type = ARRAY_A ) {

		$sql = 'SELECT * FROM ' . tqb_table_name( 'variations' ) . ' WHERE 1 ';

		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$sql .= ' AND id = %s';
			$params [] = $filters['id'];
		}

		if ( ! empty( $filters['post_id'] ) ) {
			$sql .= ' AND page_id = %d';
			$params [] = $filters['post_id'];
		}

		if ( ! empty( $filters['post_status'] ) ) {
			$sql .= ' AND post_status = %s';
			$params [] = $filters['post_status'];
		}

		/*check for parent id*/
		$sql .= ' AND parent_id = %s';
		if ( empty( $filters['parent_id'] ) ) {
			/*fetch only parent variations*/
			$params [] = 0;
		} else {
			/*For child variations*/
			$params [] = $filters['parent_id'];
		}

		/*can be 0 or 1*/
		if ( isset( $filters['is_control'] ) && is_numeric( $filters['is_control'] ) ) {
			$sql .= ' AND is_control = %d';
			$params [] = $filters['is_control'];
		}

		$sql .= ' ORDER BY id ASC';

		if ( ( ! empty( $filters['id'] ) ) || ( ! empty( $filters['is_control'] ) ) ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
		}
		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );

		foreach ( $models as $key => $model ) {
			if ( is_object( $model ) ) {
				$models[ $key ]->cache_optin_conversion_rate        = tqb_conversion_rate( $model->cache_impressions, $model->cache_optins_conversions );
				$models[ $key ]->cache_social_share_conversion_rate = tqb_conversion_rate( $model->cache_social_shares, $model->cache_social_shares_conversions );
				$models[ $key ]->tcb_fields                         = unserialize( $model->tcb_fields );
			} else {
				$models[ $key ]['cache_optin_conversion_rate']        = tqb_conversion_rate( $model['cache_impressions'], $model['cache_optins_conversions'] );
				$models[ $key ]['cache_social_share_conversion_rate'] = tqb_conversion_rate( $model['cache_social_shares'], $model['cache_social_shares_conversions'] );
				$models[ $key ]['tcb_fields']                         = unserialize( $model['tcb_fields'] );
			}
		};

		return $models;
	}

	/**
	 * Counts the quiz page variations
	 *
	 * @param array $filters
	 *
	 * @return null|string
	 */
	public function count_page_variations( $filters = array() ) {

		$sql    = 'SELECT COUNT(id) FROM ' . tqb_table_name( 'variations' ) . '  WHERE 1 ';
		$params = array();

		if ( ! empty( $filters['post_status'] ) ) {
			$sql .= ' AND post_status = %s';
			$params [] = $filters['post_status'];
		}

		if ( ! empty( $filters['post_id'] ) ) {
			$sql .= ' AND page_id = %d';
			$params [] = $filters['post_id'];
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$sql .= ' AND quiz_id = %d';
			$params [] = $filters['quiz_id'];
		}

		/*can be 0 or 1*/
		if ( isset( $filters['is_control'] ) && is_numeric( $filters['is_control'] ) ) {
			$sql .= ' AND is_control = %d';
			$params [] = $filters['is_control'];
		}

		return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
	}

	/**
	 * Get test according to filters
	 *
	 * @param array $filters
	 * @param bool $single
	 * @param string $return_type
	 *
	 * @return array|null|object|void
	 */
	public function get_test( $filters = array(), $single = false, $return_type = ARRAY_A ) {
		$params = array();
		$where  = ' 1=1 ';

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
			$where .= 'AND `id`=%d ';
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
			$where .= 'AND `page_id`=%d ';
		}

		if ( isset( $filters['status'] ) ) {
			$params ['status'] = $filters['status'];
			$where .= 'AND `status`=%d ';
		}

		$sql = 'SELECT * FROM ' . tqb_table_name( 'tests' ) . ' WHERE ' . $where;

		if ( $single ) {
			return $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
		} else {
			return $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );
		}

	}


	/**
	 * Deletes quiz variations
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_variations( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
		}

		/*can be 0 or 1*/
		if ( isset( $filters['parent_id'] ) && is_numeric( $filters['parent_id'] ) ) {
			$params ['parent_id'] = $filters['parent_id'];
		}

		if ( empty( $params ) ) {
			/* we need at least one parameter so we won't empty the table by mistake */
			return 0;
		} else {
			$this->delete_logs( $params );

			return $this->wpdb->delete( tqb_table_name( 'variations' ), $params );
		}
	}

	/**
	 * Deletes logs
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_logs( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['variation_id'] = $filters['id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
		}

		if ( empty( $params ) ) {
			return false;
		}

		return $this->wpdb->delete( tqb_table_name( 'event_log' ), $params );

	}

	public function get_variation( $id ) {

		$params = array( $id );
		$where  = ' `id`=%d ';
		$sql    = 'SELECT * FROM ' . tqb_table_name( 'variations' ) . ' WHERE ' . $where;

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
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
			'page_id',
			'date_started',
			'date_added',
			'date_completed',
			'config',
			'status',
			'conversion_goal',
			'title',
			'notes',
			'auto_win_enabled',
			'auto_win_min_conversions',
			'auto_win_min_duration',
			'auto_win_chance_original',
		);

		$data = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'tests' ), $data, array( 'id' => $data['id'] ) );

			return $update_rows !== false;
		}

		$this->wpdb->insert( tqb_table_name( 'tests' ), $data );
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
			'variation_id',
			'variation_title',
			'is_control',
			'is_winner',
			'impressions',
			'optins_conversions',
			'social_shares',
			'active',
			'stopped_date',
		);

		$data = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['is_winner'] ) ) {

			$test_model         = $this->get_test( array( 'id' => $data['test_id'] ), true, ARRAY_A );
			$stop_test          = $this->stop_test( $test_model );
			$archive_variations = $this->archive_losing_variations( $test_model, $data['variation_id'] );
			$set_winner         = $this->save_variation( array( 'id' => $data['variation_id'], 'is_control' => 1 ) );
		}

		if ( isset( $data['active'] ) && $data['active'] == 0 ) {
			$data['stopped_date'] = date( 'Y-m-d H:i:s' );
			$stopped              = $this->stop_test_if_no_items_left( $data );
			if ( $stopped ) {
				return true;
			}
		}

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'tests_items' ), $data, array( 'id' => $data['id'] ) );

			return $update_rows !== false;
		}

		$this->wpdb->insert( tqb_table_name( 'tests_items' ), $data );
		$id = $this->wpdb->insert_id;

		return $id;
	}

	/**
	 * Update test item if variation was displayed/acted upon
	 *
	 * @param $data
	 *
	 * @return bool|int
	 */
	public function update_test_item_action_counter( $data ) {
		if ( empty( $data['variation_id'] ) ) {
			return false;
		}
		$fields = '';
		$params = array();

		if ( ! empty( $data['impression'] ) ) {
			$fields .= ' impressions = impressions + 1, social_shares = social_shares + 1  ';
		}
		if ( ! empty( $data['conversion'] ) ) {
			$fields .= ' optins_conversions = optins_conversions + 1 ';
		}

		if ( ! empty( $data['social_shares_conversions'] ) ) {
			$fields .= ' social_shares_conversions = social_shares_conversions + 1 ';
		}
		$where = ' 1 ';

		$params ['active'] = 1;
		$where .= ' AND `active`=%d ';

		if ( ! empty( $data['variation_id'] ) ) {
			$params ['variation_id'] = $data['variation_id'];
			$where .= ' AND `variation_id`=%d ';
		}

		if ( ! empty( $data['test_id'] ) ) {
			$params ['test_id'] = $data['test_id'];
			$where .= ' AND `test_id`=%d ';
		}

		$sql = 'UPDATE ' . tqb_table_name( 'tests_items' ) . ' SET ' . $fields . ' WHERE ' . $where;

		return $this->wpdb->query( $this->wpdb->prepare( $sql, $params ) );
	}


	/**
	 * Update test item if variation was displayed/acted upon
	 *
	 * @param $data
	 *
	 * @return bool|int
	 */

	public function update_variation_cached_counter( $data ) {
		if ( empty( $data['variation_id'] ) ) {
			return false;
		}
		$fields = '';
		if ( ! empty( $data['impression'] ) ) {
			$fields .= ' cache_impressions = cache_impressions + 1, cache_social_shares = cache_social_shares + 1 ';
		}

		if ( ! empty( $data['conversion'] ) ) {
			$fields .= ' cache_optins_conversions = cache_optins_conversions + 1 ';
		}

		if ( ! empty( $data['social_conversion'] ) ) {
			$fields .= ' cache_social_shares_conversions = cache_social_shares_conversions + 1 ';
		}

		$where = ' `id`= %d';
		$sql   = 'UPDATE ' . tqb_table_name( 'variations' ) . ' SET ' . $fields . ' WHERE ' . $where;

		return $this->wpdb->query( $this->wpdb->prepare( $sql, array( 'id' => $data['variation_id'] ) ) );
	}

	/**
	 * Archive losing variations
	 *
	 * @param array $test_model
	 * @param array $winner_id
	 *
	 * @return false|int
	 */
	public function archive_losing_variations( $test_model = array(), $winner_id ) {
		$test_items = $this->get_test_items( array( 'test_id' => $test_model['id'] ) );
		foreach ( $test_items as $test_item ) {
			if ( $test_item['variation_id'] != $winner_id ) {
				$variation                = $this->get_variation( $test_item['variation_id'] );
				$variation['post_status'] = 'archive';
				$variation['is_control']  = 0;
				$variation                = $this->save_variation( $variation );
			}
		}

		return true;
	}

	/**
	 * Delete tests
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_tests( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
		}

		if ( ! empty( $params ) ) {
			$this->delete_page_test_items( $params );

			return $this->wpdb->delete( tqb_table_name( 'tests' ), $params );
		}

		return false;
	}

	/**
	 * Delete test items belonging to page
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_page_test_items( $filters = array() ) {
		if ( ! empty( $filters['id'] ) ) {
			$params ['test_id'] = $filters['id'];

			return $this->delete_test_items( $params );
		}

		if ( ! empty( $filters['page_id'] ) ) {
			$params ['page_id'] = $filters['page_id'];
			$prepared_statement = $this->wpdb->prepare( 'SELECT id FROM ' . tqb_table_name( 'tests' ) . ' WHERE  page_id = %d', $filters['page_id'] );
			$tests              = $this->wpdb->get_col( $prepared_statement );

			foreach ( $tests as $test ) {
				$params ['test_id'] = $test;
				$this->delete_test_items( $params );
			}
		}

		return true;
	}

	/**
	 * Delete test items
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_test_items( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['test_id'] ) ) {
			$params ['test_id'] = $filters['test_id'];
		}

		if ( ! empty( $params ) ) {
			return $this->wpdb->delete( tqb_table_name( 'tests_items' ), $params );
		}

		return false;
	}

	/**
	 * Stop test if no items left
	 *
	 * @param array $model
	 *
	 * @return false|int
	 */
	public function stop_test_if_no_items_left( $model ) {

		$test_items = $this->get_test_items( array( 'test_id' => $model['test_id'], 'active' => 1 ) );

		if ( count( $test_items ) < 3 ) {
			foreach ( $test_items as $item ) {
				if ( $item['id'] !== $model['id'] ) {
					$this->set_winner( $item );
				}
			}
			$test = $this->get_test( array( 'id' => $model['test_id'] ), true );
			$this->stop_test( $test );

			return true;
		}

		return false;
	}

	/**
	 * Stop test
	 *
	 * @param array $test
	 *
	 * @return false|int
	 */
	public function stop_test( $test ) {
		$test['status']         = 0;
		$test['date_completed'] = date( 'Y-m-d H:i:s' );

		return $this->save_test( $test );
	}

	/**
	 * Set winner
	 *
	 * @param array $item
	 *
	 * @return false|int
	 */
	public function set_winner( $item ) {

		$item['is_winner'] = 1;

		return $this->save_test_item( $item );
	}

	/**
	 * Returns a count of event_types from a group in a time period
	 *
	 * @param $filter Array of filters for the result
	 *
	 * @return Array with number of conversions per group_id in a period of time
	 */
	public function get_report_data_count_event_type( $filter ) {
		$date_interval = '';
		switch ( $filter['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`log`.`date`)," ", YEAR(`log`.`date`)) as date_interval';
				break;
			case 'week':
				$year          = 'IF( WEEKOFYEAR(`log`.`date`) = 1 AND MONTH(`log`.`date`) = 12, 1 + YEAR(`log`.`date`), YEAR(`log`.`date`) )';
				$date_interval = "CONCAT('Week ', WEEKOFYEAR(`log`.`date`), ', ', {$year}) as date_interval";
				break;
			case 'day':
				$date_interval = 'DATE(`log`.`date`) as date_interval';
				break;
		}

		$sql = 'SELECT IFNULL(COUNT( DISTINCT log.id ), 0) AS log_count, event_type, log.' . $filter['data_group'] . ' AS data_group, ' . $date_interval;

		if ( ! empty( $filter['unique_email'] ) && $filter['unique_email'] == 1 ) {
			/* count if this email is added for the first time. if so, this is a lead, else it's just a simple conversion */
			$sql .= ', SUM( IF( t_log.id IS NOT NULL , 1, 0) ) AS leads ';
		}

		$sql .= ' FROM ' . tqb_table_name( 'event_log' ) . ' AS `log` ';

		if ( ! empty( $filter['unique_email'] ) && $filter['unique_email'] == 1 ) {
			/* t_logs - temporary select to see if an email is added for the first time or not */
			$sql .= ' LEFT JOIN (SELECT user, MIN(id) AS id FROM ' . tqb_table_name( 'event_log' ) . ' GROUP BY user) AS t_log ON log.user=t_log.user AND log.id=t_log.id ';
		}

		$sql .= '  WHERE 1 ';

		$params = array();

		if ( ! empty( $filter['event_type'] ) ) {
			$sql .= 'AND `event_type` = %d ';
			$params [] = $filter['event_type'];
		}

		if ( ! empty( $filter['variation_id'] ) ) {
			$sql .= 'AND `variation_id` = %d ';
			$params [] = $filter['variation_id'];
		}

		if ( ! empty( $filter['conversion_goal'] ) ) {
			if ( $filter['conversion_goal'] === Thrive_Quiz_Builder::CONVERSION_GOAL_SOCIAL ) {
				$sql .= 'AND `social_share` = 1 ';
			} else {
				$sql .= 'AND `optin` = 1 ';
			}
		}

		if ( ! empty( $filter['page_id'] ) ) {
			$sql .= 'AND `page_id` = %d ';
			$params [] = $filter['page_id'];
		}

		if ( ! empty( $filter['start_date'] ) && ! empty( $filter['end_date'] ) ) {
			$timezone_diff = current_time( 'timestamp' ) - time();

			$sql .= 'AND `date` BETWEEN %s AND %s ';
			$params [] = $filter['start_date'];
			$params [] = date( 'Y-m-d H:i:s', ( strtotime( '+1 day', strtotime( $filter['end_date'] ) - 1 ) + $timezone_diff ) );
		}

		if ( ! empty( $filter['group_by'] ) && count( $filter['group_by'] ) > 0 ) {
			$sql .= 'GROUP BY ' . implode( ', ', $filter['group_by'] );
		}

		$sql .= ' ORDER BY `log`.`date` DESC';

		return $this->wpdb->get_results( $this->prepare( $sql, $params ) );
	}

	public function create_event_log_entry( $model ) {
		$_columns = array(
			'date',
			'event_type',
			'variation_id',
			'page_id',
			'user_unique',
			'optin',
			'social_share',
			'duplicate',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		$params = array( 'user_unique' => $data['user_unique'], 'event_type' => $data['event_type'], 'page_id' => $data['page_id'] );
		$where  = ' AND  `user_unique`=%s AND `event_type`=%d AND `page_id`=%d';
		$sql    = 'SELECT * FROM ' . tqb_table_name( 'event_log' ) . " WHERE 1 {$where}";

		$event_log = $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
		if ( ! empty( $event_log ) ) {
			$update_row = $this->wpdb->update( tqb_table_name( 'event_log' ), $data, array( 'id' => $event_log['id'] ) );

			return $event_log;
		}

		return $this->wpdb->insert( tqb_table_name( 'event_log' ), $data );
	}

	public function get_quiz_user( $unique, $quiz_id ) {

		$params = array( 'random_identifier' => $unique, 'quiz_id' => $quiz_id );
		$where  = ' AND  `random_identifier`=%s AND `quiz_id`=%d';
		$sql    = 'SELECT * FROM ' . tqb_table_name( 'users' ) . " WHERE 1 {$where}";

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
	}


	/**
	 * save a quiz user into the database
	 *
	 * @param array $model
	 *
	 * @return int
	 */
	public function save_quiz_user( $model = array() ) {

		$_columns = array(
			'id',
			'quiz_id',
			'random_identifier',
			'social_badge_link',
			'email',
			'ip_address',
			'points',
			'quiz_id',
			'completed_quiz',
			'ignore_user',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'users' ), $data, array( 'id' => $data['id'] ) );
			if ( $update_rows !== false ) {
				return $data['id'];
			}

			return $update_rows;
		}
		$data['date_started'] = date( 'Y-m-d H:i:s' );
		$this->wpdb->insert( tqb_table_name( 'users' ), $data );
		$user_id = $this->wpdb->insert_id;

		return $user_id;
	}

	/**
	 * save a quiz user's answer
	 *
	 * @param array $model
	 *
	 * @return int
	 */
	public function save_user_answer( $model = array() ) {

		$_columns = array(
			'id',
			'quiz_id',
			'user_id',
			'answer_id',
			'question_id',
		);
		$data     = array();
		foreach ( $_columns as $key ) {
			if ( isset( $model[ $key ] ) ) {
				$data[ $key ] = $model[ $key ];
			}
		}
		unset( $model );

		if ( ! empty( $data['id'] ) ) {
			$update_rows = $this->wpdb->update( tqb_table_name( 'user_answers' ), $data, array( 'id' => $data['id'] ) );
			if ( $update_rows !== false ) {
				return $data['id'];
			}

			return $update_rows;
		}

		$this->wpdb->insert( tqb_table_name( 'user_answers' ), $data );
		$answer_id = $this->wpdb->insert_id;

		return $answer_id;
	}

	/**
	 * generate dummy data for tests
	 *
	 * @return false|int
	 */
	public function generate_dummy_data( $test_id, $entry_count, $min_date, $max_date ) {
		$test_items = $this->get_test_items( array( 'test_id' => $test_id ) );
		$test       = $this->get_test( array( 'id' => $test_id ), true );
		for ( $i = 1; $i <= $entry_count; $i ++ ) {
			$random = rand( 1, ( count( $test_items ) ) );

			$data['date']         = $this->rand_date( $min_date, $max_date );
			$data['event_type']   = ( rand( 1, 3 ) % 2 ) == 0 ? 2 : 1;
			$data['variation_id'] = $test_items[ $random - 1 ]['variation_id'];
			$data['user']         = 'dummy@dummy.dumb';
			$data['page_id']      = $test['page_id'];

			$this->wpdb->insert( tqb_table_name( 'event_log' ), $data );

			$field_type = $data['event_type'] == 1 ? 'impressions' : 'optins_conversions';
			$test_items[ $random - 1 ][ $field_type ] ++;
			$this->wpdb->update(
				tqb_table_name( 'tests_items' ),
				array( $field_type => ( $test_items[ $random - 1 ][ $field_type ] ) ),
				array(
					'id' => $test_items[ $random - 1 ]['id'],
				)
			);
		}
	}

	public function rand_date( $min_date, $max_date ) {

		$min_epoch = strtotime( $min_date );
		$max_epoch = strtotime( $max_date );

		$rand_epoch = rand( $min_epoch, $max_epoch );

		return date( 'Y-m-d H:i:s', $rand_epoch );
	}

	/**
	 * Delete all the results from DB based on quiz_id
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_quiz_results( $filters = array() ) {
		return $this->wpdb->delete( tqb_table_name( 'results' ), $filters );
	}

	/**
	 * Delete quiz users
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_quiz_users( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
		}

		if ( ! empty( $params ) ) {
			return $this->wpdb->delete( tqb_table_name( 'users' ), $filters );
		}

		return false;
	}

	/**
	 * Deletes user answers
	 *
	 * @param array $filters
	 *
	 * @return bool|false|int
	 */
	public function delete_user_answers( $filters = array() ) {
		$params = array();

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
		}

		if ( ! empty( $params ) ) {
			return $this->wpdb->delete( tqb_table_name( 'user_answers' ), $params );
		}

		return false;
	}

	/**
	 * Insert results into DB
	 *
	 * @param int $quiz_id
	 * @param array $results
	 *
	 * @return array
	 */
	public function save_quiz_results( $quiz_id, $results ) {
		$return = array();

		foreach ( $results as $result ) {
			if ( empty( $result['id'] ) ) {
				$this->wpdb->insert( tqb_table_name( 'results' ), array(
					'quiz_id' => $quiz_id,
					'text'    => $result['text'],
				) );
				$inserted = $this->wpdb->insert_id;
			} else {
				$this->wpdb->update( tqb_table_name( 'results' ), array( 'text' => $result['text'] ), array( 'id' => $result['id'] ) );
				$inserted = $result['id'];
			}

			$return[] = array(
				'id'      => $inserted,
				'quiz_id' => $quiz_id,
				'text'    => $result['text'],
			);
		}

		return $return;
	}

	/**
	 * Get and array with quiz results from DB
	 *
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_quiz_results( $quiz_id ) {

		$where = ' WHERE quiz_id = %d';

		$params['quiz_id'] = $quiz_id;

		$sql = 'SELECT * FROM ' . tqb_table_name( 'results' ) . $where . ' ORDER BY id';
		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get and array with quiz results from DB
	 *
	 * @param $result_id
	 *
	 * @return array|null
	 */
	public function get_quiz_results_single( $result_id ) {

		$where = ' WHERE id = %d';

		$params['id'] = $result_id;

		$sql = 'SELECT * FROM ' . tqb_table_name( 'results' ) . $where;
		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->get_row( $sql, ARRAY_A );
	}

	/**
	 * Get explicit result
	 *
	 * @param $points
	 *
	 * @return array|null
	 */
	public function get_explicit_result( $points ) {

		if ( ! empty( $points['result_id'] ) ) {
			$result = $this->get_quiz_results_single( $points['result_id'] );
			if ( ! empty( $result['text'] ) ) {
				return $result['text'];
			}
		}
		if ( isset( $points['max_points'] ) && isset( $points['min_points'] ) ) {
			$range = $points['max_points'] - $points['min_points'];
			if ( ! $range ) {
				$result_percent = 100;
			} else {
				$result_percent = ( intval( $points['user_points'] ) - $points['min_points'] ) * 100 / $range;
			}

			return ( round( $result_percent, 2 ) . $points['extra'] );
		}

		return $points['user_points'];
	}

	/**
	 * Get completed quiz count from DB
	 *
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_completed_quiz_count( $quiz_id, $last_modified = null ) {
		$where = ' WHERE quiz_id = %d AND completed_quiz = 1 AND ignore_user IS NULL ';

		if ( isset( $last_modified ) ) {
			$where .= " AND date_started > '" . $last_modified . "'";
		}

		$params['quiz_id'] = $quiz_id;
		$sql               = 'SELECT COUNT(*) FROM ' . tqb_table_name( 'users' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get flow data from DB
	 *
	 * @param $page_id
	 *
	 * @return array|null
	 */
	public function get_flow_data( $page_id, $last_modified = null ) {
		$where = ' WHERE page_id = %d ';

		if ( isset( $last_modified ) ) {
			$where .= " AND date > '" . $last_modified . "'";
		}

		$where .= ' GROUP BY event_type';

		$params['page_id'] = $page_id;
		$sql               = 'SELECT IFNULL(COUNT(*), 0) as count, event_type FROM ' . tqb_table_name( 'event_log' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		$result = $this->wpdb->get_results( $sql );

		$standard = array(
			Thrive_Quiz_Builder::TQB_IMPRESSION,
			Thrive_Quiz_Builder::TQB_CONVERSION,
			Thrive_Quiz_Builder::TQB_SKIP_OPTIN,
		);
		$data     = array();
		foreach ( $standard as $event_type ) {
			$data[ $event_type ] = 0;
			foreach ( $result as $event ) {
				if ( $event->event_type == $event_type ) {
					$data[ $event_type ] = $event->count;
				}
			}
		}

		return $data;
	}

	/**
	 * Get page subscribers
	 *
	 * @param $page_id
	 *
	 * @return array|null
	 */
	public function get_page_subscribers( $page_id, $last_modified = null ) {
		$where = ' WHERE page_id = %d AND event_type = 2 AND optin = 1 ';

		if ( isset( $last_modified ) ) {
			$where .= " AND date > '" . $last_modified . "'";
		}

		$params['page_id'] = $page_id;
		$sql               = 'SELECT IFNULL(COUNT(*), 0) as count, event_type FROM ' . tqb_table_name( 'event_log' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		$result = $this->wpdb->get_var( $sql );

		return $result;
	}

	/**
	 * Get social shares for results page
	 *
	 * @param $page_id
	 *
	 * @return array|null
	 */
	public function get_page_social_shares( $page_id, $last_modified = null ) {
		$where = ' WHERE page_id = %d AND event_type = 2 AND social_share = 1 ';

		if ( isset( $last_modified ) ) {
			$where .= " AND date > '" . $last_modified . "'";
		}

		$params['page_id'] = $page_id;
		$sql               = 'SELECT IFNULL(COUNT(*), 0) as count, event_type FROM ' . tqb_table_name( 'event_log' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		$result = $this->wpdb->get_var( $sql );

		return $result;
	}

	/**
	 * Get quiz social share count from DB
	 *
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_quiz_social_shares_count( $quiz_id ) {

		$results_page = get_posts( array( 'post_parent' => $quiz_id, 'post_type' => Thrive_Quiz_Builder::QUIZ_STRUCTURE_ITEM_RESULTS ) );
		if ( empty( $results_page[0] ) ) {
			return 0;
		}

		$where = ' WHERE page_id = %d AND social_share = 1';

		$params['page_id'] = $results_page[0]->ID;
		$sql               = 'SELECT COUNT(*) FROM ' . tqb_table_name( 'event_log' ) . $where;
		$sql               = $this->prepare( $sql, $params );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get total quiz users count from DB
	 *
	 * @param $quiz_id
	 * @param $completed_quiz
	 *
	 * @return array|null
	 */
	public function get_quiz_users_count( $quiz_id, $completed_quiz = false ) {
		$where = ' WHERE quiz_id = %d AND ignore_user IS NULL ';

		if ( $completed_quiz ) {
			$where .= 'AND completed_quiz=1 ';
		}

		$params['quiz_id'] = $quiz_id;
		$sql               = 'SELECT COUNT(*) FROM ' . tqb_table_name( 'users' ) . $where;
		$sql               = $this->prepare( $sql, $params );
		$this->wpdb->get_var( $sql );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Get all quiz users from DB
	 *
	 * @param $quiz_id
	 * @param $params
	 *
	 * @return array|null
	 */
	public function get_quiz_users( $quiz_id, $params = array() ) {
		$where = ' WHERE quiz_id = %d AND ignore_user IS NULL ';

		if ( ! empty( $params['completed_quiz'] ) ) {
			$where .= 'AND completed_quiz=1 ';
		}
		$where .= ' ORDER BY id DESC ';
		if ( ! empty( $params['per_page'] ) && is_numeric( $params['per_page'] ) ) {
			$where .= ' LIMIT ' . $params['per_page'];
			if ( ! empty( $params['offset'] ) && is_numeric( $params['offset'] ) ) {
				$where .= ' OFFSET ' . $params['offset'];
			}
		}

		$data['quiz_id'] = $quiz_id;
		$sql             = 'SELECT * FROM ' . tqb_table_name( 'users' ) . $where;
		$sql             = $this->prepare( $sql, $data );

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Get quiz user answer from DB
	 *
	 * @param $params
	 *
	 * @return array|null
	 */
	public function get_user_answers( $params = array() ) {
		if ( empty( $params['quiz_id'] ) || empty( $params['user_id'] ) ) {
			return false;
		}
		$where = ' WHERE quiz_id = %d AND user_id =%d ';

		$data['quiz_id'] = $params['quiz_id'];
		$data['user_id'] = $params['user_id'];
		$sql             = 'SELECT * FROM ' . tqb_table_name( 'user_answers' ) . $where;
		$sql             = $this->prepare( $sql, $data );

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Get user's points from a quiz
	 *
	 * @param $user_unique
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function calculate_user_points( $user_unique, $quiz_id ) {

		$user = $this->get_quiz_user( $user_unique, $quiz_id );
		if ( empty( $user ) ) {
			return false;
		}

		$sql = 'SELECT IFNULL(SUM( answer.points ), 0) AS user_points, answer.result_id ';

		$sql .= ' FROM ' . tge_table_name( 'answers' ) . ' AS answer ';
		$sql .= ' INNER JOIN ' . tge_table_name( 'questions' ) . ' AS question ON question.id = answer.question_id ';
		$sql .= ' INNER JOIN ' . tqb_table_name( 'user_answers' ) . ' AS user_answers ON answer.id = user_answers.answer_id ';

		$sql .= '  WHERE (answer.result_id !=0 || answer.result_id IS NULL) AND user_answers.quiz_id = ' . $quiz_id . ' AND user_answers.user_id = ' . $user['id'];

		$sql .= ' GROUP BY answer.result_id';


		$data = $this->wpdb->get_results( $this->prepare( $sql, array() ), ARRAY_A );

		$end_result['user_points'] = null;
		$end_result['result_id']   = null;
		$page_type                 = TQB_Post_meta::get_quiz_type_meta( $quiz_id );
		if ( empty( $data ) && Thrive_Quiz_Builder::QUIZ_TYPE_PERSONALITY == $page_type['type'] ) {
			$results                   = $this->get_quiz_results( $quiz_id );
			$end_result['result_id']   = isset( $results[0]['id'] ) ? $results[0]['id'] : null;
			$end_result['user_points'] = true;
		} else {
			foreach ( $data as $result ) {
				if ( empty( $end_result['user_points'] ) || $result['user_points'] > $end_result['user_points'] ) {
					$end_result = $result;
				}
			}
		}
		$end_result['quiz_id'] = $quiz_id;

		$end_result['extra'] = '';
		if ( Thrive_Quiz_Builder::QUIZ_TYPE_PERCENTAGE == $page_type['type'] ) {
			$end_result['extra'] = ' %';
			$question_manager    = new TGE_Question_Manager( $quiz_id );
			$min_max             = $question_manager->get_min_max_flow();

			$end_result['max_points'] = intval( $min_max['max'] );
			$end_result['min_points'] = intval( $min_max['min'] );
		}

		return $end_result;
	}

	/**
	 * Get user's points from a quiz
	 *
	 * @param $user_unique
	 * @param $quiz_id
	 *
	 * @return array|null
	 */
	public function get_user_points( $user_unique, $quiz_id ) {
		$user = $this->get_quiz_user( $user_unique, $quiz_id );
		if ( empty( $user ) ) {
			return false;
		}

		return isset( $user['points'] ) ? $user['points'] : '-';
	}

	/**
	 * Update the user's points from a quiz
	 *
	 * @param $answer
	 * @param $user
	 *
	 * @return array|null
	 */
	public function update_user_points( $answer, $user ) {
		$user['points'] = isset( $user['points'] ) ? $user['points'] : 0;

		return $this->save_quiz_user( array( 'id' => $user['id'], 'points' => ( $user['points'] + $answer['points'] ) ) );
	}

	/**
	 * Clone variation database method
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function clone_variation( $data = array() ) {

		$query = 'INSERT INTO ' . tqb_table_name( 'variations' ) . ' (quiz_id, date_added, date_modified, page_id, parent_id, post_title,tcb_fields, content) 
		SELECT quiz_id, NOW(), NOW(), page_id, parent_id, CONCAT("' . __( 'Copy of ', Thrive_Quiz_Builder::T ) . '",post_title),tcb_fields, content FROM ' . tqb_table_name( 'variations' ) . ' WHERE id = %d';

		$query = $this->prepare( $query, array( 'id' => $data['id'] ) );
		$this->wpdb->query( $query );
		$this->replace_variation_id( $data['id'], $this->wpdb->insert_id );

		return $this->wpdb->insert_id;
	}


	/**
	 * Replace variation id inside content
	 *
	 * @param $initial
	 * @param $after
	 *
	 * @return int
	 */
	public function replace_variation_id( $initial, $after ) {
		$variation = $this->get_variation( $after );
		if ( empty( $variation ) ) {
			return false;
		}
		$content = str_replace( 'name="tqb-variation-variation_id" class="tqb-hidden-form-info" value="' . $initial . '">', 'name="tqb-variation-variation_id" class="tqb-hidden-form-info" value="' . $after . '">', $variation['content'] );

		return $this->save_variation( array( 'id' => $after, 'content' => $content ) );
	}

	/**
	 * get data for completion report
	 *
	 * @param array $filters
	 *
	 * @return array
	 */
	public function get_quiz_completion_report( $quiz_id, $filters = array() ) {

		if ( empty( $filters['interval'] ) ) {
			$filters['interval'] = 'day';
		}

		switch ( $filters['interval'] ) {
			case 'month':
				$date_interval = 'CONCAT(MONTHNAME(`user`.`date_started`)," ", YEAR(`user`.`date_started`)) as date_interval';
				break;
			case 'week':
				$year          = 'IF( WEEKOFYEAR(`user`.`date_started`) = 1 AND MONTH(`user`.`date_started`) = 12, 1 + YEAR(`user`.`date_started`), YEAR(`user`.`date_started`) )';
				$date_interval = "CONCAT('Week ', WEEKOFYEAR(`user`.`date_started`), ', ', {$year}) as date_interval";
				break;
			case 'day':
				$date_interval = 'DATE(`user`.`date_started`) as date_interval';
				break;
		}

		$sql = 'SELECT IFNULL(COUNT( user.id ), 0) AS user_count, quiz_id, ' . $date_interval;

		$sql .= ' FROM ' . tqb_table_name( 'users' ) . ' AS `user` ';

		$sql .= '  WHERE 1 AND completed_quiz=1 ';

		$params = array();

		if ( empty( $filters['date'] ) ) {
			$filters['date'] = Thrive_Quiz_Builder::TQB_LAST_7_DAYS;
		}

		$data_interval = $this->get_report_date_interval( $filters );
		$sql .= $data_interval['date_interval'];

		if ( ! empty( $quiz_id ) ) {
			$sql .= ' AND quiz_id = %d';
			$params [] = $quiz_id;
		}
		$sql .= ' GROUP BY quiz_id, date_interval ORDER BY date_interval ';

		$data  = $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A );
		$dates = tqb_generate_dates_interval( $data_interval['start_date'], $data_interval['end_date'], $filters['interval'] );

		$quizzes    = array();
		$table_quiz = array();
		foreach ( $data as $i => $quiz ) {

			if ( empty( $quizzes[ $quiz['quiz_id'] ] ) ) {
				$quiz_post = get_post( $quiz['quiz_id'] );
				if ( empty( $quiz_post ) ) {
					unset( $data[ $i ] );
					continue;
				}
				$table_quiz[ $quiz['quiz_id'] ] = intval( $quiz['user_count'] );

				$quizzes[ $quiz['quiz_id'] ] = array(
					'data' => array( $quiz['date_interval'] => intval( $quiz['user_count'] ) ),
					'name' => $quiz_post->post_title,
					'id'   => $quiz_post->ID,
				);

				$data[ $i ]['name'] = $quiz_post->post_title;

			} else {
				$quizzes[ $quiz['quiz_id'] ]['data'][ $quiz['date_interval'] ] = intval( $quiz['user_count'] );
				$table_quiz[ $quiz['quiz_id'] ] += intval( $quiz['user_count'] );
				$data[ $i ]['name'] = $quizzes[ $quiz['quiz_id'] ]['name'];
			}
		}

		//add zeros
		foreach ( $quizzes as $key => $quiz ) {
			$count_array = array();

			foreach ( $dates as $k => $date ) {
				$count_array[ $k ] = 0;
				foreach ( $quiz['data'] as $t => $count ) {

					if ( $filters['interval'] == 'day' ) {
						$t = date( 'd M, Y', strtotime( $t ) );
					}
					if ( $date == $t ) {
						$count_array[ $k ] = $count;
					}
				}
			}
			$quizzes[ $key ]['name'] = $quizzes[ $key ]['name'] . ': ' . $table_quiz[ $key ];
			$quizzes[ $key ]['data'] = $count_array;
		}

		return array( 'graph_quiz' => $quizzes, 'intervals' => $dates, 'table_quizzes' => $data );
	}

	public function get_report_date_interval( $filter ) {
		$date_interval = '';
		$end_date      = '';
		$timezone_diff = current_time( 'timestamp' ) - time();
		switch ( $filter['date'] ) {
			case Thrive_Quiz_Builder::TQB_LAST_7_DAYS :
				$start_date    = date( 'Y-m-d', ( strtotime( '-7 days' ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_30_DAYS :
				$start_date    = date( 'Y-m-d', ( strtotime( '-30 days' ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_THIS_MONTH :
				$start_date    = date( 'Y-m-d', ( strtotime( date( '01-m-Y' ) + $timezone_diff ) ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_MONTH :
				$start_date    = date( 'Y-m-d', ( strtotime( 'first day of last month' ) + $timezone_diff ) );
				$end_date      = date( 'Y-m-d', ( strtotime( '01-m-Y' ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" AND `user`.`date_started` < "' . $end_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_THIS_YEAR :
				$start_date    = date( 'Y-m-d', ( strtotime( date( 'Y-01-01' ) + $timezone_diff ) ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_YEAR :
				$year          = date( 'Y' ) - 1;
				$start_date    = date( 'Y-m-d', ( mktime( 0, 0, 0, 1, 1, $year ) + $timezone_diff ) );
				$end_date      = date( 'Y-m-d', ( mktime( 0, 0, 0, 12, 31, $year ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" AND `user`.`date_started` < "' . $end_date . '" ';
				break;
			case Thrive_Quiz_Builder::TQB_LAST_12_MONTHS :

				$start_date    = date( 'Y-m-d', ( strtotime( '-1 year', time() ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= ' . $start_date . ' ';
				break;
			case Thrive_Quiz_Builder::TQB_CUSTOM_DATE_RANGE :
				$start_date    = $filter['start_date'];
				$end_date      = date( 'Y-m-d H:i:s', ( strtotime( '+1 day', ( strtotime( $filter['end_date'] ) - 1 ) ) + $timezone_diff ) );
				$date_interval = ' AND `user`.`date_started` >= "' . $start_date . '" AND `user`.`date_started` < "' . $end_date . '" ';
				break;
		}

		return array(
			'date_interval' => $date_interval,
			'start_date'    => $start_date,
			'end_date'      => empty( $end_date ) ? date( 'Y-m-d', ( time() + $timezone_diff ) ) : $end_date,
		);
	}

	/**
	 * Get quiz data for questions report
	 *
	 * @param $quiz_id
	 *
	 * @return false|array
	 */
	public function get_questions_report_data( $quiz_id ) {
		$sql = 'SELECT IFNULL(COUNT( user_answer.id ), 0) AS answer_count, answer.question_id, answer.id AS answer_id,
		question.text AS question_text, answer.text AS answer_text, answer.image AS answer_image  ';

		$sql .= ' FROM ' . tge_table_name( 'answers' ) . ' AS answer ';

//		$sql .= ' INNER JOIN ' . tqb_table_name( 'users' ) . ' AS user ON user.id = user_answer.user_id ';
		$sql .= ' LEFT JOIN ' . tqb_table_name( 'user_answers' ) . ' AS user_answer ON answer.id = user_answer.answer_id ';
		$sql .= ' LEFT JOIN ' . tge_table_name( 'questions' ) . ' AS question ON question.id = answer.question_id ';

		$sql .= '  WHERE answer.quiz_id = ' . $quiz_id;// ' AND user.completed_quiz = 1';

		$sql .= ' GROUP BY answer.question_id, answer.id ';


		$data = $this->wpdb->get_results( $this->prepare( $sql, array( 'quiz_id' => $quiz_id ) ), ARRAY_A );

		$questions = array();

		$colors = tqb()->chart_colors();
		foreach ( $data as $entry ) {
			if ( empty( $questions[ $entry['question_id'] ] ) ) {
				$questions[ $entry['question_id'] ] = array(
					'text'    => $entry['question_text'],
					'answers' => array(
						$entry['answer_id'] => array(
							'text'  => $entry['answer_text'],
							'count' => $entry['answer_count'],
							'image' => $entry['answer_image'],
						),
					),
					'total'   => $entry['answer_count'],
					'id'      => $entry['question_id'],
				);

			} else {
				$questions[ $entry['question_id'] ]['answers'][ $entry['answer_id'] ] = array(
					'text'  => $entry['answer_text'],
					'count' => $entry['answer_count'],
					'image' => $entry['answer_image'],
				);
				$questions[ $entry['question_id'] ]['total'] += $entry['answer_count'];
			}
		}
		foreach ( $questions as $key => $question ) {
			$index = 0;
			foreach ( $question['answers'] as $id => $answer ) {
				if ( $question['total'] ) {
					$questions[ $key ]['answers'][ $id ]['percent'] = round( $answer['count'] * 100 / $question['total'], 2 );
					$questions[ $key ]['answers'][ $id ]['color']   = $colors[ $index % count( $colors ) ];
					$index ++;
				}
			}
		}

		return $questions;
	}
}

$tqbdb = new TQB_Database();

