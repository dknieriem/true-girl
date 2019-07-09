<?php

WPV_Editor_Filter_Editor::on_load();

class WPV_Editor_Filter_Editor {
	
	static function on_load() {
		// Register the section in the screen options of the editor pages
		add_filter( 'wpv_screen_options_editor_section_filter',		array( 'WPV_Editor_Filter_Editor', 'wpv_screen_options_filter_editor' ), 20 );
		add_filter( 'wpv_screen_options_wpa_editor_section_filter',	array( 'WPV_Editor_Filter_Editor', 'wpv_screen_options_filter_editor' ), 20 );
		// Register the section in the editor pages
		add_action( 'wpv_action_view_editor_section_filter',		array( 'WPV_Editor_Filter_Editor', 'wpv_editor_section_parametric_search' ), 30, 2 );
		add_action( 'wpv_action_wpa_editor_section_filter',			array( 'WPV_Editor_Filter_Editor', 'wpv_editor_section_parametric_search' ), 30, 2 );
		add_action( 'wpv_action_view_editor_section_filter',		array( 'WPV_Editor_Filter_Editor', 'wpv_editor_section_filter_editor' ), 35, 2 );
		add_action( 'wpv_action_wpa_editor_section_filter',			array( 'WPV_Editor_Filter_Editor', 'wpv_editor_section_filter_editor' ), 35, 2 );
		// AJAX management
		add_action( 'wp_ajax_wpv_get_parametric_search_hints',		array( 'WPV_Editor_Filter_Editor', 'wpv_get_parametric_search_hints' ) );
		add_action( 'wp_ajax_wpv_remove_filter_missing',			array( 'WPV_Editor_Filter_Editor', 'wpv_remove_filter_missing_callback' ) );
		
		/**
		* ----------------------------
		* Parametric search
		* ----------------------------
		*/
		
		add_action( 'wpv_parametric_search_buttons',								array( 'WPV_Editor_Filter_Editor', 'wpv_add_parametric_search_buttons_to_editor' ), 10, 4 );
		
		add_action( 'wp_ajax_wpv_parametric_search_filter_create_dialog',			array( 'WPV_Editor_Filter_Editor', 'wpv_parametric_search_filter_create_dialog' ) );
		
		add_action( 'wp_ajax_wpv_custom_search_define_query_filter',				array( 'WPV_Editor_Filter_Editor', 'wpv_custom_search_define_query_filter' ) );
	}
	
	static function wpv_screen_options_filter_editor( $sections ) {
		$sections['filter-extra-parametric'] = array(
			'name'		=> __( 'Custom Search Settings', 'wpv-views' ),
			'disabled'	=> false,
		);
		$sections['filter-extra'] = array(
			'name'		=> __( 'Search and Pagination', 'wpv-views' ),
			'disabled'	=> false,
		);
		return $sections;
	}

	static function wpv_editor_section_parametric_search( $view_settings, $view_id ) {
		$is_section_hidden = false;
		if ( isset( $view_settings['sections-show-hide'] )
			&& isset( $view_settings['sections-show-hide']['filter-extra-parametric'] )
			&& 'off' == $view_settings['sections-show-hide']['filter-extra-parametric'] )
		{
			$is_section_hidden = true;
		}
		$hidden_class = $is_section_hidden ? 'hidden' : '';
		if (
			isset( $view_settings['view-query-mode'] ) 
			&& $view_settings['view-query-mode'] == 'normal'
		) {
			$section_help_pointer = WPV_Admin_Messages::edit_section_help_pointer( 'parametric_search' );
		} else if ( isset( $view_settings['query_type'][0] ) ) {
			$section_help_pointer = WPV_Admin_Messages::edit_section_help_pointer( 'parametric_search_archive' );
		}
		?>
		<div class="wpv-setting-container js-wpv-settings-filter-extra-parametric <?php echo $hidden_class; ?>">
		
			<div class="wpv-settings-header">
				<h2>
					<?php _e( 'Custom Search Settings', 'wpv-views' ) ?>
					<i class="icon-question-sign fa fa-question-circle js-display-tooltip" 
						data-header="<?php echo esc_attr( $section_help_pointer['title'] ); ?>" 
						data-content="<?php echo esc_attr( $section_help_pointer['content'] ); ?>">
					</i>
				</h2>
			</div>

			<div class="wpv-setting js-wpv-dps-settings">
				<?php
				$listing = '';
				if ( 
					isset( $view_settings['query_type'] ) 
					&& is_array( $view_settings['query_type'] ) 
					&& in_array( 'posts', $view_settings['query_type'] ) 
				) {
					$listing = 'posts';
				}
				?>			
				<p class="toolset-alert toolset-alert-info wpv-settings-query-type-taxonomy wpv-settings-query-type-users<?php echo $listing == 'posts' ? ' hidden' : ''; ?>">
					<?php _e('Only Views listing posts can have custom search inputs.', 'wpv-views'); ?>
				</p>
				<div class="wpv-settings-query-type-posts<?php echo $listing == 'posts' ? '' : ' hidden'; ?>">
					<?php
					$controls_per_kind = wpv_count_filter_controls( $view_settings );
					$no_intersection = array();
					$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'] + $controls_per_kind['search'];
					
					if ( 
						isset( $controls_per_kind['cf'] ) 
						&& $controls_per_kind['cf'] > 1 
						&& ( 
							! isset( $view_settings['custom_fields_relationship'] ) 
							|| $view_settings['custom_fields_relationship'] != 'AND' 
						) 
					) {
						$no_intersection[] = __( 'custom field', 'wpv-views' );
					}
					if ( 
						isset( $controls_per_kind['tax'] ) 
						&& $controls_per_kind['tax'] > 1 
						&& ( 
							! isset( $view_settings['taxonomy_relationship'] ) 
							|| $view_settings['taxonomy_relationship'] != 'AND' 
						) 
					) {
						$no_intersection[] = __( 'taxonomy', 'wpv-views' );
					}
					
					if ( isset( $controls_per_kind['warning'] ) ) {
						?>
						<!--<p class="toolset-alert toolset-alert-info js-wpv-mismatch-parametric-search-count">
							<?php echo $controls_per_kind['warning']; ?>
						</p>-->
						<?php
					}
					
					if ( isset( $controls_per_kind['error'] ) ) {
						echo $controls_per_kind['error'];
					}
					
					if ( ! isset( $view_settings['dps'] ) ) {
						$view_settings['dps'] = array();
						$view_settings['dps']['mode_helper'] = '';
					} else {
						if ( ! isset( $view_settings['dps']['mode_helper'] ) ) {
							$view_settings['dps']['mode_helper'] = '';
						}
					}
					?>
					<h3><?php _e( 'How do you want to update the results?', 'wpv-views' ); ?></h3>
					<ul>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], '' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-fullrefreshonsubmitnodependency" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-fullrefreshonsubmitnodependency" value="" autocomplete="off" />
							<label for="wpv-dps-mode-helper-fullrefreshonsubmitnodependency"><?php _e( 'Full page refresh when visitors click on the search button', 'wpv-views' ); ?></label>
						</li>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'fullrefreshonsubmit' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-fullrefreshonsubmit" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-fullrefreshonsubmit" value="fullrefreshonsubmit" autocomplete="off" />
							<label for="wpv-dps-mode-helper-fullrefreshonsubmit"><?php _e( 'Full page refresh when visitors click on the search button with input values auto-updating', 'wpv-views' ); ?></label>
						</li>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'ajaxrefreshonsubmit' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-ajaxrefreshonsubmit" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-ajaxrefreshonsubmit" value="ajaxrefreshonsubmit" autocomplete="off" />
							<label for="wpv-dps-mode-helper-ajaxrefreshonsubmit"><?php _e( 'AJAX results update when visitors click on the search button', 'wpv-views' ); ?></label>
						</li>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'ajaxrefreshonchange' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-ajaxrefreshonchange" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-ajaxrefreshonchange" value="ajaxrefreshonchange" autocomplete="off" />
							<label for="wpv-dps-mode-helper-ajaxrefreshonchange"><?php _e( 'AJAX results update when visitors change any filter values', 'wpv-views' ); ?></label>
						</li>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'custom' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-custom" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-custom" value="custom" autocomplete="off" />
							<label for="wpv-dps-mode-helper-custom"><?php _e( 'Let me choose individual settings manually', 'wpv-views' ); ?></label>
						</li>
					</ul>
					<div class="wpv-advanced-setting js-wpv-ps-settings-custom"<?php if ( $view_settings['dps']['mode_helper'] != 'custom' ) { echo ' style="display:none"'; } ?>>
						<h4><?php _e('When to update the Views results', 'wpv-views'); ?></h4>
						<ul>
							<?php
							if ( ! isset( $view_settings['dps']['ajax_results'] ) ) {
								$view_settings['dps']['ajax_results'] = 'disable';
							}
							?>
							<li>
								<input type="radio" <?php checked( $view_settings['dps']['ajax_results'], 'disable' ); ?> value="disable" id="wpv-dps-ajax-results-disable" class="js-wpv-dps-ajax-results js-wpv-dps-ajax-results-disable" name="wpv-dps-ajax-results" autocomplete="off" />
								<label for="wpv-dps-ajax-results-disable"><?php _e('Update the View results only when clicking on the search button', 'wpv-views'); ?></label>
								<div class="wpv-setting-extra js-wpv-dps-ajax-results-extra js-wpv-dps-ajax-results-extra-disable"<?php if ( $view_settings['dps']['ajax_results'] != 'disable' ) { echo 'style="display:none"'; } ?>>
									<?php
									if ( !isset( $view_settings['dps']['ajax_results_submit'] ) ) {
										$view_settings['dps']['ajax_results_submit'] = 'reload';
									}
									?>
									<p>
									<ul>
										<li>
											<input type="radio" <?php checked( $view_settings['dps']['ajax_results_submit'], 'ajaxed' ); ?> name="wpv-dps-ajax-results-submit" id="wpv-ajax-results-submit-ajaxed" class="js-wpv-ajax-results-submit js-wpv-ajax-results-submit-ajaxed" value="ajaxed" autocomplete="off" />
											<label for="wpv-ajax-results-submit-ajaxed"><?php _e('Update the Views results without reloading the page', 'wpv-views'); ?></label>
										</li>
										<li>
											<input type="radio" <?php checked( $view_settings['dps']['ajax_results_submit'], 'reload' ); ?> name="wpv-dps-ajax-results-submit" id="wpv-ajax-results-submit-reload" class="js-wpv-ajax-results-submit js-wpv-ajax-results-submit-reload" value="reload" autocomplete="off" />
											<label for="wpv-ajax-results-submit-reload"><?php _e('Reload the page to update the View results', 'wpv-views'); ?></label>
										</li>
									</ul>
									</p>
								</div>
							</li>
							<li>
								<input type="radio" <?php checked( $view_settings['dps']['ajax_results'], 'enable' ); ?> value="enable" id="wpv-dps-ajax-results-enable" class="js-wpv-dps-ajax-results js-wpv-dps-ajax-results-enable" name="wpv-dps-ajax-results" autocomplete="off" />
								<label for="wpv-dps-ajax-results-enable"><?php _e('Update the View results every time an input changes', 'wpv-views'); ?></label>
							</li>
						</ul>
						<div class="wpv-ajax-results-details js-wpv-ajax-extra-callbacks"<?php if ( $view_settings['dps']['ajax_results'] != 'enable' && $view_settings['dps']['ajax_results_submit'] == 'reload' ) { echo ' style="display:none"'; } ?>>
							<?php
							$global_enable_manage_history = apply_filters( 'wpv_filter_wpv_global_parametric_search_manage_history_status', true );
							if ( $global_enable_manage_history ) {
								if ( ! isset( $view_settings['dps']['enable_history'] ) ) {
									$view_settings['dps']['enable_history'] = 'enable';
								}
								?>
								<h4><?php _e('Browser history management', 'wpv-views'); ?></h4>
								<p>
									<?php _e('You can automatically adjust the URL of the page every time that the search results are updated:', 'wpv-views'); ?>
								</p>
								<ul>
									<li>
										<input type="radio" <?php checked( $view_settings['dps']['enable_history'], 'disable' ); ?> value="disable" id="wpv-dps-history-disable" class="js-wpv-dps-history js-wpv-dps-history-disable" name="wpv-dps-history" autocomplete="off" />
										<label for="wpv-dps-history-disable"><?php _e('Do not adjust URLs after loading search results', 'wpv-views'); ?></label>
									</li>
									<li>
										<input type="radio" <?php checked( $view_settings['dps']['enable_history'], 'enable' ); ?> value="enable" id="wpv-dps-history-enable" class="js-wpv-dps-history js-wpv-dps-history-enable" name="wpv-dps-history" autocomplete="off" />
										<label for="wpv-dps-history-enable"><?php _e('Update URLs after loading search results', 'wpv-views'); ?></label>
									</li>
								</ul>
								<?php
								}
							?>
							<h4><?php _e('Javascript settings', 'wpv-views'); ?></h4>
							<p>
								<?php _e('You can execute custom javascript functions before and after the View results are updated:', 'wpv-views'); ?>
							</p>
							<ul>
								<li>
									<input type="text" id="wpv-dps-ajax-results-pre-before" class="js-wpv-dps-ajax-results-pre-before" name="wpv-dps-ajax-results-pre-before" value="<?php echo ( isset( $view_settings['dps']['ajax_results_pre_before'] ) ) ? esc_attr( $view_settings['dps']['ajax_results_pre_before'] ) : ''; ?>" autocomplete="off" />
									<label for="wpv-dps-ajax-results-pre-before"><?php _e('will run before getting the new results', 'wpv-views'); ?></label>
								</li>
								<li>
									<input type="text" id="wpv-dps-ajax-results-before" class="js-wpv-dps-ajax-results-before" name="wpv-dps-ajax-results-before" value="<?php echo ( isset( $view_settings['dps']['ajax_results_before'] ) ) ? esc_attr( $view_settings['dps']['ajax_results_before'] ) : ''; ?>" autocomplete="off" />
									<label for="wpv-dps-ajax-results-before"><?php _e('will run after getting the new results, but before updating them', 'wpv-views'); ?></label>
								</li>
								<li>
									<input type="text" id="wpv-dps-ajax-results-after" class="js-wpv-dps-ajax-results-after" name="wpv-dps-ajax-results-after" value="<?php echo ( isset( $view_settings['dps']['ajax_results_after'] ) ) ? esc_attr( $view_settings['dps']['ajax_results_after'] ) : ''; ?>" autocomplete="off" />
									<label for="wpv-dps-ajax-results-after"><?php _e('will run after updating the results', 'wpv-views'); ?></label>
								</li>
							</ul>
						</div>
						<h4><?php _e('Which options to display in the form inputs', 'wpv-views'); ?></h4>
						<?php
						if ( ! isset( $view_settings['dps']['enable_dependency'] ) ) {
							$view_settings['dps']['enable_dependency'] = 'disable';
						}
						?>
						<p class="toolset-alert toolset-alert-info js-wpv-dps-intersection-fail<?php if ( count( $no_intersection ) == 0 ) echo ' hidden'; ?>">
							<?php
							$glue = __( ' and ', 'wpv-views' );
							$no_intersection_text = implode( $glue , $no_intersection );
							echo sprintf( __( 'Your %s filters are using an internal "OR" kind of relationship, and dependant custom search for those filters needs "AND" relationships.', 'wpv-views' ), $no_intersection_text );
							?>
							<br /><br />
							<button class="button-secondary js-make-intersection-filters" data-nonce="<?php echo wp_create_nonce( 'wpv_view_make_intersection_filters' ); ?>" data-cf="<?php echo ( in_array( 'cf', $no_intersection ) ) ? 'true' : 'false'; ?>" data-tax="<?php echo ( in_array( 'tax', $no_intersection ) ) ? 'true' : 'false'; ?>">
								<?php _e('Fix filters relationship', 'wpv-views'); ?>
							</button>
						</p>
						<div class="js-wpv-dps-intersection-ok<?php if ( count( $no_intersection ) > 0 ) echo ' hidden'; ?>">
							<ul>
								<li>
									<input type="radio" <?php checked( $view_settings['dps']['enable_dependency'], 'disable' ); ?> value="disable" id="wpv-dps-enable-disable" class="js-wpv-dps-enable js-wpv-dps-enable-disable" name="wpv-dps-enable" autocomplete="off" />
									<label for="wpv-dps-enable-disable"><?php _e('Always show all values for inputs', 'wpv-views'); ?></label>
								</li>
								<li>
									<input type="radio" <?php checked( $view_settings['dps']['enable_dependency'], 'enable' ); ?> value="enable" id="wpv-dps-enable-enable" class="js-wpv-dps-enable js-wpv-dps-enable-enable" name="wpv-dps-enable" autocomplete="off" />
									<label for="wpv-dps-enable-enable"><?php _e('Show only available options for each input', 'wpv-views'); ?></label>
								</li>
							</ul>
							<div class="wpv-dps-crossed-details js-wpv-dps-crossed-details"<?php if ( $view_settings['dps']['enable_dependency'] != 'enable' ) { echo ' style="display:none"'; } ?>>
								<p>
									<?php _e('Choose if you want to hide or disable irrelevant options for inputs:', 'wpv-views'); ?>
								</p>
								<table class="widefat">
									<thead>
										<tr>
											<th>
												<?php _e('Input type', 'wpv-views'); ?>
											</th>
											<th>
												<?php _e('Disable / Hide', 'wpv-views'); ?>
											</th>
										</tr>
									</thead>
									<tbody>
										<tr class="alternate">
											<?php 
											if ( ! isset( $view_settings['dps']['empty_select'] ) ) {
												$view_settings['dps']['empty_select'] = 'hide';
											}
											?>
											<td>
												<?php _e('Select dropdown', 'wpv-views'); ?>
											</td>
											<td>
												<input type="radio" <?php checked( $view_settings['dps']['empty_select'], 'disable' ); ?> id="wpv-dps-empty-select-disable" value="disable" class="js-wpv-dps-empty-select" name="wpv-dps-empty-select" autocomplete="off" />
												<label for="wpv-dps-empty-select-disable"><?php _e('Disable', 'wpv-views'); ?></label>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<input type="radio" <?php checked( $view_settings['dps']['empty_select'], 'hide' ); ?> id="wpv-dps-empty-select-hide" value="hide" class="js-wpv-dps-empty-select" name="wpv-dps-empty-select" autocomplete="off" />
												<label for="wpv-dps-empty-select-hide"><?php _e('Hide', 'wpv-views'); ?></label>
											</td>
										</tr>
										<tr>
											<?php 
											if ( ! isset( $view_settings['dps']['empty_multi_select'] ) ) {
												$view_settings['dps']['empty_multi_select'] = 'hide';
											}
											?>
											<td>
												<?php _e('Multi-select', 'wpv-views'); ?>
											</td>
											<td>
												<input type="radio" <?php checked( $view_settings['dps']['empty_multi_select'], 'disable' ); ?> id="wpv-dps-empty-multi-select-disable" value="disable" class="js-wpv-dps-empty-multi-select" name="wpv-dps-empty-multi-select" autocomplete="off" />
												<label for="wpv-dps-empty-multi-select-disable"><?php _e('Disable', 'wpv-views'); ?></label>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<input type="radio" <?php checked( $view_settings['dps']['empty_multi_select'], 'hide' ); ?> id="wpv-dps-empty-multi-select-hide" value="hide" class="js-wpv-dps-empty-multi-select" name="wpv-dps-empty-multi-select" autocomplete="off" />
												<label for="wpv-dps-empty-multi-select-hide"><?php _e('Hide', 'wpv-views'); ?></label>
											</td>
										</tr>
										<tr class="alternate">
											<?php 
											if ( ! isset( $view_settings['dps']['empty_radios'] ) ) {
												$view_settings['dps']['empty_radios'] = 'hide';
											}
											?>
											<td>
												<?php _e('Radio inputs', 'wpv-views'); ?>
											</td>
											<td>
												<input type="radio" <?php checked( $view_settings['dps']['empty_radios'], 'disable' ); ?> id="wpv-dps-empty-radios-disable" value="disable" class="js-wpv-dps-empty-radios" name="wpv-dps-empty-radios" autocomplete="off" />
												<label for="wpv-dps-empty-radios-disable"><?php _e('Disable', 'wpv-views'); ?></label>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<input type="radio" <?php checked( $view_settings['dps']['empty_radios'], 'hide' ); ?> id="wpv-dps-empty-radios-hide" value="hide" class="js-wpv-dps-empty-radios" name="wpv-dps-empty-radios" autocomplete="off" />
												<label for="wpv-dps-empty-radios-hide"><?php _e('Hide', 'wpv-views'); ?></label>
											</td>
										</tr>
										<tr>
											<?php 
											if ( ! isset( $view_settings['dps']['empty_checkboxes'] ) ) {
												$view_settings['dps']['empty_checkboxes'] = 'hide';
											}
											?>
											<td>
												<?php _e('Checkboxes', 'wpv-views'); ?>
											</td>
											<td>
												<input type="radio" <?php checked( $view_settings['dps']['empty_checkboxes'], 'disable' ); ?> id="wpv-dps-empty-checkboxes-disable" value="disable" class="js-wpv-dps-empty-checkboxes" name="wpv-dps-empty-checkboxes" autocomplete="off" />
												<label for="wpv-dps-empty-checkboxes-disable"><?php _e('Disable', 'wpv-views'); ?></label>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<input type="radio" <?php checked( $view_settings['dps']['empty_checkboxes'], 'hide' ); ?> id="wpv-dps-empty-checkboxes-hide" value="hide" class="js-wpv-dps-empty-checkboxes" name="wpv-dps-empty-checkboxes" autocomplete="off" />
												<label for="wpv-dps-empty-checkboxes-hide"><?php _e('Hide', 'wpv-views'); ?></label>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div><!-- end .js-wpv-dps-settings -->
			<span class="update-action-wrap auto-update js-wpv-update-action-wrap">
				<span class="js-wpv-message-container"></span>
				<input type="hidden" data-success="<?php echo esc_attr( __('Updated', 'wpv-views') ); ?>" data-unsaved="<?php echo esc_attr( __('Not saved', 'wpv-views') ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_dps_nonce' ); ?>" class="js-wpv-filter-dps-update">
			</span>
		</div>
		<?php
	}

	static function wpv_editor_section_filter_editor( $view_settings, $view_id ) {
		$is_section_hidden = false;
		if ( 
			isset( $view_settings['sections-show-hide'] )
			&& isset( $view_settings['sections-show-hide']['filter-extra'] )
			&& 'off' == $view_settings['sections-show-hide']['filter-extra'] )
		{
			$is_section_hidden = true;
		}
		$hidden_class = $is_section_hidden ? 'hidden' : '';

		/* An additional class js-wpv-filter-extra-section was added to the container div, so we can be sure we can
		 * distinguish it in JS.
		 *
		 * Since 1.7 we're showing the 'content' (Output editor, see add_view_content()) section on View
		 * edit page at the same time as this one, so they have to share the "js-wpv-settings-filter-extra" class (because
		 * in Screen options we're changing visibility of elements with "js-wpv-settings-{$section_name}").
		 *
		 * Since 2.1 this relationship is broken, but we are using the new classname broadly.
		 *
		 * In case you need to select this particular element in JS, please use the "js-wpv-filter-extra-section" class,
		 * which is unique.
		 */ 
		
		$section_help_pointer = WPV_Admin_Messages::edit_section_help_pointer( 'filters_html_css_js' );
		
		$view_settings['filter_meta_html'] = ( isset( $view_settings['filter_meta_html'] ) && ! empty( $view_settings['filter_meta_html'] ) ) ? $view_settings['filter_meta_html'] : "[wpv-filter-start hide=\"false\"]\n[wpv-filter-controls][/wpv-filter-controls]\n[wpv-filter-end]";
		?>
		<div class="wpv-setting-container wpv-setting-container-horizontal wpv-settings-filter-markup js-wpv-settings-filter-extra js-wpv-filter-extra-section <?php echo $hidden_class; ?>">

			<div class="wpv-settings-header">
				<h2>
					<?php _e( 'Search and Pagination', 'wpv-views' ) ?>
					<i class="icon-question-sign fa fa-question-circle js-display-tooltip" 
						data-header="<?php echo esc_attr( $section_help_pointer['title'] ); ?>" 
						data-content="<?php echo esc_attr( $section_help_pointer['content'] ); ?>">
					</i>
				</h2>
			</div>
			<?php
			$listing	= 'posts';
			$purpose	= 'full';
			$mode		= ( isset( $view_settings['view-query-mode'] ) && $view_settings['view-query-mode'] != 'normal' ) ? 'archive' : 'normal';
			
			if ( 
				$mode == 'normal' 
				&& isset( $view_settings['query_type'][0] ) 
			) {
				$listing = $view_settings['query_type'][0];
			}
			if ( isset( $view_settings['view_purpose'] ) ) {
				$purpose = $view_settings['view_purpose'];
			}
			
			$controls_per_kind = wpv_count_filter_controls( $view_settings );
			if ( 
				isset( $controls_per_kind['missing'] ) 
				&& is_array( $controls_per_kind['missing'] ) 
				&& ! empty( $controls_per_kind['missing'] ) 
			) {
			?>
			<div class="toolset-help js-wpv-missing-filter-container"<?php echo $listing == 'posts' ? '' : ' style="display:none"'; ?>>
				<div class="toolset-help-content">
					<?php
					_e( 'This View has some query filters that are missing from the form. Maybe you have removed them:', 'wpv-views' );
					?>
					<ul class="js-wpv-filter-missing">
					<?php
					foreach ( $controls_per_kind['missing'] as $missed ) {
						?>
						<li class="js-wpv-missing-filter" data-type="<?php echo esc_attr( $missed['type'] ); ?>" data-name="<?php echo esc_attr( $missed['name'] ); ?>">
							<?php
							echo sprintf( __( 'Filter by <strong>%s</strong>', 'wpv-views' ), $missed['name'] );
							?>
						</li>
						<?php
					}
					?>
					</ul>
					<?php
					_e( 'Can they also be removed from the query filtering?', 'wpv-views' );
					?>
					<p>
						<button class="button-primary js-wpv-filter-missing-delete"><?php _e( 'Yes (recommended)', 'wpv-views' ); ?></button> <button class="button-secondary js-wpv-filter-missing-close"><?php _e( 'No', 'wpv-views' ); ?></button>
					</p>
				</div>
				<div class="toolset-help-sidebar">
					<div class="toolset-help-sidebar-ico"></div>
				</div>
			</div>
			<?php
			} else {
			?>
			<div class="toolset-help js-wpv-missing-filter-container" style="display:none"></div>
			<?php
			}

			$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'] + $controls_per_kind['search'];
			?>
            <!-- Deprecating the "No filters container" as we follow different workflow now. -->
            <!-- todo: Remove the below div along with all the related functionality. -->
            <div class="toolset-alert js-wpv-no-filters-container-deprecated"<?php if ( $listing == 'posts' && $purpose == 'parametric' && false ) { if ( $controls_count != 0 ) { echo ' style="display:none"'; } } else { echo ' style="display:none"'; } ?>>
				<p>
					<?php _e('Remember to add filters here. Right now, this custom search has no filter items.', 'wpv-views'); ?>
				</p>
			</div>

			<div class="wpv-setting">

				<div class="js-error-container js-wpv-parametric-error-container"></div>
				<div class="js-wpv-toolset-messages"></div>
				<div class="code-editor js-code-editor filter-html-editor" data-name="filter-html-editor" >
					<div class="code-editor-toolbar js-code-editor-toolbar">
						<ul class="js-wpv-filter-edit-toolbar">
							<?php
							/**
							 * Action to include the parametric search buttons
							 *
							 * @param string    $editor_id      The id of the editor where we want to include the parametric search buttons.
                             * @param array     $view_settings  The View settings array.
                             * @param string    $listing        The View listing type.
                             * @param string    $purpose        The purpose of the View.
                             *
							 * @since unknown
							 * @since 2.4.1     Added 3 more argument in order to support the new workflow in the case of parametric search
							 *                  View purpose.
							 */
							do_action( 'wpv_parametric_search_buttons', 'wpv_filter_meta_html_content', $view_settings, $listing, $purpose );
							do_action( 'wpv_views_fields_button', 'wpv_filter_meta_html_content' );
							if ( 'normal' == $mode ) {
								?>
								<li class="js-wpv-editor-pagination-button-wrapper">
									<button class="button-secondary js-code-editor-toolbar-button js-wpv-pagination-popup" 
										data-content="wpv_filter_meta_html_content">
										<i class="icon-pagination fa fa-wpv-custom"></i>
										<span class="button-label"><?php _e('Pagination controls','wpv-views'); ?></span>
										<i class="icon-bookmark fa fa-bookmark flow-warning js-wpv-pagination-control-button-incomplete" style="display:none"></i>
									</button>
								</li>
								<?php
							} else if( 'archive' == $mode ) {
								?>
								<li class="js-wpv-archive-editor-pagination-button-wrapper">
									<button class="button-secondary js-code-editor-toolbar-button js-wpv-archive-pagination-popup"
											data-content="wpv_filter_meta_html_content">
										<i class="icon-pagination fa fa-wpv-custom"></i>
										<span class="button-label"><?php _e( 'Pagination controls', 'wpv-views' ); ?></span>
										<i class="icon-bookmark fa fa-bookmark flow-warning js-wpv-pagination-control-button-incomplete" style="display:none"></i>
									</button>
								</li>
								<?php
							}
							?>
							<li class="js-wpv-editor-sorting-button-wrapper js-wpv-toolbar-item-for-posts"<?php if ( $listing != 'posts' ) { echo ' style="display:none;"'; } ?>>
								<button class="button-secondary js-code-editor-toolbar-button js-wpv-sorting-dialog" data-content="wpv_filter_meta_html_content">
									<i class="fa fa-sort"></i>
									<span class="button-label"><?php _e('Sorting controls','wpv-views'); ?></span>
								</button>
							</li>
							<?php
							// Action to add Toolset buttons to the Filter editor
							do_action( 'toolset_action_toolset_editor_toolbar_add_buttons', 'wpv_filter_meta_html_content', 'views' );
							?>
							<li>
								<button class="button-secondary js-code-editor-toolbar-button js-wpv-media-manager" data-id="<?php echo esc_attr( $view_id ); ?>" data-content="wpv_filter_meta_html_content">
									<i class="icon-picture fa fa-picture-o"></i>
									<span class="button-label"><?php _e('Media','wpv-views'); ?></span>
								</button>
							</li>
						</ul>
					</div>
					<textarea cols="30" rows="10" id="wpv_filter_meta_html_content" autocomplete="off" name="_wpv_settings[filter_meta_html]"><?php echo ( isset( $view_settings['filter_meta_html'] ) ) ? esc_textarea( $view_settings['filter_meta_html'] ) : ''; ?></textarea>
					<?php 
					$filter_extra_css	= isset( $view_settings['filter_meta_html_css'] ) ? $view_settings['filter_meta_html_css'] : '';
					$filter_extra_js	= isset( $view_settings['filter_meta_html_js'] ) ? $view_settings['filter_meta_html_js'] : '';
					?>
					<div class="wpv-editor-metadata-toggle js-wpv-editor-metadata-toggle js-wpv-assets-editor-toggle" data-instance="filter-css-editor" data-target="js-wpv-assets-filter-css-editor" data-type="css">
						<span class="wpv-toggle-toggler-icon js-wpv-toggle-toggler-icon">
							<i class="fa fa-caret-down icon-large fa-lg"></i>
						</span>
						<i class="icon-pushpin fa fa-thumb-tack js-wpv-textarea-full" style="<?php echo ( empty( $filter_extra_css ) ) ? 'display:none;' : ''; ?>"></i>
						<strong><?php _e( 'CSS editor', 'wpv-views' ); ?></strong>
					</div>
					<div id="wpv-assets-filter-css-editor" class="wpv-assets-editor hidden js-wpv-assets-filter-css-editor">
						<textarea cols="30" rows="10" id="wpv_filter_meta_html_css" autocomplete="off" name="_wpv_settings[filter_meta_html_css]"><?php echo esc_textarea( $filter_extra_css ); ?></textarea>
					</div>
					
					<div class="wpv-editor-metadata-toggle js-wpv-editor-metadata-toggle js-wpv-assets-editor-toggle" data-instance="filter-js-editor" data-target="js-wpv-assets-filter-js-editor" data-type="js">
						<span class="wpv-toggle-toggler-icon js-wpv-toggle-toggler-icon">
							<i class="fa fa-caret-down icon-large fa-lg"></i>
						</span>
						<i class="icon-pushpin fa fa-thumb-tack js-wpv-textarea-full" style="<?php echo ( empty( $filter_extra_js ) ) ? 'display:none;' : ''; ?>"></i>
						<strong><?php _e( 'JS editor', 'wpv-views' ); ?></strong>
					</div>
					<div id="wpv-assets-filter-js-editor" class="wpv-assets-editor hidden js-wpv-assets-filter-js-editor">
						<div class="code-editor-toolbar js-code-editor-toolbar">
							<ul class="js-wpv-filter-js-edit-toolbar">
								<li class="js-wpv-views-frontend-events-wrapper">
									<button class="button button-secondary button-small js-code-editor-toolbar-button js-wpv-views-frontend-events-popup" data-content="wpv_filter_meta_html_js">
										<i class="icon-fire fa fa-fire"></i>
										<span class="button-label"><?php _e('Frontend events','wpv-views'); ?></span>
									</button>
								</li>
							</ul>
						</div>
						<textarea cols="30" rows="10" id="wpv_filter_meta_html_js" autocomplete="off" name="_wpv_settings[filter_meta_html_js]"><?php echo esc_textarea( $filter_extra_js ); ?></textarea>
					</div>
					<?php
					wpv_formatting_help_filter();
					?>
				</div>
				<p class="update-button-wrap js-wpv-update-button-wrap">
					<span class="js-wpv-message-container"></span>
					<button data-success="<?php echo esc_attr( __('Updated', 'wpv-views') ); ?>" data-unsaved="<?php echo esc_attr( __('Not saved', 'wpv-views') ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_extra_nonce' ); ?>" class="js-wpv-filter-extra-update button-secondary" disabled="disabled"><?php _e('Update', 'wpv-views'); ?></button>
				</p>
			</div>

		</div>
	<?php
	}
	
	static function wpv_get_parametric_search_hints() {
		// Authentication
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if ( 
			! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'wpv_view_filters_nonce' ) 
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1 
		) {
			$data = array(
				'type' => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		$view_id = (int) $_POST['id'];
		$parametric_search_hints = wpv_get_parametric_search_hints_data( $view_id );

		// Indicate success.
		$data = array(
			'id'			=> $view_id,
			'parametric'	=> $parametric_search_hints
		);
		wp_send_json_success( $data );
	}
	
	static function wpv_remove_filter_missing_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if ( 
			! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'wpv_view_filters_nonce' ) 
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_POST['id'] )
			|| ! is_numeric( $_POST['id'] )
			|| intval( $_POST['id'] ) < 1 
		) {
			$data = array(
				'type' => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		$view_id = (int) $_POST['id'];
		$view_array = get_post_meta( $view_id, '_wpv_settings', true );
		
		$filters_to_delete = array();
		if (
			isset( $_POST['filters'] ) 
			&& is_array( $_POST['filters'] )
		) {
			$filters_to_delete = $_POST['filters'];
		};
		
		$field_filters_to_delete = isset( $filters_to_delete['cf'] ) ? $filters_to_delete['cf'] : array();
		if ( is_array( $field_filters_to_delete ) ) {
			foreach ( $field_filters_to_delete as $field ) {
				$field = sanitize_text_field( $field );
				$to_delete = array(
					'custom-field-' . $field . '_compare',
					'custom-field-' . $field . '_type',
					'custom-field-' . $field . '_value',
					'custom-field-' . $field . '_relationship'
				);
				foreach ( $to_delete as $slug ) {
					if ( isset( $view_array[$slug] ) ) {
						unset( $view_array[$slug] );
					}
				}
			}
		}
		
		$tax_filters_to_delete = isset( $filters_to_delete['tax'] ) ? $filters_to_delete['tax'] : array();
		if ( is_array( $tax_filters_to_delete ) ) {
			////// Ported from wpv-filter-category.php's wpv_filter_taxonomy_delete_callback
			// @todo: Consider refactoring in the future.
			foreach ( $tax_filters_to_delete as $taxonomy ) {
				$taxonomy = sanitize_text_field( $taxonomy );
				$to_delete = array(
					'tax_' . $taxonomy . '_relationship',
					'taxonomy-' . $taxonomy . '-attribute-url',
					'taxonomy-' . $taxonomy . '-attribute-url-format',
					'taxonomy-' . $taxonomy . '-attribute-operator',
					'taxonomy-' . $taxonomy . '-framework',
					// Backwards compatibility: 
					// those entries existed in the View settings up until 2.3.2
					'filter_controls_field_name',
					'filter_controls_mode',
					'filter_controls_label',
					'filter_controls_type',
					'filter_controls_values',
					'filter_controls_enable',
					'filter_controls_param'
				);
				if ( 'category' == $taxonomy ) {
					$to_delete[] = 'post_category';
				} else {
					$to_delete[] = 'tax_input_' . $taxonomy;
				}
				foreach ( $to_delete as $index ) {
					if ( isset( $view_array[$index] ) ) {
						unset( $view_array[$index] );
					}
				}
			}
		}
		
		$rel_filters_to_delete = isset( $filters_to_delete['rel'] ) ? $filters_to_delete['rel'] : array();
		if ( 
			is_array( $rel_filters_to_delete ) 
			&& ! empty( $rel_filters_to_delete ) 
		) {
			$to_delete = array(
				'post_relationship_mode',
				'post_relationship_shortcode_attribute',
				'post_relationship_url_parameter',
				'post_relationship_id',
				// Legacy entry that existed until m2m
				'post_relationship_url_tree',
			);
			foreach ( $to_delete as $slug ) {
				if ( isset( $view_array[$slug] ) ) {
					unset( $view_array[$slug] );
				}
			}
		}
		
		$search_filters_to_delete = isset( $filters_to_delete['search'] ) ? $filters_to_delete['search'] : array();
		if ( 
			is_array( $search_filters_to_delete ) 
			&& ! empty( $search_filters_to_delete ) 
		) {
			$to_delete = array(
				'search_mode',
				'post_search_value',
				'post_search_content',
			);
			foreach ( $to_delete as $slug ) {
				if ( isset( $view_array[$slug] ) ) {
					unset( $view_array[$slug] );
				}
			}
		}
		update_post_meta( $view_id, '_wpv_settings', $view_array );
		do_action( 'wpv_action_wpv_save_item', $view_id );
		// Filters list
		ob_start();
		wpv_display_filters_list( $view_array );
		$filters_list = ob_get_contents();
		ob_end_clean();
		
		$parametric_search_hints = wpv_get_parametric_search_hints_data( $view_id );
		
		$data = array(
			'id'					=> $view_id,
			'updated_filters_list'	=> $filters_list,
			'parametric'			=> $parametric_search_hints,
			'message'				=> __( 'Success', 'wpv-views' )
		);
		wp_send_json_success( $data );
	}

	/**
	 * Function to add the parametric search buttons to the selected editor
	 *
	 * @param string    $editor_id      The id of the editor where we want to include the parametric search buttons.
	 * @param array     $view_settings  The View settings array.
	 * @param string    $listing        The View listing type.
	 * @param string    $purpose        The purpose of the View.
	 *
	 * @since unknown
	 * @since 2.4.1     Added 3 more argument in order to support the new workflow in the case of parametric search
	 *                  View purpose.
	 */
	static function wpv_add_parametric_search_buttons_to_editor( $editor_id, $view_settings = array(), $listing = 'posts', $purpose = 'full' ) {

		/**
		 * Filter to determine whether the filter editor content matches the default or not.
		 *
		 * @param array     $view_settings  The View settings.
         * @param integer   $view_id        The View id (optional).
         *
         * @since 2.4.1
		 */
	    $has_default_filter_output = apply_filters( 'wpv_filter_wpv_has_default_filter_editor_content', false, $view_settings );

		?>
		<li>
			<button class="button-secondary js-code-editor-toolbar-button js-wpv-parametric-search-filter-create" data-editor="<?php echo esc_attr( $editor_id ); ?>">
                <i class="icon-create fa fa-filter fa fa-parametric_filter_create"></i><?php _e( 'New filter', 'wpv-views' ); ?>
            </button>
		</li>

        <?php
		if ( $listing == 'posts' && $purpose == 'parametric' && $has_default_filter_output ) {
            ?>
            <li>
                <a href="#" class="js-wpv-filter-editor-unlock" style="display:inline-block;height:28px;line-height:26px;">
                    <?php echo esc_html( __( 'Unlock editor', 'wpv-views' ) ); ?>
                </a>
            </li>
            <?php
		}
        ?>

		<li>
			<button class="button-secondary js-code-editor-toolbar-button js-wpv-parametric-search-filter-edit" data-editor="<?php echo esc_attr( $editor_id ); ?>">
				<i class="icon-edit fa fa-pencil-square-o fa fa-parametric_filter_edit"></i><?php _e( 'Edit filter', 'wpv-views' ); ?>
			</button>
		</li>
		<li>
			<button class="button-secondary js-code-editor-toolbar-button js-wpv-parametric-search-text-filter-manage" data-editor="<?php echo esc_attr( $editor_id ); ?>">
				<i class="icon-search fa fa-search"></i><?php _e( 'Text search', 'wpv-views' ); ?>
				<i class="icon-bookmark fa fa-bookmark flow-complete js-parametric-search-text-filter-button-complete" style="display:none"></i>
				<i class="icon-bookmark fa fa-bookmark flow-warning js-parametric-search-text-filter-button-filter-missing" style="display:none"></i>
			</button>
		</li>
		<li>
			<button class="button-secondary js-code-editor-toolbar-button js-wpv-parametric-search-submit-add" data-editor="<?php echo esc_attr( $editor_id ); ?>">
				<i class="icon-forward fa fa-forward"></i><?php _e( 'Submit button', 'wpv-views' ); ?>
				<i class="icon-bookmark fa fa-bookmark flow-complete js-wpv-parametric-search-submit-button-complete" style="display:none"></i>
				<i class="icon-bookmark fa fa-bookmark flow-warning js-wpv-parametric-search-submit-button-incomplete" style="display:none"></i>
				<i class="icon-bookmark fa fa-bookmark flow-info js-wpv-parametric-search-submit-button-irrelevant" style="display:none"></i>
			</button>
		</li>
		<li>
			<button class="button-secondary js-code-editor-toolbar-button js-wpv-parametric-search-reset-add" data-editor="<?php echo esc_attr( $editor_id ); ?>">
				<i class="icon-recycle fa fa-recycle"></i><?php _e( 'Reset button', 'wpv-views' ); ?>
				<i class="icon-bookmark fa fa-bookmark flow-complete js-wpv-parametric-search-reset-button-complete" style="display:none"></i>
			</button>
		</li>
		<li>
			<button class="button-secondary js-code-editor-toolbar-button js-wpv-parametric-search-spinner-add" data-editor="<?php echo esc_attr( $editor_id ); ?>">
				<i class="icon-spinner fa fa-spinner"></i><?php _e( 'Spinner graphics', 'wpv-views' ); ?>
				<i class="icon-bookmark fa fa-bookmark flow-complete js-wpv-parametric-search-spinner-button-complete" style="display:none"></i>
			</button>
		</li>
		<?php
	}
	
	static function wpv_parametric_search_filter_create_dialog() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		if (
			! isset( $_GET["id"] )
			|| ! is_numeric( $_GET["id"] )
			|| intval( $_GET['id'] ) < 1 
		) {
			$data = array(
				'type' => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		
		$view_id = (int) $_GET["id"];
		/*
		if ( 
			! isset( $_GET["wpnonce"] )
			|| ! wp_verify_nonce( $_GET["wpnonce"], 'wpv_view_filters_add_filter_nonce' ) 
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		*/
		$view_settings	= get_post_meta( $view_id, '_wpv_settings', true );
		$view_query_type = apply_filters( 'wpv_filter_wpv_get_query_type', 'posts', $view_id );
		
		$custom_search_shortcodes = apply_filters( 'wpv_filter_wpv_get_form_filters_shortcodes', array() );
		$custom_search_filters = array();
		
		foreach ( $custom_search_shortcodes as $search_shortcode_key => $search_shortcode_data ) {
			if ( $search_shortcode_data['query_type_target'] != $view_query_type ) {
				return;
			}
			if ( isset( $search_shortcode_data['custom_search_filter_subgroups'] ) ) {
				foreach( $search_shortcode_data['custom_search_filter_subgroups'] as $search_shortcode_data_subgroup ) {
					if ( 
						isset( $search_shortcode_data_subgroup['custom_search_filter_group'] ) 
						&& isset( $search_shortcode_data_subgroup['custom_search_filter_items'] )
					) {
						if ( ! isset( $custom_search_filters[ $search_shortcode_data_subgroup['custom_search_filter_group'] ] ) ) {
							$custom_search_filters[ $search_shortcode_data_subgroup['custom_search_filter_group'] ] = array();
						}
						foreach ( $search_shortcode_data_subgroup['custom_search_filter_items'] as $search_shortcode_data_item ) {
							$custom_search_filters[ $search_shortcode_data_subgroup['custom_search_filter_group'] ][] = array(
								'shortcode'		=> $search_shortcode_key,
								'name'			=> $search_shortcode_data_item['name'],
								'params'		=> $search_shortcode_data_item['params'],
								'present'		=> $search_shortcode_data_item['present']
							);
						}
					}
				}
			} else if ( 
				isset( $search_shortcode_data['custom_search_filter_group'] ) 
				&& isset( $search_shortcode_data['custom_search_filter_items'] )
			) {
				if ( ! isset( $custom_search_filters[ $search_shortcode_data['custom_search_filter_group'] ] ) ) {
					$custom_search_filters[ $search_shortcode_data['custom_search_filter_group'] ] = array();
				}
				foreach ( $search_shortcode_data['custom_search_filter_items'] as $search_shortcode_data_item ) {
					$custom_search_filters[ $search_shortcode_data['custom_search_filter_group'] ][] = array(
						'shortcode'		=> $search_shortcode_key,
						'name'			=> $search_shortcode_data_item['name'],
						'params'		=> $search_shortcode_data_item['params'],
						'present'		=> $search_shortcode_data_item['present']
						
					);
				}
			}
		}
		ob_start();
		?>
		<div class="wpv-dialog wpv-dialog-parametric-search-filter-dialog">
			<div class="searchbar">
				<label for="searchbar-input-for-parametric-search"><?php echo __( 'Search:', 'wpv-views' ); ?></label>
				<input id="searchbar-input-for-parametric-search" class="search_field" onkeyup="wpv_on_search_filter(this)" type="text" />
			</div>
			<?php
			foreach ( $custom_search_filters as $filter_group => $filter_items ) {
				echo '<div class="group">';
				echo '<h4 class="group-title">' . esc_html( $filter_group ) . '</h4>';
				foreach ( $filter_items as $filter_item_key => $filter_item_data ) {
					echo '<button class="item button button-small js-wpv-parametric-search-filter-item-dialog"'
						. ' onclick="WPViews.shortcodes_gui.wpv_insert_shortcode_dialog_open({ shortcode: \'' . esc_attr( $filter_item_data['shortcode'] ) . '\', title: \'' . esc_attr( $filter_item_data['name'] ) . '\', params: ' . esc_attr( json_encode( $filter_item_data['params'] ) ) . ' }); return false;"'
						. ' ' . disabled( isset( $view_settings[ $filter_item_data['present'] ] ), true, false )
						. ' style="margin:5px 5px 0 0;font-size:11px;"'
						. '>'
						. esc_html( $filter_item_data['name'] )
						. '</button>';
				}
				echo '</div>';
			}
			echo '<div class="group js-wpv-parametric-search-filter-group-native-postmeta">';
			echo '<h4 class="group-title">' . __( 'Post fields', 'wpv-views' ) . '</h4>';
			echo '<button class="item button button-small js-wpv-parametric-search-filter-load-group-native-postmeta">'
				. __( 'Load non-Types custom fields', 'wpv-views' ) 
				. '</button>';
			echo '</div>';
			?>
		</div>
		<?php
		$dialog = ob_get_clean();
		$data = array(
			'dialog' => $dialog
		);
		wp_send_json_success( $data );
	}
	
	static function wpv_custom_search_define_query_filter() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$data = array(
				'type' => 'capability',
				'message' => __( 'You do not have permissions for that.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		/*
		if ( 
			! isset( $_POST["wpnonce"] )
			|| ! wp_verify_nonce( $_POST["wpnonce"], 'wpv_view_filter_post_author_nonce' ) 
		) {
			$data = array(
				'type' => 'nonce',
				'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		*/
		if (
			! isset( $_POST["id"] )
			|| ! is_numeric( $_POST["id"] )
			|| intval( $_POST['id'] ) < 1 
		) {
			$data = array(
				'type' => 'id',
				'message' => __( 'Wrong or missing ID.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
		
		$view_id	= (int) $_POST['id'];
		$shortcode	= isset( $_POST['shortcode'] ) ? sanitize_text_field( $_POST['shortcode'] ) : '';
		$attributes	= ( isset( $_POST['attributes'] ) && is_array( $_POST['attributes'] ) ) ? array_map( 'sanitize_text_field', $_POST['attributes'] ) : array();
		$attributes_raw	= ( isset( $_POST['attributes_raw'] ) && is_array( $_POST['attributes_raw'] ) ) ? array_map( 'sanitize_text_field', $_POST['attributes_raw'] ) : array();
		
		$expected_shortcodes = apply_filters( 'wpv_filter_wpv_get_form_filters_shortcodes', array() );
		
		if (
			isset( $expected_shortcodes[ $shortcode ] ) 
			&& isset( $expected_shortcodes[ $shortcode ]['query_filter_define_callback'] ) 
			&& is_callable( $expected_shortcodes[ $shortcode ]['query_filter_define_callback'] )
		) {
			call_user_func( $expected_shortcodes[ $shortcode ]['query_filter_define_callback'], $view_id, $shortcode, $attributes, $attributes_raw );
			$filters_list	= '';
			$view_array		= get_post_meta( $view_id, '_wpv_settings', true );
			ob_start();
			wpv_display_filters_list( $view_array );
			$filters_list = ob_get_contents();
			ob_end_clean();
			
			$data = array(
				'id'			=> $view_id,
				'message'		=> __( 'Query filter saved', 'wpv-views' ),
				'query_filters'	=> $filters_list
			);
			wp_send_json_success( $data );
		} else {
			$data = array(
				'type'		=> 'generic',
				'message'	=> __( 'Wrong or missing shortcode.', 'wpv-views' )
			);
			wp_send_json_error( $data );
		}
	}

}

add_action( 'wp_ajax_wpv_filter_update_dps_settings', 'wpv_filter_update_dps_settings' );

function wpv_filter_update_dps_settings() {

    // Authentication
	if ( ! current_user_can( 'manage_options' ) ) {
		$data = array(
			'type' => 'capability',
			'message' => __( 'You do not have permissions for that.', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}
	if ( 
		! isset( $_POST["wpnonce"] )
		|| ! wp_verify_nonce( $_POST["wpnonce"], 'wpv_view_filter_dps_nonce' ) 
	) {
		$data = array(
			'type' => 'nonce',
			'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}
    $view_id = (int) wpv_getpost( 'id', 0 );
    $view = WPV_View::get_instance( $view_id );
	if ( $view_id < 1 || null == $view ) {
		$data = array(
			'type' => 'id',
			'message' => __( 'Wrong or missing ID.', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}
	$view_array = get_post_meta( $view_id, '_wpv_settings', true );
	if ( ! isset( $view_array['dps'] ) ) {
		$view_array['dps'] = array();
	}
	if ( isset( $_POST['dpsdata'] ) ) {
		$passed_data = wp_parse_args( $_POST['dpsdata'] );
		// Helper mode
		if ( 
			isset( $passed_data['wpv-dps-mode-helper'] ) 
			&& in_array( $passed_data['wpv-dps-mode-helper'], array( 'fullrefreshonsubmit', 'ajaxrefreshonsubmit', 'ajaxrefreshonchange', 'custom' ) ) 
		) {
			$view_array['dps']['mode_helper'] = $passed_data['wpv-dps-mode-helper'];
		} else {
			$view_array['dps']['mode_helper'] = '';
		}
		// AJAX update View results
		if ( 
			isset( $passed_data['wpv-dps-ajax-results'] ) 
			&& $passed_data['wpv-dps-ajax-results'] == 'enable' 
		) {
			$view_array['dps']['ajax_results'] = 'enable';
		} else {
			$view_array['dps']['ajax_results'] = 'disable';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-pre-before'] ) ) {
			$view_array['dps']['ajax_results_pre_before'] = esc_attr( $passed_data['wpv-dps-ajax-results-pre-before'] );
		} else {
			$view_array['dps']['ajax_results_pre_before'] = '';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-before'] ) ) {
			$view_array['dps']['ajax_results_before'] = esc_attr( $passed_data['wpv-dps-ajax-results-before'] );
		} else {
			$view_array['dps']['ajax_results_before'] = '';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-after'] ) ) {
			$view_array['dps']['ajax_results_after'] = esc_attr( $passed_data['wpv-dps-ajax-results-after'] );
		} else {
			$view_array['dps']['ajax_results_after'] = '';
		}
		if ( 
			isset( $passed_data['wpv-dps-ajax-results-submit'] ) 
			&& in_array( $passed_data['wpv-dps-ajax-results-submit'], array( 'ajaxed', 'reload' ) ) 
		) {
			$view_array['dps']['ajax_results_submit'] = $passed_data['wpv-dps-ajax-results-submit'];
		} else {
			$view_array['dps']['ajax_results_submit'] = 'reload';
		}
		// Enable dependency and input defaults
		if ( 
			isset( $passed_data['wpv-dps-enable'] ) 
			&& $passed_data['wpv-dps-enable'] == 'disable' 
		) {
			$view_array['dps']['enable_dependency'] = 'disable';
		} else {
			$view_array['dps']['enable_dependency'] = 'enable';
		}
		if ( 
			isset( $passed_data['wpv-dps-history'] ) 
			&& $passed_data['wpv-dps-history'] == 'disable' 
		) {
			$view_array['dps']['enable_history'] = 'disable';
		} else {
			$view_array['dps']['enable_history'] = 'enable';
		}
		if ( 
			isset( $passed_data['wpv-dps-empty-select'] ) 
			&& $passed_data['wpv-dps-empty-select'] == 'disable' 
		) {
			$view_array['dps']['empty_select'] = 'disable';
		} else {
			$view_array['dps']['empty_select'] = 'hide';
		}
		if ( 
			isset( $passed_data['wpv-dps-empty-multi-select'] ) 
			&& $passed_data['wpv-dps-empty-multi-select'] == 'disable' 
		) {
			$view_array['dps']['empty_multi_select'] = 'disable';
		} else {
			$view_array['dps']['empty_multi_select'] = 'hide';
		}
		if ( 
			isset( $passed_data['wpv-dps-empty-radios'] ) 
			&& $passed_data['wpv-dps-empty-radios'] == 'disable' 
		) {
			$view_array['dps']['empty_radios'] = 'disable';
		} else {
			$view_array['dps']['empty_radios'] = 'hide';
		}
		if ( 
			isset( $passed_data['wpv-dps-empty-checkboxes'] ) 
			&& $passed_data['wpv-dps-empty-checkboxes'] == 'disable' 
		) {
			$view_array['dps']['empty_checkboxes'] = 'disable';
		} else {
			$view_array['dps']['empty_checkboxes'] = 'hide';
		}
		/*
		Spinners - DEPRECATED, so we might want to clean; keep it for now for backwards compatibility
		$view_array['dps']['spinner'] = 'none';
		$view_array['dps']['spinner_image_uploaded'] = '';
		$view_array['dps']['spinner_image'] = '';
		*/
	} else {
		
	}
	update_post_meta( $view_id, '_wpv_settings', $view_array );
	do_action( 'wpv_action_wpv_save_item', $view_id );
	$data = array(
		'id' => $view_id,
		'message' => __( 'Parametric Search Settings saved', 'wpv-views' )
	);
	wp_send_json_success( $data );
}

// @todo add proper wp_send_json_error/wp_send_json_success management here

add_action( 'wp_ajax_wpv_get_dps_related', 'wpv_get_dps_related' );

function wpv_get_dps_related() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( "Security check" );
	}
	if ( ! wp_verify_nonce( $_POST["nonce"], 'wpv_view_edit_general_nonce' ) ) {
		die( "Security check" );
	}
	$return_result = array(
		'existence'		=> '',
		'intersection'	=> '',
		'missing'		=> ''
	);
	if ( isset( $_POST['id'] ) ) {
		global $WP_Views;
		$view_id = (int) $_POST['id'];
		$view_settings = $WP_Views->get_view_settings( $view_id );
		$controls_per_kind = wpv_count_filter_controls( $view_settings );
		$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'] + $controls_per_kind['search'];
		$no_intersection = array();				
		if ( 
			isset( $controls_per_kind['cf'] ) 
			&& $controls_per_kind['cf'] > 1 
			&& (
				! isset( $view_settings['custom_fields_relationship'] ) 
				|| $view_settings['custom_fields_relationship'] != 'AND' 
			) 
		) {
			$no_intersection[] = __( 'custom field', 'wpv-views' );
		}
		if ( 
			isset( $controls_per_kind['tax'] ) 
			&& $controls_per_kind['tax'] > 1 && ( 
				! isset( $view_settings['taxonomy_relationship'] ) 
				|| $view_settings['taxonomy_relationship'] != 'AND' 
			) 
		) {
			$no_intersection[] = __( 'taxonomy', 'wpv-views' );
		}
		// Existence
		if ( $controls_count == 0 ) {
			$return_result['existence'] = '<p>' . __('Remember to add filters here. Right now, this custom search has no filter items.', 'wpv-views') . '</p>';
		}
		// Intersection
		if ( count( $no_intersection ) > 0 ) {
			$glue = __( ' and ', 'wpv-views' );
			$no_intersection_text = implode( $glue , $no_intersection );
			$return_result['intersection'] = sprintf( __( 'Your %s filters are using an internal "OR" kind of relationship, and dependant custom search for those filters needs "AND" relationships.', 'wpv-views' ), $no_intersection_text );
			$return_result['intersection'] .= '<br /><br />';
			$return_result['intersection'] .= '<button class="button-secondary js-make-intersection-filters" data-nonce="' . wp_create_nonce( 'wpv_view_make_intersection_filters' ) .'"';
			if ( in_array( 'cf', $no_intersection ) ) {
				$return_result['intersection'] .= ' data-cf="true"';
			} else {
				$return_result['intersection'] .= ' data-cf="false"';
			}
			if ( in_array( 'tax', $no_intersection ) ) {
				$return_result['intersection'] .= ' data-tax="true"';
			} else {
				$return_result['intersection'] .= ' data-tax="false"';
			}
			$return_result['intersection'] .= '>';
				$return_result['intersection'] .= __('Fix filters relationship', 'wpv-views');
			$return_result['intersection'] .= '</button>';
		}
		// Missing
		if ( 
			isset( $controls_per_kind['missing'] ) 
			&& is_array( $controls_per_kind['missing'] ) 
			&& ! empty( $controls_per_kind['missing'] ) 
		) {
			$return_result['missing'] = '<div class="toolset-help-content">';
			$return_result['missing'] .= __( 'This View has some query filters that are missing from the form. Maybe you have removed them:', 'wpv-views' );
			$return_result['missing'] .= '<ul class="js-wpv-filter-missing">';
			foreach ( $controls_per_kind['missing'] as $missed ) {
				$return_result['missing'] .= '<li class="js-wpv-missing-filter" data-type="' . $missed['type'] . '" data-name="' . $missed['name'] . '">';
				$return_result['missing'] .= sprintf( __( 'Filter by <strong>%s</strong>', 'wpv-views' ), $missed['name'] );
				$return_result['missing'] .= '</li>';
			}
			$return_result['missing'] .= '</ul>';
			$return_result['missing'] .= __( 'Can they also be removed from the query filtering?', 'wpv-views' );
			$return_result['missing'] .= '<p>';
				$return_result['missing'] .= '<button class="button button-primary js-wpv-filter-missing-delete">' . __( 'Yes (recommended)', 'wpv-views' ) . '</button> <button class="button-secondary js-wpv-filter-missing-close">' . __( 'No', 'wpv-views' ) . '</button>';
			$return_result['missing'] .= '</p>';
			$return_result['missing'] .= '</div>';
			$return_result['missing'] .= '<div class="toolset-help-sidebar"><div class="toolset-help-sidebar-ico"></div></div>';
		}
	}
	echo json_encode( $return_result );
	die();
}

function wpv_get_parametric_search_hints_data( $view_id ) {
	$return_result = array(
		'existence'		=> '',
		'intersection'	=> '',
		'missing'		=> ''
	);
	$view_settings		= apply_filters( 'wpv_filter_wpv_get_object_settings', array(), $view_id );
	$controls_per_kind	= wpv_count_filter_controls( $view_settings );
	$controls_count		= $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'] + $controls_per_kind['search'];
	$no_intersection	= array();		
	
	// Existence
	if ( $controls_count == 0 ) {
		$return_result['existence'] = '<p>' . __('Remember to add filters here. Right now, this custom search has no filter items.', 'wpv-views') . '</p>';
	}
	
	// Intersection
	if ( 
		isset( $controls_per_kind['cf'] ) 
		&& $controls_per_kind['cf'] > 1 
		&& (
			! isset( $view_settings['custom_fields_relationship'] ) 
			|| $view_settings['custom_fields_relationship'] != 'AND' 
		) 
	) {
		$no_intersection[] = __( 'custom field', 'wpv-views' );
	}
	if ( 
		isset( $controls_per_kind['tax'] ) 
		&& $controls_per_kind['tax'] > 1 && ( 
			! isset( $view_settings['taxonomy_relationship'] ) 
			|| $view_settings['taxonomy_relationship'] != 'AND' 
		) 
	) {
		$no_intersection[] = __( 'taxonomy', 'wpv-views' );
	}
	if ( count( $no_intersection ) > 0 ) {
		$glue = __( ' and ', 'wpv-views' );
		$no_intersection_text = implode( $glue , $no_intersection );
		$return_result['intersection'] = sprintf( __( 'Your %s filters are using an internal "OR" kind of relationship, and dependant parametric search for those filters needs "AND" relationships.', 'wpv-views' ), $no_intersection_text );
		$return_result['intersection'] .= '<br /><br />';
		$return_result['intersection'] .= '<button class="button-secondary js-make-intersection-filters" data-nonce="' . wp_create_nonce( 'wpv_view_make_intersection_filters' ) .'"';
		if ( in_array( 'cf', $no_intersection ) ) {
			$return_result['intersection'] .= ' data-cf="true"';
		} else {
			$return_result['intersection'] .= ' data-cf="false"';
		}
		if ( in_array( 'tax', $no_intersection ) ) {
			$return_result['intersection'] .= ' data-tax="true"';
		} else {
			$return_result['intersection'] .= ' data-tax="false"';
		}
		$return_result['intersection'] .= '>';
			$return_result['intersection'] .= __('Fix filters relationship', 'wpv-views');
		$return_result['intersection'] .= '</button>';
	}
	
	// Missing
	if ( 
		isset( $controls_per_kind['missing'] ) 
		&& is_array( $controls_per_kind['missing'] ) 
		&& ! empty( $controls_per_kind['missing'] ) 
	) {
		$return_result['missing'] = '<div class="toolset-help-content">';
		$return_result['missing'] .= __( 'This View has some query filters that are missing from the form. Maybe you have removed them:', 'wpv-views' );
		$return_result['missing'] .= '<ul class="js-wpv-filter-missing">';
		foreach ( $controls_per_kind['missing'] as $missed ) {
			$return_result['missing'] .= '<li class="js-wpv-missing-filter" data-type="' . $missed['type'] . '" data-name="' . $missed['name'] . '">';
			$return_result['missing'] .= sprintf( __( 'Filter by <strong>%s</strong>', 'wpv-views' ), $missed['name'] );
			$return_result['missing'] .= '</li>';
		}
		$return_result['missing'] .= '</ul>';
		$return_result['missing'] .= __( 'Can they also be removed from the query filtering?', 'wpv-views' );
		$return_result['missing'] .= '<p>';
			$return_result['missing'] .= '<button class="button-primary js-wpv-filter-missing-delete">' . __( 'Yes (recommended)', 'wpv-views' ) . '</button> <button class="button-secondary js-wpv-filter-missing-close">' . __( 'No', 'wpv-views' ) . '</button>';
		$return_result['missing'] .= '</p>';
		$return_result['missing'] .= '</div>';
		$return_result['missing'] .= '<div class="toolset-help-sidebar"><div class="toolset-help-sidebar-ico"></div></div>';
	}
		
	return $return_result;
}

// Filter Extra save callback function

add_action( 'wp_ajax_wpv_update_filter_extra', 'wpv_update_filter_extra_callback' );

function wpv_update_filter_extra_callback() {
    // Authentication
	if ( ! current_user_can( 'manage_options' ) ) {
		$data = array(
			'type' => 'capability',
			'message' => __( 'You do not have permissions for that.', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}
	if ( 
		! isset( $_POST["wpnonce"] )
		|| ! wp_verify_nonce( $_POST["wpnonce"], 'wpv_view_filter_extra_nonce' ) 
	) {
		$data = array(
			'type' => 'nonce',
			'message' => __( 'Your security credentials have expired. Please reload the page to get new ones.', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}

    // Get the View
    $view_id = (int) wpv_getpost( 'id', 0 );
	// @todo this can also be a WPA instance...
    $view = WPV_View::get_instance( $view_id );
    if ( $view_id < 1 || null == $view ) {
		$data = array(
			'type' => 'id',
			'message' => __( 'Wrong or missing ID.', 'wpv-views' )
		);
		wp_send_json_error( $data );
	}

    // Update View settings. Note that if any of those properties fail to update, nothing will be saved -
    // that doesn't happen until finish_modifying_view_settings() is called.
    try {
        $view->begin_modifying_view_settings();

        $filter_meta_html = wpv_getpost('query_val', null);
        if (null != $filter_meta_html) {
            $view->filter_meta_html = $filter_meta_html;
        }
        $view->filter_css = wpv_getpost('query_css_val');
        $view->filter_js = wpv_getpost('query_js_val');

        $view->finish_modifying_view_settings();
    } catch ( WPV_RuntimeExceptionWithMessage $e ) {
        wp_send_json_error( array( 'type' => '', 'message' => $e->getUserMessage() ) );
    } catch ( Exception $e ) {
        wp_send_json_error( array( 'type' => '', 'message' => __( 'An unexpected error ocurred.', 'wpv-views' ) ) );
    }
	
	$parametric_search_hints = wpv_get_parametric_search_hints_data( $view_id );

    // Indicate success.
	$data = array(
		'id'			=> $view_id,
		'message'		=> __( 'Filter saved', 'wpv-views' ),
		'parametric'	=> $parametric_search_hints
	);
	wp_send_json_success( $data );
}

