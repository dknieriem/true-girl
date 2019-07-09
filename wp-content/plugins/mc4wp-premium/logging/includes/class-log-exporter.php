<?php

/**
 * Class MC4WP_Log_Exporter
 *
 * @ignore
 */
class MC4WP_Log_Exporter {

	/**
	 * @var MC4WP_Logger
	 */
	protected $logger;

	/**
	 * @var string The entire CSV string
	 */
	protected $csv_string = '';

	/**
	 * @var bool
	 */
	protected $built = false;

	/**
	 * @var string
	 */
	protected $filename = "mailchimp-for-wp-log.csv";

	/**
	 * @var array
	 */
	protected $filter_arguments = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->logger = new MC4WP_Logger();
	}

	/**
	 * @param array $filter_arguments
	 */
	public function filter( $filter_arguments = array() ) {
		$this->filter_arguments = $filter_arguments;
	}

	/**
	 * @param array $arguments
	 * @return array
	 */
	public function get_logs( $arguments = array() ) {
		$arguments = array_merge( $this->filter_arguments, $arguments );
		return $this->logger->find( $arguments );
	}

	/**
	 * Build the CSV string
	 */
	public function output() {

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"{$this->filename}\";" );
		header("Content-Transfer-Encoding: binary");

		// Open output stream
		$handle = fopen('php://output', 'w');

		// create csv header
		$headers = array(
			"list_id",
			"email_address",
			"merge_fields",
			"interests",
			"status",
			"vip",
			"ip_signup",
			"language",
			"type",
			"source",
			"datetime",
		);
		fputcsv( $handle, $headers, "\t" );

		$offset = 0;
		$batch_size = 500;

		while( true ) {
			$log_items = $this->get_logs( array( 'limit' => $batch_size, 'offset' => $offset ) );

			// stop when we processed all
			if( empty( $log_items ) ) {
				break;
			}

			// loop through log items
			foreach( $log_items as $item ) {

				fputcsv( $handle, array(
                        $item->list_id,
                        $item->email_address,
                        empty( $item->merge_fields ) ? '' : json_encode( $item->merge_fields ),
                        empty( $item->interests ) ? '' : json_encode( $item->interests ),
                        $item->status,
                        $item->vip,
                        (string) $item->ip_signup,
                        (string) $item->language,
                        $item->type,
                        $item->url,
                        $item->datetime
					), "\t"
				);
			}

			// increase offset for next batch
			$offset = $offset + $batch_size;
		}


		// ... close the "file"...
		fclose($handle);
	}

}
