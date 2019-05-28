<?php
/**
 * Handles database operations
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/22/2016
 * Time: 5:16 PM
 */

global $tgedb;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class TGE_Database {
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
		$prefix = tge_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * get quiz questions
	 *
	 * @param array $filters
	 * @param bool $single
	 * @param string $return_type
	 *
	 * @return array|null|object
	 */
	public function get_quiz_questions( $filters, $single, $return_type = ARRAY_A ) {
		$params   = array();
		$where    = ' 1=1 ';
		$order_by = ' ORDER by start DESC, id ASC';

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
			$where .= 'AND `id`=%d ';
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
			$where .= 'AND `quiz_id`=%d ';
		}

		if ( ! empty( $filters['start'] ) ) {
			$where .= 'AND `start`=1 ';
		}

		if ( isset( $filters['previous_question_id'] ) ) {
			$params ['previous_question_id'] = $filters['previous_question_id'];
			$where .= 'AND `previous_question_id`=%d ';
		}

		$sql = 'SELECT * FROM ' . tge_table_name( 'questions' ) . ' WHERE ' . $where . $order_by;

		if ( $single ) {
			$model = $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
			if ( ! empty( $model['image'] ) ) {
				$model['image'] = json_decode( $model['image'] ) ? json_decode( $model['image'] ) : $model['image'];
			}

			return $model;
		}

		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );
		foreach ( $models as &$question ) {
			if ( ! empty( $question['image'] ) ) {
				$question['image'] = json_decode( $question['image'] ) ? json_decode( $question['image'] ) : $question['image'];
			}
		}

		return $models;
	}

	/**
	 * @param array $filters
	 *
	 * @return null|string
	 */
	public function count_questions( $filters = array() ) {
		$sql    = 'SELECT COUNT(id) FROM ' . tge_table_name( 'questions' ) . '  WHERE 1 ';
		$params = array();

		if ( ! empty( $filters['quiz_id'] ) ) {
			$sql .= ' AND quiz_id = %d';
			$params [] = $filters['quiz_id'];
		}

		return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
	}

	/**
	 *
	 * get question answers
	 *
	 * @param $filters
	 *
	 * @return false|null|string
	 */
	public function get_answers( $filters, $single, $return_type = ARRAY_A ) {
		$params = array();
		$where  = ' 1=1 ';

		if ( ! empty( $filters['id'] ) ) {
			$params ['id'] = $filters['id'];
			$where .= 'AND `id`=%d ';
		}

		if ( ! empty( $filters['question_id'] ) ) {
			$params ['question_id'] = $filters['question_id'];
			$where .= 'AND `question_id`=%d ';
		}

		if ( ! empty( $filters['quiz_id'] ) ) {
			$params ['quiz_id'] = $filters['quiz_id'];
			$where .= 'AND `quiz_id`=%d ';
		}

		$sql = 'SELECT * FROM ' . tge_table_name( 'answers' ) . ' WHERE ' . $where . ' ORDER BY `order` ASC';

		if ( $single ) {
			$model          = $this->wpdb->get_row( $this->prepare( $sql, $params ), $return_type );
			$model['image'] = json_decode( $model['image'] ) ? json_decode( $model['image'] ) : $model['image'];

			return $model;

		}

		$models = $this->wpdb->get_results( $this->prepare( $sql, $params ), $return_type );
		foreach ( $models as &$answer ) {
			$answer['image'] = json_decode( $answer['image'] ) ? json_decode( $answer['image'] ) : $answer['image'];
		}

		return $models;
	}

	/**
	 * Insert or Update question in DB
	 *
	 * @param array $data
	 *
	 * @return false|int
	 */
	public function save_question( $data ) {

		$model   = array();
		$columns = array(
			'id',
			'quiz_id',
			'start',
			'q_type',
			'text',
			'image',
			'description',
			'next_question_id',
			'previous_question_id',
			'position',
		);

		/**
		 * filter the data accordingly to $columns
		 */
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $columns ) ) {
				$model[ $key ] = $value;
			}
		}

		if ( ! empty( $model['position'] ) && is_array( $model['position'] ) ) {
			$position['x']     = ! empty( $model['position']['x'] ) ? $model['position']['x'] : 0;
			$position['y']     = ! empty( $model['position']['y'] ) ? $model['position']['y'] : 0;
			$model['position'] = wp_json_encode( $position );
		}

		if ( ! empty( $model['image'] ) && is_array( $model['image'] ) ) {
			$model['image'] = wp_json_encode( $model['image'] );
		}

		if ( ! empty( $model['id'] ) ) {
			return $this->wpdb->update( tge_table_name( 'questions' ), $model, array( 'id' => $model['id'] ) );
		}

		return $this->wpdb->insert( tge_table_name( 'questions' ), $model ) !== false ? $this->wpdb->insert_id : false;
	}

	/**
	 * Insert or Update an answer in DB
	 *
	 * @param array $data
	 *
	 * @return false|int
	 */
	public function save_answer( $data ) {

		$model   = array();
		$columns = array(
			'id',
			'quiz_id',
			'question_id',
			'next_question_id',
			'order',
			'text',
			'image',
			'points',
			'result_id',
		);

		/**
		 * filter the data accordingly to $columns
		 */
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $columns ) ) {
				$model[ $key ] = $value;
			}
		}

		if ( ! empty( $model['image'] ) && is_array( $model['image'] ) ) {
			$model['image'] = wp_json_encode( $model['image'] );
		}

		if ( ! empty( $model['id'] ) ) {
			return $this->wpdb->update( tge_table_name( 'answers' ), $model, array( 'id' => $model['id'] ) );
		}

		return $this->wpdb->insert( tge_table_name( 'answers' ), $model ) !== false ? $this->wpdb->insert_id : false;
	}

	/**
	 * Deletes from question table based on a given filter
	 *
	 * @param array $filters
	 *
	 * @return bool|false|int
	 */
	public function delete_question( $filters = array() ) {

		if ( ! empty( $filters ) ) {
			return $this->wpdb->delete( tge_table_name( 'questions' ), $filters );
		}

		return false;
	}

	/**
	 * Deletes from answer table based on a given filter
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	public function delete_answer( $filters = array() ) {

		if ( ! empty( $filters ) ) {
			return $this->wpdb->delete( tge_table_name( 'answers' ), $filters );
		}

		return false;
	}

	/**
	 * Update all answers that have assigned $results_ids
	 * with new $value for result_id column
	 *
	 * @param array $results_ids
	 * @param null|int $value
	 *
	 * @return int|false
	 */
	public function update_answers_result( $results_ids, $value ) {

		if ( ! is_int( $value ) ) {
			$value = 'NULL';
		}

		$ids = implode( ',', $results_ids );
		$sql = 'UPDATE ' . tge_table_name( 'answers' ) . ' SET `result_id` = ' . $value . ' WHERE result_id IN (' . $ids . ')';

		return $this->wpdb->query( $sql );
	}
}

$tgedb = new TGE_Database();
