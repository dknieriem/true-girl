<?php

class MC4WP_Ecommerce_Worker {

	/**
	 * @var MC4WP_Queue
	 */
	protected $queue;

	/**
	 * @var MC4WP_Ecommerce
	 */
	protected $ecommerce;

	/**
	 * MC4WP_Ecommerce_Scheduler constructor.
	 *
	 * @param MC4WP_Queue $queue
	 * @param MC4WP_Ecommerce $ecommerce
	 */
	public function __construct( MC4WP_Queue $queue, MC4WP_Ecommerce $ecommerce) {
		$this->queue = $queue;
		$this->ecommerce = $ecommerce;
	}

	/**
	 * Hook
	 */
	public function hook() {
		$worker = $this;

		add_action( 'woocommerce_order_status_completed', array( $worker, 'schedule_add_order' ) );
		add_action( 'edd_complete_purchase', array( $worker, 'schedule_add_order' ) );

		// delete orders whenever order status changes from "completed" or "publish" to something else
		add_action( 'woocommerce_order_status_changed', function( $order_id, $old_status, $new_status ) use( $worker ) {
			if( $new_status !== 'completed' && $old_status === 'completed' ) {
				$worker->schedule_delete_order( $order_id );
			}
		}, 10, 3 );
		add_action( 'edd_update_payment_status', function( $order_id, $new_status, $old_status ) use( $worker ) {
			if ( $new_status !== 'publish' && $old_status === 'publish' ) {
				$worker->schedule_delete_order( $order_id );
			}
		}, 10, 3 );
	}

	/**
	 * Schedule to add an order
	 *
	 * @param int $order_id
	 */
	public function schedule_add_order( $order_id ) {
		$this->queue->put(
			array(
			'type' => 'add',
			'order_id' => $order_id
			)
		);
	}

	/**
	 * @param int $order_id
	 */
	public function schedule_delete_order( $order_id ) {
		$this->queue->put(
			array(
				'type' => 'delete',
				'order_id' => $order_id,
			)
		);
	}

	/**
	 * Work!
	 */
	public function work() {

		while( ( $job = $this->queue->get() ) ) {

			$type = $job->data['type'];
			$order_id = $job->data['order_id'];

			if( $type === 'delete' ) {
				$this->ecommerce->delete_order( $order_id );
			} else {
				$this->ecommerce->add_order( $order_id );
			}

			// remove job from queue
			$this->queue->delete( $job );
		}
	}

}