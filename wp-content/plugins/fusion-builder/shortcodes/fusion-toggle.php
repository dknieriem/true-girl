<?php

if ( fusion_is_element_enabled( 'fusion_accordion' ) ) {

	if ( ! class_exists( 'FusionSC_Toggle' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Toggle extends Fusion_Element {

			/**
			 * Counter for accordians.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $accordian_counter = 1;

			/**
			 * Counter for collapsed items.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $collapse_counter = 1;

			/**
			 * The ID of the collapsed item.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $collapse_id;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $child_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_toggle-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_toggle-shortcode-panelgroup', array( $this, 'panelgroup_attr' ) );
				add_filter( 'fusion_attr_toggle-shortcode-panel', array( $this, 'panel_attr' ) );
				add_filter( 'fusion_attr_toggle-shortcode-title', array( $this, 'title_attr' ) );
				add_filter( 'fusion_attr_toggle-shortcode-fa-icon', array( $this, 'fa_icon_attr' ) );
				add_filter( 'fusion_attr_toggle-shortcode-data-toggle', array( $this, 'data_toggle_attr' ) );
				add_filter( 'fusion_attr_toggle-shortcode-collapse', array( $this, 'collapse_attr' ) );

				add_shortcode( 'fusion_accordion', array( $this, 'render_parent' ) );
				add_shortcode( 'fusion_toggle', array( $this, 'render_child' ) );

			}

			/**
			 * Render the parent shortcode
			 *
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				global $fusion_library, $fusion_settings;

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'background_color'          => ( '' !== $fusion_settings->get( 'accordian_background_color' ) ) ? $fusion_settings->get( 'accordian_background_color' ) : '#ffffff',
						'border_color'              => ( '' !== $fusion_settings->get( 'accordian_border_color' ) ) ? $fusion_settings->get( 'accordian_border_color' ) : '#cccccc',
						'border_size'               => intval( $fusion_settings->get( 'accordion_border_size' ) ) . 'px',
						'boxed_mode'                => ( '' !== $fusion_settings->get( 'accordion_boxed_mode' ) ) ? $fusion_settings->get( 'accordion_boxed_mode' ) : 'no',
						'class'                     => '',
						'divider_line'              => $fusion_settings->get( 'accordion_divider_line' ),
						'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
						'hover_color'               => ( '' !== $fusion_settings->get( 'accordian_hover_color' ) ) ? $fusion_settings->get( 'accordian_hover_color' ) : $fusion_library->sanitize->color( $fusion_settings->get( 'primary_color' ) ),
						'icon_alignment'            => ( '' !== $fusion_settings->get( 'accordion_icon_align' ) ) ? $fusion_settings->get( 'accordion_icon_align' ) : 'left',
						'icon_boxed_mode'           => ( '' !== $fusion_settings->get( 'accordion_icon_boxed' ) ) ? $fusion_settings->get( 'accordion_icon_boxed' ) : 'no',
						'icon_box_color'            => $fusion_settings->get( 'accordian_inactive_color' ),
						'icon_color'                => ( '' !== $fusion_settings->get( 'accordian_icon_color' ) ) ? $fusion_settings->get( 'accordian_icon_color' ) : '#ffffff',
						'icon_size'                 => ( '' !== $fusion_settings->get( 'accordion_icon_size' ) ) ? $fusion_settings->get( 'accordion_icon_size' ) : '13px',
						'id'                        => '',
						'title_font_size'           => $fusion_settings->get( 'accordion_title_font_size' ),
						'toggle_hover_accent_color' => $fusion_settings->get( 'accordian_active_color' ),
						'type'                      => ( '' !== $fusion_settings->get( 'accordion_type' ) ) ? $fusion_settings->get( 'accordion_type' ) : 'accordions',
					),
					$args,
					'fusion_accordion'
				);

				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['icon_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['icon_size'], 'px' );

				extract( $defaults );

				$this->parent_args = $defaults;

				$style_tag = $styles = '';

				if ( '1' == $this->parent_args['boxed_mode'] || 'yes' === $this->parent_args['boxed_mode'] ) {

					if ( ! empty( $this->parent_args['hover_color'] ) ) {
						$styles .= '#accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-panel:hover{ background-color: ' . $this->parent_args['hover_color'] . ' }';
					}

					$styles .= ' #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-panel {';

					if ( ! empty( $this->parent_args['border_color'] ) ) {
						$styles .= ' border-color:' . $this->parent_args['border_color'] . ';';
					}

					if ( ! empty( $this->parent_args['border_size'] ) ) {
						$styles .= ' border-width:' . $this->parent_args['border_size'] . ';';
					}

					if ( ! empty( $this->parent_args['background_color'] ) ) {
						$styles .= ' background-color:' . $this->parent_args['background_color'] . ';';
					}

					$styles .= ' }';
				}

				if ( ! empty( $this->parent_args['icon_color'] ) ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a .fa-fusion-box{ color: ' . $this->parent_args['icon_color'] . ';}';
				}

				if ( ! empty( $this->parent_args['icon_size'] ) ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a .fa-fusion-box:before{ font-size: ' . $this->parent_args['icon_size'] . '; width: ' . $this->parent_args['icon_size'] . ';}';
				}

				if ( ! empty( $this->parent_args['icon_alignment'] ) && 'right' === $this->parent_args['icon_alignment'] ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . '.fusion-toggle-icon-right .fusion-toggle-heading{ margin-right: ' . FusionBuilder::validate_shortcode_attr_value( intval( $this->parent_args['icon_size'] ) + 18, 'px' ) . ';}';
				}

				if ( ! empty( $this->parent_args['title_font_size'] ) ) {
					$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a{font-size:' . FusionBuilder::validate_shortcode_attr_value( $this->parent_args['title_font_size'], 'px' ) . ';}';
				}

				if ( ( '1' === $this->parent_args['icon_boxed_mode'] || 'yes' === $this->parent_args['icon_boxed_mode'] ) && ! empty( $this->parent_args['icon_box_color'] ) ) {
					$icon_box_color = $fusion_library->sanitize->color( $this->parent_args['icon_box_color'] );
					$styles        .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fa-fusion-box { background-color: ' . $icon_box_color . ';border-color: ' . $icon_box_color . ';}';
				}

				if ( ! empty( $this->parent_args['toggle_hover_accent_color'] ) ) {
					$toggle_hover_accent_color = $fusion_library->sanitize->color( $this->parent_args['toggle_hover_accent_color'] );
					$styles                   .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a:hover, #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-toggle-boxed-mode:hover .panel-title a { color: ' . $toggle_hover_accent_color . ';}';

					if ( '1' === $this->parent_args['icon_boxed_mode'] || 'yes' === $this->parent_args['icon_boxed_mode'] ) {
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title .active .fa-fusion-box,';
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .panel-title a:hover .fa-fusion-box { background-color: ' . $toggle_hover_accent_color . '!important;border-color: ' . $toggle_hover_accent_color . '!important;}';
					} else {
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . ' .fusion-toggle-boxed-mode:hover .panel-title a .fa-fusion-box{ color: ' . $toggle_hover_accent_color . ';}';
						$styles .= '.fusion-accordian  #accordion-' . get_the_ID() . '-' . $this->accordian_counter . '.fusion-toggle-icon-unboxed .fusion-panel .panel-title a:hover .fa-fusion-box{ color: ' . $toggle_hover_accent_color . ' !important;}';
					}
				}

				if ( $styles ) {

					$style_tag = '<style type="text/css" scoped="scoped">' . $styles . '</style>';

				}

				$html = sprintf(
					'%s<div %s><div %s>%s</div></div>',
					$style_tag,
					FusionBuilder::attributes( 'toggle-shortcode' ),
					FusionBuilder::attributes( 'toggle-shortcode-panelgroup' ),
					do_shortcode( $content )
				);

				$this->accordian_counter++;

				return $html;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					array(
						'class' => 'accordian fusion-accordian',
					)
				);

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the panel-group attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function panelgroup_attr() {
				$attr = array(
					'class' => 'panel-group',
					'id'    => 'accordion-' . get_the_ID() . '-' . $this->accordian_counter,
				);

				if ( 'right' == $this->parent_args['icon_alignment'] ) {
					$attr['class'] .= ' fusion-toggle-icon-right';
				}

				if ( '0' == $this->parent_args['icon_boxed_mode'] || 'no' === $this->parent_args['icon_boxed_mode'] ) {
					$attr['class'] .= ' fusion-toggle-icon-unboxed';
				}

				return $attr;
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'open'  => 'no',
						'title' => '',
					),
					$args,
					'fusion_toggle'
				);

				extract( $defaults );

				$this->child_args                 = $defaults;
				$this->child_args['toggle_class'] = '';

				if ( 'yes' === $open ) {
					$this->child_args['toggle_class'] = 'in';
				}

				$this->collapse_id = substr( md5( sprintf( 'collapse-%s-%s-%s', get_the_ID(), $this->accordian_counter, $this->collapse_counter ) ), 15 );

				$html = sprintf(
					'<div %s><div %s><h4 %s><a %s><div %s><i %s></i></div><div %s>%s</div></a></h4></div><div %s><div %s>%s</div></div></div>',
					FusionBuilder::attributes( 'toggle-shortcode-panel' ),
					FusionBuilder::attributes( 'panel-heading' ),
					FusionBuilder::attributes( 'panel-title toggle' ),
					FusionBuilder::attributes( 'toggle-shortcode-data-toggle' ),
					FusionBuilder::attributes( 'fusion-toggle-icon-wrapper' ),
					FusionBuilder::attributes( 'toggle-shortcode-fa-icon' ),
					FusionBuilder::attributes( 'fusion-toggle-heading' ),
					$title,
					FusionBuilder::attributes( 'toggle-shortcode-collapse' ),
					FusionBuilder::attributes( 'panel-body toggle-content fusion-clearfix' ),
					do_shortcode( $content )
				);

				$this->collapse_counter++;

				return $html;

			}

			/**
			 * Builds the panel attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function panel_attr() {

				$attr = array(
					'class' => 'fusion-panel panel-default',
				);

				if ( '1' == $this->parent_args['boxed_mode'] || 'yes' === $this->parent_args['boxed_mode'] ) {
					$attr['class'] .= ' fusion-toggle-no-divider fusion-toggle-boxed-mode';
				} elseif ( '0' == $this->parent_args['divider_line'] || 'no' === $this->parent_args['divider_line'] ) {
					$attr['class'] .= ' fusion-toggle-no-divider';
				}

				return $attr;

			}

			/**
			 * Builds the font-awesome icon attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function fa_icon_attr() {
				return array(
					'class' => 'fa-fusion-box',
				);
			}

			/**
			 * Builds the data-toggle attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function data_toggle_attr() {

				$attr = array();

				if ( 'yes' === $this->child_args['open'] ) {
					$attr['class'] = 'active';
				}

				$attr['data-toggle'] = 'collapse';
				if ( 'toggles' !== $this->parent_args['type'] ) {
					$attr['data-parent'] = sprintf( '#accordion-%s-%s', get_the_ID(), $this->accordian_counter );
				}
				$attr['data-target'] = '#' . $this->collapse_id;
				$attr['href']        = '#' . $this->collapse_id;

				return $attr;

			}

			/**
			 * Builds the collapse attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function collapse_attr() {
				return array(
					'id'    => $this->collapse_id,
					'class' => 'panel-collapse collapse ' . $this->child_args['toggle_class'],
				);
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {

				global $fusion_library, $fusion_settings, $dynamic_css_helpers;

				$main_elements = apply_filters( 'fusion_builder_element_classes', array( '.fusion-accordian' ), '.fusion-accordian' );
				$elements      = $dynamic_css_helpers->map_selector( $main_elements, ' .panel-title a:hover' );
				$elements      = array_merge( $elements, $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-toggle-boxed-mode:hover .panel-title a' ) );

				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'accordian_active_color' ) );

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .panel-title a .fa-fusion-box' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'accordian_inactive_color' ) );

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .panel-title a:hover .fa-fusion-box' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'accordian_active_color' ) ) . ' !important';
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = $fusion_library->sanitize->color( $fusion_settings->get( 'accordian_active_color' ) ) . ' !important';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .panel-title .active .fa-fusion-box' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'accordian_active_color' ) );

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-panel' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'sep_color' ) );

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Toggles settings.
			 */
			public function add_options() {

				global $fusion_library, $fusion_settings;

				return array(
					'toggle_shortcode_section' => array(
						'label'  => esc_html__( 'Toggles Element', 'fusion-builder' ),
						'id'     => 'accordion_shortcode_section',
						'type'   => 'accordion',
						'fields' => array(
							'accordion_type'             => array(
								'label'       => esc_html__( 'Toggles or Accordions', 'fusion-builder' ),
								'description' => esc_html__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-builder' ),
								'id'          => 'accordion_type',
								'default'     => 'accordions',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'toggles'    => esc_html__( 'Toggles', 'fusion-builder' ),
									'accordions' => esc_html__( 'Accordions', 'fusion-builder' ),
								),
							),
							'accordion_boxed_mode'       => array(
								'label'       => esc_html__( 'Toggle Boxed Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display items in boxed mode. Toggle divider line must be disabled for this option to work.', 'fusion-builder' ),
								'id'          => 'accordion_boxed_mode',
								'default'     => '0',
								'type'        => 'switch',
							),
							'accordion_border_size'      => array(
								'label'       => esc_html__( 'Toggle Boxed Mode Border Width', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the toggle item.', 'fusion-builder' ),
								'id'          => 'accordion_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'required'    => array(
									array(
										'setting'  => 'accordion_boxed_mode',
										'operator' => '!=',
										'value'    => '0',
									),
								),
								'choices'     => array(
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								),
							),
							'accordian_border_color'     => array(
								'label'       => esc_html__( 'Toggle Boxed Mode Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the toggle item.', 'fusion-builder' ),
								'id'          => 'accordian_border_color',
								'default'     => '#cccccc',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'accordion_boxed_mode',
										'operator' => '!=',
										'value'    => '0',
									),
								),
							),
							'accordian_background_color' => array(
								'label'       => esc_html__( 'Toggle Boxed Mode Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the toggle item.', 'fusion-builder' ),
								'id'          => 'accordian_background_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'accordion_boxed_mode',
										'operator' => '!=',
										'value'    => '0',
									),
								),
							),
							'accordian_hover_color'      => array(
								'label'       => esc_html__( 'Toggle Boxed Mode Background Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background hover color of the toggle item.', 'fusion-builder' ),
								'id'          => 'accordian_hover_color',
								'default'     => '#f9f9f9',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'accordion_boxed_mode',
										'operator' => '!=',
										'value'    => '0',
									),
								),
							),
							'accordion_divider_line'     => array(
								'label'       => esc_html__( 'Toggle Divider Line', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display a divider line between each item.', 'fusion-builder' ),
								'id'          => 'accordion_divider_line',
								'default'     => '1',
								'type'        => 'switch',
								'required'    => array(
									array(
										'setting'  => 'accordion_boxed_mode',
										'operator' => '!=',
										'value'    => '1',
									),
								),
							),
							'accordion_title_font_size'  => array(
								'label'       => esc_html__( 'Toggle Title Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of the title text.', 'fusion-builder' ),
								'id'          => 'accordion_title_font_size',
								'default'     => $fusion_settings->get( 'h4_typography', 'font-size' ),
								'type'        => 'dimension',
							),
							'accordion_icon_size'        => array(
								'label'       => esc_html__( 'Toggle Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Set the size of the icon.', 'fusion-builder' ),
								'id'          => 'accordion_icon_size',
								'default'     => '13',
								'min'         => '1',
								'max'         => '40',
								'step'        => '1',
								'type'        => 'slider',
							),
							'accordian_icon_color'       => array(
								'label'       => esc_html__( 'Toggle Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of icon in toggle box.', 'fusion-builder' ),
								'id'          => 'accordian_icon_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
							),
							'accordion_icon_boxed'       => array(
								'label'       => esc_html__( 'Toggle Icon Boxed Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display toggle icon in boxed mode.', 'fusion-builder' ),
								'id'          => 'accordion_icon_boxed',
								'default'     => '1',
								'type'        => 'switch',
							),
							'accordian_inactive_color'   => array(
								'label'       => esc_html__( 'Toggle Icon Inactive Box Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the inactive toggle box.', 'fusion-builder' ),
								'id'          => 'accordian_inactive_color',
								'default'     => '#333333',
								'type'        => 'color-alpha',
								'required'    => array(
									array(
										'setting'  => 'accordion_icon_boxed',
										'operator' => '==',
										'value'    => '1',
									),
								),
							),
							'accordion_icon_align'       => array(
								'label'       => esc_html__( 'Toggle Icon Alignment', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the alignment of toggle icon.', 'fusion-builder' ),
								'id'          => 'accordion_icon_align',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'left'  => esc_html__( 'Left', 'fusion-builder' ),
									'right' => esc_html__( 'Right', 'fusion-builder' ),
								),
							),
							'accordian_active_color'     => array(
								'label'       => esc_html__( 'Toggle Hover Accent Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the accent color on hover for icon box and title.', 'fusion-builder' ),
								'id'          => 'accordian_active_color',
								'default'     => $fusion_library->sanitize->color( $fusion_settings->get( 'primary_color' ) ),
								'type'        => 'color-alpha',
							),
						),
					),
				);
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 1.1
			 * @return void
			 */
			public function add_scripts() {

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-toggles',
					FusionBuilder::$js_folder_url . '/general/fusion-toggles.js',
					FusionBuilder::$js_folder_path . '/general/fusion-toggles.js',
					array( 'bootstrap-collapse', 'fusion-equal-heights' ),
					'1',
					true
				);
			}
		}
	}

	new FusionSC_Toggle();

}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_accordion() {

	global $fusion_settings;

	fusion_builder_map(
		array(
			'name'          => esc_attr__( 'Toggles', 'fusion-builder' ),
			'shortcode'     => 'fusion_accordion',
			'multi'         => 'multi_element_parent',
			'element_child' => 'fusion_toggle',
			'icon'          => 'fusiona-expand-alt',
			'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-toggles-preview.php',
			'preview_id'    => 'fusion-builder-block-module-toggles-preview-template',
			'params'        => array(
				array(
					'type'        => 'tinymce',
					'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
					'description' => esc_attr__( 'Enter some content for this contentbox.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => '[fusion_toggle title="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" open="no" ]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_toggle]',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Toggles or Accordions', 'fusion-builder' ),
					'description' => esc_attr__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-builder' ),
					'param_name'  => 'type',
					'value'       => array(
						''           => esc_attr__( 'Default', 'fusion-builder' ),
						'toggles'    => esc_attr__( 'Toggles', 'fusion-builder' ),
						'accordions' => esc_attr__( 'Accordions', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Boxed Mode', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to display items in boxed mode.', 'fusion-builder' ),
					'param_name'  => 'boxed_mode',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Boxed Mode Border Width', 'fusion-builder' ),
					'description' => esc_attr__( 'Set the border width for toggle item. In pixels.', 'fusion-builder' ),
					'param_name'  => 'border_size',
					'value'       => $fusion_settings->get( 'accordion_border_size' ),
					'default'     => $fusion_settings->get( 'accordion_border_size' ),
					'min'         => '0',
					'max'         => '20',
					'step'        => '1',
					'dependency'  => array(
						array(
							'element'  => 'boxed_mode',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Boxed Mode Border Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Set the border color for toggle item.', 'fusion-builder' ),
					'param_name'  => 'border_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'accordian_border_color' ),
					'dependency'  => array(
						array(
							'element'  => 'boxed_mode',
							'value'    => 'no',
							'operator' => '!=',
						),
						array(
							'element'  => 'border_size',
							'value'    => '0',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Boxed Mode Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Set the background color for toggle item.', 'fusion-builder' ),
					'param_name'  => 'background_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'accordian_background_color' ),
					'dependency'  => array(
						array(
							'element'  => 'boxed_mode',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Boxed Mode Background Hover Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Set the background hover color for toggle item.', 'fusion-builder' ),
					'param_name'  => 'hover_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'accordian_hover_color' ),
					'dependency'  => array(
						array(
							'element'  => 'boxed_mode',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Divider Line', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to display a divider line between each item.', 'fusion-builder' ),
					'param_name'  => 'divider_line',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => '',
					'dependency'  => array(
						array(
							'element'  => 'boxed_mode',
							'value'    => 'yes',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Title Size', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the size of the title. Enter value including any valid CSS unit, ex: 13px.', 'fusion-builder' ),
					'param_name'  => 'title_font_size',
					'value'       => '',
				),
				array(
					'heading'     => esc_html__( 'Toggle Icon Size', 'fusion-builder' ),
					'description' => esc_html__( 'Set the size of the icon. In pixels (px), ex: 13px.', 'fusion-builder' ),
					'param_name'  => 'icon_size',
					'default'     => $fusion_settings->get( 'accordion_icon_size' ),
					'min'         => '1',
					'max'         => '40',
					'step'        => '1',
					'type'        => 'range',
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Toggle Icon Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Set the color of icon in toggle box.', 'fusion-builder' ),
					'param_name'  => 'icon_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'accordian_icon_color' ),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Toggle Icon Boxed Mode', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to display icon in boxed mode.', 'fusion-builder' ),
					'param_name'  => 'icon_boxed_mode',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Toggle Icon Inactive Box Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the color of the inactive toggle box.', 'fusion-builder' ),
					'param_name'  => 'icon_box_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'accordian_inactive_color' ),
					'dependency'  => array(
						array(
							'element'  => 'icon_boxed_mode',
							'value'    => 'no',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Toggle Icon Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the alignment of toggle icon.', 'fusion-builder' ),
					'param_name'  => 'icon_alignment',
					'value'       => array(
						''      => esc_attr__( 'Default', 'fusion-builder' ),
						'left'  => esc_attr__( 'Left', 'fusion-builder' ),
						'right' => esc_attr__( 'Right', 'fusion-builder' ),
					),
					'default'     => '',
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Toggle Hover Accent Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the accent color on hover for icon box and title.', 'fusion-builder' ),
					'param_name'  => 'toggle_hover_accent_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'accordian_active_color' ),
				),
				array(
					'type'        => 'checkbox_button_set',
					'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
					'param_name'  => 'hide_on_mobile',
					'value'       => fusion_builder_visibility_options( 'full' ),
					'default'     => fusion_builder_default_visibility( 'array' ),
					'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'class',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
					'group'       => esc_attr__( 'General', 'fusion-builder' ),
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_accordion' );

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_toggle() {
	fusion_builder_map(
		array(
			'name'              => esc_attr__( 'Toggle', 'fusion-builder' ),
			'shortcode'         => 'fusion_toggle',
			'hide_from_builder' => true,
			'allow_generator'   => true,
			'params'            => array(
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Title', 'fusion-builder' ),
					'description' => esc_attr__( 'Insert the toggle title.', 'fusion-builder' ),
					'param_name'  => 'title',
					'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
					'placeholder' => true,
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Open by Default', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose to have the toggle open when page loads.', 'fusion-builder' ),
					'param_name'  => 'open',
					'value'       => array(
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					),
					'default'     => 'no',
				),
				array(
					'type'        => 'tinymce',
					'heading'     => esc_attr__( 'Toggle Content', 'fusion-builder' ),
					'description' => esc_attr__( 'Insert the toggle content.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
					'placeholder' => true,
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_toggle' );
