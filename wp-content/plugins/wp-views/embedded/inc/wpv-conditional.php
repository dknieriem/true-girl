<?php

/**
 * wpv-conditional.php
 *
 * Manages the wpv-conditional shortcode
 *
 * @since 1.10
 */

/**
 * AJAX action to fill wpv-conditional shortcode
 *
 * AJAX action to fill wpv-conditional shortcode modal window with all needed
 * content
 *
 * @since 1.9.0
 *
 */
add_action( 'wp_ajax_wpv_shortcode_gui_dialog_conditional_create', 'wp_ajax_wpv_shortcode_gui_dialog_conditional_create' );

/**
 * Content of wpv-conditional modal window.
 *
 * Content of wpv-conditional modal window used when we editing a entryn and
 * setup a wpv-conditional shortcode using Views Shortcode GUI API
 *
 * @since 1.9.0
 *
 * @param string $id Shortcode id
 * @param array $ shortcode data
 * @param array $ css classes
 * @param array $post_type post type
 *
 * @return string Content of modal window for wpv-shortcode.
 */
function wpv_shortcode_wpv_conditional_callback( $id, $data = array(), $classes = array(), $post_type = '' ) {
	global $WP_Views;
	$options = get_option( 'wpv_options' );
	$post_meta_keys = $WP_Views->get_meta_keys();
	$content = '';
	$fields = array(
		'types' => array(
			'label' => __( 'Types Fields', 'wpv-views' ),
		),
		'custom-fields' => array(
			'label' => __( 'Custom Fields', 'wpv-views' ),
		),
		'views-shortcodes' => array(
			'label' => __( 'Views Shortcodes', 'wpv-views' ),
		),
		'custom-shortcodes' => array(
			'label' => __( 'Custom Shortcodes', 'wpv-views' ),
		),
		'custom-functions' => array(
			'label' => __( 'Custom Functions', 'wpv-views' ),
		),
	);
	foreach( array_keys( $fields ) as $key ) {
		$fields[ $key ]['fields'] = array();
		$fields[ $key ]['slug'] = $key;
	}

	if( !empty( $post_meta_keys ) ) {
		foreach( $post_meta_keys as $key ) {
			if( empty( $key ) ) {
				continue;
			}
			if( wpv_is_types_custom_field( $key ) ) {
				$fields['types']['fields'][ $key ] = array(
					'label' => wpv_types_get_field_name( $key ),
					'slug' => sprintf( '$(%s)', $key ),
					'type' => 'text',
				);

			} else {
				$fields['custom-fields']['fields'][ $key ] = array(
					'label' => $key,
					'slug' => sprintf( '$(%s)', $key ),
					'type' => 'text',
				);
			}
		}

		//Post types
		$parent_post_types = array();
		global $wpcf;
		$post_types_all = get_post_types( array( 'show_ui' => true ), 'objects' );
		foreach ( $post_types_all as $post_type ) {
            if ( in_array( $post_type->name, $wpcf->excluded_post_types ) ) {
                continue;
            }
            $parent_post_types[ $post_type->name ] = sprintf( __( 'from parent %s', 'wpv-views' ), $post_type->labels->singular_name );
        }
        $fields['types']['parents'] = $parent_post_types;
	}

	/**
	 * Views Shortcodes
	 *
	 * @todo this shoults REFACTOR!!!!!
	 */

	global $shortcode_tags;
	if( is_array( $shortcode_tags ) ) {
		foreach( array_keys( $shortcode_tags ) as $key ) {
			$views_shortcodes_regex = wpv_inner_shortcodes_list_regex();
			$include_expression = "/(" . $views_shortcodes_regex . ").*?/i";

			$check_shortcode = preg_match_all( $include_expression, $key, $inner_matches );
			if( $check_shortcode == 0 ) {
				continue;
			}
			/**
			 * do not add non-Views shortcodes
			 * disallow wpv-post-body
			 */
			if( !preg_match( '/^wpv/', $key ) ) {
				continue;
			}
			if( $key == 'wpv-post-body' ) {
				continue;
			}
			/**
			 * add shortode to list
			 */
			$fields['views-shortcodes']['fields'][ $key ] = array(
				'label' => $key,
				'slug' => sprintf( '\'[%s]\'', $key ),
				'type' => 'text',
			);
		}
		ksort( $fields['views-shortcodes']['fields'] );
	}

	/**
	 * Custom Functions
	 */
	if( isset( $options['wpv_custom_conditional_functions'] ) && !empty( $options['wpv_custom_conditional_functions'] ) ) {
		foreach( $options['wpv_custom_conditional_functions'] as $key ) {
			if( empty( $key ) ) {
				continue;
			}
			$fields['custom-functions']['fields'][ $key ] = array(
				'label' => $key,
				'slug' => sprintf( '%s()', $key ),
				'type' => 'text',
			);
		}
	}

	/**
	 * Custom Shortcodes
	 */
	if( isset( $options['wpv_custom_inner_shortcodes'] ) && !empty( $options['wpv_custom_inner_shortcodes'] ) ) {

		foreach( $options['wpv_custom_inner_shortcodes'] as $key ) {
			if( empty( $key ) ) {
				continue;
			}
			$fields['custom-shortcodes']['fields'][ $key ] = array(
				'label' => $key,
				'slug' => sprintf( '\'[%s]\'', $key ),
				'type' => 'text',
			);
		}
	}

	/**
	 * fields json
	 */
	$fields = array(
		'labels' => array(
			'select_choose' => esc_html( __( '-- Select data type --', 'wpv-views' ) ),
			'select_field' => esc_html( __( '-- Select field --', 'wpv-views' ) ),
			'button_delete' => esc_html( __( 'Delete', 'wpv-views' ) ),
			'select_parent' => esc_html( __( '-- from a related post:', 'wpv-views' ) ),
			'no_parent' => esc_html( __( 'from current post', 'wpv-views' ) ),
		),
		'fields' => $fields,
	);

	foreach( $fields['fields'] as $key => $data ) {
		if( empty( $data ) ) {
			unset( $fields['fields'][ $key ] );
		}
	}

	$content .= '<script type="text/javascript">';
	$content .= 'var WPViews = WPViews || {};';
	$content .= sprintf( 'WPViews.wpv_conditional_data = %s;', json_encode( $fields ) );
	$content .= '</script>';
	$content .= '<span class="js-wpv-shortcode-conditional-gui-content"></span>';

	$content .= '<div class="js-wpv-conditionals-set-with-gui">';
	$content .= '<table id="js-wpv-conditionals" class="wpv-conditionals" data-field-name="wpv-conditional"><thead><tr>';
	$content .= '<th style="width:100%">' . esc_html__( 'Data origin', 'wpv-views' ) . '</th>';
	$content .= '<th style="width:100%">' . esc_html__( 'Comparison', 'wpv-views' ) . '</th>';
	$content .= '<th style="width:100%">' . esc_html__( 'Value', 'wpv-views' ) . '</th>';
	$content .= '<th style="width:100%">' . esc_html__( 'Relationship', 'wpv-views' ) . '</th>';
	$content .= '<th style="width:50px;">&nbsp</th></tr></thead>';

	$content .= '<tbody class="js-wpv-views-conditional-body"></tbody>';
	$content .= '</table>';
	$content .= '</div>';

	$content .= '<div class="js-wpv-conditionals-set-manual" style="display:none">';
	$content .= '<textarea id="wpv-conditional-custom-expressions" class="js-shortcode-gui-field large-text" data-placeholder="" placeholder="" data-type="textarea"></textarea>';
	$content .= '</div>';

	$content .= '<p style="overflow:hidden;">';
	$content .= sprintf(
		'<a href="#" class="js-wpv-shortcode-expression-switcher">%s</a>',
		esc_html( __( 'Edit conditions manually', 'wpv-views' ) )
	);
	$content .= sprintf(
		'<button class="button js-wpv-views-conditional-add-term" style="float:right">%s</button>',
		esc_html( __( 'Add another condition', 'wpv-views' ) )
	);
	$content .= '</p>';

	return $content;
}

/**
 * wp_ajax_wpv_shortcode_gui_dialog_conditional_create
 *
 * Render dialog_conditional for shortcodes attributes
 *
 * @since 1.9.0
 */
function wp_ajax_wpv_shortcode_gui_dialog_conditional_create() {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpv_editor_callback' ) ) {
		$data = array(
			'message' => __( 'Security verification failed, please reload the page and try again', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}
	/**
	 * If post_id was passed, get the current post type object
	 */
	$post_id = 0;
	if( isset( $_GET['post_id'] ) ) {
		$post_id = intval( $_GET['post_id'] );
	}
	$post_type = array();
	if( $post_id ) {
		$post_type = get_post_type_object( get_post_type( $post_id ) );
	}
	$shortcode = 'wpv-conditional';
	$options = array(
		'post-selection' => true,
		'attributes' => array(
			'conditions' => array(
				'label' => __( 'Conditions', 'wpv-views' ),
				'header' => __( 'Conditional output', 'wpv-views' ),
				'fields' => array(
					'if' => array(
						'label' => __( 'Conditions to evaluate', 'wpv-views' ),
						'type' => 'callback',
						'callback' => 'wpv_shortcode_wpv_conditional_callback',
					),
					'evaluate' => array(
						'label' => __( 'Conditions evaluation', 'wpv-views' ),
						'type' => 'radio',
						'options' => array(
							'true' => __( 'The evaluation result should be TRUE', 'wpv-views' ),
							'false' => __( 'The evaluation result should be FALSE', 'wpv-views' ),
						),
						'description' => __( 'Whether the condition should be compared to TRUE or to FALSE.', 'wpv-views' ),
						'default' => 'true',
					),
				),
			),
		),
	);

	if( current_user_can( 'manage_options' ) ) {
		$options['attributes']['extra-debug'] = array(
			'label' => __( 'Debug', 'wpv-views' ),
			'header' => __( 'Conditional debug', 'wpv-views' ),
			'fields' => array(
				'debug' => array(
					'label' => __( 'Show debug', 'wpv-views' ),
					'type' => 'radio',
					'options' => array(
						'true' => __( 'Show debug information to administrators', 'wpv-views' ),
						'false' => __( 'Don\'t show any debug information', 'wpv-views' ),
					),
					'description' => __( 'Show additional information to administrators about the evaluation process.', 'wpv-views' ),
					'default' => 'false',
				),
			)
		);
	}
	
	ob_start();

	printf(
		'<div class="wpv-dialog js-insert-%s-dialog">',
		esc_attr( $shortcode )
	);
	echo '<input type="hidden" value="' . esc_attr( $shortcode ) . '" class="wpv-views-conditional-shortcode-gui-dialog-name js-wpv-views-conditional-shortcode-gui-dialog-name" />';
	echo '<div id="js-wpv-shortcode-gui-dialog-tabs" class="wpv-shortcode-gui-tabs js-wpv-conditional-shortcode-gui-tabs">';
	$tabs = '';
	$content = '';
	foreach( $options['attributes'] as $group_id => $group_data ) {
		$tabs .= sprintf(
			'<li><a href="#%s-%s">%s</a></li>',
			esc_attr( $shortcode ),
			esc_attr( $group_id ),
			esc_html( $group_data['label'] )
		);
		$content .= sprintf(
			'<div id="%s-%s" style="position:relative">',
			esc_attr( $shortcode ),
			esc_attr( $group_id )
		);
		if( isset( $group_data['header'] ) ) {
			$content .= sprintf(
				'<h2>%s</h2>',
				esc_html( $group_data['header'] )
			);
		}
		/**
		 * add fields
		 */
		foreach( $group_data['fields'] as $key => $data ) {
			if( !isset( $data['type'] ) ) {
				continue;
			}
			$id = sprintf(
				'%s-%s',
				$shortcode,
				$key
			);
			$content .= sprintf(
				'<div class="wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper js-wpv-shortcode-gui-attribute-wrapper-for-%s" data-type="%s" data-attribute="%s" data-default="%s">',
				esc_attr( $key ),
				esc_attr( $data['type'] ),
				esc_attr( $key ),
				isset( $data['default'] ) ? esc_attr( $data['default'] ) : ''
			);
			$attr_value = isset( $data['default'] ) ? $data['default'] : '';
			$attr_value = isset( $data['default_force'] ) ? $data['default_force'] : $attr_value;

			$classes = array( 'js-shortcode-gui-field' );
			$required = '';
			if(
				isset( $data['required'] )
				&& $data['required']
			) {
				$classes[] = 'js-wpv-shortcode-gui-required';
				$required = ' <span>- ' . esc_html( __( 'required', 'wpv-views' ) ) . '</span>';
			}
			if( isset( $data['label'] ) ) {
				$content .= sprintf(
					'<h3>%s%s</h3>',
					esc_html( $data['label'] ),
					$required
				);
			}
			/**
			 * require
			 */
			if( isset( $data['required'] ) && $data['required'] ) {
				$classes[] = 'js-required';
			}
			/**
			 * Filter of options
			 *
			 * This filter allow to manipulate of radio/select field options.
			 * Filter is 'wpv_filter_wpv_shortcodes_gui_api_{shortode}_options'
			 *
			 * @param array $options for description see param $options in
			 * wpv_filter_wpv_shortcodes_gui_api filter.
			 *
			 * @param string $type field type
			 *
			 */
			if( isset( $data['options'] ) ) {
				$data['options'] = apply_filters( 'wpv_filter_wpv_shortcodes_gui_api_' . $id . '_options', $data['options'], $data['type'] );
			}

			$content .= wpv_shortcode_gui_dialog_render_attribute( $id, $data, $classes, $post_type );

			$desc_and_doc = array();
			if( isset( $data['description'] ) ) {
				$desc_and_doc[] = esc_html( $data['description'] );
			}
			if( isset( $data['documentation'] ) ) {
				$desc_and_doc[] = sprintf(
					__( 'Specific documentation: %s', 'wpv-views' ),
					$data['documentation']
				);
			}
			if( !empty( $desc_and_doc ) ) {
				$content .= '<p class="description">' . implode( '<br />', $desc_and_doc ) . '</p>';
			}
			$content .= '</div>';
		}
		/*
        if ( isset( $group_data['content'] ) ) {
            if ( isset( $group_data['content']['hidden'] ) ) {
                $content .= '<span class="wpv-shortcode-gui-content-wrapper js-wpv-shortcode-gui-content-wrapper" style="display:none">';
                $content .= sprintf(
                    '<input id="shortcode-gui-content-%s" type="text" class="large-text js-wpv-shortcode-gui-content" />',
                    esc_attr( $shortcode )
                );
                $content .= '</span>';
            } else {
                $content .= '<div class="wpv-shortcode-gui-content-wrapper js-wpv-shortcode-gui-content-wrapper">';
                $content .= sprintf(
                    '<h3>%s</h3>',
                    esc_html( $group_data['content']['label'] )
                );
                $content .= sprintf(
                    '<input id="shortcode-gui-content-%s" type="text" class="large-text js-wpv-shortcode-gui-content" />',
                    esc_attr( $shortcode )
                );
                $desc_and_doc = array();
                if ( isset( $group_data['content']['description'] ) ) {
                    $desc_and_doc[] = $group_data['content']['description'];
                }
                if ( isset( $group_data['content']['documentation'] ) ) {
                    $desc_and_doc[] = sprintf(
                        __( 'Specific documentation: %s', 'wpv-views' ),
                        $group_data['content']['documentation']
                    );
                }
                if ( ! empty( $desc_and_doc ) ) {
                    $content .= '<p class="description">' . implode( '<br />', $desc_and_doc ) . '</p>';
                }
                $content .= '</div>';
            }
        }
		*/
		$content .= '</div>';
	}
	if( current_user_can( 'manage_options' ) ) {
		global $WP_Views;
		if( !$WP_Views->is_embedded() ) {
			$options = get_option( 'wpv_options' );
			$tabs .= sprintf(
				'<li><a href="#%s-%s">%s</a></li>',
				esc_attr( $shortcode ),
				'extra-settings',
				esc_html( __( 'Settings', 'wpv-views' ) )
			);
			$content .= sprintf(
				'<div id="%s-%s" style="position:relative">',
				esc_attr( $shortcode ),
				'extra-settings'
			);
			$content .= '<h2>' . __( 'Additional settings', 'wpv-views' ) . '</h2>';

			$content .= '<div class="wpv-shortcode-gui-attribute-wrapper">';
			$content .= '<h3>' . __( 'Third-party shortcodes', 'wpv-views' ) . '</h3>';
			$content .= '<p>' . __( 'The following shortcodes can be used inside the conditions. Use the form below to register new shortcodes.', 'wpv-views' ) . '</p>';
			$content .= '<div class="js-wpv-add-item-settings-wrapper">';
			$content .= '<ul class="wpv-taglike-list js-wpv-add-item-settings-list js-wpv-custom-shortcode-list">';
			if( isset( $options['wpv_custom_inner_shortcodes'] ) && $options['wpv_custom_inner_shortcodes'] != '' ) {
				$custom_shrt = $options['wpv_custom_inner_shortcodes'];
			} else {
				$custom_shrt = array();
			}
			if( !is_array( $custom_shrt ) ) {
				$custom_shrt = array();
			}
			if( count( $custom_shrt ) > 0 ) {
				sort( $custom_shrt );
				foreach( $custom_shrt as $custom_shrtcode ) {
					$content .= '<li class="js-' . esc_attr( $custom_shrtcode ) . '-item">';
					$content .= '<span class="">[' . $custom_shrtcode . ']</span>';
					$content .= '</li>';
				}
			}
			$content .= '</ul>';
			$content .= '<form class="js-wpv-add-item-settings-form js-wpv-custom-inner-shortcodes-form-add">';
			$content .= '<input type="text" placeholder="' . esc_attr( __( 'Shortcode name', 'wpv-views' ) ) . '" class="js-wpv-add-item-settings-form-newname js-wpv-custom-inner-shortcode-newname" autocomplete="off" />';
			$content .= '<button class="button button-secondary js-wpv-add-item-settings-form-button js-wpv-custom-inner-shortcodes-add" type="button" disabled="disabled"><i class="icon-plus fa fa-plus"></i> ' . __( 'Add', 'wpv-views' ) . '</button>';
			$content .= '<span class="toolset-alert toolset-alert-error hidden js-wpv-cs-error js-wpv-permanent-alert-error">' . __( 'Only letters, numbers, underscores and dashes', 'wpv-views' ) . '</span>';
			$content .= '<span class="toolset-alert toolset-alert-info hidden js-wpv-cs-dup js-wpv-permanent-alert-error">' . __( 'That shortcode already exists', 'wpv-views' ) . '</span>';
			$content .= '<span class="toolset-alert toolset-alert-info hidden js-wpv-cs-ajaxfail js-wpv-permanent-alert-error">' . __( 'An error ocurred', 'wpv-views' ) . '</span>';
			$content .= '</form>';
			$content .= '</div>';
			$content .= '</div>';

			$content .= '<div class="wpv-shortcode-gui-attribute-wrapper">';
			$content .= '<h3>' . __( 'Registered functions', 'wpv-views' ) . '</h3>';

			$content .= '<p>' . __( 'The following functions can be used inside the conditions. Use the form below to register new functions.', 'wpv-views' ) . '</p>';
			$content .= '<div class="js-wpv-add-item-settings-wrapper">';
			$content .= '<ul class="wpv-taglike-list js-wpv-add-item-settings-list js-custom-functions-list">';
			if( isset( $options['wpv_custom_conditional_functions'] ) && $options['wpv_custom_conditional_functions'] != '' ) {
				$custom_func = $options['wpv_custom_conditional_functions'];
			} else {
				$custom_func = array();
			}
			if( !is_array( $custom_func ) ) {
				$custom_func = array();
			}
			if( count( $custom_func ) > 0 ) {
				sort( $custom_func );
				foreach( $custom_func as $custom_function ) {
					$content .= '<li class="js-' . esc_attr( str_replace( '::', '-_paamayim_-', $custom_function ) ) . '-item">';
					$content .= '<span class="">' . $custom_function . '</span>';
					$content .= '</li>';
				}
			}
			$content .= '</ul>';
			$content .= '<form class="js-wpv-add-item-settings-form js-wpv-custom-conditional-functions-form-add">';
			$content .= '<input type="text" placeholder="' . esc_attr( __( 'Function name', 'wpv-views' ) ) . '" class="js-wpv-add-item-settings-form-newname js-wpv-custom-conditional-functions-newname" autocomplete="off" />';
			$content .= '<button class="button button-secondary js-wpv-add-item-settings-form-button js-wpv-custom-conditional-functions-add" type="button" disabled="disabled"><i class="icon-plus fa fa-plus"></i> ' . __( 'Add', 'wpv-views' ) . '</button>';
			$content .= '<span class="toolset-alert toolset-alert-error hidden js-wpv-cs-error js-wpv-permanent-alert-error">' . __( 'Only letters, numbers, underscores and dashes', 'wpv-views' ) . '</span>';
			$content .= '<span class="toolset-alert toolset-alert-info hidden js-wpv-cs-dup js-wpv-permanent-alert-error">' . __( 'That shortcode already exists', 'wpv-views' ) . '</span>';
			$content .= '<span class="toolset-alert toolset-alert-info hidden js-wpv-cs-ajaxfail js-wpv-permanent-alert-error">' . __( 'An error ocurred', 'wpv-views' ) . '</span>';
			$content .= '</form>';
			$content .= '</div>';
			$content .= '</div>';

			$content .= '<p class="description" style="margin-top:15px;padding-top:10px;border-top:solid 1px #dedede;">'
				. sprintf(
					__( 'If you need to remove any of those registered items, please go to the <a href="%s" target="_blank">Views Settings page</a>.', 'wpv-views' ),
					admin_url( 'admin.php?page=toolset-settings&tab=front-end-content' )
				)
				. '</p>';
			$content .= wp_nonce_field( 'wpv_custom_conditional_extra_settings', 'wpv_custom_conditional_extra_settings', true, false );

			$content .= '</div>';
		}
	}
	$content .= '</div>';
	printf(
		'<ul>%s</ul>',
		$tabs
	);
	echo $content;
	echo '</div>';
	
	$dialog = ob_get_clean();
	$data = array(
		'dialog' => $dialog
	);
	wp_send_json_success( $data );
}

function wpv_preprocess_wpv_conditional_shortcodes( $content ) {
	global $shortcode_tags, $WPV_Views_Conditional;

	// Back up current registered shortcodes and clear them all out
	$orig_shortcode_tags = $shortcode_tags;
	remove_all_shortcodes();

	add_shortcode( 'wpv-conditional', array( $WPV_Views_Conditional, 'wpv_shortcode_wpv_conditional' ) );

	$expression = '/\\[wpv-conditional((?!\\[wpv-conditional).)*\\[\\/wpv-conditional\\]/isU';
	$counts = preg_match_all( $expression, $content, $matches );

	while( $counts ) {
		foreach( $matches[0] as $match ) {

			// this will only processes the [wpv-if] shortcode
			$pattern = get_shortcode_regex();
			$match_corrected = $match;
			if( 0 !== preg_match( "/$pattern/s", $match, $match_data ) ) {
				// Base64 Encode the inside part of the expression so the WP can't strip out any data it doesn't like.
				// Be sure to prevent base64_encoding more than just the needed: only do it if there are inner shortcodes
				if( strpos( $match_data[5], '[' ) !== false ) {
					$match_corrected = str_replace( $match_data[5], 'wpv-b64-' . base64_encode( $match_data[5] ), $match_corrected );
				}

				$match_attributes = wpv_shortcode_parse_condition_atts( $match_data[3] );
				if( isset( $match_attributes['if'] ) ) {
					$match_evaluate_corrected = str_replace( '<=', 'lte', $match_attributes['if'] );
					$match_evaluate_corrected = str_replace( '<>', 'ne', $match_evaluate_corrected );
					$match_evaluate_corrected = str_replace( '<', 'lt', $match_evaluate_corrected );
					$match_corrected = str_replace( $match_attributes['if'], $match_evaluate_corrected, $match_corrected );
				}

			}

			$shortcode = do_shortcode( $match_corrected );
			$content = str_replace( $match, $shortcode, $content );

		}

		$counts = preg_match_all( $expression, $content, $matches );
	}

	// Put the original shortcodes back
	$shortcode_tags = $orig_shortcode_tags;

	return $content;
}

class WPV_Views_Conditional {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}


	public function init() {
		add_shortcode( 'wpv-conditional', array( $this, 'wpv_shortcode_wpv_conditional' ) );
		
		$toolset_common_bootstrap = Toolset_Common_Bootstrap::getInstance();
		$toolset_common_sections = array(
			'toolset_parser',
            'toolset_forms'
		);
		$toolset_common_bootstrap->load_sections( $toolset_common_sections );
		
		add_filter( "mce_external_plugins", array( $this, "wpv_add_views_conditional_button_scripts" ) );
		add_filter( "mce_buttons", array( $this, "register_buttons_editor" ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'add_quicktags' ), 99 );
		add_action( 'wp_print_footer_scripts', array( $this, 'add_quicktags' ), 99 );
		
	}


	public function admin_init() {
		
	}


	public function wpv_shortcode_wpv_conditional( $attr, $content = '' ) {
		global $post;
		$has_post = true;
		$id = '';
		if( empty( $post->ID ) ) {
			// Will not execute any condition that involves custom fields
			$has_post = false;
		} else {
			$id = $post->ID;
		}

		if(
			empty( $attr['if'] )
			|| (
				empty( $content )
				&& $content !== '0'
			)
		) {
			return ''; // ignore
		}

		extract(
			shortcode_atts(
				array(
					'evaluate' => 'true',
					'debug' => false,
					'if' => true
				),
				$attr
			)
		);


		$out = '';
		$evaluate = ( $evaluate == 'true' || $evaluate === true ) ? true : false;
		$debug = ( $debug == 'true' || $debug === true ) ? true : false;

		$attr['if'] = str_replace( " NEQ ", " ne ", $attr['if'] );
		$attr['if'] = str_replace( " neq ", " ne ", $attr['if'] );
		$attr['if'] = str_replace( " EQ ", " = ", $attr['if'] );
		$attr['if'] = str_replace( " eq ", " = ", $attr['if'] );
		$attr['if'] = str_replace( " NE ", " ne ", $attr['if'] );
		$attr['if'] = str_replace( " != ", " ne ", $attr['if'] );

		$attr['if'] = str_replace( " LT ", " < ", $attr['if'] );
		$attr['if'] = str_replace( " lt ", " < ", $attr['if'] );
		$attr['if'] = str_replace( " LTE ", " <= ", $attr['if'] );
		$attr['if'] = str_replace( " lte ", " <= ", $attr['if'] );
		$attr['if'] = str_replace( " GT ", " > ", $attr['if'] );
		$attr['if'] = str_replace( " gt ", " > ", $attr['if'] );
		$attr['if'] = str_replace( " GTE ", " >= ", $attr['if'] );
		$attr['if'] = str_replace( " gte ", " >= ", $attr['if'] );

		if( strpos( $content, 'wpv-b64-' ) === 0 ) {
			$content = substr( $content, 7 );
			$content = base64_decode( $content );
		}

		$evaluation_result = $this->parse_conditional( $post, $attr['if'], $debug, $attr, $id, $has_post );

		if(
			(
				$evaluate
				&& $evaluation_result['passed']
			) || (
				!$evaluate
				&& !$evaluation_result['passed']
			)
		) {
			$out = $content;
		}

		if(
			$debug
			&& current_user_can( 'manage_options' )
		) {
			$out .= '<pre>' . $evaluation_result['debug'] . '</pre>';
		}

		apply_filters( 'wpv_shortcode_debug', 'wpv-conditional', json_encode( $attr ), '', 'Data received from cache', $out );

		return $out;
	}


	function wpv_add_views_conditional_button_scripts( $plugin_array ) {
		if(
			wp_script_is( 'quicktags' )
			&& wp_script_is( 'views-shortcodes-gui-script' )
		) {
			//enqueue TinyMCE plugin script with its ID.
			$plugin_array["wpv_add_views_conditional_button"] = WPV_URL_EMBEDDED . '/res/js/views_conditional_button_plugin.js';
		}

		return $plugin_array;
	}


	function register_buttons_editor( $buttons ) {
		if(
			wp_script_is( 'quicktags' )
			&& wp_script_is( 'views-shortcodes-gui-script' )
		) {
			//register buttons with their id.
			array_push( $buttons, "wpv_conditional_output" );
		}

		return $buttons;
	}


	public function add_quicktags() {
		if(
			wp_script_is( 'quicktags' )
			&& wp_script_is( 'views-shortcodes-gui-script' )
		) {
			?>
			<script type="text/javascript">
				QTags.addButton('wpv_conditional', '<?php echo esc_js( __( 'conditional output', 'wpv-views' ) ); ?>', wpv_add_conditional_quicktag_function, '', 'c', '<?php echo esc_js( __( 'Views conditional output', 'wpv-views' ) ); ?>', 121, '', {
					ariaLabel: '<?php echo esc_js( __( 'Views conditional output', 'wpv-views' ) ); ?>',
					ariaLabelClose: '<?php echo esc_js( __( 'Close Views conditional output', 'wpv-views' ) ); ?>'
				});
			</script>
			<?php
		}
	}


	public static function parse_conditional( $post, $condition, $debug = false, $attr, $id, $has_post ) {

		$logging_string = "####################\nwpv-conditional attributes\n####################\n"
			. print_r( $attr, true )
			. "\n####################\nDebug information\n####################"
			. "\n--------------------\nOriginal expression: "
			. $condition
			. "\n--------------------";

		/* resolve parent */
		if ( strpos( $condition, ".id(" ) !== false ) {
			preg_match_all( "/[$]\(([^\)]+)\)\.id\(([^\)]+)\)/Uim", $condition, $matches );
			if ( count( $matches[0] ) > 0 ){
				for( $i = 0; $i < count( $matches[0] ); $i++ ){
					$parent_name = '$' . str_replace( array( '"', "'" ), '', $matches[2][ $i ] );
					$field_name = str_replace( array( '"', "'" ), '', $matches[1][ $i ] );

					//Change existing $post to $parent
					$post_id_atts = new WPV_wpcf_switch_post_from_attr_id( array( 'id' => $parent_name ) );
					global $post;
					if ( isset( $post->ID ) ){
						$post_id = $post->ID;
					}else{
						continue;
					}
					//Restore original $post
					$post_id_atts = null;

					$temp_condition = $matches[0][ $i ];
					$data = WPToolset_Types::getCustomConditional( $temp_condition, '', WPToolset_Types::getConditionalValues( $post_id ) );
					if ( isset( $data['values'][ $field_name ] ) ) {
						if ( is_string( $data['values'][ $field_name ] ) ){
							$data['values'][ $field_name ] = "'" . $data['values'][ $field_name ] . "'";
						}
						$condition = str_replace( $temp_condition, $data['values'][ $field_name ], $condition );
					} else {
						$condition = str_replace( $temp_condition, "''", $condition );
					}
				}
			}
		}

		/* Resolve parent*/

		$data = WPToolset_Types::getCustomConditional( $condition, '', WPToolset_Types::getConditionalValues( $id ) );

		$evaluate = $data['custom'];
		$values = $data['values'];

		if ( strpos( $evaluate, "REGEX" ) === false ) {
			$evaluate = trim( stripslashes( $evaluate ) );
			// Check dates
			$evaluate = wpv_filter_parse_date( $evaluate );
			$evaluate = self::handle_user_function( $evaluate );
		}

		$fields = self::extractFields( $evaluate );

		$evaluate = apply_filters( 'wpv-extra-condition-filters', $evaluate );
		$temp = self::extractVariables( $evaluate, $attr, $has_post, $id );
		$evaluate = $temp[0];
		$logging_string .= $temp[1];
		if (
			empty( $fields ) 
			&& empty( $values )
		) {
			$passed = self::evaluateCustom( $evaluate );
		} else {
			$evaluate = self::_update_values_in_expression( $evaluate, $fields, $values, $id );
			$logging_string .= "\n--------------------\nConverted expression: "
				. $evaluate
				. "\n--------------------";
			$passed = self::evaluateCustom( $evaluate );
		}
		
		return array( 'debug' => $logging_string, 'passed' => $passed );

	}


	public static function extractVariables( $evaluate, $atts, $has_post, $id ) {
		$logging_string = '';
		// Evaluate quoted variables that are to be used as strings
		// '$f1' will replace $f1 with the custom field value

		$strings_count = preg_match_all( '/(\'[\$\w^\']*\')/', $evaluate, $matches );
		if(
			$strings_count
			&& $strings_count > 0
		) {
			for( $i = 0; $i < $strings_count; $i++ ) {
				$string = $matches[1][ $i ];
				// remove single quotes from string literals to get value only
				$string = ( strpos( $string, '\'' ) === 0 ) ? substr( $string, 1, strlen( $string ) - 2 ) : $string;
				if( strpos( $string, '$' ) === 0 ) {
					$quoted_variables_logging_extra = '';
					$variable_name = substr( $string, 1 ); // omit dollar sign
					if( isset( $atts[ $variable_name ] ) ) {
						$string = get_post_meta( $id, $atts[ $variable_name ], true );
						$evaluate = str_replace( $matches[1][ $i ], "'" . $string . "'", $evaluate );
					} else {
						$evaluate = str_replace( $matches[1][ $i ], "", $evaluate );
						$quoted_variables_logging_extra = "\n\tERROR: Key " . $matches[1][ $i ] . " does not point to a valid attribute in the wpv-if shortcode: expect parsing errors";
					}
					$logging_string .= "\nAfter replacing " . ( $i + 1 ) . " quoted variables: " . $evaluate . $quoted_variables_logging_extra;
				}
			}
		}

		// Evaluate non-quoted variables, by de-quoting the quoted ones if needed


		$strings_count = preg_match_all(
			'/((\$\w+)|(\'[^\']*\'))\s*([\!<>\=|lt|lte|eq|ne|gt|gte]+)\s*((\$\w+)|(\'[^\']*\'))/',
			$evaluate, $matches
		);

		// get all string comparisons - with variables and/or literals
		if(
			$strings_count
			&& $strings_count > 0
		) {
			for( $i = 0; $i < $strings_count; $i++ ) {

				// get both sides and sign
				$first_string = $matches[1][ $i ];
				$second_string = $matches[5][ $i ];
				$math_sign = $matches[4][ $i ];

				$general_variables_logging_extra = '';

				// remove single quotes from string literals to get value only
				$first_string = ( strpos( $first_string, '\'' ) === 0 ) ? substr( $first_string, 1, strlen( $first_string ) - 2 ) : $first_string;
				$second_string = ( strpos( $second_string, '\'' ) === 0 ) ? substr( $second_string, 1, strlen( $second_string ) - 2 ) : $second_string;
				$general_variables_logging_extra .= "\n\tComparing " . $first_string . " to " . $second_string;

				// replace variables with text representation
				if(
					strpos( $first_string, '$' ) === 0
					&& $has_post
				) {
					$variable_name = substr( $first_string, 1 ); // omit dollar sign
					if( isset( $atts[ $variable_name ] ) ) {
						$first_string = get_post_meta( $id, $atts[ $variable_name ], true );
					} else {
						$first_string = '';
						$general_variables_logging_extra .= "\n\tERROR: Key " . $variable_name . " does not point to a valid attribute in the wpv-if shortcode";
					}
				}
				if( strpos( $second_string, '$' ) === 0 && $has_post ) {
					$variable_name = substr( $second_string, 1 );
					if( isset( $atts[ $variable_name ] ) ) {
						$second_string = get_post_meta( $id, $atts[ $variable_name ], true );
					} else {
						$second_string = '';
						$general_variables_logging_extra .= "\n\tERROR: Key " . $variable_name . " does not point to a valid attribute in the wpv-if shortcode";
					}
				}


				$evaluate = ( is_numeric( $first_string ) ? str_replace( $matches[1][ $i ], $first_string, $evaluate ) : str_replace( $matches[1][ $i ], "'$first_string'", $evaluate ) );
				$evaluate = ( is_numeric( $second_string ) ? str_replace( $matches[5][ $i ], $second_string, $evaluate ) : str_replace( $matches[5][ $i ], "'$second_string'", $evaluate ) );
				$logging_string .= "\nAfter replacing " . ( $i + 1 ) . " general variables and comparing strings: " . $evaluate . $general_variables_logging_extra;
			}
		}
		// Evaluate comparisons when at least one of them is numeric
		$strings_count = preg_match_all( '/(\'[^\']*\')/', $evaluate, $matches );
		if(
			$strings_count
			&& $strings_count > 0
		) {
			for( $i = 0; $i < $strings_count; $i++ ) {
				$string = $matches[1][ $i ];
				// remove single quotes from string literals to get value only
				$string = ( strpos( $string, '\'' ) === 0 ) ? substr( $string, 1, strlen( $string ) - 2 ) : $string;
				if( is_numeric( $string ) ) {
					$evaluate = str_replace( $matches[1][ $i ], $string, $evaluate );
					$logging_string .= "\nAfter matching " . ( $i + 1 ) . " numeric strings into real numbers: " . $evaluate;
					$logging_string .= "\n\tMatched " . $matches[1][ $i ] . " to " . $string;
				}
			}
		}

		// Evaluate all remaining variables
		if( $has_post ) {
			$count = preg_match_all( '/\$(\w+)/', $evaluate, $matches );

			// replace all variables with their values listed as shortcode parameters
			if(
				$count
				&& $count > 0
			) {
				$logging_string .= "\nRemaining variables: " . var_export( $matches[1], true );
				// sort array by length desc, fix str_replace incorrect replacement
				// wpv_sort_matches_by_length belongs to common/functions.php
				$matches[1] = wpv_sort_matches_by_length( $matches[1] );

				foreach( $matches[1] as $match ) {
					if( isset( $atts[ $match ] ) ) {
						$meta = get_post_meta( $id, $atts[ $match ], true );
						if ( 
							empty( $meta ) 
							&& ! is_numeric( $meta )
						) {
							$meta = "''";
						}
					} else {
						$meta = "0";
					}
					$evaluate = str_replace( '$' . $match, $meta, $evaluate );
					$logging_string .= "\nAfter replacing remaining variables: " . $evaluate;
				}
			}
		}

		return array( $evaluate, $logging_string );
	}


	/**
	 * Evaluates conditions using custom conditional statement.
	 *
	 * @uses wpv_condition()
	 *
	 * @param type $post
	 * @param type $evaluate
	 * @return boolean
	 */
	public static function evaluateCustom( $evaluate ) {
		$check = false;
		try {
			$parser = new Toolset_Parser( $evaluate );
			$parser->parse();
			$check = $parser->evaluate();
		} catch( Exception $e ) {
			$check = false;
		}

		return $check;
	}


	public static function extractFields( $evaluate ) {
		//###############################################################################################
		//https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193583580/comments
		//Fix REGEX conditions that contains \ that is stripped out
		if( strpos( $evaluate, "REGEX" ) === false ) {
			$evaluate = trim( stripslashes( $evaluate ) );
			// Check dates
			$evaluate = wpv_filter_parse_date( $evaluate );
			$evaluate = self::handle_user_function( $evaluate );
		}

		// Add quotes = > < >= <= === <> !==
		$strings_count = preg_match_all( '/[=|==|===|<=|<==|<===|>=|>==|>===|\!===|\!==|\!=|<>]\s(?!\$)(\w*)[\)|\$|\W]/', $evaluate, $matches );

		if( !empty( $matches[1] ) ) {
			foreach( $matches[1] as $temp_match ) {
				$temp_replace = is_numeric( $temp_match ) ? $temp_match : '\'' . $temp_match . '\'';
				$evaluate = str_replace( ' ' . $temp_match . ')', ' ' . $temp_replace . ')', $evaluate );
			}
		}
		// if new version $(field-value) use this regex
		if( preg_match( '/\$\(([^()]+)\)/', $evaluate ) ) {
			preg_match_all( '/\$\(([^()]+)\)/', $evaluate, $matches );
		} // if old version $field-value use this other
		else {
			preg_match_all( '/\$([^\s]*)/', $evaluate, $matches );
		}


		$fields = array();
		if( !empty( $matches ) ) {
			foreach( $matches[1] as $field_name ) {
				$fields[ trim( $field_name, '()' ) ] = trim( $field_name, '()' );
			}
		}

		return $fields;
	}


	static function sortByLength( $a, $b ) {
		return strlen( $b ) - strlen( $a );
	}


	private static function _update_values_in_expression( $evaluate, $fields, $values, $id ) {

		// use string replace to replace any fields with their values.
		// Sort by length just in case a field name contians a shorter version of another field name.
		// eg.  $my-field and $my-field-2

		$keys = array_keys( $fields );
		usort( $keys, 'WPV_Views_Conditional::sortByLength' );

		foreach( $keys as $key ) {
			$is_numeric = false;
			$is_array = false;
			$value = isset( $values[ $fields[ $key ] ] ) ? $values[ $fields[ $key ] ] : '';
			
			if ( ! empty( $id ) ) {
				/**
				 * Maybe filter the postmeta value based on its meta key.
				 *
				 * This filter mimics the native get_post_metadata filter documented here:
				 * https://developer.wordpress.org/reference/functions/get_metadata/
				 * If it returns a not null value, then use the returned value instead of the original.
				 * It will hijack legacy '_wpcf_belongs_{slug}_id' meta keys and return the current m2m-compatible
				 * parent value, if any.
				 *
				 * @param null
				 * @param $id int|string The ID of the post that the postmeta belongs to
				 * @patam $fields[ $key ] string The key of the postmeta
				 * @param true Return always a single value, not an array
				 *
				 * @since m2m
				 */
				$postmeta_access_m2m_value = apply_filters( 'toolset_postmeta_access_m2m_get_post_metadata', null, $id, $fields[ $key ], true );
				$value = ( null === $postmeta_access_m2m_value ) 
					? $value 
					: $postmeta_access_m2m_value;
			}
			
			if( $value == '' ) {
				$value = "''";
			}

			if( is_numeric( $value ) ) {
				$is_numeric = true;
			}

			if( 'array' === gettype( $value ) ) {
				$is_array = true;
				// workaround for datepicker data to cover all cases
				if( array_key_exists( 'timestamp', $value ) ) {
					if( is_numeric( $value['timestamp'] ) ) {
						$value = $value['timestamp'];
					} else if( is_array( $value['timestamp'] ) ) {
						$value = implode( ',', array_values( $value['timestamp'] ) );
					}
				} else if( array_key_exists( 'datepicker', $value ) ) {
					if( is_numeric( $value['datepicker'] ) ) {
						$value = $value['datepicker'];
					} else if( is_array( $value['datepicker'] ) ) {
						$value = implode( ',', array_values( $value['datepicker'] ) );
					}
				} else {
					$value = implode( ',', array_values( $value ) );
				}
			}

			if( !empty( $value ) && $value != "''" && !$is_numeric && !$is_array ) {
				$value = str_replace( "'", "", $value );
				$value = '\'' . $value . '\'';
			}

			// First replace the $(field_name) format
			$evaluate = str_replace( '$(' . $fields[ $key ] . ')', $value, $evaluate );
			// next replace the $field_name format
			$evaluate = str_replace( '$' . $fields[ $key ], $value, $evaluate );
		}

		return $evaluate;
	}


	public static function handle_user_function( $evaluate ) {
		$evaluate = stripcslashes( $evaluate );
		$occurrences = preg_match_all( '/(\\w+)\(([^\)]*)\)/', $evaluate, $matches );

		if( $occurrences > 0 ) {
			for( $i = 0; $i < $occurrences; $i++ ) {
				$result = false;
				$function = $matches[1][ $i ];
				$field = isset( $matches[2] ) ? rtrim( $matches[2][ $i ], ',' ) : '';

				if( $function === 'USER' ) {
					$result = WPV_Handle_Users_Functions::get_user_field( $field );
				}

				if( $result ) {
					$evaluate = str_replace( $matches[0][ $i ], $result, $evaluate );
				}
			}
		}

		return $evaluate;
	}


	private static function getStringFromArray( $array ) {
		if( is_object( $array ) ) {
			return $array;
		}
		if( is_array( $array ) ) {
			return self::getStringFromArray( array_shift( $array ) );
		}

		return strval( $array );
	}


	public static function getCustomConditional( $custom, $suffix = '', $cond_values = array() ) {

	}

}

global $WPV_Views_Conditional;
$WPV_Views_Conditional = new WPV_Views_Conditional();