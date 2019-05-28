<?php

if ( fusion_is_element_enabled( 'fusion_alert' ) ) {

	if ( ! class_exists( 'FusionSC_Alert' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Alert extends Fusion_Element {

			/**
			 * The alert class.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $alert_class;

			/**
			 * The icon class.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $icon_class;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_alert-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_alert-shortcode-icon', array( $this, 'icon_attr' ) );
				add_filter( 'fusion_attr_alert-shortcode-button', array( $this, 'button_attr' ) );

				add_shortcode( 'fusion_alert', array( $this, 'render' ) );

			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				global $fusion_settings;
				if ( ! $fusion_settings ) {
					$fusion_settings = Fusion_Settings::get_instance();
				}

				$defaults                = FusionBuilder::set_shortcode_defaults(
					array(
						'accent_color'        => '',
						'animation_direction' => 'left',
						'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
						'animation_speed'     => '',
						'animation_type'      => '',
						'background_color'    => '',
						'border_size'         => $fusion_settings->get( 'alert_border_size' ),
						'box_shadow'          => ( '' !== $fusion_settings->get( 'alert_box_shadow' ) ) ? strtolower( $fusion_settings->get( 'alert_box_shadow' ) ) : 'no',
						'class'               => '',
						'dismissable'         => $fusion_settings->get( 'alert_box_dismissable' ),
						'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
						'icon'                => '',
						'id'                  => '',
						'text_align'          => $fusion_settings->get( 'alert_box_text_align' ),
						'text_transform'      => $fusion_settings->get( 'alert_box_text_transform' ),
						'type'                => 'general',
					),
					$args,
					'fusion_alert'
				);
				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				switch ( $this->args['type'] ) {

					case 'general':
						$this->alert_class = 'info';
						if ( ! $icon || 'none' !== $icon ) {
							$this->args['icon'] = $icon = 'fa-info-circle';
						}
						break;
					case 'error':
						$this->alert_class = 'danger';
						if ( ! $icon || 'none' !== $icon ) {
							$this->args['icon'] = $icon = 'fa-exclamation-triangle';
						}
						break;
					case 'success':
						$this->alert_class = 'success';
						if ( ! $icon || 'none' !== $icon ) {
							$this->args['icon'] = $icon = 'fa-check-circle';
						}
						break;
					case 'notice':
						$this->alert_class = 'warning';
						if ( ! $icon || 'none' !== $icon ) {
							$this->args['icon'] = $icon = 'fa-lg fa-cog fa';
						}
						break;
					case 'blank':
						$this->alert_class = 'blank';
						break;
					case 'custom':
						$this->alert_class = 'custom';
						break;
				}

				$html  = '<div ' . FusionBuilder::attributes( 'alert-shortcode' ) . '>';
				$html .= ( 'yes' === $dismissable ) ? '<button ' . FusionBuilder::attributes( 'alert-shortcode-button' ) . '>&times;</button>' : '';
				$html .= '<div class="fusion-alert-content-wrapper">';
				if ( $icon && 'none' !== $icon ) {
					$html .= '<span ' . FusionBuilder::attributes( 'alert-icon' ) . '>';
					$html .= '<i ' . FusionBuilder::attributes( 'alert-shortcode-icon' ) . '></i>';
					$html .= '</span>';
				}
				// Make sure the title text is not wrapped with an unattributed p tag.
				$content = preg_replace( '!^<p>(.*?)</p>$!i', '$1', trim( $content ) );

				$html .= '<span class="fusion-alert-content">' . do_shortcode( $content ) . '</span>';
				$html .= '</div>';
				$html .= '</div>';

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

				global $fusion_settings;

				$attr = array();
				$args = array();

				$attr['class'] = 'fusion-alert alert ' . $this->args['type'] . ' alert-' . $this->alert_class . ' fusion-alert-' . $this->args['text_align'];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( 'capitalize' === $this->args['text_transform'] ) {
					$attr['class'] .= ' fusion-alert-capitalize';
				}

				if ( 'yes' === $this->args['dismissable'] ) {
					$attr['class'] .= ' alert-dismissable';
				}

				if ( 'yes' === $this->args['box_shadow'] ) {
					$attr['class'] .= ' alert-shadow';
				}

				if ( 'custom' === $this->alert_class ) {
					$args['background_color'] = $this->args['background_color'];
					$args['accent_color']     = $this->args['accent_color'];
					$args['border_size']      = $this->args['border_size'];
				} else {
					$args['background_color'] = ( '' !== $fusion_settings->get( $this->alert_class . '_bg_color' ) ) ? strtolower( $fusion_settings->get( $this->alert_class . '_bg_color' ) ) : '#ffffff';
					$args['accent_color']     = $fusion_settings->get( $this->alert_class . '_accent_color' );
					$args['border_size']      = FusionBuilder::validate_shortcode_attr_value( $fusion_settings->get( 'alert_border_size' ), 'px' );
				}

				$styles  = '';
				$styles .= ( $args['background_color'] ) ? 'background-color:' . $args['background_color'] . ';' : '';
				$styles .= ( $args['accent_color'] ) ? 'color:' . $args['accent_color'] . ';border-color:' . $args['accent_color'] . ';' : '';
				$styles .= ( $args['border_size'] ) ? 'border-width:' . $args['border_size'] . ';' : '';

				if ( $styles ) {
					$attr['style'] = $styles;
				}

				if ( $this->args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						array(
							'type'      => $this->args['animation_type'],
							'direction' => $this->args['animation_direction'],
							'speed'     => $this->args['animation_speed'],
							'offset'    => $this->args['animation_offset'],
						)
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds theicon  attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {
				return array(
					'class' => 'fa-lg ' . FusionBuilder::font_awesome_name_handler( $this->args['icon'] ),
				);
			}

			/**
			 * Builds the button attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function button_attr() {

				$attr = array();

				if ( 'custom' === $this->alert_class ) {
					$attr['style'] = 'color:' . $this->args['accent_color'] . ';border-color:' . $this->args['accent_color'] . ';';
				}

				$attr['type']         = 'button';
				$attr['class']        = 'close toggle-alert';
				$attr['data-dismiss'] = 'alert';
				$attr['aria-hidden']  = 'true';

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1.6
			 * @return array $sections Blog settings.
			 */
			public function add_options() {
				return array(
					'alert_shortcode_section' => array(
						'label'       => esc_attr__( 'Alert Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'alert_shortcode_section',
						'default'     => '',
						'type'        => 'accordion',
						'fields'      => array(
							'info_bg_color'            => array(
								'label'       => esc_attr__( 'General Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for general alert boxes.', 'fusion-builder' ),
								'id'          => 'info_bg_color',
								'default'     => '#ffffff',
								'type'        => 'color-alpha',
							),
							'info_accent_color'        => array(
								'label'       => esc_attr__( 'General Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for general alert boxes.', 'fusion-builder' ),
								'id'          => 'info_accent_color',
								'default'     => '#808080',
								'type'        => 'color-alpha',
							),
							'danger_bg_color'          => array(
								'label'       => esc_attr__( 'Error Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for error alert boxes.', 'fusion-builder' ),
								'id'          => 'danger_bg_color',
								'default'     => '#f2dede',
								'type'        => 'color-alpha',
							),
							'danger_accent_color'      => array(
								'label'       => esc_attr__( 'Error Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for error alert boxes.', 'fusion-builder' ),
								'id'          => 'danger_accent_color',
								'default'     => '#a64242',
								'type'        => 'color-alpha',
							),
							'success_bg_color'         => array(
								'label'       => esc_attr__( 'Success Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for success alert boxes.', 'fusion-builder' ),
								'id'          => 'success_bg_color',
								'default'     => '#dff0d8',
								'type'        => 'color-alpha',
							),
							'success_accent_color'     => array(
								'label'       => esc_attr__( 'Success Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for success alert boxes.', 'fusion-builder' ),
								'id'          => 'success_accent_color',
								'default'     => '#5ca340',
								'type'        => 'color-alpha',
							),
							'warning_bg_color'         => array(
								'label'       => esc_attr__( 'Notice Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for notice alert boxes.', 'fusion-builder' ),
								'id'          => 'warning_bg_color',
								'default'     => '#fcf8e3',
								'type'        => 'color-alpha',
							),
							'warning_accent_color'     => array(
								'label'       => esc_attr__( 'Notice Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for notice alert boxes.', 'fusion-builder' ),
								'id'          => 'warning_accent_color',
								'default'     => '#d9b917',
								'type'        => 'color-alpha',
							),
							'alert_box_text_align'     => array(
								'label'       => esc_attr__( 'Content Alignment', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how the content should be displayed.', 'fusion-builder' ),
								'id'          => 'alert_box_text_align',
								'type'        => 'radio-buttonset',
								'default'     => 'center',
								'choices'     => array(
									'left'   => esc_attr__( 'Left', 'fusion-builder' ),
									'center' => esc_attr__( 'Center', 'fusion-builder' ),
									'right'  => esc_attr__( 'Right', 'fusion-builder' ),
								),
							),
							'alert_box_text_transform' => array(
								'label'       => esc_attr__( 'Text Transform', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
								'id'          => 'alert_box_text_transform',
								'default'     => 'capitalize',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'normal'     => esc_attr__( 'Normal', 'fusion-builder' ),
									'capitalize' => esc_attr__( 'Uppercase', 'fusion-builder' ),
								),
							),
							'alert_box_dismissable'    => array(
								'label'       => esc_attr__( 'Dismissable Box', 'fusion-builder' ),
								'description' => esc_attr__( 'Select if the alert box should be dismissable.', 'fusion-builder' ),
								'id'          => 'alert_box_dismissable',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								),
							),
							'alert_box_shadow'         => array(
								'label'       => esc_attr__( 'Box Shadow', 'fusion-builder' ),
								'description' => esc_attr__( 'Display a box shadow below the alert box.', 'fusion-builder' ),
								'id'          => 'alert_box_shadow',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'choices'     => array(
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								),
							),
							'alert_border_size'        => array(
								'label'       => esc_html__( 'Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the alert boxes.', 'fusion-builder' ),
								'id'          => 'alert_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								),
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-animations' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-alert' );
			}
		}
	}

	new FusionSC_Alert();
}


/**
 * Map shortcode to Fusion Builder
 *
 * @since 1.0
 */
function fusion_element_alert() {

	global $fusion_settings;

	fusion_builder_map(
		array(
			'name'            => esc_attr__( 'Alert', 'fusion-builder' ),
			'shortcode'       => 'fusion_alert',
			'icon'            => 'fusiona-exclamation-triangle',
			'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-alert-preview.php',
			'preview_id'      => 'fusion-builder-block-module-alert-preview-template',
			'allow_generator' => true,
			'params'          => array(
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Alert Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the type of alert message. Choose custom for advanced color options below.', 'fusion-builder' ),
					'param_name'  => 'type',
					'default'     => 'error',
					'value'       => array(
						'general' => esc_attr__( 'General', 'fusion-builder' ),
						'error'   => esc_attr__( 'Error', 'fusion-builder' ),
						'success' => esc_attr__( 'Success', 'fusion-builder' ),
						'notice'  => esc_attr__( 'Notice', 'fusion-builder' ),
						'custom'  => esc_attr__( 'Custom', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Accent Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Custom setting only. Set the border, text and icon color for custom alert boxes.', 'fusion-builder' ),
					'param_name'  => 'accent_color',
					'value'       => '#808080',
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'custom',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Custom setting only. Set the background color for custom alert boxes.', 'fusion-builder' ),
					'param_name'  => 'background_color',
					'value'       => '#ffffff',
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'custom',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
					'param_name'  => 'border_size',
					'default'     => preg_replace( '/[a-z,%]/', '', $fusion_settings->get( 'alert_border_size' ) ),
					'description' => esc_attr__( 'Custom setting only. Set the border size for custom alert boxes. In pixels.', 'fusion-builder' ),
					'choices'     => array(
						'min'  => '0',
						'max'  => '20',
						'step' => '1',
					),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'custom',
							'operator' => '==',
						),
					),

				),
				array(
					'type'        => 'iconpicker',
					'heading'     => esc_attr__( 'Select Custom Icon', 'fusion-builder' ),
					'param_name'  => 'icon',
					'value'       => '',
					'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'type',
							'value'    => 'custom',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose how the content should be displayed.', 'fusion-builder' ),
					'param_name'  => 'text_align',
					'default'     => '',
					'value'       => array(
						''       => esc_attr__( 'Default', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'center' => esc_attr__( 'Center', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Text Transform', 'fusion-builder' ),
					'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
					'param_name'  => 'text_transform',
					'default'     => '',
					'value'       => array(
						''           => esc_attr__( 'Default', 'fusion-builder' ),
						'normal'     => esc_attr__( 'Normal', 'fusion-builder' ),
						'capitalize' => esc_attr__( 'Uppercase', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Dismissable Box', 'fusion-builder' ),
					'description' => esc_attr__( 'Select if the alert box should be dismissable.', 'fusion-builder' ),
					'param_name'  => 'dismissable',
					'default'     => '',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Box Shadow', 'fusion-builder' ),
					'description' => esc_attr__( 'Display a box shadow below the alert box.', 'fusion-builder' ),
					'param_name'  => 'box_shadow',
					'default'     => '',
					'value'       => array(
						''    => esc_attr__( 'Default', 'fusion-builder' ),
						'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						'no'  => esc_attr__( 'No', 'fusion-builder' ),
					),
				),
				array(
					'type'        => 'tinymce',
					'heading'     => esc_attr__( 'Alert Content', 'fusion-builder' ),
					'description' => esc_attr__( "Insert the alert's content.", 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => esc_html__( 'Your Content Goes Here', 'fusion-builder' ),
					'placeholder' => true,
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Animation Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the type of animation to use on the element.', 'fusion-builder' ),
					'param_name'  => 'animation_type',
					'value'       => fusion_builder_available_animations(),
					'default'     => '',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Direction of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the incoming direction for the animation.', 'fusion-builder' ),
					'param_name'  => 'animation_direction',
					'value'       => array(
						'down'   => esc_attr__( 'Top', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						'up'     => esc_attr__( 'Bottom', 'fusion-builder' ),
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'static' => esc_attr__( 'Static', 'fusion-builder' ),
					),
					'default'     => 'left',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => esc_attr__( 'Speed of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Type in speed of animation in seconds (0.1 - 1).', 'fusion-builder' ),
					'param_name'  => 'animation_speed',
					'min'         => '0.1',
					'max'         => '1',
					'step'        => '0.1',
					'value'       => '0.3',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Offset of Animation', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls when the animation should start.', 'fusion-builder' ),
					'param_name'  => 'animation_offset',
					'value'       => array(
						''                => esc_attr__( 'Default', 'fusion-builder' ),
						'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
						'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-builder' ),
						'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-builder' ),
					),
					'default'     => '',
					'group'       => esc_attr__( 'Animation', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'animation_type',
							'value'    => '',
							'operator' => '!=',
						),
					),
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
					'param_name'  => 'class',
					'value'       => '',
					'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_alert' );
