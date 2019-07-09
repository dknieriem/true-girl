<?php

class MC4WP_Ecommerce_Admin_Ajax {

    public function hook() {
        add_action( 'wp_ajax_mc4wp_ecommerce_synchronize_products', array( $this, 'synchronize_products' ) );
        add_action( 'wp_ajax_mc4wp_ecommerce_synchronize_orders', array( $this, 'synchronize_orders' ) );
        add_action( 'wp_ajax_mc4wp_ecommerce_process_queue', array( $this, 'process_queue' ) );
        add_action( 'wp_ajax_mc4wp_ecommerce_reset_queue', array( $this, 'reset_queue' ) );
    }

    /**
     * Checks if current user has `manage_options` capability or kills the request.
     */
    private function authorize() {
        if( ! current_user_can( 'manage_options' ) ) {
            status_header( 401 );
            exit;
        }
    }

    /**
     * Synchronize a product,
     */
    public function synchronize_products() {
        $this->authorize();

        $ecommerce = $this->get_ecommerce();
        $product_id = empty( $_REQUEST['product_id'] ) ? 0 : (int) $_REQUEST['product_id'];

        // make sure product id is given
        if( empty( $product_id ) ) {
            wp_send_json_error(
                array(
                    'message' => sprintf( 'Invalid product ID.' )
                )
            );
        }

        try {
            $ecommerce->update_product( $product_id );
        } catch( Exception $e ) {
            wp_send_json_error(
                array(
                    'message' => sprintf( "Error adding product %d: %s", $product_id, $e )
                )
            );
        }

        wp_send_json_success(
            array(
                'message' => sprintf( 'Success! Added product %d to MailChimp.', $product_id )
            )
        );
    }

    /**
     * Synchronize an order.
     */
    public function synchronize_orders() {
        $this->authorize();

        $ecommerce = $this->get_ecommerce();
        $order_id = empty( $_REQUEST['order_id'] ) ? 0 : (int) $_REQUEST['order_id'];

        // make sure order id is given
        if( empty( $order_id ) ) {
            wp_send_json_error(
                array(
                    'message' => sprintf( 'Invalid order ID.' )
                )
            );
            exit;
        }

        // unset tracking cookies temporarily because these would be the admin's cookie
        unset( $_COOKIE['mc_tc'] );
        unset( $_COOKIE['mc_cid'] );

        // add order
        $ecommerce = $this->get_ecommerce();

        try {
            $ecommerce->update_order( $order_id );
        } catch( Exception $e ) {
            // order contains no items is a soft-error
            if( $e->getCode() === MC4WP_Ecommerce::ERR_NO_ITEMS ) {
                wp_send_json_error(
                    array(
                        'message' => sprintf( "Skipping order %d: %s", $order_id, $e->getMessage() )
                    )
                );
                exit;
            }

            // more important errors
            wp_send_json_error(
                array(
                    'message' => sprintf( "Error adding order %d: %s", $order_id, $e )
                )
            );
            exit;
        }

        wp_send_json_success(
            array(
                'message' => sprintf( 'Success! Added order %d to MailChimp.', $order_id )
            )
        );
        exit;
    }

    /**
     * Process the background queue.
     */
    public function process_queue() {
        $this->authorize();
        do_action( 'mc4wp_ecommerce_process_queue' );
        wp_send_json(true);
        exit;
    }

     /**
     * Process the background queue.
     */
    public function reset_queue() {
        $this->authorize();
        $queue = mc4wp('ecommerce.queue');
        $queue->reset();
        $queue->save();
        wp_send_json(true);
        exit;
    }

    /**
     * @return MC4WP_Ecommerce
     */
    public function get_ecommerce() {
        return mc4wp('ecommerce');
    }
}
