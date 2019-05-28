<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Class TQB_Test_Manager
 *
 * Handles Test operations
 */
class TQB_Test_Manager {

	/**
	 * @var TQB_Test_Manager $instance
	 */
	protected $test_id;

	/**
	 * TQB_Test_Manager constructor.
	 */
	public function __construct( $test_id ) {
		$this->test_id = $test_id;
	}

	/**
	 * Insert new split test in database
	 *
	 * @param $model
	 *
	 * @return int
	 */
	function save_test( $model ) {
		$defaults = array(
			'status'       => 1,
			'date_added'   => date( 'Y-m-d H:i:s' ),
			'date_started' => date( 'Y-m-d H:i:s' ),
		);

		$model = array_merge( $defaults, $model );

		global $tqbdb;

		$test_id = $tqbdb->save_test( $model );
		if ( $test_id ) {
			$test_item  = array();
			$is_control = true;
			foreach ( $model['item_ids'] as $item_id ) {

				$variation = $tqbdb->get_variation( $item_id );
				if ( $variation ) {
					$test_item['test_id']      = $test_id;
					$test_item['variation_id'] = $item_id;
					$test_item['is_control']   = $is_control;
					$is_control                = false;
					$this->save_test_item( $test_item );
				}
			}

			return $test_id;
		}

		return false;

	}

	/**
	 * Get test items belonging to running test
	 *
	 * @param $model
	 *
	 * @return int
	 */
	function get_test_items( $model ) {
		global $tqbdb;

//		$tqbdb->generate_dummy_data( $model['test_id'], 1000, '2016-10-20 10:58:11', '2016-11-04 11:58:11' );
		return $tqbdb->get_test_items( $model );

	}

	/**
	 * Get test
	 *
	 * @param $model
	 *
	 * @return int
	 */
	function get_test( $model ) {
		global $tqbdb;

		$model = $tqbdb->get_test( $model, true );
		if ( empty( $model ) ) {
			return false;
		}
		$test_items             = $this->get_test_items( array( 'test_id' => $model['id'] ) );
		$test_items             = $this->parse_test_items_data( $test_items, $model );
		$model['test_items']    = $test_items['test_items'];
		$model['stopped_items'] = $test_items['stopped_items'];

		$page                     = new TQB_Page_Manager( $model['page_id'] );
		$model['completed_tests'] = $page->get_tests_for_page( array( 'page_id' => $model['page_id'], 'status' => 0 ), false );
		if ( $model['status'] == '0' ) {
			$model['running_test'] = $page->get_tests_for_page( array( 'page_id' => $model['page_id'], 'status' => 1 ), true );
		} else {
			$model['running_test'] = null;
		}
		$model['quiz_id']   = $page->get_quiz_id();
		$model['quiz_name'] = html_entity_decode( get_the_title( $model['quiz_id'] ) );

		return $model;
	}

	/**
	 *  Parse test items data
	 */
	function parse_test_items_data( $test_items = null, $test ) {
		if ( ! empty( $test_items ) && ! empty( $test ) ) {
			global $tqbdb;
			$result['test_items']    = array();
			$result['stopped_items'] = array();
			$active_item_index       = 0;
			$stopped_item_index      = 0;
			foreach ( $test_items as $key => $item ) {
				$variation_data         = $tqbdb->get_variation( $item['variation_id'] );
				$item                   = array_merge( (array) $variation_data, (array) $test_items[ $key ] );
				$item['tcb_editor_url'] = TQB_Variation_Manager::get_preview_url( $test['page_id'], $item['variation_id'] );

				if ( $test['conversion_goal'] === Thrive_Quiz_Builder::CONVERSION_GOAL_SOCIAL ) {
					$item['impressions']        = $item['social_shares'];
					$item['optins_conversions'] = $item['social_shares_conversions'];
				}

				$item['conversion_rate'] = tqb_conversion_rate( $item['impressions'], $item['optins_conversions'], '' );
				if ( empty( $test_items[0]['conversion_rate'] ) ) {
					$test_items[0]['conversion_rate'] = $item['conversion_rate'];
				}
				if ( $key > 0 ) {
					//Percentage improvement = conversion rate of variation - conversion rate of control
					if ( is_numeric( $test_items[0]['conversion_rate'] ) ) {
						$item['percentage_improvement'] = round( ( ( $item['conversion_rate'] - $test_items[0]['conversion_rate'] ) * 100 ) / $test_items[0]['conversion_rate'], 2 );
					} else {
						$item['percentage_improvement'] = 'N/A';
					}

					$item['beat_original'] = $this->test_item_beat_original( $item['conversion_rate'], $item['impressions'], $test_items[0]['conversion_rate'], $test_items[0]['impressions'] );
				}

				if ( $item['active'] ) {
					$item['index'] = $active_item_index;
					array_push( $result['test_items'], $item );
					$active_item_index ++;
				} else {
					$item['index'] = $stopped_item_index;
					array_push( $result['stopped_items'], $item );
					$stopped_item_index ++;
				}
			}
		} else {
			return null;
		}

		return $result;
	}

	/**
	 * Insert new split test in database
	 *
	 * @param $model
	 *
	 * @return int
	 */
	function save_test_item( $model ) {

		global $tqbdb;

		return $tqbdb->save_test_item( $model );

	}

	function delete_test_items() {
		global $tqbdb;

		return $tqbdb->delete_test_items( array( 'test_id' => $this->test_id ) );
	}

	/**
	 * Deletes a test
	 *
	 * @param array $filters
	 *
	 * @return false|int
	 */
	function delete_test( $filters = array() ) {
		global $tqbdb;

		$defaults = array(
			'id' => $this->test_id,
		);

		return $tqbdb->delete_tests( array_merge( $defaults, $filters ) );
	}

	/**
	 *  Get test item names
	 */
	function get_test_items_data_names( $test_items = null ) {
		if ( ! empty( $test_items ) ) {
			$result_ids   = array();
			$result_names = array();
			foreach ( $test_items as $item ) {
				global $tqbdb;
				$variation                             = $tqbdb->get_variation( $item['variation_id'] );
				$result_ids[ $item['variation_id'] ]   = $item['is_control'] ? - 1 : $item['id'];
				$result_names[ $item['variation_id'] ] = $variation['post_title'];
			}

			return array( 'ids' => $result_ids, 'names' => $result_names );
		}

		return false;
	}

	/**
	 * @param $test_id  ID of the test we want to get data from
	 * @param $interval can be 'day', 'week', or 'month' - the date interval of the chart
	 *
	 * @return array
	 */
	function get_test_chart_data( $interval ) {
		$filter = array(
			'id' => $this->test_id,
		);
		global $tqbdb;
		$test       = $tqbdb->get_test( $filter, true );
		$test_items = $tqbdb->get_test_items( array( 'test_id' => $test['id'] ) );
		$data       = $this->get_test_items_data_names( $test_items );
		if ( ! $data ) {
			return null;
		}
		$test_items    = $data['names'];
		$test_item_ids = $data['ids'];

		$filter = array(
			'interval'        => $interval,
			'group_names'     => $test_items,
			'data_group'      => 'variation_id',
			'page_id'         => $test['page_id'],
			'conversion_goal' => $test['conversion_goal'],
			'group_ids'       => array_keys( $test_items ),
			'start_date'      => $test['date_started'],
			'end_date'        => $test['date_completed'] && $test['status'] == 'archived' ? $test['date_completed'] : date( 'Y-m-d H:i:s' ),
			'group_by'        => array( 'variation_id', 'date_interval', 'event_type' ),
		);

		$chart_data = tqb_get_conversion_rate_test_data( $filter );

		foreach ( $chart_data['chart_data'] as $main_id => $item ) {
			foreach ( $item['data'] as $index => $conversion_rate ) {
				// calculate the new conversion rate as a sum of the total numbers from the beginning of the test until at this point
				$impressions = $conversions = 0;
				for ( $i = 0; $i <= $index; $i ++ ) {
					$impressions += $item['impression_count'][ $i ];
					$conversions += $item['conversion_count'][ $i ];
				}
				$chart_data['chart_data'][ $main_id ]['data'][ $index ] = (double) tqb_conversion_rate( $impressions, $conversions, '' );
				unset( $chart_data['chart_data'][ $main_id ]['impression_count'], $chart_data['chart_data'][ $main_id ]['conversion_count'] );
			}
		}

		$temp = array();
		asort( $test_item_ids );
		foreach ( $test_item_ids as $main_id => $order ) {
			if ( ! isset( $chart_data['chart_data'][ $main_id ] ) ) {
				continue;
			}
			$temp [] = $chart_data['chart_data'][ $main_id ];
		}
		$chart_data['chart_data'] = $temp;

		unset( $chart_data['table_data'] );

		return $chart_data;
	}

	/**
	 * Calculate the chance of a variation to beat the original during a test
	 *
	 * @param $variation_conversion_rate
	 * @param $variation_unique_impressions
	 * @param $control_conversion_rate
	 * @param $control_unique_impressions
	 *
	 * @return string $confidence_level
	 */
	function test_item_beat_original( $variation_conversion_rate, $variation_unique_impressions, $control_conversion_rate, $control_unique_impressions, $suffix = '%' ) {
		if ( $variation_unique_impressions == 0 || $control_unique_impressions == 0 ) {
			return 'N/A';
		}

		$variation_conversion_rate = $variation_conversion_rate / 100;
		$control_conversion_rate   = $control_conversion_rate / 100;

		//standard deviation = sqrt((conversionRate*(1-conversionRate)/uniqueImpressions)
		$variation_standard_deviation = sqrt( ( $variation_conversion_rate * ( 1 - $variation_conversion_rate ) / $variation_unique_impressions ) );
		$control_standard_deviation   = sqrt( ( $control_conversion_rate * ( 1 - $control_conversion_rate ) / $control_unique_impressions ) );

		if ( ( $variation_standard_deviation == 0 && $control_standard_deviation == 0 ) || ( is_nan( $variation_standard_deviation ) || is_nan( $control_standard_deviation ) ) ) {
			return 'N/A';
		}
		//z-score = (control_conversion_rate - variation_conversion_rate) / sqrt((controlStandardDeviation^2)+(variationStandardDeviation^2))
		$z_score = ( $control_conversion_rate - $variation_conversion_rate ) / sqrt( pow( $control_standard_deviation, 2 ) + pow( $variation_standard_deviation, 2 ) );

		if ( is_nan( $z_score ) ) {
			return 'N/A';
		}

		//Confidence_level (which is synonymous with “chance to beat original”)  = normdist(z-score)
		$confidence_level = tqb_norm_dist( $z_score );

		return number_format( round( ( 1 - $confidence_level ) * 100, 2 ), 2 ) . $suffix;
	}


	/**
	 * Checks a test auto win settings and checks if a variation satisfy them.
	 * If so, it makes that variation winner
	 */
	public function check_test_auto_win() {

		if ( empty( $this->test_id ) ) {
			return;
		}

		$test_id = $this->test_id;

		global $tqbdb;
		$test = $tqbdb->get_tests( array(
			'test_id' => $test_id,
			'status'  => 1, // Status RUNNING
		), true, ARRAY_A );

		if ( empty( $test ) ) {
			return;
		}

		if ( empty( $test['auto_win_enabled'] ) ) {
			return;
		}

		if ( ! empty( $test['auto_win_min_duration'] ) ) {
			/* check if this amount of time has passed -> if not, no need for further processing */
			if ( time() < strtotime( $test['date_started'] . ' +' . $test['auto_win_min_duration'] . 'days' ) ) {
				return;
			} /* The time interval has passed, we can check the other conditions */
		}

		if ( empty( $test['conversion_goal'] ) || $test['conversion_goal'] == 1 ) {
			$conversions_key = 'optins_conversions';
		} else {
			$conversions_key = 'social_shares_conversions';
		}

		if ( $test[ $conversions_key ] < $test['auto_win_min_conversions'] ) {
			return;
		}

		$test_items = $tqbdb->get_test_items( array(
			'test_id' => $test_id,
			'active'  => 1,
		), ARRAY_A );

		/* find the control variation for later use */
		foreach ( $test_items as $item ) {
			if ( ! empty( $item['is_control'] ) ) {
				$control = $item;
				break;
			}
		}

		if ( empty( $control ) ) {
			return;
		}

		$control_conversion_rate  = floatval( tqb_conversion_rate( $control['impressions'], $control[ $conversions_key ], '' ) );
		$variations_beat_original = 100.0 - (float) $test['auto_win_chance_original'];
		$variation_win_array      = array();

		foreach ( $test_items as $variation ) {

			if ( $variation['is_control'] ) {
				$control_win = true;
				foreach ( $test_items as $var ) {
					if ( $var['is_control'] ) {
						continue;
					}
					$conversion_rate = tqb_conversion_rate( $var['impressions'], $var[ $conversions_key ] );
					$beat_original   = floatval( $this->test_item_beat_original( $conversion_rate, $var['impressions'], $control_conversion_rate, $control['impressions'], '' ) );
					if ( $variations_beat_original < $beat_original || empty( $beat_original ) ) {
						$control_win = false;
					}

					if ( $control_win ) {
						$tqbdb->set_winner( $variation );
						break;
					}
				}
			} else {
				$conversion_rate = tqb_conversion_rate( $variation['impressions'], $variation[ $conversions_key ] );
				$beat_original   = floatval( $this->test_item_beat_original( $conversion_rate, $variation['impressions'], $control_conversion_rate, $control['impressions'], '' ) );

				if ( (float) $beat_original > (float) $test['auto_win_chance_original'] ) {

					$obj                   = new stdClass();
					$obj->beat_original    = (float) $beat_original;
					$obj->variation        = $variation;
					$variation_win_array[] = $obj;
				}
			}
		}

		if ( ! empty( $variation_win_array ) ) {
			$winner_variation     = $variation_win_array[0]->variation;
			$winner_beat_original = $variation_win_array[0]->beat_original;
			foreach ( $variation_win_array as $var_win_arr ) {
				if ( $winner_beat_original <= $var_win_arr->beat_original ) {
					$winner_beat_original = $var_win_arr->beat_original;
					$winner_variation     = $var_win_arr->variation;
				}
			}
			/*Set the winner to the highest beat original*/
			$tqbdb->set_winner( $winner_variation );
		}
	}

	/**
	 * Stop the under performing variations
	 */
	public function stop_underperforming_variations() {
		if ( empty( $this->test_id ) ) {
			return;
		}

		$test_id = $this->test_id;

		global $tqbdb;
		$test = $tqbdb->get_tests( array(
			'test_id' => $test_id,
			'status'  => 1, // Status RUNNING
		), true, ARRAY_A );

		if ( empty( $test ) ) {
			return;
		}

		if ( empty( $test['auto_win_enabled'] ) ) {
			return;
		}

		if ( ! empty( $test['auto_win_min_duration'] ) ) {
			/* check if this amount of time has passed -> if not, no need for further processing */
			if ( time() < strtotime( $test['date_started'] . ' +' . $test['auto_win_min_duration'] . 'days' ) ) {
				return;
			} /* The time interval has passed, we can check the other conditions */
		}

		if ( empty( $test['conversion_goal'] ) || $test['conversion_goal'] == 1 ) {
			$conversions_key = 'optins_conversions';
		} else {
			$conversions_key = 'social_shares_conversions';
		}

		if ( $test[ $conversions_key ] < $test['auto_win_min_conversions'] ) {
			return;
		}

		$test_items = $tqbdb->get_test_items( array(
			'test_id' => $test_id,
			'active'  => 1,
		), ARRAY_A );

		/* find the control variation for later use */
		foreach ( $test_items as $item ) {
			if ( ! empty( $item['is_control'] ) ) {
				$control = $item;
				break;
			}
		}

		if ( empty( $control ) ) {
			return;
		}

		$control_conversion_rate  = floatval( tqb_conversion_rate( $control['impressions'], $control[ $conversions_key ], '' ) );
		$variations_beat_original = 100.0 - (float) $test['auto_win_chance_original'];

		foreach ( $test_items as $variation ) {
			if ( $variation['is_control'] ) {
				continue;
			}

			$conversion_rate = tqb_conversion_rate( $variation['impressions'], $variation[ $conversions_key ] );
			$beat_original   = floatval( $this->test_item_beat_original( $conversion_rate, $variation['impressions'], $control_conversion_rate, $control['impressions'], '' ) );

			if ( (float) $beat_original < $variations_beat_original ) {
				/* We stop the variation */
				$variation['active']       = 0;
				$variation['stopped_date'] = date( 'Y-m-d H:i:s' );
				$this->save_test_item( $variation );
			}
		}
	}
}
