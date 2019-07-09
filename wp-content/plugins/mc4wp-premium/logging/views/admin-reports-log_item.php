<?php
defined( 'ABSPATH' ) or exit;

/** @var object $item */
/** @var MC4WP_MailChimp_List $list */
/** @var array $fields */
/** @var array $interests */
?>

<style type="text/css" scoped>

	table {
		font-size: 13px;
		border-collapse: collapse;
		border-spacing: 0;
		background: white;
		width: 100%;
		table-layout: fixed;
	}

	th, td {
		border: 1px solid #ddd;
		padding: 12px;
	}

	th {
		width: 160px;
		font-size: 14px;
		text-align: left;
	}

	pre{
		background: white;
		padding: 20px;
		border: 1px solid #ddd;
	}
</style>
<h2 style="margin-top: 0;"><span><?php esc_html_e( 'View log item', 'mailchimp-for-wp' ); ?></span></h2>

<table>
	<tr>
		<th><?php esc_html_e( 'Email Address', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo esc_html( $item->email_address ); ?></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'List', 'mailchimp-for-wp' ); ?></th>
		<td><?php printf( '<a href="%s">%s</a>', $list->get_web_url(), esc_html( $list->name ) ); ?></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Merge Fields', 'mailchimp-for-wp' ); ?></th>
		<td><?php
			foreach( (array) $item->merge_fields as $tag => $value ) {
				if( in_array( $tag, array( 'INTERESTS', 'GROUPINGS', 'OPTIN_IP' ) ) ) { continue; }

				// address fields and other array style fields
				$value = is_array( $value ) ? join( ', ', $value ) : $value;

				try {
					$field = $list->get_field_by_tag( $tag );
					printf( '<strong>%s</strong>: %s <br />', $field->name, esc_html( $value ) );
				} catch( Exception $e ) {
					printf( '<strong>%s</strong>: %s <br />', esc_html( $tag ), esc_html( $value ) );
				} catch( Error $e ) {
					printf( '<strong>%s</strong>: %s <br />', esc_html( $tag ), esc_html( $value ) );
				}
			}
			
			if( empty( $item->merge_fields ) ) {
				echo '&mdash;';
			}
			?>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Interests', 'mailchimp-for-wp' ); ?></th>
		<td><?php
			foreach( (array) $item->interests as $id => $value ) {

				// only show interests which were marked as "true"
				if( ! $value ) {
					continue;
				}

				$name = esc_html( $id );

				// first check if new method exists (since 4.0)
				if( method_exists( $list, 'get_interest_category_by_interest_id' ) ) {
					try {
						$category = $list->get_interest_category_by_interest_id( $id );
						$interest_name = $category->get_interest_name_by_id( $id );

						$name = sprintf( '<strong>%s</strong>: %s', $category->name, $interest_name );
					} catch( Exception $e ) {
						// do nothing special, just print ID
					}
				}

				printf( '%s <br />', $name );
			}
			
			// for BC with v3.x
			if( ! empty( $fields->GROUPINGS ) && method_exists( $list, 'get_grouping' ) ) {
				foreach( (array) $fields->GROUPINGS  as $value ) {
					$grouping = $list->get_grouping( $value->id );
					$name = sprintf( '<strong>%s</strong>: %s', $grouping->name, join( ', ', $value->groups ) );
					printf( '%s <br />', $name );
				}
			}

			if( empty( $fields->GROUPINGS ) && empty( $item->interests ) ) {
				echo '&mdash;';
			}
			?>
		</td>
	</tr>
	<?php if( strlen( $item->status ) ) { ?>
	<tr>
		<th><?php esc_html_e( 'Status', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo esc_html( $item->status ); ?></td>
	</tr>
	<?php } // end if status ?>
	<?php if( strlen( $item->ip_signup ) ) { ?>
	<tr>
		<th><?php esc_html_e( 'IP Address', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo esc_html( $item->ip_signup ); ?></td>
	</tr>
	<?php } // end if ip_signup ?>
	<?php if( strlen( $item->language ) ) { ?>
	<tr>
		<th><?php esc_html_e( 'Language', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo esc_html( $item->language ); ?></td>
	</tr>
	<?php } // end if language ?>
	<?php if( strlen( $item->vip ) ) { ?>
	<tr>
		<th><?php esc_html_e( 'VIP', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo esc_html( $item->vip ); ?></td>
	</tr>
	<?php } // end if vip ?>
	<tr>
		<th><?php esc_html_e( 'Source', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo make_clickable( strip_tags( $item->url ) ); ?></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Method', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo $item->type; // TODO: Use pretty name here. ?></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Datetime', 'mailchimp-for-wp' ); ?></th>
		<td><?php echo mc4wp_logging_gmt_date_format( $item->datetime ); ?></td>
	</tr>
</table>

<p>
	<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-reports&tab=log' ); ?>">&leftarrow; <?php esc_html_e( 'Back to log overview', 'mailchimp-for-wp' ); ?></a>
</p>

<?php

if( WP_DEBUG ) {
	echo '<h4>' . __( 'Raw', 'mailchimp-for-wp' ) . '</h4>';
	echo '<pre>';
    echo version_compare( PHP_VERSION, '5.4', '>=' ) ? json_encode( $item->to_json(), JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT ) : json_encode( $item->to_json() ); 
    echo '</pre>';
}

?>

