<?php

class MC4WP_Graph {

	/**
	* @var string
	*/
	private $table_name;

	/**
	 * @var array
	 */
	private $initial_data = array();

	/**
	 * @var array
	 */
	private $config = array();

	/**
	 * @var string
	 */
	public $range = '';

	/**
	 * @var
	 */
	public $start_date;

	/**
	 * @var
	 */
	public $end_date;

	/**
	 * @var string
	 */
	public $step_size = 'day';

	/**
	 * @var array
	 */
	public $datasets = array();

	/**
	 * @var array
	 */
	public $lines = array();

	/**
	 * @var
	 */
	private $day;

	/**
	* Constructor
	*/
	public function __construct( $config ) {

		// store config
		$this->config = $config;

		// set table prefix
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'mc4wp_log';

		// get range and setup start dates etc..
		$this->range = ( isset( $config['range'] ) ) ? $config['range'] : 'last_week';
	}

	/**
	* Initialize various settings to use
	*/
	public function init() {
		$this->day = date( 'd' );

		switch ( $this->range ) {
			case 'today':
				$this->start_date = strtotime( 'now midnight' );
				$this->end_date = strtotime( 'tomorrow midnight' );
				$this->step_size = 'hour';
			break;

			case 'yesterday':
				$this->start_date = strtotime( 'yesterday midnight' );
				$this->end_date = strtotime( 'today midnight' );
				$this->step_size = 'hour';
			break;

			case 'last_week':
				$this->start_date = strtotime( '-1 week midnight' );
				$this->end_date = strtotime( 'tomorrow midnight' );
				$this->step_size = 'day';
			break;

			case 'last_month':
				$this->start_date = strtotime( '-1 month midnight' );
				$this->end_date = strtotime( 'tomorrow midnight' );
				$this->step_size = 'day';
			break;

			case 'last_quarter':
				$this->start_date = strtotime( '-3 months midnight', strtotime(date('01-m-Y')) );
				$this->end_date = strtotime( 'tomorrow midnight' );
				$this->step_size = 'month';
			break;

			case 'last_year':
				$this->start_date = strtotime( '-1 year midnight', strtotime(date('01-m-Y')) );
				$this->end_date = strtotime( 'tomorrow midnight' );
				$this->step_size = 'month';
			break;

			case 'custom':
				$this->start_date = strtotime( implode( '-', array( $this->config['start_year'], $this->config['start_month'], $this->config['start_day'] ) ) );
				$this->end_date = strtotime( implode( '-', array( $this->config['end_year'], $this->config['end_month'], $this->config['end_day'] ) ) );
				$this->step_size = $this->calculate_step_size( $this->start_date, $this->end_date );
				$this->day = $this->config['start_day'];

				// add 1 step to end date so we include today
				$this->end_date = strtotime( "+1 {$this->step_size}", $this->end_date );
				break;

			default:
				$this->start_date = strtotime( '-1 week midnight' );
				$this->end_date = strtotime( 'tomorrow midnight' );
				$this->step_size = 'day';
			break;
		}

		// If start is before end, revert back to "week" range and re-init.
		if( $this->start_date > $this->end_date ) {
			add_settings_error( 'mc4wp', 'mc4wp-stats', __( 'End date can\'t be before the start date', 'mailchimp-for-wp' ) );
			$this->range = 'last_week';
			$this->init();
		}

		// setup array of dates with 0's
		$current = $this->start_date;
		$this->initial_data = array();
		while ( $current <= $this->end_date ) {
			$this->initial_data["{$current}"] = 0;
			$current = strtotime( "+1 {$this->step_size}", $current );
		}

		$this->query();
	}

	/**
	 * Calculates an appropriate step size
	 *
	* @param int $start
	* @param int $end
	*
	* @return string
	*/
	public function calculate_step_size( $start, $end ) {
		$difference = $end - $start;
		$dayseconds = 86400;
		$monthseconds = 2592000;

		if ( $difference > ( $monthseconds * 6 ) ) {
			$step = 'month';
		} elseif ( $difference > $dayseconds ) {
			$step = 'day';
		} else {
			$step = 'hour';
		}

		return $step;
	}

	/**
	 * @return mixed
	 */
	protected function get_date_format() {
		$date_formats = array(
			'hour' => '%Y-%m-%d %H:00:00',
			'day' => '%Y-%m-%d 00:00:00',
			'week' => '%YW%v 00:00:00',
			'month' =>  "%Y-%m-{$this->day} 00:00:00",
		);

		return $date_formats[ $this->step_size ];
	}

	/**
	 * @return array
	 */
	public function query() {
		$datasets = array();
		$lines = array();
		$day_counts = $this->get_total_day_counts();

		// everything
		$datasets['all'] = array(
			'label' => __( 'Any sign-up method', 'mailchimp-for-wp' ),
			'data' => array_map( array( $this, 'format_graph_data' ), $day_counts, array_keys( $day_counts ) ),
			'total_count' => array_sum( $day_counts )
		);
		$lines['global'] = array( __( "General" ), 'all' );

		// forms
		$forms = mc4wp_get_forms();
		$lines['forms'] = array( __( "Sign-Up Forms", 'mailchimp-for-wp' ) );

		foreach( $forms as $form ) {
			$day_counts = $this->get_day_counts_for_form( $form->ID );
			$dataset = array(
				'label' => sprintf( '%d | %s', $form->ID, esc_html( $form->name ) ),
				'data' => array_map( array( $this, 'format_graph_data' ), $day_counts, array_keys( $day_counts ) ),
				'total_count' => array_sum( $day_counts )
			);
			$datasets["form-" . $form->ID] = $dataset;
			$lines['forms'][] = "form-". $form->ID;
		}


		// integrations
		$integrations = mc4wp_get_integrations();
		$lines['integrations'] = array( __( 'Integrations', 'mailchimp-for-wp' ) );
		foreach( $integrations as $integration ) {

			$day_counts = $this->get_day_counts_for_type( $integration->slug );
			$dataset = array(
				'label' => $integration->name,
				'data' => array_map( array( $this, 'format_graph_data' ), $day_counts, array_keys( $day_counts ) ),
				'total_count' => array_sum( $day_counts )
			);
			$datasets[ "{$integration->slug}" ] = $dataset;
			$lines['integrations'][] = $integration->slug;
		}

		// Top Bar
        $day_counts = $this->get_day_counts_for_type( 'mc4wp-top-bar' );
        $dataset = array(
            'label' => 'Top Bar',
            'data' => array_map( array( $this, 'format_graph_data' ), $day_counts, array_keys( $day_counts ) ),
            'total_count' => array_sum( $day_counts )
        );
        $datasets[ "mc4wp-top-bar" ] = $dataset;
        $lines['integrations'][] = 'mc4wp-top-bar';


		$this->lines = $lines;
		$this->datasets = $datasets;
	}

	/**
	 * @param $totals
	 *
	 * @return array
	 */
	public function get_day_counts( $totals ) {
		$counts = $this->initial_data;
		$timestamps = array_keys( $counts );

		for($i = 0; $i < count($timestamps); $i++) {
			$start = $timestamps[$i];
			$end = isset( $timestamps[$i+1] ) ? $timestamps[$i+1] : strtotime('+1 year');

			foreach( $totals as $group ) {
				$timestamp = strtotime( $group->date_group );
				
				if( $timestamp >= $end ) {
					break;
				}

				if( $timestamp >= $start ) {
					$counts[$start] = $counts[$start] + $group->count;
				}				
			}
		}


		return $counts;
	}

	/**
	 * @return array
	 */
	public function get_total_day_counts() {
		global $wpdb;
		$sql = "SELECT COUNT(*) AS count, DATE_FORMAT(datetime, %s) AS date_group FROM `{$this->table_name}` WHERE UNIX_TIMESTAMP(datetime) >= %d AND UNIX_TIMESTAMP(datetime) <= %d GROUP BY date_group ORDER BY date_group ASC";
		
		$query = $wpdb->prepare( $sql, $this->get_date_format(), $this->start_date, $this->end_date );
		$totals = $wpdb->get_results( $query );
		return $this->get_day_counts( $totals );
	}

	public function get_day_counts_for_type( $type ) {
		global $wpdb;
		$sql = "SELECT COUNT(*) AS count, DATE_FORMAT(datetime, '%s') AS date_group FROM `{$this->table_name}` WHERE `type` = '%s' AND UNIX_TIMESTAMP(datetime) >= %d AND UNIX_TIMESTAMP(datetime) <= %d GROUP BY date_group ORDER BY date_group ASC";
		$query = $wpdb->prepare( $sql, $this->get_date_format(), $type, $this->start_date, $this->end_date );
		$totals = $wpdb->get_results( $query );
		return $this->get_day_counts( $totals );
	}

	public function get_day_counts_for_form( $form_id ) {
		global $wpdb;
		$sql = "SELECT COUNT(*) AS count, DATE_FORMAT(datetime, '%s') AS date_group FROM `{$this->table_name}` WHERE `related_object_ID` = %d AND `type` = '%s' AND UNIX_TIMESTAMP(datetime) >= %d AND UNIX_TIMESTAMP(datetime) <= %d GROUP BY date_group ORDER BY date_group ASC";
		$query = $wpdb->prepare( $sql, $this->get_date_format(), $form_id, 'mc4wp-form', $this->start_date, $this->end_date );
		$totals = $wpdb->get_results( $query );
		return $this->get_day_counts( $totals );
	}

	/**
	 * @param $count
	 * @param $timestamp
	 *
	 * @return array
	 */
	public function format_graph_data( $count, $timestamp ) {
		return array( $timestamp * 1000, $count );
	}
}
