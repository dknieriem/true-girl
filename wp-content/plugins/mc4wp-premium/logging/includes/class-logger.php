<?php

/**
 * Class MC4WP_Logger
 *
 * @ignore
 */
class MC4WP_Logger {

	/**
	 * @var string
	 */
	private $table_name = '';

	/**
	 * @var WPDB
	 */
	private $db;

	/**
	 * Constructor
	 *
	 * @param WPDB $wpdb
	 */
	public function __construct( $wpdb = null ) {

		if( null === $wpdb ) {
			global $wpdb;
		}

		$this->db = $wpdb;
		$this->table_name = $wpdb->prefix . 'mc4wp_log';
	}

	/**
	 *
	 */
	public function add_hooks() {
		// add listeners for core logging
		add_action( 'mctb_subscribed', array( $this, 'log_top_bar_request' ), 10, 4 );
		add_action( 'mc4wp_integration_subscribed', array( $this, 'log_integration_request' ), 10, 5 );
		add_action( 'mc4wp_form_subscribed', array( $this, 'log_form_request' ), 10, 4 );
      add_action( 'mc4wp_logging_purge_old_items', array( $this, 'purge_old_items' ) );

      add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ), 90 );
      add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ), 90 );

	}

	/**
	 * @param string $list_id
	 * @param string $email_address
	 * @param string $merge_vars
	 * @param MC4WP_MailChimp_Subscriber $subscriber_data
	 *
	 * @return int
	 */
	public function log_top_bar_request( $list_id, $email_address, $merge_vars, MC4WP_MailChimp_Subscriber $subscriber_data = null ) {

		$data = array(
			'list_id' => $list_id,
			'type' => 'mc4wp-top-bar',
		);

		if( $subscriber_data instanceof MC4WP_MailChimp_Subscriber ) {
			$data['email_address'] = $subscriber_data->email_address;
			$data['merge_fields'] = $subscriber_data->merge_fields;
			$data['vip'] = $subscriber_data->vip;
			$data['ip_signup'] = $subscriber_data->ip_signup;
			$data['interests'] = $subscriber_data->interests;
			$data['status'] = $subscriber_data->status;
			$data['language'] = $subscriber_data->language;
		} else {
			// for backwards compatibility with older versions of Top Bar.
			$data['email_address'] = $email_address;
         $data['merge_fields'] = $merge_vars;
		}

		return $this->log_request( $data );
	}

	/**
	 *
	 * @param MC4WP_Integration $integration
	 * @param string $email
	 * @param array $merge_vars
	 * @param array $data_map
	 * @param int $related_object_id
	 *
	 * @return false|int
	 */
	public function log_integration_request( MC4WP_Integration $integration, $email, $merge_vars, $data_map = null, $related_object_id = 0 ) {

		// backwards compatibility for hook parameter order with MailChimp for WordPress v3.x
		if( $related_object_id === 0 && is_numeric( $data_map ) ) {
			$related_object_id = $data_map;
			$data_map = null;
		}

		$data = array(
			'type' => $integration->slug,
			'related_object_ID' => $related_object_id
        );

		// for BC with v3.x of MailChimp for Wordpress
		if( ! is_array( $data_map ) ) {
			$lists = $integration->get_lists();
			$list_id = array_shift( $lists );

			$data += array(
				'list_id' => $list_id,
				'email_address' => $email,
				'merge_fields' => $merge_vars,
			);

			return $this->log_request( $data );
		}

		// add each data map individually
		foreach( $data_map as $list_id => $subscriber_data ) {
			$data = array_merge( $data, array(
				'list_id' => $list_id,
				'email_address' => $subscriber_data->email_address,
				'merge_fields' => $subscriber_data->merge_fields,
				'interests' => $subscriber_data->interests,
				'vip' => $subscriber_data->vip,
				'ip_signup' => $subscriber_data->ip_signup,
				'status' => $subscriber_data->status,
				'language' => $subscriber_data->language,
			));

			$this->log_request( $data );
		}

		return true;
	}

	/**
	 * @param MC4WP_Form $form
	 * @param string $email_address
	 * @param array $merge_vars
	 * @param MC4WP_MailChimp_Subscriber[] $data_map
	 *
	 * @return false|int
	 */
	public function log_form_request( MC4WP_Form $form, $email_address, $merge_vars, $data_map = null ) {

		$data = array(
			'type' => 'mc4wp-form',
			'related_object_ID' => $form->ID,
		);

		// for BC with v3.x of MailChimp for WordPress
      $plain_data_map = array_values( $data_map );
		if( empty( $plain_data_map ) || ! $plain_data_map[0] instanceof MC4WP_MailChimp_Subscriber ) {
			$lists = $form->get_lists();
			$list_id = array_shift( $lists );

			$data += array(
				'list_id' => $list_id,
				'email_address' => $email_address,
				'merge_fields' => $merge_vars,
			);

			return $this->log_request( $data );
		}

		// add each data map individually
		foreach( $data_map as $list_id => $subscriber_data ) {
			$data = array_merge( $data, array(
				'list_id' => $list_id,
				'email_address' => $subscriber_data->email_address,
				'merge_fields' => $subscriber_data->merge_fields,
				'interests' => $subscriber_data->interests,
				'vip' => $subscriber_data->vip,
				'ip_signup' => $subscriber_data->ip_signup,
				'status' => $subscriber_data->status,
				'language' => $subscriber_data->language,
			) );

			$this->log_request( $data );
		}

		return true;
	}




	/**
	 * @param array $data
	 *
	 * @return false|int
	 */
	public function add( $data ) {

		$defaults = array(
			'datetime' => current_time( 'mysql', true ),
		);

		// because json_encode turns empty object into array, use NULL
		$data['merge_fields'] = empty( $data['merge_fields'] ) ? NULL : json_encode( $data['merge_fields'] );
		$data['interests'] = empty( $data['interests'] ) ? NULL : json_encode( $data['interests'] );

		$data = array_merge( $defaults, $data );

		return $this->db->insert( $this->table_name, $data );
	}

	/**
	 * @param array $args
	 * @return int
	 */
	public function count( $args = array() ) {
		$args['select'] = 'COUNT(*)';
		return $this->find( $args );
	}

	/**
	 * @param array $args
     *
	 * @return int|array
	 */
	public function find( $args ) {

		$args = wp_parse_args( $args, array(
			'select' => '*',
			'offset' => 0,
			'limit' => 1,
			'orderby' => 'id',
			'order' => 'DESC',

			// where params
			'type' => '',
			'email' => '',
			'datetime_after' => '',
			'datetime_before' => '',
			'include_errors' => true
		) );

		$where = array();
		$params = array();

		// build general select from query
		$query = sprintf( "SELECT %s FROM %s", $args['select'], $this->table_name );

		// add email to WHERE clause
		if ( '' !== $args['email'] ) {
			$where[] = 'email_address = %s';
			$params[] = $args['email'];
		}

		// add type to WHERE clause
		if ( '' !== $args['type'] ) {
			$where[] = 'type = %s';
			$params[] = $args['type'];
		}

		// add datetime to WHERE clause
		if( '' !== $args['datetime_after'] ) {
			$where[] = 'UNIX_TIMESTAMP(datetime) >= %d';
			$datetime_after = is_numeric( $args['datetime_after'] ) ? $args['datetime_after'] : strtotime( $args['datetime_after'] );
			$params[] = $datetime_after;
		}

		if( '' !== $args['datetime_before'] ) {
			$where[] = 'UNIX_TIMESTAMP(datetime) <= %d';
			$datetime_before = is_numeric( $args['datetime_before'] ) ? $args['datetime_before'] : strtotime( $args['datetime_before'] );
			$params[] = $datetime_before;
		}

		// add where parameters
		if ( count( $where ) > 0 ) {
			$query .= ' WHERE '. implode( ' AND ', $where );
		}

		// prepare parameters
		if( ! empty( $params ) ) {
			$query = $this->db->prepare( $query, $params );
		}

		// return result count
		if ( $args['select'] === 'COUNT(*)' ) {
			return (int) $this->db->get_var( $query );
		}

		// return single row
		if( $args['limit'] === 1 ) {
			$query .= ' LIMIT 1';
			return $this->db->get_row( $query );
		}

		// perform rest of query
		$args['limit']  = absint( $args['limit'] );
		$args['offset'] = absint( $args['offset'] );
		$args['orderby'] = preg_replace( "/[^a-zA-Z_]/", "", $args['orderby'] );
		$args['order'] = preg_replace( "/[^a-zA-Z]/", "", $args['order'] );

		// add ORDER BY, OFFSET and LIMIT to SQL
		$query .= sprintf( ' ORDER BY %s %s LIMIT %d, %d', $args['orderby'], $args['order'], $args['offset'], $args['limit'] );

		$results = $this->db->get_results( $query );
        return $this->hydrate( $results );
	}

	/**
	 * @param $ids string|array
	 *
	 * @return mixed
	 */
	public function find_by_id( $ids ) {

		if( is_array( $ids ) ) {
			// create comma-separated string
			$ids = implode( ',', $ids );
		}

		// escape string for usage in IN clause
		$ids = esc_sql( $ids );
		$sql = sprintf( "SELECT * FROM `%s` WHERE ID IN (%s)", $this->table_name, $ids );

		$results = $this->db->get_results( $sql );
        $objects = $this->hydrate( $results );

        // return single row if only one id is given
        if( substr_count( $ids, ',' ) === 0 ) {
            return array_shift( $objects );
        }

        return $objects;
	}

	/**
	 * @param $ids Array or string of log ID's to delete
	 *
	 * @return false|int
	 */
	public function delete_by_id( $ids) {

		if( is_array( $ids ) ) {
			// create comma-separated string
			$ids = implode( ',', $ids );
		}

		// escape string for usage in IN clause
		$ids = esc_sql( $ids );

		$sql = sprintf( "DELETE FROM `%s` WHERE ID IN (%s)", $this->table_name, $ids );
		return $this->db->query( $sql );
	}

	/**
	 * Deletes ALL log items. Use with caution.
	 */
	public function truncate() {
		$query =sprintf( "DELETE FROM `%s`", $this->table_name );
		$this->db->query( $query );
	}

    /**
     * @param $results
     *
     * @return MC4WP_Log_Item[]
     */
	protected function hydrate( $results ) {
        if( ! is_array( $results ) ) {
            return array();
        }

        $objects = array();
        foreach( $results as $raw_result ) {
            // create object and set properties
            $object = new MC4WP_Log_Item;
            foreach( $raw_result as $property => $value ) {

            	if( in_array( $property, array( 'success' ) ) ) {
            		continue;
            	}

            	if( in_array( $property, array( 'merge_fields', 'interests') ) && is_string( $value ) ) {
            		$value = json_decode( $value );
            	}

                $object->$property = $value;
            }

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * @param $data
     * @return false|int
     */
    private function log_request( $data ) {
        if( empty( $data['merge_fields'] ) ) {
            $data['merge_fields'] = array();
        }

        // copy over OPTIN_IP from merge_fields
        if( ! empty( $data['merge_fields']['OPTIN_IP'] ) ) {
            $data['ip_signup'] = $data['merge_fields']['OPTIN_IP'];
            unset( $data['merge_fields']['OPTIN_IP'] );
        }

        // make sure merge_fields has an EMAIL key
        if( empty( $data['merge_fields']['EMAIL'] ) ) {
            $data['merge_fields']['EMAIL'] = $data['email_address'];
        }

        if( empty( $data['url'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
         $data['url'] = $_SERVER['HTTP_REFERER'];
        }

        return $this->add( $data );
    }

    public function purge_old_items() {
      $options = get_option( 'mc4wp' );
      if( empty( $options['log_purge_days'] ) ) {
         return;
      }

      $days = (int) $options['log_purge_days'];
      $date_treshold = strtotime( "-{$days} days" );
      $query = "DELETE FROM {$this->table_name} WHERE UNIX_TIMESTAMP(datetime) < %d";
      $params = array( $date_treshold );
      $query = $this->db->prepare( $query, $params );
      $this->db->query( $query );
    }

    public function register_exporter( $exporters ) {
      $exporters['mc4wp-log'] = array(
         'exporter_friendly_name' => 'MailChimp for WordPress',
         'callback'               => array( $this, 'gdpr_export' ),
      );
      return $exporters;
    }

    public function gdpr_export( $email_address, $page = 1 ) {
      $log_items = $this->find( array( 'email' => $email_address, 'limit' => 10000 ) );
      $data_to_export = array();

      if( ! empty( $log_items ) ) {
         foreach( $log_items as $item ) {
            $data_to_export[] = array(
               'group_id'    => 'mc4wp_log_items',
               'group_label' => __( 'MailChimp sign-up requests', 'mailchimp-for-wp' ),
               'item_id'     => sprintf( 'mc4wp-log-item-%d', $item->ID ),
               'data'        => $this->gdpr_export_log_item( $item ),
            );
         }
      }
      return array(
         'data' => $data_to_export,
         'done' => true,
      );
    }

    public function gdpr_export_log_item( MC4WP_Log_Item $item ) {
      $data = array();

      $data[] = array(
            'name' => 'Date', 
            'value' => $item->datetime,
      );

      $data[] = array( 
         'name' => 'Email address',
         'value' => $item->email,
      );

      $data[] = array( 
         'name' => 'IP address',
         'value' => $item->ip_signup,
      );

      if( ! empty( $item->language ) ) {
         $data[] = array( 
            'name' => 'Language',
            'value' => $item->language,
         );
      }

      foreach( $item->merge_fields as $field => $value ) {
         if( $field === 'EMAIL' ) { 
            continue; 
         }

          $data[] = array( 
            'name' => $field,
            'value' => is_array( $value ) ? join( ', ', $value ) : $value,
         );
      }

      return $data;
    }

    public function register_eraser( $erasers ) {
      $erasers['mc4wp-log'] = array(
         'eraser_friendly_name' => 'MailChimp for WordPress',
         'callback'             => array( $this, 'gdpr_erase' ),
      );
      return $erasers;
    }

    public function gdpr_erase( $email_address, $page = 1 ) {
      $log_items = $this->find( array( 'email' => $email_address, 'limit' => 10000 ) );
      $items_removed = false;
      
      foreach( $log_items as $item ) {
         $this->delete_by_id( $item->ID );
         $items_removed = true;
      }

      return array(
            'items_removed'  => $items_removed,
            'items_retained' => false,
            'messages'       => array(),
            'done'           => true,
         );
    }
}
