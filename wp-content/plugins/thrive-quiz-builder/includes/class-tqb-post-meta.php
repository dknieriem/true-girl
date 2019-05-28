<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 9/15/2016
 * Time: 4:22 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TQB_Post_meta Class.
 */
class TQB_Post_meta {

	/**
	 * METAs
	 */
	const META_NAME_FOR_QUIZ_TYPE = 'tqb_quiz_type';
	const META_NAME_FOR_QUIZ_STYLE = 'tqb_quiz_style';
	const META_NAME_FOR_QUIZ_STRUCTURE = 'tqb_quiz_structure';
	const META_NAME_FOR_QUIZ_TPL = 'tqb_quiz_tpl';
	const META_NAME_FOR_QUIZ_ORDER = 'tqb_quiz_order';
	/**
	 * Result intervals that the result page state could have.
	 */
	const META_NAME_FOR_RESULT_INTERVALS = 'tqb_result_intervals';

	/**
	 * Wizard complete
	 */
	const META_NAME_FOR_WIZARD_COMPLETE = 'tqb_wizard_complete';

	/**
	 * Updates the quiz type meta
	 *
	 * @param $post_id
	 * @param $model
	 *
	 * @return false|int
	 */
	public static function update_quiz_type_meta( $post_id, $model ) {
		$meta_value = array( 'type' => $model['type'] );
		$result     = update_post_meta( $post_id, self::META_NAME_FOR_QUIZ_TYPE, $meta_value );

		return $result;
	}

	/**
	 * Get quiz type meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_quiz_type_meta( $post_id ) {
		return get_post_meta( $post_id, self::META_NAME_FOR_QUIZ_TYPE, true );
	}

	/**
	 * Updates the quiz style meta
	 *
	 * @param $post_id
	 * @param $model
	 *
	 * @return false|int
	 */
	public static function update_quiz_style_meta( $post_id, $model ) {
		$meta_value = $model['style'];
		$result     = update_post_meta( $post_id, self::META_NAME_FOR_QUIZ_STYLE, $meta_value );

		return $result;
	}

	/**
	 * Get quiz style meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_quiz_style_meta( $post_id ) {
		return get_post_meta( $post_id, self::META_NAME_FOR_QUIZ_STYLE, true );
	}


	/**
	 * Updates the quiz tpl meta
	 *
	 * @param $post_id
	 * @param $model
	 *
	 * @return false|int
	 */
	public static function update_quiz_tpl_meta( $post_id, $model ) {
		$meta_value = $model['tpl'];
		$result     = update_post_meta( $post_id, self::META_NAME_FOR_QUIZ_TPL, $meta_value );

		return $result;
	}

	/**
	 * Get quiz tpl meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_quiz_tpl_meta( $post_id ) {
		$tpl = get_post_meta( $post_id, self::META_NAME_FOR_QUIZ_TPL, true );

		return $tpl;
	}

	/**
	 * Update quiz order
	 *
	 * @param $post_id
	 * @param $order
	 *
	 * @return bool|int
	 */
	public static function update_quiz_order( $post_id, $order ) {
		return update_post_meta( $post_id, self::META_NAME_FOR_QUIZ_ORDER, $order );
	}

	/**
	 * Gets quiz order
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_quiz_order( $post_id ) {
		return get_post_meta( $post_id, self::META_NAME_FOR_QUIZ_ORDER, true );
	}

	/**
	 * Update wizard meta
	 *
	 * @param $post_id
	 *
	 * @return bool|int
	 */
	public static function update_wizard_meta( $post_id ) {
		return update_post_meta( $post_id, self::META_NAME_FOR_WIZARD_COMPLETE, 1 );
	}

	/**
	 * Gets wizard meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_wizard_meta( $post_id ) {
		return get_post_meta( $post_id, self::META_NAME_FOR_WIZARD_COMPLETE, true );
	}
}
