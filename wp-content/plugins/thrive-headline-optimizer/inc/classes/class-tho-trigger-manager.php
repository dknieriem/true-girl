<?php

/**
 * Created by PhpStorm.
 * User: sala
 * Date: 08-Feb-16
 * Time: 10:38
 */
class THO_Trigger_Manager {

	/**
	 * @var THO_Trigger_Manager
	 */
	private static $instance;

	/**
	 * singleton implementation
	 *
	 * @return THO_Trigger_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new THO_Trigger_Manager();
		}

		return self::$instance;
	}

	/**
	 * Add frontend triggers that will log impressions and engagements
	 *
	 * @param $post_id
	 * @param $title
	 *
	 * @return mixed
	 */
	public function title_trigger( $post_id, $title ) {

		/* We don't track logs for logged users */
		if ( empty( $post_id ) || tho_ignore_log() ) {
			return $title;
		}

		$test = tho_get_running_test( array(
			'post_id' => $post_id
		) );

		if ( empty( $test ) ) {
			return $title;
		}

		$has_click = '';
		if ( ! empty( $test->click_through ) ) {
			$has_click = 'click';
		}

		/* Wrap the title so we can later remove it and add the variation we want. */
		$title = '<' . THO_HEADLINE_TAG . ' ' . $has_click . ' tho-post-' . $post_id . ' tho-test-' . $test->id . '>' . $title . '</' . THO_HEADLINE_TAG . '>';

		return $title;
	}

	/**
	 * Get active triggers for a certain
	 *
	 * @param int $post_id
	 * @param $is_single
	 *
	 * @return string
	 */
	public function get_active_triggers( $post_id = 0, $is_single ) {

		if ( ! $is_single ) {
			return $this->get_index_triggers();
		}

		$test = tho_get_running_test( array(
			'post_id' => $post_id
		) );

		/* If there is no test, we still have to trigger click impressions, for when a users spots a post */
		if ( empty( $test ) ) {
			return array(
				'viewport' => THO_HEADLINE_TAG
			);
		}

		return $this->get_single_triggers( $test );
	}

	/**
	 * Return triggers used for index pages.
	 * Right now we only "comes in viewport" that is used to store an impression
	 * @return array
	 */
	private function get_index_triggers() {
		return array( 'viewport' => THO_HEADLINE_TAG );
	}

	/**
	 * Get the triggers that will be used for is_singular() pages to log engagements
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	private function get_single_triggers( $settings ) {
		if ( empty( $settings ) ) {
			return array();
		}

		$triggers = self::get_settings_triggers();

		$response = array();

		foreach ( $triggers as $trigger => $config ) {
			if ( $settings->$trigger == '1' ) {
				$response[ $trigger ] = empty( $settings->$config ) ? '' : $settings->$config;
			}
		}

		/* On single we have to log impressions for posts that we see on page and have a click_through engagement */
		$response['viewport'] = THO_HEADLINE_TAG;

		return $response;
	}

	/**
	 * Those are the triggers that are saved in the settings section.
	 * @return array key-value representing trigger-config
	 */
	private static function get_settings_triggers() {
		return array(
			'click_through'          => '',
			'scrolling_signal'       => 'scrolling_signal_value',
			'time_on_content_signal' => 'time_on_content_signal_value'
		);
	}

	/**
	 * Return engagement type constant for each trigger
	 *
	 * @param $triggers
	 *
	 * @return array
	 */
	public static function get_trigger_engagement( $triggers ) {
		$engagements = array(
			'click_through'          => THO_CLICK_ENGAGEMENT,
			'scrolling_signal'       => THO_SCROLL_ENGAGEMENT,
			'time_on_content_signal' => THO_TIME_ENGAGEMENT
		);

		$response = array();
		foreach ( $triggers as $t => $config ) {
			if ( ! empty( $engagements[ $t ] ) ) {
				$response[] = $engagements[ $t ];
			}
		}

		return $response;
	}
}