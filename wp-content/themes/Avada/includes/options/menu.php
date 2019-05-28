<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Menu
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_menu( $sections ) {

	$settings = get_option( Avada::get_option_name(), array() );
	/* translators: value. */
	$menu_height_hint = '<span id="fusion-menu-height-hint" style="display: none">' . sprintf( esc_html__( '  To match the logo height set to %s.', 'Avada' ), '<strong>Unknown</strong>' ) . '</span>';

	// If we can get logo height and the logo margins are in pixels, then we can provide a hint.
	if ( is_admin() ) {
		$logo_data = Avada()->images->get_logo_data( 'logo' );
		if ( isset( $logo_data['height'] ) && '' !== $logo_data['height'] && isset( $settings['logo_margin']['top'] ) && isset( $settings['logo_margin']['bottom'] ) ) {
			$logo_top_margin    = Fusion_Sanitize::size( $settings['logo_margin']['top'] );
			$logo_bottom_margin = Fusion_Sanitize::size( $settings['logo_margin']['bottom'] );
			if ( strpos( $logo_top_margin, 'px' ) && strpos( $logo_bottom_margin, 'px' ) ) {
				$total_logo_height = intval( $logo_top_margin ) + intval( $logo_bottom_margin ) + intval( $logo_data['height'] );
				/* translators: value. */
				$menu_height_hint = '<span id="fusion-menu-height-hint" style="display:inline">' . sprintf( esc_html__( '  To match the logo height set to %s.', 'Avada' ), '<strong>' . $total_logo_height . '</strong>' ) . '</span>';
			}
		}
	}
	$sections['menu'] = array(
		'label'    => esc_html__( 'Menu', 'Avada' ),
		'id'       => 'heading_menu_section',
		'priority' => 1,
		'icon'     => 'el-icon-lines',
		'fields'   => array(
			'heading_menu'               => array(
				'label'    => esc_html__( 'Main Menu', 'Avada' ),
				'id'       => 'heading_menu',
				'priority' => 6,
				'type'     => 'sub-section',
				'fields'   => array(

					'nav_height'                         => array(
						'label'       => esc_html__( 'Main Menu Height', 'Avada' ),
						'description' => esc_html__( 'Controls the menu height.', 'Avada' ) . $menu_height_hint,
						'id'          => 'nav_height',
						'default'     => '84',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '300',
							'step' => '1',
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_highlight_style'               => array(
						'label'       => esc_html__( 'Main Menu Highlight Style', 'Avada' ),
						'description' => __( 'Controls the highlight style for main menu links and also affects the look of menu dropdowns. Arrow style cannot work with a transparent header background. Bar highlights will display vertically on side header layouts. <strong>IMPORTANT:</strong> Arrow & Background style can require configuration of other options depending on desired effect.', 'Avada' ) . ' <a href="https://theme-fusion.com/documentation/avada/main-menu-highlight-styles/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'See this post for more information', 'Avada' ) . '</a>.',
						'id'          => 'menu_highlight_style',
						'default'     => 'bar',
						'choices'     => array(
							'bar'        => esc_html__( 'Top Bar', 'Avada' ),
							'bottombar'  => esc_html__( 'Bottom Bar', 'Avada' ),
							'arrow'      => esc_html__( 'Arrow', 'Avada' ),
							'background' => esc_html__( 'Background', 'Avada' ),
							'textcolor'  => esc_html__( 'Color Only', 'Avada' ),
						),
						'type'        => 'radio-buttonset',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_highlight_background'          => array(
						'label'       => esc_html__( 'Main Menu Highlight Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of main menu highlight.', 'Avada' ),
						'id'          => 'menu_highlight_background',
						'default'     => '#a0ce4e',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-or-and',
						'required'    => array(
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'background',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'background',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_arrow_size'                    => array(
						'label'       => esc_html__( 'Main Menu Arrow Size', 'Avada' ),
						'description' => esc_html__( 'Controls the width and height of the main menu arrow.', 'Avada' ),
						'id'          => 'menu_arrow_size',
						'units'       => false,
						'default'     => array(
							'width'  => ( isset( $settings['header_position'] ) && 'Top' !== $settings['header_position'] ) ? '12px' : '23px',
							'height' => ( isset( $settings['header_position'] ) && 'Top' !== $settings['header_position'] ) ? '23px' : '12px',
						),
						'type'        => 'dimensions',
						'class'       => 'fusion-gutter-and-or-and',
						'required'    => array(
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'arrow',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'arrow',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'nav_highlight_border'               => array(
						'label'       => esc_html__( 'Main Menu Highlight Bar Size', 'Avada' ),
						'description' => esc_html__( 'Controls the size of the menu highlight bar.', 'Avada' ),
						'id'          => 'nav_highlight_border',
						'default'     => '3',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '40',
							'step' => '1',
						),
						'class'       => 'fusion-gutter-and-or-and-or-and-or-and',
						'required'    => array(
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bar',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bar',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bottombar',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bottombar',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'nav_padding'                        => array(
						'label'       => esc_html__( 'Main Menu Item Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the right padding for menu text (left on RTL).', 'Avada' ),
						'id'          => 'nav_padding',
						'default'     => '45',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '200',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '==',
								'value'    => 'Top',
							),
						),
					),
					'mobile_nav_padding'                 => array(
						'label'       => esc_html__( 'Main Menu Item Padding On Mobile', 'Avada' ),
						'description' => esc_html__( 'Controls the right padding for menu text (left on RTL) when the normal desktop menu is used on mobile devices.', 'Avada' ),
						'id'          => 'mobile_nav_padding',
						'default'     => '25',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '200',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '==',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'megamenu_shadow'                    => array(
						'label'       => esc_html__( 'Main Menu Drop Shadow', 'Avada' ),
						'description' => esc_html__( 'Turn on to display a drop shadow on menu dropdowns.', 'Avada' ),
						'id'          => 'megamenu_shadow',
						'default'     => '1',
						'type'        => 'switch',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'main_menu_sub_menu_animation'               => array(
						'label'       => esc_html__( 'Main Menu Dropdown / Mega Menu Animation', 'Avada' ),
						'description' => esc_html__( 'Controls the animation type for all sub-menus.', 'Avada' ),
						'id'          => 'main_menu_sub_menu_animation',
						'type'        => 'radio-buttonset',
						'default'     => 'fade',
						'choices'     => array(
							'fade'  => esc_html__( 'Fade', 'Avada' ),
							'slide' => esc_html__( 'Slide', 'Avada' ),
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'dropdown_menu_top_border_size'      => array(
						'label'       => esc_html__( 'Main Menu Dropdown Top Border Size', 'Avada' ),
						'description' => esc_html__( 'Controls top border size of dropdown menus and mega menus.', 'Avada' ),
						'id'          => 'dropdown_menu_top_border_size',
						'default'     => '3',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '50',
							'step' => '1',
						),
						'class'       => 'fusion-gutter-and-or-and-or-and-or-and',
						'required'    => array(
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bar',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bar',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bottombar',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'menu_highlight_style',
								'operator' => '==',
								'value'    => 'bottombar',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'dropdown_menu_width'                => array(
						'label'       => esc_html__( 'Main Menu Dropdown Width', 'Avada' ),
						'description' => esc_html__( 'Controls the width of the dropdown.', 'Avada' ),
						'id'          => 'dropdown_menu_width',
						'default'     => '180',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '500',
							'step' => '1',
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'mainmenu_dropdown_vertical_padding' => array(
						'label'       => esc_html__( 'Main Menu Dropdown Item Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the top/bottom padding for dropdown menu items.', 'Avada' ),
						'id'          => 'mainmenu_dropdown_vertical_padding',
						'default'     => '7',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '50',
							'step' => '1',
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'mainmenu_dropdown_display_divider'  => array(
						'label'       => esc_html__( 'Main Menu Dropdown Divider', 'Avada' ),
						'description' => esc_html__( 'Turn on to display a divider line on dropdown menu items.', 'Avada' ),
						'id'          => 'mainmenu_dropdown_display_divider',
						'default'     => '1',
						'type'        => 'switch',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_display_dropdown_indicator'    => array(
						'label'       => esc_html__( 'Main Menu Dropdown Indicator', 'Avada' ),
						'description' => esc_html__( 'Turn on to display arrow indicators next to parent level menu items.', 'Avada' ),
						'id'          => 'menu_display_dropdown_indicator',
						'default'     => 'none',
						'choices'     => array(
							'parent'       => esc_html__( 'Parent', 'Avada' ),
							'parent_child' => esc_html__( 'Parent + Child', 'Avada' ),
							'none'         => esc_html__( 'None', 'Avada' ),
						),
						'type'        => 'radio-buttonset',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'main_nav_search_icon'               => array(
						'label'       => esc_html__( 'Main Menu Search Icon', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the search icon in the main menu.', 'Avada' ),
						'id'          => 'main_nav_search_icon',
						'default'     => '1',
						'type'        => 'switch',
					),
					'main_nav_icon_circle'               => array(
						'label'       => esc_html__( 'Main Menu Icon Circle Borders', 'Avada' ),
						'description' => esc_html__( 'Turn on to display a circle border on the cart and search icons.', 'Avada' ),
						'id'          => 'main_nav_icon_circle',
						'default'     => '0',
						'type'        => 'switch',
					),
					'main_nav_highlight_radius'          => array(
						'label'       => esc_html__( 'Menu Highlight Label Radius', 'Avada' ),
						'description' => esc_html__( 'Controls the border radius of all your menu highlight labels.', 'Avada' ),
						'id'          => 'main_nav_highlight_radius',
						'default'     => '0px',
						'type'        => 'dimension',
					),
					'menu_sub_bg_color'                  => array(
						'label'       => esc_html__( 'Main Menu Dropdown Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the main menu dropdown.', 'Avada' ),
						'id'          => 'menu_sub_bg_color',
						'default'     => '#f2efef',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_bg_hover_color'                => array(
						'label'       => esc_html__( 'Main Menu Dropdown Background Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background hover color of the main menu dropdown.', 'Avada' ),
						'id'          => 'menu_bg_hover_color',
						'default'     => '#f8f8f8',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_sub_sep_color'                 => array(
						'label'       => esc_html__( 'Main Menu Dropdown Separator Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the separators in the main menu dropdown.', 'Avada' ),
						'id'          => 'menu_sub_sep_color',
						'default'     => '#dcdadb',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'menu_h45_bg_color'                  => array(
						'label'       => esc_html__( 'Main Menu Background Color For Header 4 & 5', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the main menu when using header 4 or 5.', 'Avada' ),
						'id'          => 'menu_h45_bg_color',
						'default'     => '#ffffff',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '==',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '==',
								'value'    => 'v5',
							),
						),
					),
					'main_menu_typography_info'          => array(
						'label'       => esc_html__( 'Main Menu Typography', 'Avada' ),
						'description' => '',
						'id'          => 'main_menu_typography_info',
						'type'        => 'info',
					),
					'nav_typography'                     => array(
						'id'          => 'nav_typography',
						'label'       => esc_html__( 'Menus Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for all menus.', 'Avada' ),
						'type'        => 'typography',
						'class'       => 'avada-no-fontsize',
						'choices'     => array(
							'font-family'    => true,
							'font-weight'    => true,
							'font-size'      => true,
							'letter-spacing' => true,
							'color'          => true,
						),
						'default'     => array(
							'font-family'    => 'Antic Slab',
							'font-weight'    => '400',
							'font-size'      => '14px',
							'letter-spacing' => '0',
							'color'          => '#333333',
						),
					),
					'menu_hover_first_color'             => array(
						'label'       => esc_html__( 'Main Menu Font Hover/Active Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color for main menu text hover and active states, highlight bar and dropdown border.', 'Avada' ),
						'id'          => 'menu_hover_first_color',
						'default'     => '#a0ce4e',
						'type'        => 'color-alpha',
					),
					'menu_text_align'                    => array(
						'label'       => esc_html__( 'Main Menu Text Align', 'Avada' ),
						'description' => esc_html__( 'Controls the main menu text alignment for top headers 4-5 and side headers.', 'Avada' ),
						'id'          => 'menu_text_align',
						'default'     => 'center',
						'choices'     => array(
							'left'   => esc_html__( 'Left', 'Avada' ),
							'center' => esc_html__( 'Center', 'Avada' ),
							'right'  => esc_html__( 'Right', 'Avada' ),
						),
						'type'        => 'radio-buttonset',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '==',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '==',
								'value'    => 'v5',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
						),
					),
					'menu_sub_color'                     => array(
						'label'       => esc_html__( 'Main Menu Dropdown Font Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color for main menu dropdown text.', 'Avada' ),
						'id'          => 'menu_sub_color',
						'default'     => '#333333',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'nav_dropdown_font_size'             => array(
						'label'       => esc_html__( 'Main Menu Dropdown Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for main menu dropdown text.', 'Avada' ),
						'id'          => 'nav_dropdown_font_size',
						'default'     => '13px',
						'type'        => 'dimension',
						'choices'     => array(
							'units' => array( 'px', 'em' ),
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'side_nav_font_size'                 => array(
						'label'       => esc_html__( 'Side Navigation Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for the menu text when using the side navigation page template.', 'Avada' ),
						'id'          => 'side_nav_font_size',
						'default'     => '14px',
						'type'        => 'dimension',
						'choices'     => array(
							'units' => array( 'px', 'em' ),
						),
					),
				),
			),
			'flyout_menu_subsection'     => array(
				'label'  => esc_html__( 'Flyout Menu', 'Avada' ),
				'id'     => 'flyout_menu_subsection',
				'type'   => 'sub-section',
				'fields' => array(
					'flyout_menu_important_note_info' => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong>  Flyout Menu Options are only available when using Header Layout #6 or Mobile Flyout Menu. Your current setup does not utilize the flyout menu.', 'Avada' ) . '</div>',
						'id'          => 'flyout_menu_important_note_info',
						'type'        => 'custom',
						'class'       => 'fusion-gutter-and-or-and',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_menu_icon_font_size'      => array(
						'label'       => esc_html__( 'Flyout Menu Icon Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for the flyout menu icons.', 'Avada' ),
						'id'          => 'flyout_menu_icon_font_size',
						'default'     => '20px',
						'type'        => 'dimension',
						'class'       => 'fusion-gutter-and-or',
						'choices'     => array(
							'units' => array( 'px', 'em' ),
						),
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_nav_icons_padding'        => array(
						'label'       => esc_html__( 'Flyout Menu Icon Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the right padding for flyout menu icons (left on RTL).', 'Avada' ),
						'id'          => 'flyout_nav_icons_padding',
						'default'     => '45',
						'type'        => 'slider',
						'class'       => 'fusion-gutter-and-or',
						'choices'     => array(
							'min'  => '0',
							'max'  => '200',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_menu_icon_color'          => array(
						'label'       => esc_html__( 'Flyout Menu Icon Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the flyout menu icons.', 'Avada' ),
						'id'          => 'flyout_menu_icon_color',
						'default'     => '#333333',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-or',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_menu_icon_hover_color'    => array(
						'label'       => esc_html__( 'Flyout Menu Icon Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the hover color of the flyout menu icons.', 'Avada' ),
						'id'          => 'flyout_menu_icon_hover_color',
						'default'     => '#a0ce4e',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-or',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_menu_background_color'    => array(
						'label'       => esc_html__( 'Flyout Menu Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the flyout menu', 'Avada' ),
						'id'          => 'flyout_menu_background_color',
						'default'     => 'rgba(255,255,255,0.95)',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-or',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_menu_direction'           => array(
						'label'       => esc_html__( 'Flyout Menu Direction', 'Avada' ),
						'description' => esc_html__( 'Controls the direction the flyout menu starts from.', 'Avada' ),
						'id'          => 'flyout_menu_direction',
						'default'     => 'fade',
						'type'        => 'select',
						'class'       => 'fusion-gutter-and-or',
						'choices'     => array(
							'fade'   => esc_html__( 'Fade', 'Avada' ),
							'left'   => esc_html__( 'Left', 'Avada' ),
							'right'  => esc_html__( 'Right', 'Avada' ),
							'bottom' => esc_html__( 'Bottom', 'Avada' ),
							'top'    => esc_html__( 'Top', 'Avada' ),
						),
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
					'flyout_menu_item_padding' => array(
						'label'       => esc_html__( 'Flyout Menu Item Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the padding between flyout menu items.', 'Avada' ),
						'id'          => 'flyout_menu_item_padding',
						'default'     => '28',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '100',
							'step' => '1',
						),
						'class'       => 'fusion-gutter-and-or',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '=',
								'value'    => 'flyout',
							),
						),
					),
				),
			),
			'heading_secondary_top_menu' => array(
				'label'    => esc_html__( 'Secondary Top Menu', 'Avada' ),
				'id'       => 'heading_secondary_top_menu',
				'priority' => 6,
				'type'     => 'sub-section',
				'fields'   => array(
					'no_secondary_menu_note'          => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Secondary Top Menu Options are only available when using Header Layouts #2-5. Your current Header Layout does not utilize the secondary top menu.', 'Avada' ) . '</div>',
						'id'          => 'no_secondary_menu_note',
						'type'        => 'custom',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '==',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v5',
							),
						),
					),
					'topmenu_dropwdown_width'         => array(
						'label'       => esc_html__( 'Secondary Menu Dropdown Width', 'Avada' ),
						'description' => esc_html__( 'Controls the width of the secondary menu dropdown.', 'Avada' ),
						'id'          => 'topmenu_dropwdown_width',
						'default'     => '180',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '500',
							'step' => '1',
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'header_top_first_border_color'   => array(
						'label'       => esc_html__( 'Secondary Menu Divider Color', 'Avada' ),
						'description' => esc_html__( 'Controls the divider color of the first level secondary menu.', 'Avada' ),
						'id'          => 'header_top_first_border_color',
						'default'     => '#e5e5e5',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'header_top_sub_bg_color'         => array(
						'label'       => esc_html__( 'Secondary Menu Dropdown Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the secondary menu dropdown.', 'Avada' ),
						'id'          => 'header_top_sub_bg_color',
						'default'     => '#ffffff',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'header_top_menu_bg_hover_color'  => array(
						'label'       => esc_html__( 'Secondary Menu Dropdown Background Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background hover color of the secondary menu dropdown.', 'Avada' ),
						'id'          => 'header_top_menu_bg_hover_color',
						'default'     => '#fafafa',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'header_top_menu_sub_sep_color'   => array(
						'label'       => esc_html__( 'Secondary Menu Dropdown Separator Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the separators in the secondary menu dropdown.', 'Avada' ),
						'id'          => 'header_top_menu_sub_sep_color',
						'default'     => '#e5e5e5',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'secondary_menu_typography_info'  => array(
						'label'       => '',
						'description' => esc_html__( 'Secondary Top Menu Typography', 'Avada' ),
						'id'          => 'secondary_menu_typography_info',
						'type'        => 'custom',
						'style'       => 'heading',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'snav_font_size'                  => array(
						'label'       => esc_html__( 'Secondary Menu Font Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for secondary menu text.', 'Avada' ),
						'id'          => 'snav_font_size',
						'default'     => '12px',
						'type'        => 'dimension',
						'choices'     => array(
							'units' => array( 'px', 'em' ),
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'sec_menu_lh'                     => array(
						'label'       => esc_html__( 'Secondary Menu Line Height', 'Avada' ),
						'description' => esc_html__( 'Controls the line height for secondary menu.', 'Avada' ),
						'id'          => 'sec_menu_lh',
						'default'     => '44px',
						'type'        => 'dimension',
						'choices'     => array(
							'units' => array( 'px', 'em' ),
						),
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'snav_color'                      => array(
						'label'       => esc_html__( 'Secondary Menu Font Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color for secondary menu text.', 'Avada' ),
						'id'          => 'snav_color',
						'default'     => '#747474',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'header_top_menu_sub_color'       => array(
						'label'       => esc_html__( 'Secondary Menu Dropdown Font Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color for secondary menu dropdown text.', 'Avada' ),
						'id'          => 'header_top_menu_sub_color',
						'default'     => '#747474',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
					'header_top_menu_sub_hover_color' => array(
						'label'       => esc_html__( 'Secondary Menu Dropdown Font Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the hover color for secondary menu dropdown text.', 'Avada' ),
						'id'          => 'header_top_menu_sub_hover_color',
						'default'     => '#333333',
						'type'        => 'color-alpha',
						'class'       => 'fusion-or-gutter',
						'required'    => array(
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							),
						),
					),
				),
			),
			'heading_mobile_menu'        => array(
				'label'    => esc_html__( 'Mobile Menu', 'Avada' ),
				'id'       => 'heading_mobile_menu',
				'priority' => 6,
				'type'     => 'sub-section',
				'fields'   => array(
					'no_responsive_mode_info_1'      => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Please enable responsive mode. Mobile menus are only available when you\'re using the responsive mode. To enable it please go to the "Responsive" section and set the "Responsive Design" option to ON.', 'Avada' ) . '</div>',
						'id'          => 'no_responsive_mode_info_1',
						'type'        => 'custom',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '0',
							),
						),
					),
					'no_mobile_menu_note'            => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Because of the design of your Header Layout #6, only a few options are available here. More options are available when using Header Layouts #1-5 or 7. The rest of the options for Header Layout #6 are on the Flyout Menu and Main Menu tab.', 'Avada' ) . '</div>',
						'id'          => 'no_mobile_menu_note',
						'type'        => 'custom',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '==',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '==',
								'value'    => 'Top',
							),
						),
					),
					'mobile_menu_design'             => array(
						'label'       => esc_html__( 'Mobile Menu Design Style', 'Avada' ),
						'description' => esc_html__( 'Controls the design of the mobile menu. Flyout design style only allows parent level menu items.', 'Avada' ),
						'id'          => 'mobile_menu_design',
						'default'     => 'modern',
						'type'        => 'radio-buttonset',
						'class'       => 'fusion-gutter-and-or-and',
						'choices'     => array(
							'classic' => esc_html__( 'Classic', 'Avada' ),
							'modern'  => esc_html__( 'Modern', 'Avada' ),
							'flyout'  => esc_html__( 'Flyout', 'Avada' ),
						),
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
						),
					),
					'mobile_menu_icons_top_margin'   => array(
						'label'       => esc_html__( 'Mobile Menu Icons Top Margin', 'Avada' ),
						'description' => esc_html__( 'Controls the top margin for the icons in the modern and flyout mobile menu design.', 'Avada' ),
						'id'          => 'mobile_menu_icons_top_margin',
						'default'     => '0',
						'type'        => 'slider',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'choices'     => array(
							'min'  => '0',
							'max'  => '200',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'classic',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'classic',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
						),
					),
					'mobile_menu_nav_height'         => array(
						'label'       => esc_html__( 'Mobile Menu Dropdown Item Height', 'Avada' ),
						'description' => esc_html__( 'Controls the height of each dropdown menu item.', 'Avada' ),
						'id'          => 'mobile_menu_nav_height',
						'default'     => '35',
						'type'        => 'slider',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'choices'     => array(
							'min'  => '0',
							'max'  => '200',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_nav_submenu_slideout'    => array(
						'label'       => esc_html__( 'Mobile Menu Dropdown Slide Outs', 'Avada' ),
						'description' => esc_html__( 'Turn on to allow dropdown sections to slide out when tapped.', 'Avada' ),
						'id'          => 'mobile_nav_submenu_slideout',
						'default'     => '1',
						'type'        => 'switch',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_menu_search'             => array(
						'label'       => esc_html__( 'Display Mobile Menu Search Icon/Field', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the search icon/field in the mobile menu.', 'Avada' ),
						'id'          => 'mobile_menu_search',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'mobile_menu_submenu_indicator'  => array(
						'label'       => esc_html__( 'Mobile Menu Sub-Menu Indicator', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the mobile menu sub-menu indicator: "-".', 'Avada' ),
						'id'          => 'mobile_menu_submenu_indicator',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_header_bg_color'         => array(
						'label'       => esc_html__( 'Mobile Header Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the header on mobile devices.', 'Avada' ),
						'id'          => 'mobile_header_bg_color',
						'default'     => '#ffffff',
						'type'        => 'color-alpha',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'mobile_archive_header_bg_color' => array(
						'label'       => esc_html__( 'Mobile Archive Header Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the archive page header on mobile devices.', 'Avada' ),
						'id'          => 'mobile_archive_header_bg_color',
						'type'        => 'color-alpha',
						'default'     => '#ffffff',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'mobile_menu_background_color'   => array(
						'label'       => esc_html__( 'Mobile Menu Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color of the mobile menu dropdown and classic mobile menu box.', 'Avada' ),
						'id'          => 'mobile_menu_background_color',
						'default'     => '#f9f9f9',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_menu_hover_color'        => array(
						'label'       => esc_html__( 'Mobile Menu Background Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background hover color of the mobile menu dropdown.', 'Avada' ),
						'id'          => 'mobile_menu_hover_color',
						'default'     => '#f6f6f6',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_menu_border_color'       => array(
						'label'       => esc_html__( 'Mobile Menu Border Color', 'Avada' ),
						'description' => esc_html__( 'Controls the border and divider colors of the mobile menu dropdown and classic mobile menu box.', 'Avada' ),
						'id'          => 'mobile_menu_border_color',
						'default'     => '#dadada',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_menu_toggle_color'       => array(
						'label'       => esc_html__( 'Mobile Menu Toggle Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the mobile menu toggle icon.', 'Avada' ),
						'id'          => 'mobile_menu_toggle_color',
						'default'     => ( isset( $settings['mobile_menu_border_color'] ) ) ? $settings['mobile_menu_border_color'] : '#dadada',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
					'mobile_menu_typography_info'    => array(
						'label'       => esc_html__( 'Mobile Menu Typography', 'Avada' ),
						'description' => '',
						'id'          => 'mobile_menu_typography_info',
						'type'        => 'info',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
						),
					),
					'mobile_menu_typography'         => array(
						'id'          => 'mobile_menu_typography',
						'label'       => esc_html__( 'Mobile Menu Typography', 'Avada' ),
						'description' => esc_html__( 'These settings control the typography for mobile menu.', 'Avada' ),
						'type'        => 'typography',
						'class'       => 'fusion-gutter-and-or-and',
						'choices'     => array(
							'font-family'    => true,
							'font-size'      => true,
							'font-weight'    => true,
							'line-height'    => true,
							'letter-spacing' => true,
							'color'          => true,
						),
						'default'     => array(
							'font-family'    => 'PT Sans',
							'font-size'      => '12px',
							'font-weight'    => '400',
							'line-height'    => '35px',
							'letter-spacing' => '0',
							'color'          => '#333333',
						),
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
						),
					),
					'mobile_menu_font_hover_color'   => array(
						'label'       => esc_html__( 'Mobile Menu Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the hover color of the mobile menu item. Also, used to highlight current mobile menu item.', 'Avada' ),
						'id'          => 'mobile_menu_font_hover_color',
						'default'     => '#333333',
						'type'        => 'color-alpha',
						'class'       => 'fusion-gutter-and-or-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
						),
					),
					'mobile_menu_text_align'         => array(
						'label'       => esc_html__( 'Mobile Menu Text Align', 'Avada' ),
						'description' => esc_html__( 'Controls the mobile menu text alignment.', 'Avada' ),
						'id'          => 'mobile_menu_text_align',
						'default'     => 'left',
						'choices'     => array(
							'left'   => esc_html__( 'Left', 'Avada' ),
							'center' => esc_html__( 'Center', 'Avada' ),
							'right'  => esc_html__( 'Right', 'Avada' ),
						),
						'type'        => 'radio-buttonset',
						'class'       => 'fusion-gutter-and-and-or-and-and',
						'required'    => array(
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'Top',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
							array(
								'setting'  => 'responsive',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'mobile_menu_design',
								'operator' => '!=',
								'value'    => 'flyout',
							),
						),
					),
				),
			),
			'mega_menu_subsection'       => array(
				'label'  => esc_html__( 'Mega Menu', 'Avada' ),
				'id'     => 'mega_menu_subsection',
				'type'   => 'sub-section',
				'fields' => array(
					'header_v6_used_note'            => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Mega Menu Options are only available when using Header Layouts #1-5. Your current Header Layout #6 does not utilize the mega menu.', 'Avada' ) . '</div>',
						'id'          => 'header_v6_used_note',
						'type'        => 'custom',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '==',
								'value'    => 'v6',
							),
						),
					),
					'megamenu_disabled_note'         => ( '0' === Avada()->settings->get( 'dependencies_status' ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Mega Menu is disabled in Advanced > Theme Features section. Please enable it to see the options.', 'Avada' ) . '</div>',
						'id'          => 'megamenu_disabled_note',
						'type'        => 'custom',
						'required'    => array(
							array(
								'setting'  => 'disable_megamenu',
								'operator' => '=',
								'value'    => '0',
							),
						),
					),
					'megamenu_max_width'             => array(
						'label'       => esc_html__( 'Mega Menu Max-Width', 'Avada' ),
						'description' => esc_html__( 'Controls the max width of the mega menu.', 'Avada' ),
						'id'          => 'megamenu_max_width',
						'default'     => '1100',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '4096',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'disable_megamenu',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'megamenu_title_size'            => array(
						'label'       => esc_html__( 'Mega Menu Column Title Size', 'Avada' ),
						'description' => esc_html__( 'Controls the font size for mega menu column titles.', 'Avada' ),
						'id'          => 'megamenu_title_size',
						'default'     => '18px',
						'type'        => 'dimension',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'disable_megamenu',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'megamenu_item_vertical_padding' => array(
						'label'       => esc_html__( 'Mega Menu Dropdown Item Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the top/bottom padding for mega menu dropdown items.', 'Avada' ),
						'id'          => 'megamenu_item_vertical_padding',
						'default'     => '5',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '50',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'disable_megamenu',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
					'megamenu_item_display_divider'  => array(
						'label'       => esc_html__( 'Mega Menu Item Divider', 'Avada' ),
						'description' => esc_html__( 'Turn on to display a divider between mega menu dropdown items.', 'Avada' ),
						'id'          => 'megamenu_item_display_divider',
						'default'     => '0',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							),
							array(
								'setting'  => 'disable_megamenu',
								'operator' => '=',
								'value'    => '1',
							),
						),
					),
				),
			),
			'menu_icons_subsection'      => array(
				'label'  => esc_html__( 'Main Menu Icons', 'Avada' ),
				'id'     => 'menu_icons_subsection',
				'type'   => 'sub-section',
				'fields' => array(
					'menu_icons_note'       => array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Icons are available for both the main and dropdown menus. However, the options below only apply to the main menu. Dropdown menu icons do not use these options below, they follow the dropdown font size and color. The icons themselves can be added to your menu items in the Appearance > Menus section.', 'Avada' ) . '</div>',
						'id'          => 'menu_icons_note',
						'type'        => 'custom',
					),
					'menu_icon_position'    => array(
						'label'       => esc_html__( 'Main Menu Icon Position', 'Avada' ),
						'description' => esc_html__( 'Controls the main menu icon position.', 'Avada' ),
						'id'          => 'menu_icon_position',
						'default'     => 'left',
						'choices'     => array(
							'top'    => esc_html__( 'Top', 'Avada' ),
							'right'  => esc_html__( 'Right', 'Avada' ),
							'bottom' => esc_html__( 'Bottom', 'Avada' ),
							'left'   => esc_html__( 'Left', 'Avada' ),
						),
						'type'        => 'radio-buttonset',
					),
					'menu_icon_size'        => array(
						'label'       => esc_html__( 'Main Menu Icon Size', 'Avada' ),
						'description' => esc_html__( 'Controls the size of the top-level menu icons.', 'Avada' ),
						'id'          => 'menu_icon_size',
						'default'     => ( isset( $settings['nav_typography']['font-size'] ) && ! empty( $settings['nav_typography']['font-size'] ) && false !== strpos( $settings['nav_typography']['font-size'], 'px' ) ) ? intval( $settings['nav_typography']['font-size'] ) : '14',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '0',
							'max'  => '100',
							'step' => '1',
						),
					),
					'menu_icon_color'       => array(
						'label'       => esc_html__( 'Main Menu Icon Color', 'Avada' ),
						'description' => esc_html__( 'Controls the color of the top-level main menu icons.', 'Avada' ),
						'id'          => 'menu_icon_color',
						'default'     => ( isset( $settings['nav_typography']['color'] ) && ! empty( $settings['nav_typography']['color'] ) ) ? $settings['nav_typography']['color'] : '#333333',
						'type'        => 'color-alpha',
					),
					'menu_icon_hover_color' => array(
						'label'       => esc_html__( 'Main Menu Icon Hover Color', 'Avada' ),
						'description' => esc_html__( 'Controls the hover color of the top-level main menu icons.', 'Avada' ),
						'id'          => 'menu_icon_hover_color',
						'default'     => ( isset( $settings['primary_color'] ) && ! empty( $settings['primary_color'] ) ) ? $settings['primary_color'] : '#a0ce4e',
						'type'        => 'color-alpha',
					),
					'menu_thumbnail_size'   => array(
						'label'       => esc_html__( 'Mega Menu Thumbnail Size', 'Avada' ),
						'description' => esc_html__( 'Controls the width and height of the top-level mega menu thumbnails. Use "auto" for automatic resizing if you added either width or height.', 'Avada' ),
						'id'          => 'menu_thumbnail_size',
						'units'       => false,
						'default'     => array(
							'width'  => '26px',
							'height' => ( isset( $settings['nav_typography']['font-size'] ) && ! empty( $settings['nav_typography']['font-size'] ) ) ? $settings['nav_typography']['font-size'] : '14px',
						),
						'type'        => 'dimensions',
						'required'    => array(
							array(
								'setting'  => 'disable_megamenu',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
			),
		),
	);

	return $sections;

}
