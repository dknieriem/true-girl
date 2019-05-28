<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TGE_Question_Manager
 *
 * Handles Question operations
 */
class TGE_Question_Manager {

	/**
	 * @var TGE_Question_Manager $instance
	 */
	protected $quiz_id;

	/**
	 * @var TGE_Database
	 */
	protected $tgedb;

	protected $questions = array();

	protected $cache_min_max = array();

	protected $costs = array();

	/**
	 * TGE_Question_Manager constructor.
	 *
	 * @param null $quiz_id
	 */
	public function __construct( $quiz_id = null ) {

		global $tgedb;

		$this->quiz_id = $quiz_id;
		$this->tgedb   = $tgedb;
	}

	/**
	 * Get all quiz questions according to filter
	 *
	 * @param array $filters
	 * @param bool $single
	 *
	 * @return array
	 */
	public function get_quiz_questions( $filters = array(), $single = false ) {

		$single  = (bool) $single;
		$filters = array_merge( $filters, array( 'quiz_id' => $this->quiz_id ) );

		$questions = $this->tgedb->get_quiz_questions( $filters, $single );

		if ( ! empty( $filters['with_answers'] ) ) {
			foreach ( $questions as &$question ) {
				$question['position'] = json_decode( $question['position'] );
				$question['answers']  = $this->get_answers( array(
					'question_id' => $question['id'],
				) );
			}
		}

		return $questions;
	}

	/**
	 * Gets the markup and the CSS for the first question of the quiz, as a preview
	 *
	 * @param array $question_data
	 *
	 * @return string
	 */
	public function get_first_question_preview( $question_data = array() ) {
		$html      = '';
		$questions = $this->get_quiz_questions( array( 'with_answers' => 1 ) );

		foreach ( $question_data['css'] as $css ) {
			$html .= '<link rel="stylesheet" type="text/css" href="' . $css . '" media="all">';
		}
		ob_start();
		include tqb()->plugin_path( 'includes/frontend/views/templates/question-answer.php' );
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	protected function get_items() {

		if ( ! empty( $this->questions ) ) {
			return $this->questions;
		}

		$questions = $this->get_quiz_questions( array(
			'with_answers' => true,
		) );

		foreach ( $questions as $question ) {
			$this->questions[ $question['id'] ] = array(
				'id'   => $question['id'],
				'next' => ! empty( $question['next_question_id'] ) ? $question['next_question_id'] : null,
			);

			$answers = array();

			foreach ( $question['answers'] as $answer ) {
				$answers[ $answer['id'] ] = array(
					'id'     => $answer['id'],
					'points' => $answer['points'],
					'next'   => ! empty( $answer['next_question_id'] ) ? $answer['next_question_id'] : null,
				);
			}

			$this->questions[ $question['id'] ]['answers'] = $answers;
		}

		return $this->questions;
	}

	/**
	 * Count all quiz questions according to filter
	 *
	 * @param array $filters
	 *
	 * @return int
	 */
	public function count_questions( $filters = array() ) {

		$filters = array_merge( $filters, array(
			'quiz_id' => $this->quiz_id,
		) );

		return intval( $this->tgedb->count_questions( $filters ) );
	}

	/**
	 * Get all answers according to filter
	 *
	 * @param array $filters
	 * @param bool $single
	 *
	 * @return false|null|string
	 */
	public function get_answers( $filters, $single = false ) {

		return $this->tgedb->get_answers( $filters, $single );
	}

	/**
	 * Get question html for frontend
	 *
	 * @param int|null $answer_id
	 *
	 * @return array|false
	 */
	public function get_question_content( $answer_id = null ) {

		if ( empty( $answer_id ) ) {
			$question['data'] = $this->get_quiz_questions( array( 'start' => 1 ), true );
		} else {
			$answer           = $this->get_answers( array( 'id' => $answer_id ), true );
			$question['data'] = $this->get_next_question( $answer );
		}

		if ( empty( $question['data'] ) ) {
			return false;
		}

		$question['answers'] = $this->get_answers( array( 'question_id' => $question['data']['id'] ), false );

		$quiz_style_meta   = TQB_Post_meta::get_quiz_style_meta( $this->quiz_id );
		$template_css_file = tqb()->get_style_css( $quiz_style_meta );
		if ( ! empty( $template_css_file ) ) {
			$file = tqb()->plugin_path( 'tcb-bridge/editor-templates/css/tqb_qna/' . $template_css_file );
			if ( file_exists( $file ) ) {
				$question['css'] = array(
					tqb()->plugin_url( 'tcb-bridge/editor-templates/css/tqb_qna/' . $template_css_file ),
				);
			}
		}

		return $question;
	}

	/**
	 * Get get next question information
	 *
	 * @param array $answer
	 *
	 * @return bool|false|null|string
	 */
	public function get_next_question( $answer ) {

		if ( ! is_array( $answer ) ) {
			return false;
		}

		if ( ! empty( $answer['next_question_id'] ) ) {
			return $this->get_quiz_questions( array( 'id' => $answer['next_question_id'] ), true );
		}

		$question = $this->get_quiz_questions( array( 'id' => $answer['question_id'] ), true );
		if ( empty( $question['next_question_id'] ) ) {
			return false;
		}

		return $this->get_quiz_questions( array( 'id' => $question['next_question_id'] ), true );
	}

	/**
	 * Question types
	 *
	 * @return array
	 */
	public static function get_question_types() {
		return array(
			array(
				'id'   => 1,
				'key'  => 'button',
				'name' => __( 'Multiple Choice with Buttons', Thrive_Graph_Editor::T ),
			),
			array(
				'id'   => 2,
				'key'  => 'image',
				'name' => __( 'Multiple Choice with Images', Thrive_Graph_Editor::T ),
			),
		);
	}

	/**
	 * Save question
	 *
	 * @param array $question
	 *
	 * @return false|array
	 */
	public function save_question( &$question ) {
		$question_id = null;

		if ( empty( $question['id'] ) ) {
			$question_id = $this->tgedb->save_question( $question );
		} else {
			$question_id = $this->tgedb->save_question( $question ) !== false ? $question['id'] : false;
		}

		/**
		 * question not saved
		 */
		if ( empty( $question_id ) ) {
			return false;
		}

		$question['id'] = $question_id;

		if ( ! empty( $question['answers'] ) ) {

			$old_answers = $this->get_answers( array(
				'question_id' => $question['id'],
			) );

			$answers_to_be_deleted = array();

			foreach ( $old_answers as $old ) {
				$found = false;
				foreach ( $question['answers'] as $new ) {
					if ( intval( $old['id'] ) === intval( $new['id'] ) ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					$answers_to_be_deleted[] = $old['id'];
				}
			}

			foreach ( $answers_to_be_deleted as $d ) {
				$this->tgedb->delete_answer( array(
					'id' => $d,
				) );
			}
		}

		if ( ! empty( $question['answers'] ) && is_array( $question['answers'] ) ) {
			foreach ( $question['answers'] as &$answer ) {
				$answer['question_id'] = $question_id;
				$answer['quiz_id']     = $this->quiz_id;
				$this->save_answer( $answer );
			}
		}

		return $question;
	}


	public function delete_question( $id ) {

		$q_deleted = $this->tgedb->delete_question( array(
			'id' => $id,
		) );

		$a_deleted = $this->tgedb->delete_answer( array(
			'question_id' => $id,
		) );

		return $q_deleted && $a_deleted;
	}

	public function save_answer( &$answer ) {

		$answer_id = null;

		if ( empty( $answer['id'] ) ) {
			$answer_id = $this->tgedb->save_answer( $answer );
		} else {
			$answer_id = $this->tgedb->save_answer( $answer ) !== false ? $answer['id'] : false;
		}

		/**
		 * answer not saved
		 */
		if ( empty( $answer_id ) ) {
			return false;
		}

		$answer['id'] = $answer_id;

		return $answer;
	}

	/**
	 * Loop through questions and answers and prefix question ids with 'q' char
	 * and with 'a' answers id
	 *
	 * @param array $questions
	 *
	 * @return array
	 */
	public function prepare_questions( $questions ) {

		foreach ( $questions as &$question ) {
			$this->prepare_question( $question );
		}

		return $questions;
	}

	public function prepare_question( &$question ) {
		$question['id']   = intval( $question['id'] ) . 'q';
		$question['type'] = $question['q_type'] == 1 ? 'tge.Question' : 'tge.Question';

		! empty( $question['next_question_id'] ) ? $question['next_question_id'] = intval( $question['next_question_id'] ) . 'q' : null;
		! empty( $question['previous_question_id'] ) ? $question['previous_question_id'] = intval( $question['previous_question_id'] ) . 'q' : null;

		if ( ! empty( $question['answers'] ) ) {
			foreach ( $question['answers'] as &$answer ) {
				$answer['id'] = intval( $answer['id'] ) . 'a';
			}
		}

		return $question;
	}

	/**
	 * Deletes all quiz data from the graph editor table
	 *
	 * @param array $filters
	 *
	 * @return bool
	 */
	public function delete_quiz_dependencies( $filters = array() ) {

		$filters = array_merge( $filters, array(
			'quiz_id' => $this->quiz_id,
		) );

		$answer_deleted   = $this->tgedb->delete_answer( $filters );
		$question_deleted = $this->tgedb->delete_question( $filters );

		return $answer_deleted && $question_deleted;
	}

	protected function calculate_paths( $q, $point_sum, $path_key ) {

		$return = array(
			'q'        => $q['id'],
			'points'   => $point_sum,
			'path_key' => $path_key,
			'paths'    => array(),
		);

		foreach ( $q['answers'] as $id => $answer ) {
			$next_question_id = ! empty( $answer['next'] ) ? $answer['next'] : $q['next'];
			if ( $next_question_id && isset( $this->questions[ $next_question_id ] ) ) {
				$return['paths'][ 'answer-' . $id ] = $this->calculate_paths( $this->questions[ $next_question_id ], $point_sum + $answer['points'], $path_key . ':' . 'answer-' . $id );
			} else {
				/* end path */
				$return['paths'][ 'answer-' . $id ]                = array(
					'points' => $point_sum + $answer['points'],
				);
				$this->costs [ $path_key . ':' . 'answer-' . $id ] = $return['paths'][ 'answer-' . $id ]['points'];
			}
		}

		return $return;
	}


	/**
	 * Tests if a question is leaf
	 * (has no question that follows it)
	 *
	 * @param $q
	 *
	 * @return bool
	 */
	protected function is_leaf( $q ) {
		if ( ! empty( $q['next'] ) ) {
			return false;
		}
		foreach ( $q['answers'] as $id => $answer ) {
			if ( ! empty( $answer['next'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the answer points
	 *
	 * @param $a
	 *
	 * @return int
	 */
	protected function get_answer_points( $a ) {
		return intval( $a['points'] );
	}

	/**
	 * @param $q
	 *
	 * @return mixed
	 */
	public function get_min_max( $q ) {
		if ( isset( $this->cache_min_max[ $q['id'] ] ) ) {
			return $this->cache_min_max[ $q['id'] ];
		}
		if ( $this->is_leaf( $q ) ) {
			$points                          = array_map( array( $this, 'get_answer_points' ), $q['answers'] );
			$this->cache_min_max[ $q['id'] ] = array( 'min' => min( $points ), 'max' => max( $points ) );

			return $this->cache_min_max[ $q['id'] ];
		}

		$min = PHP_INT_MAX;
		$max = - 1;
		foreach ( $q['answers'] as $answer ) {
			if ( empty( $answer['next'] ) && empty( $q['next'] ) ) {
				$min_max = array( 'min' => 0, 'max' => 0 );
			} else {
				$min_max = $this->get_min_max( $this->questions[ empty( $answer['next'] ) ? $q['next'] : $answer['next'] ] );
			}

			if ( $answer['points'] + $min_max['min'] < $min ) {
				$min = $answer['points'] + $min_max['min'];
			}

			if ( $answer['points'] + $min_max['max'] > $max ) {
				$max = $answer['points'] + $min_max['max'];
			}
		}
		$this->cache_min_max[ $q['id'] ] = array( 'min' => $min, 'max' => $max );

		return $this->cache_min_max[ $q['id'] ];
	}


	public function get_costs() {

		if ( ! empty( $this->costs ) ) {
			return $this->costs;
		}

		$questions = $this->get_items();
		$start     = current( $questions );
		$this->calculate_paths( $start, 0, 'root' );

		return $this->costs;
	}

	/**
	 * Gets the min max from a flow
	 *
	 * @return mixed
	 */
	public function get_min_max_flow() {
		$questions = $this->get_items();

		return $this->get_min_max( current( $questions ) );
	}

	/*
	 *TODO: This method is ABSOLITE please check dependencies and replace it with get_min_max method
	 */
	public function get_min_flow() {

		return min( array_values( $this->get_costs() ) );
	}

	/*
	 *TODO: This method is ABSOLITE please check dependencies and replace it with get_min_max method
	 */
	public function get_max_flow() {

		return max( array_values( $this->get_costs() ) );
	}

	/**
	 * Set the answers with $results_ids
	 * with 0 for result_id column
	 *
	 * @param array $results_ids
	 *
	 * @return bool
	 */
	public function set_answers_on_none( $results_ids ) {

		return $this->tgedb->update_answers_result( $results_ids, 0 ) === false;
	}
}
