<?php
defined( 'ABSPATH' ) or exit;

class MC4WP_Ecommerce_Command extends WP_CLI_Command  {

	/**
	 * Tracks the order with the given ID in MailChimp
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * <order_id>
	 * : Order to add to MailChimp
	 *
	 * ## EXAMPLES
	 *
	 *     wp mc4wp-ecommerce add-order
	 *
	 * @synopsis <order_id>
	 *
	 * @subcommand add-order
	 */
	public function add_order( $args, $assoc_args = array() ) {
		$order_id = (int) $args[0];
		$success = $this->ecommerce()->add_order( $order_id );

		if( $success ) {
			WP_CLI::success( 'Success!' );
		} else {
			WP_CLI::error( 'Error!' );
		}
	}

	/**
	 * Deletes the order with the given ID in MailChimp
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * <order_id>
	 * : Order to delete in MailChimp
	 *
	 * ## EXAMPLES
	 *
	 *     wp mc4wp-ecommerce delete-order
	 *
	 * @synopsis <order_id>
	 *
	 * @subcommand delete-order
	 */
	public function delete_order( $args, $assoc_args = array() ) {
		$order_id = (int) $args[0];
		$success = $this->ecommerce()->delete_order( $order_id );

		if( $success ) {
			WP_CLI::success( 'Success!' );
		} else {
			WP_CLI::error( 'Error!' );
		}
	}

	/**
	 * Adds multiple untracked orders, starting with the most recent orders.
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * [--<limit>=<limit>]
	 * : Limit # of orders to this number. Default: 1000
	 *
	 * [--offset=<offset>]
	 * : Skip the first # orders. Default: 0
	 *
	 * ## EXAMPLES
	 *
	 *     wp mc4wp-ecommerce add-orders --limit=5000 --offset=1000
	 *
	 * @synopsis [--limit=<limit>] [--offset=<offset>]
	 *
	 * @subcommand add-orders
	 */
	public function add_orders( $args, $assoc_args = array() ) {
		$offset = empty( $assoc_args['offset'] ) ? 0 : (int) $assoc_args['offset'];
		$limit = empty( $assoc_args['limit'] ) ? 1000 : (int) $assoc_args['limit'];

		$helper = new MC4WP_Ecommerce_Helper();
		$order_ids = $helper->get_untracked_order_ids( $offset, $limit );
		$count = count( $order_ids );

		WP_CLI::line( sprintf( "%d untracked orders found.", $count ) );

		if( $count > 0 ) {

			$ecommerce = $this->ecommerce();

			// show progress bar
			$notify = \WP_CLI\Utils\make_progress_bar( __( 'Sending orders to MailChimp', 'mc4wp-ecommerce'), $count );

			foreach( $order_ids as $order_id ) {
				$success = $ecommerce->add_order( $order_id );
				$notify->tick();
			}

			$notify->finish();
		}

	}

	/**
	 * @return MC4WP_Ecommerce
	 */
	private function ecommerce() {
		return mc4wp('ecommerce');
	}

}