<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WP_List_Table', false ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class MC4WP_Forms_Table
 */
class MC4WP_Forms_Table extends WP_List_Table {

	/**
	 * @var MC4WP_MailChimp
	 */
	protected $mailchimp;

	/**
	 * @var bool
	 */
	public $is_trash = false;

	/**
	 * Constructor
	 *
	 * @param MC4WP_MailChimp $mailchimp
	 */
	public function __construct( MC4WP_MailChimp $mailchimp ) {
		parent::__construct(
			array(
				'singular' => 'form',
				'plural'   => 'forms',
				'ajax'     => false
			)
		);

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$hidden   = array();
		$this->mailchimp = $mailchimp;
		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash';

		$this->process_bulk_action();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $this->get_items();
		$this->set_pagination_args(
			array(
				'per_page' => 50,
				'total_items' => count( $this->items )
			)
		);
	}

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	public function get_views() {

		$counts = wp_count_posts( 'mc4wp-form' );
		$current = isset( $_GET['post_status'] ) ? $_GET['post_status'] : '';

		$count_any = $counts->publish + $counts->draft + $counts->future + $counts->pending;

		return array(
			'' => sprintf( '<a href="%s" class="%s">%s</a> (%d)', remove_query_arg( 'post_status' ), $current == '' ? 'current' : '', __( 'All' ), $count_any ),
			'trash' => sprintf( '<a href="%s" class="%s">%s</a> (%d)', add_query_arg( array( 'post_status' => 'trash' ) ), $current == 'trash' ? 'current' : '', __( 'Trash' ), $counts->trash ),
		);
	}

	/**
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array();

		if( $this->is_trash ) {
			$actions['untrash'] = __( 'Restore' );
			$actions['delete'] = __( 'Delete Permanently' );
			return $actions;
		}

		$actions['trash'] = __( 'Move to Trash' );
		$actions['duplicate'] = __( 'Duplicate' );
		return $actions;
	}

	public function get_default_primary_column_name() {
		return 'form_name';
	}

	/**
	 * @return array
	 */
	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'mc4wp-table' );
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'       => '<input type="checkbox" />',
			'form_name'    => __( 'Form', 'mailchimp-for-wp' ),
			'ID'            => __( 'ID', 'mailchimp-for-wp' ),
			'shortcode'     => __( 'Shortcode', 'mailchimp-for-wp' ),
			'lists'         => __( 'List(s)', 'mailchimp-for-wp' ),
		);
	}

	/**
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_items() {
		$args = array(
			'post_status' =>  array( 'publish', 'draft', 'pending', 'future' )
		);

		if( ! empty( $_GET['s'] ) ) {
			$args['s'] = sanitize_text_field( $_GET['s'] );
		}

		if( ! empty( $_GET['post_status' ] ) ) {
			$args['post_status'] = sanitize_text_field( $_GET['post_status'] );
		}


		$items = mc4wp_get_forms( $args );

		return $items;
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="forms[]" value="%s" />', $item->ID );
	}

	/**
	 * @param MC4WP_Form $form
	 *
	 * @return mixed
	 */
	public function column_ID( MC4WP_Form $form ) {
		return $form->ID;
	}

	/**
	 * @param MC4WP_Form $form
	 * @return string
	 */
	public function column_form_name( $form ) {

		if( $this->is_trash ) {
			return sprintf( '<strong>%s</strong>', esc_html( $form->name ) );
		}

		$edit_link = mc4wp_get_edit_form_url( $form->ID );
		$title      = '<strong><a class="row-title" href="' . $edit_link . '">' . esc_html( $form->name ) . '</a></strong>';

		$actions    = array(
			'edit'   => '<a href="' . $edit_link . '">' . __( 'Fields', 'mailchimp-for-wp' ) . '</a>',
			'settings' => '<a href="'. add_query_arg( array( 'tab' => 'settings' ), $edit_link ) .'">'. __( 'Settings', 'mailchimp-for-wp' ) . '</a>',
			'messages' => '<a href="'. add_query_arg( array( 'tab' => 'messages' ), $edit_link ) .'">'. __( 'Messages', 'mailchimp-for-wp' ) .'</a>',
			'appearance' => '<a href="'. add_query_arg( array( 'tab' => 'appearance' ), $edit_link ) .'">'. __( 'Appearance', 'mailchimp-for-wp' ) .'</a>'
		);

		return $title . $this->row_actions( $actions );
	}

	/**
	 * @param MC4WP_Form $form
	 *
	 * @return string
	 */
	public function column_shortcode( MC4WP_Form $form ) {
		return sprintf( '<input type="text" onfocus="this.select();" readonly="readonly" value="%s">', esc_attr( '[mc4wp_form id="' . $form->ID . '"]' ) );
	}

	/**
	 * @param MC4WP_Form $form
	 *
	 * @return string
	 */
	public function column_lists( MC4WP_Form $form ) {

		$list_ids = $form->settings['lists'];

		if( empty( $list_ids ) ) {
			$content = '<a href="'. mc4wp_get_edit_form_url( $form->ID, 'settings' ) .'" style="color: red;">' . __( 'No lists selected.', 'mailchimp-for-wp' ) . '</a>';
			return $content;
		}

		$names = array();

		foreach( $list_ids as $list_id ) {
			$list = $this->mailchimp->get_list( $list_id );
			if( $list instanceof MC4WP_MailChimp_List ) {
				$names[] = sprintf( '<a href="https://admin.mailchimp.com/lists/members/?id=%s" target="_blank">%s</a>', esc_attr( $list->web_id ), esc_html( $list->name ) );
			}
		}

		return join( '<br />', $names );
	}

	/**
	 * The text that is shown when there are no items to show
	 */
	public function no_items() {
		echo sprintf( __( 'No forms found. <a href="%s">Would you like to create one now</a>?', 'mailchimp-for-wp' ), mc4wp_get_add_form_url() );
	}

	/**
	 *
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		if( empty( $action ) ) {
			return false;
		}

		$method = 'process_bulk_action_' . $action;
		$forms = (array) $_REQUEST['forms'];
		if( method_exists( $this, $method ) ) {
			return call_user_func_array( array( $this, $method ), array( $forms ) );
		}

		return false;
	}

	public function process_bulk_action_duplicate( $forms ) {
		foreach( $forms as $form_id ) {
			$post = get_post( $form_id );
			$post_meta = get_post_meta( $form_id );

			$new_post_id = wp_insert_post(
				array(
					'post_title' => $post->post_title,
					'post_content' => $post->post_content,
					'post_type' => 'mc4wp-form',
					'post_status' => 'publish'
				)
			);
			foreach( $post_meta as $meta_key => $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value[0] );
				update_post_meta( $new_post_id, $meta_key, $meta_value );
			}
		}
	}

	public function process_bulk_action_trash( $forms ) {
		return array_map( 'wp_trash_post', $forms );
	}

	public function process_bulk_action_delete( $forms ) {
		return array_map( 'wp_delete_post', $forms );
	}

	public function process_bulk_action_untrash( $forms ) {
		return array_map( 'wp_untrash_post', $forms );
	}

}
