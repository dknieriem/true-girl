<?php

/**
* wpv-deprecated.php
*
* Holds some functions that might be deprecated but it is worth checking using Code Coverage
*
* @since 1.6.2
*/

/*************
* Layout functions
**************/

class View_layout_field { // NOT SURE IF DEPRECATED
    protected $type;
    protected $prefix;
    protected $suffix;
    protected $edittext;
    
    function __construct($type, $prefix = "", $suffix = "", $row_title = "", $edittext = "", $types_field_name = "", $types_field_data = ""){
        
        $this->type = $type;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->row_title = $row_title;
        $this->edittext = $edittext;
        $this->types_field_name = $types_field_name;
        $this->types_field_data = $types_field_data;
    }
    
    function render_to_table($index) {
        global $wpv_shortcodes, $WPV_templates, $WP_Views;
        
        $view_template = null;
        $view = null;
        $view_type = null;
        
        if (strpos($this->type, 'wpv-post-field - ') === 0) {
            $name = substr($this->type, strlen('wpv-post-field - '));
            $title = $name;
        } elseif ($this->type == 'types-field') {
            $name = $this->type;
            $title = 'Types - ' . $this->types_field_name;
        } elseif (strpos($this->type, 'types-field - ') === 0) {
            $name = substr($this->type, strlen('types-field - '));
            $title = $name;
        } elseif(strpos($this->type, 'wpvtax') === 0) {
        	// $name = substr($this->type, strlen('wpvtax-'));
        	$name = 'Taxonomy - '. $this->type;
            $title = $name;
        } elseif (strpos($this->type, 'wpv-post-body ') === 0) {
            $name = $wpv_shortcodes['wpv-post-body'][1];
            $parts = explode(' ', $this->type);
            if (isset($parts[1])) {
                $view_template = $parts[1];
            }
            $title = $name;
        } elseif (strpos($this->type, WPV_TAXONOMY_VIEW . ' ') === 0) {
            $name = 'Taxonomy View';
            $parts = explode(' ', $this->type);
            if (isset($parts[1])) {
                $view = $parts[1];
                $view_type = 'taxonomy';
            }
            $title = $name;
        } elseif (strpos($this->type, WPV_POST_VIEW . ' ') === 0) {
            $name = 'Post View';
            $parts = explode(' ', $this->type);
            if (isset($parts[1])) {
                $view = $parts[1];
                $view_type = 'post';
            }
            $title = $name;
        } else {
            $name = $wpv_shortcodes[$this->type][1];
            $title = $name;
        }
        
        ?>
        <td width="120px"><input class="wpv_field_prefix" id="wpv_field_prefix_<?php echo $index; ?>" type="text" value="<?php echo htmlspecialchars($this->prefix); ?>" name="_wpv_layout_settings[fields][prefix_<?php echo $index; ?>]" /></td>
        <td width="120px">
            <span id="wpv_field_name_<?php echo $index; ?>"><?php echo $title; ?></span>
            <input id="wpv_field_name_hidden_<?php echo $index; ?>" type="hidden" value="<?php echo $name; ?>" name="_wpv_layout_settings[fields][name_<?php echo $index; ?>]" />
            <input id="wpv_types_field_name_hidden_<?php echo $index; ?>" type="hidden" value="<?php echo $this->types_field_name; ?>" name="_wpv_layout_settings[fields][types_field_name_<?php echo $index; ?>]" />
            <input id="wpv_types_field_data_hidden_<?php echo $index; ?>" type="hidden" value="<?php echo esc_js($this->types_field_data); ?>" name="_wpv_layout_settings[fields][types_field_data_<?php echo $index; ?>]" />
        </td>
        <?php
        $row_title = $this->row_title;
        ?>
        <td class="row-title hidden" width="120px"><input type="text" id="wpv_field_row_title_<?php echo $index; ?>" value="<?php echo $row_title; ?>" name="_wpv_layout_settings[fields][row_title_<?php echo $index; ?>]" /></td>
        <td width="120px"><input class="wpv_field_suffix"  id="wpv_field_suffix_<?php echo $index; ?>" type="text" value="<?php echo htmlspecialchars($this->suffix); ?>" name="_wpv_layout_settings[fields][suffix_<?php echo $index; ?>]" /></td>
        <?php
    }
    
    function render_table_row_attributes($view_settings) {
        
        if (strpos($this->type, 'wpv-taxonomy-') === 0 || strpos($this->type, WPV_TAXONOMY_VIEW) === 0) {
            // taxonomy type.
            $output = 'class="wpv-taxonomy-field"';
            if ($view_settings['query_type'][0] != 'taxonomy') {
                $output .= ' style="display:none"';
            }
        } else {
            // post type
            $output = 'class="wpv-post-type-field"';
            if ($view_settings['query_type'][0] != 'posts') {
                $output .= ' style="display:none"';
            }
        }
        
        return $output;
        
    }
    
    function get_body_template() {
        if (strpos($this->type, 'wpv-post-body ') === 0) {
            $parts = explode(' ', $this->type);
            return $parts[1];
        } else {
            return -1;
        }
    }
    
}

$link_layout_number = 0;

function view_layout_fields_to_classes($fields) {
    $output = array();
    for ($i = 0; $i < sizeof($fields); $i++) {
        if (!isset($fields["name_{$i}"])) {
            break;
        }
        $output[] = new View_layout_field($fields["name_{$i}"],
                                          $fields["prefix_{$i}"],
                                          $fields["suffix_{$i}"],
                                          isset($fields["row_title_{$i}"]) ? $fields["row_title_{$i}"] : '',
        								  isset($fields["edittext_{$i}"]) ? $fields["edittext_{$i}"] : '',
                                          isset($fields["types_field_name_{$i}"]) ? $fields["types_field_name_{$i}"] : '',
                                          isset($fields["types_field_data_{$i}"]) ? $fields["types_field_data_{$i}"] : '');
        
    }
    return $output;
}
function view_layout_fields($post, $view_layout_settings) {
    global $WP_Views;
    $view_settings = $WP_Views->get_view_settings($post->ID);
    if (isset($view_layout_settings['fields'])) {
        $view_layout_settings['fields'] = view_layout_fields_to_classes($view_layout_settings['fields']);
    } else {
        $view_layout_settings['fields'] = array();
    }
    view_layout_javascript();
    global $WPV_templates;
    $template_selected = 0;
    foreach ($view_layout_settings['fields'] as $field) {
        $posible_template = $field->get_body_template();
        if ($posible_template >= 0) {
            $template_selected = $posible_template;
            break;
        }
    }
    ?>
    <div id="view_layout_fields" class="view_layout_fields">
        <p id="view_layout_fields_to_include"><strong><?php echo __('Fields to include:', 'wpv-views'); ?></strong></p>
        <p id="view_layout_add_field_message_1"><?php echo __("Click on <strong>Add field</strong> to insert additional fields. Drag them to reorder, or delete fields that you don't need.", 'wpv-views'); ?></p>
        <p id="view_layout_add_field_message_2" style="display:none"><?php echo __("Click on <strong>Add field</strong> to insert fields to this View.", 'wpv-views'); ?></p>
        
        <table id="view_layout_fields_table" class="widefat fixed">
            <thead>
                <tr>
                    <th width="20px"></th><th width="120px"><?php echo __('Prefix', 'wpv-views'); ?></th><th width="220px"><?php echo __('Field', 'wpv-views'); ?></th><th class="row-title hidden" width="120px"><?php echo __('Row Title', 'wpv-views'); ?></th><th width="120px"><?php echo __('Suffix', 'wpv-views'); ?></th><th width="16px"></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th><th></th><th></th><th class="row-title hidden"></th><th></th><th></th>
                </tr>
            </tfoot>
            
            <tbody>
                <?php
                $count = sizeof($view_layout_settings['fields']);
                foreach($view_layout_settings['fields'] as $index => $field) {
                    ?>
                    <tr id="wpv_field_row_<?php echo $index; ?>" <?php echo $field->render_table_row_attributes($view_settings); ?>>
                    
                        <td width="20px"><img src="<?php echo WPV_URL . '/res/img/delete.png'; ?>" onclick="on_delete_wpv(<?php echo $index; ?>)" style="cursor: pointer" /></td><?php $field->render_to_table($index); ?><td width="16px"><img src="<?php echo WPV_URL; ?>/res/img/move.png" class="move" style="cursor: move;" /></td>
                    
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        
        </table>
        <br />
    </div>
    
    <?php
        $show = $view_settings['query_type'][0] == 'posts';
    ?>
    <input class="button-secondary wpv_add_fields_button" type="button" value="<?php echo __('Add field', 'wpv-views'); ?>" name="wpv-layout-add-field" <?php if($show) {echo '';} else {echo ' style="display:none"';} ?> />
    <div id="add_field_popup" style="display:none; overflow: auto;">

        <?php
        global $link_layout_number;
        $link_layout_number = 0;
		// @todo Views 2.3.0, commented out since this will not be a $WP_Views object property anymore
        // $WP_Views->editor_addon->add_form_button('', 'wpv_layout_meta_html_content', false);
        
        ?>

    </div>  

	<?php // echo $WP_Views->editor_addon->add_form_button('', '#wpv_layout_meta_html_content', false); ?>
    <?php // Add a popup for taxonomy fields ?>
    
    <div id="add_taxonomy_field_popup" style="display:none">

        <table id="wpv_taxonomy_field_popup_table" width="100%">
        <tr>
        <?php
        global $link_layout_number;
        $link_layout_number = 0;
        ?>
        </tr>
        </table>

    </div>  

    <script type="text/javascript">
		jQuery('.wpv_add_fields_button').click(function(){
			setTimeout(searchFocus,300);
		});
		function searchFocus(){
			jQuery('#add_field_popup').find('.search_field').focus();
		}
        var wpv_shortcodes = new Array();
        <?php
            $current_index = 0;
        ?>
        wpv_shortcodes[<?php echo $current_index++; ?>] = new Array('Taxonomy View', '<?php echo WPV_TAXONOMY_VIEW; ?>');
        wpv_shortcodes[<?php echo $current_index++; ?>] = new Array('Post View', '<?php echo WPV_POST_VIEW; ?>');
        <?php 
        if (defined('WPV_WOOCOMERCE_VIEWS_SHORTCODE')) {
        ?>
        wpv_shortcodes[<?php echo $current_index; ?>] = new Array('Add to cart button', '<?php echo WPV_WOOCOMERCE_VIEWS_SHORTCODE; ?>');
        <?php 
        }
        ?>
        <?php 
        if (defined('WPV_WOOCOMERCEBOX_VIEWS_SHORTCODE')) {
        ?>
        wpv_shortcodes[<?php echo $current_index++; ?>] = new Array('Add to cart box', '<?php echo WPV_WOOCOMERCEBOX_VIEWS_SHORTCODE; ?>');
        <?php 
        }
        ?>
        var wpv_view_template_text = "<?php echo esc_js(__('Content template', 'wpv-views')); ?>";
        var wpv_taxonomy_view_text = "<?php echo esc_js(__('Taxonomy View', 'wpv-views')); ?>";
        var wpv_post_view_text = "<?php echo esc_js(__('Post View', 'wpv-views')); ?>";
        var wpv_add_field_text = "<?php echo esc_js(__('Field', 'wpv-views')); ?>";
        var wpv_add_taxonomy_text = "<?php echo esc_js(__('Taxonomy', 'wpv-views')); ?>";
    </script>
    <?php
        $show = $view_settings['query_type'][0] == 'taxonomy';
    ?>
    <input alt="#TB_inline?inlineId=add_taxonomy_field_popup" class="thickbox button-secondary wpv_add_taxonomy_fields_button" type="button" value="<?php echo __('Add field', 'wpv-views'); ?>" name="Add a taxonomy field" <?php if($show) {echo '';} else {echo ' style="display:none"';} ?> />
    
    <?php
        $show = $view_settings['query_type'][0] == 'posts' ? '' : 'style="display:none"';
    ?>
    <span id="wpv-layout-help-posts" <?php echo $show;?>><i><?php echo __('Want to add complex fields?', 'wpv-views') . '&nbsp;' .
                                                                               '<a class="wpv-help-link" href="https://toolset.com/user-guides/using-a-view-template-in-a-view-layout/" target="_blank">' .
                                                                               __('Learn about using Content Templates to customize fields.', 'wpv-views') .
                                                                               ' &raquo;</a>'; ?></i></span>
    <?php
        $show = $view_settings['query_type'][0] == 'taxonomy' ? '' : 'style="display:none"';
    ?>
    <span id="wpv-layout-help-taxonomy" <?php echo $show;?>><i><?php echo sprintf(__('Want to display posts that belong to this taxonomy? Learn about %sinserting child Views to Taxonomy Views%s.', 'wpv-views'),
                                                                                  '<a href="https://toolset.com/user-guides/using-a-child-view-in-a-taxonomy-view-layout/" target="_blank">',
                                                                                  ' &raquo;</a>'); ?></i></span>

    <?php
        // Warn if Types is less than 1.0.2
        // We need at least 1.0.2 for the Types popups to work when adding fields.
        if (defined('WPCF_VERSION') && version_compare(WPCF_VERSION, '1.0.2', '<')) {
            echo '<br /><p style="color:red;"><strong>';
            _e('* Views requires Types 1.0.2 or greater for best results when adding fields.', 'wpv-views');
            echo '</strong></p>';
        }
    ?>

    
    <?php
}
function view_layout_javascript() {
    global $pagenow;
    ?>
    <script type="text/javascript">
        var wpv_layout_constants = {
            'WPV_TAXONOMY_VIEW' : '<?php echo WPV_TAXONOMY_VIEW;?>',
            'WPV_POST_VIEW': '<?php echo WPV_POST_VIEW;?>'
        };
        var wpv_url = '<?php echo WPV_URL; ?>';
        var wpv_field_text = '<?php echo esc_js(__('Field', 'wpv-views')); ?> - ';
        var wpv_confirm_layout_change = '<?php echo esc_js(__("Are you sure you want to change the layout?", 'wpv-views')); echo "\\n\\n"; echo esc_js(__("It appears that you made modifications to the layout.", 'wpv-views')); ?>';
        var no_post_results_text = "[wpv-no-posts-found][wpml-string context=\"wpv-views\"]<strong>No posts found</strong>[/wpml-string][/wpv-no-posts-found]";
        var no_taxonomy_results_text = "[wpv-no-taxonomy-found][wpml-string context=\"wpv-views\"]<strong>No taxonomy found</strong>[/wpml-string][/wpv-no-taxonomy-found]";
    </script>
    <?php
}
function view_layout_additional_js($post, $view_layout_settings) {
    $js = isset($view_layout_settings['additional_js']) ? strval($view_layout_settings['additional_js']) : '';
    ?>
    <br /><br />
    <fieldset><legend><?php _e('Additional Javascript files to be loaded with this View (comma separated): ', 'wpv-views'); ?></legend>
    <input type="text" name="_wpv_layout_settings[additional_js]" style="width:100%;" value="<?php echo $js; ?>" />
    </fieldset>
    <?php
}

function short_code_taxonomy_menu_callback($index, $cf_key, $function_name, $menu, $shortcode) {
    global $link_layout_number;
    static $taxonomy_view_started = false;
    static $post_view_started = false;
    static $suffix = '';
    if (!$taxonomy_view_started && $menu == esc_js(__('Taxonomy View', 'wpv-views'))) {
        echo '</tr><tr><td></td>';
        echo '</tr><tr><td></td></tr><tr>';
        echo '<td colspan="2"><strong>' . $menu . '</strong> ' . __(' - Use to layout child taxonomy terms', 'wpv-views') . '</td>';
        echo '</tr><tr>';
        $link_layout_number = 0;
        $taxonomy_view_started = true;
        $suffix = ' - ' . __('Taxonomy View', 'wpv-views');
    }
    if (!$post_view_started && $menu == esc_js(__('Post View', 'wpv-views'))) {
        echo '</tr><tr><td></td>';
        echo '</tr><tr><td></td></tr><tr>';
        echo '<td colspan="2"><strong>' . $menu . '</strong> ' . __(' - Use to layout posts for the current taxonomy term', 'wpv-views') . '</td>';
        echo '</tr><tr>';
        $link_layout_number = 0;
        $post_view_started = true;
        $suffix = ' - ' . __('Post View', 'wpv-views');
    }
    if (!($link_layout_number % 2)) {
        if ($link_layout_number != 0) {
            echo '</tr><tr>' ;
        }
        
    }
    echo '<td><a style="cursor: pointer" onclick="on_add_field_wpv(\''. $menu . '\', \'' . esc_js($cf_key) . '\', \'' . base64_encode($cf_key . $suffix) . '\')">';
    echo $cf_key;
    echo '</a></td>';
    $link_layout_number++;
}
function short_code_variable_callback($index, $cf_key, $function_name, $menu, $shortcode) {
    ?>
        wpv_shortcodes[<?php echo $index?>] = new Array('<?php echo esc_js($cf_key);?>', '<?php echo esc_js($shortcode); ?>');
    <?php
}

/**********************
* Pagination
************************/

if (isset($_GET['wpv-pagination-spinner-media-insert'])) {// DEPRECATED now we use the Media Manager from WordPress
    // Add JS
    add_action('admin_head', 'wpv_pagination_spinner_media_admin_head');
    // Filter media TABs
    add_filter('media_upload_tabs',
            'wpv_pagination_spinner_media_upload_tabs_filter');
    // Add button
    add_filter('attachment_fields_to_edit',
            'wpv_pagination_spinner_attachment_fields_to_edit_filter', 10, 2);
}

/**
 * Media popup JS.
 */
function wpv_pagination_spinner_media_admin_head() { // DEPRECATED

    ?>
    <script type="text/javascript">
        function wpvPaginationSpinnerMediaTrigger(guid, type) {
            window.parent.jQuery('#wpv-pagination-spinner-image').val(guid);
            window.parent.jQuery('#wpv-pagination-spinner-image-preview').attr('src', guid);
            window.parent.jQuery('#TB_closeWindowButton').trigger('click');
        }
    </script>
    <style type="text/css">
        tr.submit { display: none; }
    </style>
    <?php
}

/**
 * Adds 'Spinner' column to media item table.
 * 
 * @param type $form_fields
 * @param type $post
 * @return type 
 */
function wpv_pagination_spinner_attachment_fields_to_edit_filter($form_fields, $post) {// DEPRECATED
    $type = (strpos($post->post_mime_type, 'image/') !== false) ? 'image' : 'file';
    $form_fields['wpcf_fields_file'] = array(
        'label' => __('Views Pagination', 'wpv-views'),
        'input' => 'html',
        'html' => '<a href="#" title="' . $post->guid
        . '" class="wpv-pagination-spinner-insert-button'
        . ' button-primary" onclick="wpvPaginationSpinnerMediaTrigger(\''
        . $post->guid . '\', \'' . $type . '\')">'
        . __('Use as spinner image', 'wpv-views') . '</a><br /><br />',
    );
    return $form_fields;
}

/**
 * Filters media TABs.
 * 
 * @param type $tabs
 * @return type 
 */
function wpv_pagination_spinner_media_upload_tabs_filter($tabs) { // DEPRECATED
    unset($tabs['type_url']);
    return $tabs;
}

// @todo get_user_meta_keys is DEPRECATED and kept for backwards compatibility as it is called from common - let's remove it from there before deleting this.

function get_user_meta_keys( $include_hidden = false ) {
	global $wpdb;
	$values_to_prepare = array();
	//static $cf_keys = null;
	$umf_mulsitise_string = " 1 = 1 ";
	if ( is_multisite() ) {
		global $blog_id;
		$umf_mulsitise_string = " ( meta_key NOT REGEXP '^{$wpdb->base_prefix}[0-9]_' OR meta_key REGEXP '^{$wpdb->base_prefix}%d_' ) ";
		$values_to_prepare[] = $blog_id;
	}
	$umf_hidden = " 1 = 1 ";
	if ( ! $include_hidden ) {
		$hidden_usermeta = array('first_name','last_name','name','nickname','description','yim','jabber','aim',
		'rich_editing','comment_shortcuts','admin_color','use_ssl','show_admin_bar_front',
		'capabilities','user_level','user-settings',
		'dismissed_wp_pointers','show_welcome_panel',
		'dashboard_quick_press_last_post_id','managenav-menuscolumnshidden',
		'primary_blog','source_domain',
		'closedpostboxes','metaboxhidden','meta-box-order_dashboard','meta-box-order','nav_menu_recently_edited',
		'new_date','show_highlight','language_pairs',
		'module-manager',
		'screen_layout');
	//	$umf_hidden = " ( meta_key NOT REGEXP '" . implode("|", $hidden_usermeta) . "' AND meta_key NOT REGEXP '^_' ) "; // NOTE this one make sites with large usermeta tables to fall
		$umf_hidden = " ( meta_key NOT IN ('" . implode("','", $hidden_usermeta) . "') AND meta_key NOT REGEXP '^_' ) ";
	}
	$where = " WHERE {$umf_mulsitise_string} AND {$umf_hidden} ";
	$values_to_prepare[] = 100;
	$usermeta_keys = $wpdb->get_col( 
		$wpdb->prepare(
			"SELECT DISTINCT meta_key FROM {$wpdb->usermeta} 
			{$where} 
			LIMIT 0, %d",
			$values_to_prepare
		)
	);
	if ( ! empty( $usermeta_keys ) ) {
		natcasesort( $usermeta_keys );
	}
	return $usermeta_keys;
}

/*
* ---------------------
* TEMPORARY FUNCTIONS
* ---------------------
*/

/**
* _wpv_deprecated_remove_admin_bar_toolset
*
* Workaround to remove the Toolset Admin Bar Menu until we can be sure that the class containing it applies the new toolset_filter_toolset_admin_bar_menu_disable filter
*
* Added in Views 1.10 and common 1.7
*
* @toremove on a couple of join releases
*/

add_action( 'init', '_wpv_deprecated_remove_admin_bar_toolset' );

function _wpv_deprecated_remove_admin_bar_toolset() {
	global $toolset_admin_bar_menu;
	$toolset_options = get_option( 'toolset_options', array() );
	$toolset_admin_bar_menu_remove = ( isset( $toolset_options['show_admin_bar_shortcut'] ) && $toolset_options['show_admin_bar_shortcut'] == 'off' ) ? true : false;
	if ( $toolset_admin_bar_menu_remove ) {
		remove_action( 'admin_bar_menu', array( $toolset_admin_bar_menu, 'admin_bar_menu' ), 99 );
	}
}