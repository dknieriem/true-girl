<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TGE_Link_Manager {

	/**
	 * @var TGE_Database
	 */
	protected $tgedb;

	protected $source;
	protected $target;

	/**
	 * @var TGE_Question_Manager
	 */
	protected $manager;

	public function __construct( $source, $target ) {

		global $tqbdb;
		$this->tgedb   = $tqbdb;
		$this->source  = $source;
		$this->target  = $target;
		$this->manager = new TGE_Question_Manager();
	}

	/**
	 * @return bool
	 */
	public function connect() {
		if ( $this->source['id'] === 'start' ) {
			return $this->set_start_flow();
		}

		return $this->connect_source() && $this->connect_target();
	}

	/**
	 * return bool
	 */
	public function disconnect() {

		if ( $this->source['id'] === 'start' ) {
			return $this->set_stop_flow();
		}

		return $this->disconnect_source() && $this->disconnect_target();
	}

	/**
	 * @return bool
	 */
	protected function connect_source() {

		$type = strpos( $this->source['id'], 'q' ) !== false ? 'question' : 'answer';

		$model = array();

		switch ( $type ) {
			case 'question':
				$model['id']               = intval( $this->source['id'] );
				$model['next_question_id'] = intval( $this->target['id'] );
				break;
			case 'answer':
				$model['id']               = intval( $this->source['id'] );
				$model['next_question_id'] = intval( $this->target['id'] );

				break;
		}

		if ( empty( $model ) ) {
			return false;
		}

		$method = 'save_' . $type;

		return $this->manager->$method( $model );
	}

	/**
	 * @return bool
	 */
	protected function disconnect_source() {

		$type  = strpos( $this->source['id'], 'q' ) !== false ? 'question' : 'answer';
		$model = array();
		switch ( $type ) {
			case 'question':
				$model['id']               = intval( $this->source['id'] );
				$model['next_question_id'] = null;
				break;
			case 'answer':
				$model['id']               = intval( $this->source['id'] );
				$model['next_question_id'] = null;
				break;
		}

		if ( empty( $model ) ) {
			return false;
		}

		$method = 'save_' . $type;

		return $this->manager->$method( $model );
	}

	protected function connect_target() {

		if ( strpos( $this->source['id'], 'a' ) !== false ) {
			return true;
		}

		$type  = strpos( $this->target['id'], 'q' ) !== false ? 'question' : 'answer';
		$model = array();
		switch ( $type ) {
			case 'question':

				$model['id']                   = intval( $this->target['id'] );
				$model['previous_question_id'] = intval( $this->source['id'] );
				break;
		}

		if ( empty( $model ) ) {
			return false;
		}

		$method = 'save_' . $type;

		return $this->manager->$method( $model );
	}

	protected function disconnect_target() {
		if ( strpos( $this->source['id'], 'a' ) !== false ) {
			return true;
		}
		$type  = strpos( $this->target['id'], 'q' ) !== false ? 'question' : 'answer';
		$model = array();
		switch ( $type ) {
			case 'question':
				$model['id']                   = intval( $this->target['id'] );
				$model['previous_question_id'] = null;
				break;
		}

		if ( empty( $model ) ) {
			return false;
		}

		$method = 'save_' . $type;

		return $this->manager->$method( $model );
	}

	protected function set_start_flow() {

		$model['id']    = intval( $this->target['id'] );
		$model['start'] = 1;

		return $this->manager->save_question( $model ) !== false;
	}

	protected function set_stop_flow() {

		$model['id']    = intval( $this->target['id'] );
		$model['start'] = null;

		return $this->manager->save_question( $model ) !== false;
	}
}
