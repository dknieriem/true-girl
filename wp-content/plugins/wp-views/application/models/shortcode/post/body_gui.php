<?php

/**
 * Class WPV_Shortcode_Post_Body_GUI
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Body_GUI extends WPV_Shortcode_Base_GUI {
	
	/**
	 * Register the wpv-post-body shortcode in the GUI API.
	 *
	 * @param $views_shortcodes
	 *
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function register_shortcode_data( $views_shortcodes ) {
		$views_shortcodes['wpv-post-body'] = array(
			'callback' => array( $this, 'get_shortcode_data' )
		);
		return $views_shortcodes;
	}
	
	/*
	 * Get the wpv-post-body shortcode attributes data.
	 *
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function get_shortcode_data() {
		$data = array(
			'name'           => __( 'Post body', 'wpv-views' ),
			'label'          => __( 'Post body', 'wpv-views' ),
			'post-selection' => true,
			'attributes' => array(
				'info'				=> array(
					'label'			=> __( 'Info', 'wpv-views' ),
					'header'		=> __( 'Information', 'wpv-views' ),
					'fields'		=> array(
						'information'	=> array(
							'type'		=> 'info',
							'content'	=> __( 'This field will display the <em>body</em> (main content) of the page.', 'wpv-views' )
						)
					),
				),
				'display-options' => array(
					'label' => __('Display options', 'wpv-views'),
					'header' => __('Display options', 'wpv-views'),
					'fields' => array(
						'view_template' => array(
							'label' => __( 'Content Template to apply', 'wpv-views'),
							'type' => 'radio',
							'options' => array(
								'None' => __( 'No Content Template (display the "body" of the post)', 'wpv-views'),
								'custom-combo' => $this->get_view_template_options(),
							),
							'description' => __( 'Select a Content Template to display its content, referred to the current post.', 'wpv-views' ),
							'default_force' => 'None',
						),
						'suppress_filters' => array(
							'label'		=> __( 'Third-party filters ', 'wpv-views'),
							'type'		=> 'radio',
							'options'	=> array(
								'true'	=> __( 'Suppress third party filters', 'wpv-views' ),
								'false'	=> __( 'Keep third party filters', 'wpv-views' ),
							),
							'default'	=> 'false',
							'description' => __( 'Avoid applying third-party filters into the output.', 'wpv-views' )
						),
						/*
						'output' => array(
							'label' => __( 'Output', 'wpv-views'),
							'type' => 'radio',
							'options' => array(
								'normal' => __('normal', 'wpv-views'),
								'raw' => __('raw', 'wpv-views'),
								'inherit' => __('inherit', 'wpv-views'),
							),
							'default' => 'normal',
						),
						*/
					),
				),
			)
		);
		return $data;
	}
	
	private function get_view_template_options() {
		global $wpdb, $sitepress;
		$custom_combo_settings = array(
			'label'    => __( 'Display using a Content Template:', 'wpv-views' ),
			'required' => true
		);
		
		$values_to_prepare = array();
		$wpml_join = $wpml_where = "";
		if (
			isset( $sitepress ) 
			&& function_exists( 'icl_object_id' )
		) {
			$content_templates_translatable = $sitepress->is_translated_post_type( 'view-template' );
			if ( $content_templates_translatable ) {
				$wpml_current_language = $sitepress->get_current_language();
				$wpml_join = " JOIN {$wpdb->prefix}icl_translations t ";
				$wpml_where = " AND p.ID = t.element_id AND t.language_code = %s AND t.element_type LIKE 'post_%' ";
				$values_to_prepare[] = $wpml_current_language;
			}
		}
		
		$exclude_loop_templates = '';
		$exclude_loop_templates_ids = wpv_get_loop_content_template_ids();
		// Be sure not to include the current CT when editing one
		if ( isset( $_REQUEST['wpv_suggest_wpv_post_body_view_template_exclude'] ) ) {
			$requested_ex_ids = $_REQUEST['wpv_suggest_wpv_post_body_view_template_exclude'];

			// Refactored to accept an array of excluded content template IDs
			if ( is_array( $requested_ex_ids ) ) {
				$exclude_loop_templates_ids = array_merge( $exclude_loop_templates_ids, $requested_ex_ids );
			} else {
				// @todo: Left for any backward compatibility
				$exclude_loop_templates_ids[] = $_REQUEST['wpv_suggest_wpv_post_body_view_template_exclude'];
			}
		}
		if (
			isset( $_GET['page'] )
			&& 'ct-editor' == $_GET['page'] 
			&& isset( $_GET['ct_id'] )
		) {
			$exclude_loop_templates_ids[] = $_GET['ct_id'];
		}
		if ( count( $exclude_loop_templates_ids ) > 0 ) {
			$exclude_loop_templates_ids_sanitized = array_map( 'esc_attr', $exclude_loop_templates_ids );
			$exclude_loop_templates_ids_sanitized = array_map( 'trim', $exclude_loop_templates_ids_sanitized );
			// is_numeric + intval does sanitization
			$exclude_loop_templates_ids_sanitized = array_filter( $exclude_loop_templates_ids_sanitized, 'is_numeric' );
			$exclude_loop_templates_ids_sanitized = array_map( 'intval', $exclude_loop_templates_ids_sanitized );
			if ( count( $exclude_loop_templates_ids_sanitized ) > 0 ) {
				$exclude_loop_templates = " AND p.ID NOT IN ('" . implode( "','" , $exclude_loop_templates_ids_sanitized ) . "') ";
			}
		}
		$values_to_prepare[] = 'view-template';
		$view_tempates_available = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_name, p.post_title 
				FROM {$wpdb->posts} p {$wpml_join} 
				WHERE p.post_status = 'publish' 
				{$wpml_where} 
				AND p.post_type = %s 
				{$exclude_loop_templates}
				ORDER BY p.post_title 
				LIMIT 16",
				$values_to_prepare
			)
		);
		if ( count( $view_tempates_available ) > 15 ) {
			$custom_combo_settings['type']        = 'suggest';
			$custom_combo_settings['action']      = 'wpv_suggest_wpv_post_body_view_template';
			$custom_combo_settings['placeholder'] = __( 'Start typing', 'wpv-views' );
		} else {
			$options = array(
				'' => __( 'Select one Content Template', 'wpv-views' )
			);
			foreach ( $view_tempates_available as $row ) {
				$options[ esc_js( $row->post_name ) ] = esc_html( $row->post_title );
			}
			$custom_combo_settings['type'] = 'select';
			$custom_combo_settings['options'] = $options;
			$custom_combo_settings['default'] = '';
		}
		return $custom_combo_settings;
	}
	
	
}