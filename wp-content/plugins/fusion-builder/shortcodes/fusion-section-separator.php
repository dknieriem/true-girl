<?php

if ( fusion_is_element_enabled( 'fusion_section_separator' ) ) {

	if ( ! class_exists( 'FusionSC_SectionSeparator' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_SectionSeparator extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0.0
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
				add_filter( 'fusion_attr_section-separator-shortcode', array( $this, 'attr' ) );
				add_filter( 'fusion_attr_section-separator-shortcode-icon', array( $this, 'icon_attr' ) );
				add_filter( 'fusion_attr_section-separator-shortcode-divider-candy', array( $this, 'divider_candy_attr' ) );
				add_filter( 'fusion_attr_section-separator-shortcode-divider-candy-arrow', array( $this, 'divider_candy_arrow_attr' ) );
				add_filter( 'fusion_attr_section-separator-shortcode-divider-rounded-split', array( $this, 'divider_rounded_split_attr' ) );
				add_filter( 'fusion_attr_section-separator-shortcode-divider-svg', array( $this, 'divider_svg_attr' ) );

				add_shortcode( 'fusion_section_separator', array( $this, 'render' ) );

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

				$defaults = FusionBuilder::set_shortcode_defaults(
					array(
						'divider_type'     => 'triangle',
						'divider_position' => 'center',
						'hide_on_mobile'   => fusion_builder_default_visibility( 'string' ),
						'class'            => '',
						'id'               => '',
						'backgroundcolor'  => $fusion_settings->get( 'section_sep_bg' ),
						'bordersize'       => $fusion_settings->get( 'section_sep_border_size' ),
						'bordercolor'      => $fusion_settings->get( 'section_sep_border_color' ),
						'divider_candy'    => 'top',
						'icon'             => '',
						'icon_color'       => $fusion_settings->get( 'icon_color' ),
					),
					$args,
					'fusion_section_separator'
				);

				$defaults['bordersize'] = FusionBuilder::validate_shortcode_attr_value( $defaults['bordersize'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				if ( 'triangle' === $divider_type ) {
					if ( $icon ) {
						if ( ! $icon_color ) {
							$this->args['icon_color'] = $bordercolor;
						}

						$icon = '<div ' . FusionBuilder::attributes( 'section-separator-shortcode-icon' ) . '></div>';
					}

					$candy = '<div ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-candy-arrow' ) . '></div><div ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-candy' ) . '></div>';

					if ( false !== strpos( $this->args['divider_candy'], 'top' ) && false !== strpos( $this->args['divider_candy'], 'bottom' ) ) {
						$candy = '<div ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-candy triangle' ) . '></div>';
					}

					$candy = $icon . $candy;
				} elseif ( 'bigtriangle' === $divider_type ) {
					$candy = '<svg class="fusion-big-triangle-candy" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100" viewBox="0 0 100 100" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';

					if ( 'top' === $divider_candy ) {
						if ( 'right' === $divider_position ) {
							$candy .= '<path d="M0 100 L75 0 L100 100 Z"></path>';
						} elseif ( 'left' === $divider_position ) {
							$candy .= '<path d="M0 100 L25 2 L100 100 Z"></path>';
						} else {
							$candy .= '<path d="M0 100 L50 2 L100 100 Z"></path>';
						}
					} else {
						if ( 'right' === $divider_position ) {
							$candy .= '<path d="M-1 -1 L75 99 L101 -1 Z"></path>';
						} elseif ( 'left' === $divider_position ) {
							$candy .= '<path d="M0 -1 L25 100 L101 -1 Z"></path>';
						} else {
							$candy .= '<path d="M-1 -1 L50 99 L101 -1 Z"></path>';
						}
					}

					$candy .= '</svg>';
				} elseif ( 'slant' === $divider_type ) {
					$candy = '<svg class="fusion-slant-candy" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100" viewBox="0 0 100 102" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';

					if ( 'left' === $divider_position && 'top' === $divider_candy ) {
						$candy .= '<path d="M100 -1 L100 100 L0 0 Z"></path>';
					} elseif ( 'right' === $divider_position && 'top' === $divider_candy ) {
						$candy .= '<path d="M0 100 L0 -1 L100 0 Z"></path>';
					} elseif ( 'right' === $divider_position && 'bottom' === $divider_candy ) {
						$candy .= '<path d="M100 0 L-2 100 L101 100 Z"></path>';
					} else {
						$candy .= '<path d="M0 0 L0 99 L100 99 Z"></path>';
					}
					$candy .= '</svg>';
				} elseif ( 'rounded-split' === $divider_type ) {
					$candy = sprintf( '<div %s></div>', FusionBuilder::attributes( 'section-separator-shortcode-divider-rounded-split' ) );
				} elseif ( 'big-half-circle' === $divider_type ) {
					$candy = '<svg class="fusion-big-half-circle-candy" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100" viewBox="0 0 100 100" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';

					if ( 'top' === $divider_candy ) {
						$candy .= '<path d="M0 100 C40 0 60 0 100 100 Z"></path>';
					} else {
						$candy .= '<path d="M0 0 C55 180 100 0 100 0 Z"></path>';
					}

					$candy .= '</svg>';
				} elseif ( 'curved' === $divider_type ) {
					$candy = '<svg class="fusion-curved-candy" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100" viewBox="0 0 100 100" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';

					if ( 'left' === $divider_position ) {
						if ( 'top' === $divider_candy ) {
							$candy .= '<path d="M0 100 C 20 0 50 0 100 100 Z"></path>';
						} else {
							$candy .= '<path d="M0 0 C 20 100 50 100 100 0 Z"></path>';
						}
					} else {
						if ( 'top' === $divider_candy ) {
							$candy .= '<path d="M0 100 C 60 0 75 0 100 100 Z"></path>';
						} else {
							$candy .= '<path d="M0 0 C 50 100 80 100 100 0 Z"></path>';
						}
					}
					$candy .= '</svg>';
				} elseif ( 'clouds' === $divider_type ) {
					$candy  = '<svg class="fusion-clouds-candy" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100" viewBox="0 0 100 100" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';
					$candy .= '<path d="M-5 100 Q 0 20 5 100 Z"></path>
								<path d="M0 100 Q 5 0 10 100"></path>
								<path d="M5 100 Q 10 30 15 100"></path>
								<path d="M10 100 Q 15 10 20 100"></path>
								<path d="M15 100 Q 20 30 25 100"></path>
								<path d="M20 100 Q 25 -10 30 100"></path>
								<path d="M25 100 Q 30 10 35 100"></path>
								<path d="M30 100 Q 35 30 40 100"></path>
								<path d="M35 100 Q 40 10 45 100"></path>
								<path d="M40 100 Q 45 50 50 100"></path>
								<path d="M45 100 Q 50 20 55 100"></path>
								<path d="M50 100 Q 55 40 60 100"></path>
								<path d="M55 100 Q 60 60 65 100"></path>
								<path d="M60 100 Q 65 50 70 100"></path>
								<path d="M65 100 Q 70 20 75 100"></path>
								<path d="M70 100 Q 75 45 80 100"></path>
								<path d="M75 100 Q 80 30 85 100"></path>
								<path d="M80 100 Q 85 20 90 100"></path>
								<path d="M85 100 Q 90 50 95 100"></path>
								<path d="M90 100 Q 95 25 100 100"></path>
								<path d="M95 100 Q 100 15 105 100 Z"></path>';
					$candy .= '</svg>';
				} elseif ( 'horizon' === $divider_type ) {
					$candy = '<svg class="fusion-horizon-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 0 1000 173" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';
					if ( 'top' === $divider_candy ) {
						$candy .= '<polygon class="st0" points="1000,173 0,173 0,0 495.8,130.8 1000,0 "/>
									<polygon class="st1" points="1000,173.4 0,173.4 0,38.3 495.8,131 1000,38.3 "/>
									<polygon class="st2" points="1000,173.4 0,173.4 0,107.1 495.8,131 1000,107.1 "/>
									<polygon class="st3" points="1000,173.4 0,173.4 0,169.6 495.8,131 1000,169.6 "/>
								';
					} else {
						$candy .= '<polygon class="st0" points="1000,173 495.8,42.2 0,173 0,0 1000,0 "/>
									<polygon class="st1" points="1000,134.8 495.8,42.1 0,134.8 0,-0.3 1000,-0.3 "/>
									<polygon class="st2" points="1000,66.1 495.8,42.1 0,66.1 0,-0.3 1000,-0.3 "/>
									<polygon class="st3" points="1000,3.5 495.8,42.1 0,3.5 0,-0.3 1000,-0.3 "/>
								';
					}
					$candy .= '</svg>';
				} elseif ( 'hills' === $divider_type ) {
					if ( 'top' === $divider_candy ) {
						$candy = '<svg class="fusion-hills-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 73 1000 104" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';
						$candy .= '<path class="st4" d="M0,177.7h1000v-75.5c-47.9,19.6-117.7,41.4-188.7,41.1c-125.9-0.5-156.1-70.6-249.8-70.6c-87,0-131.5,78.3-239.3,80.1C214.4,154.6,165.1,96.9,48.8,95C32,94.7,15.7,96.8,0,100.6V177.7z"/>';
					} else {
						$candy = '<svg class="fusion-hills-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 1 1000 104" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';
						$candy .= '<path class="st4" d="M0,0.1h1000v75.5C952.1,56,882.3,34.2,811.3,34.5c-125.9,0.5-156.1,70.6-249.8,70.6c-87,0-131.5-78.3-239.3-80.1C214.4,23.2,165.1,80.9,48.8,82.8C32,83.1,15.7,81,0,77.2V0.1z"/>';
					}
					$candy .= '</svg>';
				} elseif ( 'hills_opacity' === $divider_type ) {
					if ( 'top' === $divider_candy ) {
						$candy = '<svg class="fusion-hills-opacity-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 1 1000 176" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';
						$candy .= '<path class="st0" d="M0,177.7h1000V40.5c-27.4-21-58.7-36.7-94.8-36.7c-110.2,0-193.5,91.6-283,91.6S427.4,6.5,343.6,6.5S204.7,86.7,110.7,86.7c-61.6,0-74-31-110.7-52.4V177.7z"/>
									<path class="st1" d="M1001,176.7v-74.1c-38.8,15.5-79.9,26.7-115.9,25.2C777.3,123.4,697.8,20.6,567.2,14.3C460.6,9.3,231.1,131.6,147.8,176.7H1001z"/>
									<path class="st2" d="M0,176.7h1000V87.8c-56.2-29.8-114.2-71-195.2-72.2c-130.7-2-155.2,84.7-334,84.7S197.4-0.5,72.5,1.1C45.4,1.4,21.3,5.1,0,10.6V176.7z"/>
									<path class="st3" d="M0,177.7h1000V78.5c-36.3,19.1-78.8,34.5-136.3,35.4C716.1,116.2,725.4,86,572.2,85.4c-153.2-0.6-135.6,45.9-298.3,42.7C129.7,125.3,89.1,43.4,0,27.4V177.7z"/>
									<path class="st4" d="M0,177.7h1000v-75.5c-47.9,19.6-117.7,41.4-188.7,41.1c-125.9-0.5-156.1-70.6-249.8-70.6c-87,0-131.5,78.3-239.3,80.1C214.4,154.6,165.1,96.9,48.8,95C32,94.7,15.7,96.8,0,100.6V177.7z"/>
								';
					} else {
						$candy = '<svg class="fusion-hills-opacity-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 0 1000 176" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';
						$candy .= '<path class="st0" d="M0,0.1h1000v137.2c-27.4,21-58.7,36.7-94.8,36.7c-110.2,0-193.5-91.6-283-91.6s-194.8,88.9-278.6,88.9S204.7,91.1,110.7,91.1c-61.6,0-74,31-110.7,52.4V0.1z"/>
									<path class="st1" d="M1001,1.1v74.1C962.2,59.7,921.1,48.5,885.1,50c-107.8,4.4-187.3,107.2-317.9,113.5C460.6,168.6,231.1,46.2,147.8,1.1H1001z"/>
									<path class="st2" d="M0,1.1h1000V90c-56.2,29.8-114.2,71-195.2,72.2c-130.7,2-155.2-84.7-334-84.7S197.4,178.3,72.5,176.7c-27.1-0.3-51.2-4-72.5-9.4V1.1z"/>
									<path class="st3" d="M0,0.1h1000v99.2c-36.3-19.1-78.8-34.5-136.3-35.4c-147.6-2.3-138.3,27.9-291.5,28.5C419,93,436.6,46.5,273.9,49.7C129.7,52.5,89.1,134.4,0,150.5V0.1z"/>
									<path class="st4" d="M0,0.1h1000v75.5C952.1,56,882.3,34.2,811.3,34.5c-125.9,0.5-156.1,70.6-249.8,70.6c-87,0-131.5-78.3-239.3-80.1C214.4,23.2,165.1,80.9,48.8,82.8C32,83.1,15.7,81,0,77.2V0.1z"/>
								';
					}
					$candy .= '</svg>';
				} elseif ( 'waves' === $divider_type ) {
					$y_min = ( 'top' === $divider_candy ) ? '53' : '0';
					$candy = '<svg class="fusion-waves-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 ' . $y_min . ' 1000 156" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';

					if ( 'left' === $divider_position ) {
						if ( 'top' === $divider_candy ) {
							$candy .= '<path class="st3" d="M0,52.7c3.9,0.2,7.8,0.4,11.6,0.7c201.7,14.7,222.6,121.4,424.3,138.2c180.6,15.1,221-40.1,395.4-20.8c62.5,7,119,20.3,168.7,37v3.1H0L0,52.7z"/>';
						} else {
							$candy .= '<path class="st3" d="M0,158.2c3.9-0.2,7.8-0.4,11.6-0.7C213.3,142.8,234.2,36.1,435.9,19.3c180.6-15.1,221,40.1,395.4,20.8c62.5-7,119-20.3,168.7-37V0L0,0L0,158.2z"/>';
						}
					} else {
						if ( 'top' === $divider_candy ) {
							$candy .= '<path class="st3" d="M1000,52.7c-3.9,0.2-7.8,0.4-11.6,0.7C786.7,68.1,765.8,174.8,564.1,191.6c-180.6,15.1-221-40.1-395.4-20.8c-62.5,7-119,20.3-168.7,37v3.1h1000V52.7z"/>';
						} else {
							$candy .= '<path class="st3" d="M1000,158.2c-3.9-0.2-7.8-0.4-11.6-0.7C786.7,142.8,765.8,36.1,564.1,19.3c-180.6-15.1-221,40.1-395.4,20.8c-62.5-7-119-20.3-168.7-37V0h1000V158.2z"/>';
						}
					}

					$candy .= '</svg>';
				} elseif ( 'waves_opacity' === $divider_type ) {
					$candy = '<svg class="fusion-waves-opacity-candy" fill="' . esc_attr( $this->args['backgroundcolor'] ) . '" xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 0 1000 211" preserveAspectRatio="none" ' . FusionBuilder::attributes( 'section-separator-shortcode-divider-svg' ) . '>';

					if ( 'left' === $divider_position ) {
						if ( 'top' === $divider_candy ) {
							$candy .= '<path class="st0" d="M0,0.2c18.9-0.5,37.8-0.1,56.3,1.2C258.1,16.1,299,124,500.6,140.9c180.5,15.1,279-59.7,453.4-40.4c16.5,1.8,31.7,4.3,46,7.4V211H0V0.2z"/>
										<path class="st1" d="M0,19.4c29.5-1.6,58.2-1.6,84.8,0.4c201.8,14.7,192.7,119.7,394.4,136.5c180.6,15.1,272-56.8,446.4-37.5c27.6,3.1,52.2,8,74.4,14.3v78H0V19.4z"/>
										<path class="st2" d="M0,45.3C21.7,45,42.8,45.5,62.7,47c201.8,14.6,193.2,109.9,394.9,126.7c180.5,15.1,221.5-49.9,395.9-30.6C907,149,955.9,161,1000,176v35.1H0V45.3z"/>
										<path class="st3" d="M0,52.8c3.9,0.2,7.8,0.4,11.6,0.7c201.7,14.7,222.6,121.4,424.3,138.2c180.6,15.1,221-40.1,395.4-20.8c62.5,7,119,20.3,168.7,37v3.1H0V52.8z"/>
									';
						} else {
							$candy .= '<path class="st0" d="M0,210.4c18.9,0.5,37.8,1.6,56.3,0.3C258.1,196.1,299,87.1,500.6,70.3c180.5-15.1,279,59.5,453.4,40.3c16.5-1.8,31.7-4.3,46-7.4V0.4H0V210.4z"/>
										<path class="st1" d="M0,191.8c29.5,1.6,58.2,1.6,84.8-0.4C286.6,176.7,277.5,71.7,479.2,54.9c180.6-15.1,272,56.8,446.4,37.5c27.6-3.1,52.2-8,74.4-14.3v-78H0V191.8z"/>
										<path class="st2" d="M0,165.9c21.7,0.3,42.8-0.2,62.7-1.7C264.5,149.6,255.9,54.3,457.6,37.5c180.5-15.1,221.5,49.9,395.9,30.6C907,62.2,955.9,50.2,1000,35.2V0.1H0V165.9z"/>
										<path class="st3" d="M0,158.3c3.9-0.2,7.8-0.4,11.6-0.7C213.3,142.9,234.2,36.2,435.9,19.4c180.6-15.1,221,40.1,395.4,20.8c62.5-7,119-20.3,168.7-37V0.1H0V158.3z"/>
									';
						}
					} else {
						if ( 'top' === $divider_candy ) {
							$candy .= '<path class="st0" d="M1000,0.2c-18.9-0.5-37.8-0.1-56.3,1.2C741.9,16.1,701,124,499.4,140.9C318.9,156,220.4,81.1,46,100.4c-16.5,1.8-31.7,4.3-46,7.4V211h1000V0.2z"/>
										<path class="st1" d="M1000,19.4c-29.5-1.6-58.2-1.6-84.8,0.4C713.4,34.4,722.5,139.4,520.8,156.2c-180.6,15.1-272-56.8-446.4-37.5c-27.6,3.1-52.2,8-74.4,14.3v78h1000V19.4z"/>
										<path class="st2" d="M1000,45.3c-21.7-0.3-42.8,0.2-62.7,1.7C735.5,61.5,744.1,156.8,542.4,173.6c-180.5,15.1-221.5-49.9-395.9-30.6C93,149,44.1,160.9,0,176v35.1h1000V45.3z"/>
										<path class="st3" d="M1000,52.8c-3.9,0.2-7.8,0.4-11.6,0.7C786.7,68.2,765.8,174.9,564.1,191.7c-180.6,15.1-221-40.1-395.4-20.8c-62.5,7-119,20.3-168.7,37v3.1h1000V52.8z"/>
									';
						} else {
							$candy .= '<path class="st0" d="M1000,210.8c-18.9,0.5-37.8,0.1-56.3-1.2C741.9,194.9,701,87,499.4,70.1C318.9,55,220.4,129.9,46,110.6c-16.5-1.8-31.7-4.3-46-7.4V0h1000V210.8z"/>
										<path class="st1" d="M1000,191.8c-29.5,1.6-58.2,1.6-84.8-0.4C713.4,176.7,722.5,71.7,520.8,54.9c-180.6-15.1-272,56.8-446.4,37.5c-27.6-3.1-52.2-8-74.4-14.3v-78h1000V191.8z"/>
										<path class="st2" d="M1000,165.9c-21.7,0.3-42.8-0.2-62.7-1.7C735.5,149.6,744.1,54.3,542.4,37.5C361.9,22.4,320.9,87.4,146.5,68.1C93,62.2,44.1,50.2,0,35.2V0.1h1000V165.9z"/>
										<path class="st3" d="M1000,158.3c-3.9-0.2-7.8-0.4-11.6-0.7C786.7,142.9,765.8,36.2,564.1,19.4c-180.6-15.1-221,40.1-395.4,20.8c-62.5-7-119-20.3-168.7-37V0.1h1000V158.3z"/>
									';
						}
					}

					$candy .= '</svg>';
				}

				$html   = '<div ' . FusionBuilder::attributes( 'section-separator-shortcode' ) . '>' . $candy . '</div>';

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

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					array(
						'class' => 'fusion-section-separator section-separator ' . esc_attr( str_replace( '_', '-', $this->args['divider_type'] ) ),
					)
				);

				$attr['style'] = '';

				if ( 'triangle' === $this->args['divider_type'] ) {
					if ( $this->args['bordercolor'] ) {
						if ( 'bottom' === $this->args['divider_candy'] ) {
							$attr['style'] = 'border-bottom:' . $this->args['bordersize'] . ' solid ' . $this->args['bordercolor'] . ';';

						} elseif ( 'top' === $this->args['divider_candy'] ) {
							$attr['style'] = 'border-top:' . $this->args['bordersize'] . ' solid ' . $this->args['bordercolor'] . ';';

						} elseif ( false !== strpos( $this->args['divider_candy'], 'top' ) && false !== strpos( $this->args['divider_candy'], 'bottom' ) ) {
							$attr['style'] = 'border:' . $this->args['bordersize'] . ' solid ' . $this->args['bordercolor'] . ';';
						}
					}
				} elseif ( 'bigtriangle' === $this->args['divider_type'] || 'slant' === $this->args['divider_type'] || 'big-half-circle' === $this->args['divider_type'] || 'clouds' === $this->args['divider_type'] || 'curved' === $this->args['divider_type'] ) {
					$attr['style'] = 'padding:0;';
				} elseif ( 'horizon' === $this->args['divider_type'] || 'waves' === $this->args['divider_type'] || 'waves_opacity' === $this->args['divider_type'] || 'hills' === $this->args['divider_type'] || 'hills_opacity' === $this->args['divider_type'] ) {
					$attr['style'] = 'font-size:0;';
				}

				if ( 'rounded-split' === $this->args['divider_type'] ) {
					$attr['class'] .= ' rounded-split-separator';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				global $fusion_fwc_type, $fusion_col_type;

				if ( ! empty( $fusion_fwc_type ) ) {
					$margin_left  = $fusion_fwc_type['padding']['left'];
					$margin_right = $fusion_fwc_type['padding']['right'];
					if ( isset( $fusion_col_type['type'] ) && '1_1' !== $fusion_col_type['type'] ) {
						$margin_left  = fusion_builder_single_dimension( $fusion_col_type['padding'], 'left' );
						$margin_right = fusion_builder_single_dimension( $fusion_col_type['padding'], 'right' );
					}

					$margin_left = ( '0' === $margin_left ) ? $margin_left . 'px' : $margin_left;
					$margin_right = ( '0' === $margin_right ) ? $margin_right . 'px' : $margin_right;

					$margin_left_unitless  = (int) filter_var( $margin_left, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
					$margin_right_unitless = (int) filter_var( $margin_right, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

					$container_percentage = 100 - $margin_left_unitless - $margin_right_unitless;
					if ( false !== strpos( $margin_left, '%' ) ) {
						$margin_left_unitless_scaled = $margin_left_unitless / $container_percentage * 100;
					}

					if ( false !== strpos( $margin_right, '%' ) ) {
						$margin_right_unitless_scaled = $margin_right_unitless / $container_percentage * 100;
					}

					$viewport_width = '100vw';

					if ( 'Boxed' === $fusion_settings->get( 'layout' ) ) {
						$viewport_width = $fusion_settings->get( 'site_width' );
					}

					if ( 'Top' !== $fusion_settings->get( 'header_position' ) ) {
						$viewport_width = $viewport_width . ' - ' . intval( $fusion_settings->get( 'side_header_width' ) ) . 'px';
					}

					// 100% width template && non 100% interior width container.
					if ( $fusion_fwc_type['width_100_percent'] && 'contained' === $fusion_fwc_type['content'] && ( isset( $fusion_col_type['type'] ) && '1_1' === $fusion_col_type['type'] ) ) {

						// Both container paddings use px.
						if ( false !== strpos( $margin_left, 'px' ) && false !== strpos( $margin_right, 'px' ) ) {
							$margin_unit            = 'px';
							$margin_difference_half = abs( $margin_left_unitless - $margin_right_unitless ) / 2 . $margin_unit;

							if ( 'Boxed' === $fusion_settings->get( 'layout' ) ) {
								$margin_left_negative  = '-' . $margin_left;
								$margin_right_negative = '-' . $margin_right;
							} else {
								if ( $margin_left_unitless > $margin_right_unitless ) {
									$margin_left  = '- ' . $margin_difference_half;
									$margin_right = '+ ' . $margin_difference_half;
								} elseif ( $margin_left_unitless < $margin_right_unitless ) {
									$margin_left  = '+ ' . $margin_difference_half;
									$margin_right = '- ' . $margin_difference_half;
								} elseif ( $margin_left_unitless === $margin_right_unitless ) {
									$margin_left  = '';
									$margin_right = '';
								}

								$margin_left_negative  = 'calc( (' . $viewport_width . ' - 100% ) / -2 ' . $margin_left . ' )';
								$margin_right_negative = 'calc( (' . $viewport_width . ' - 100% ) / -2  ' . $margin_right . ' )';
							}
							$attr['class'] .= ' fusion-section-separator-with-offset';

							// Both container paddings use %.
						} elseif ( false !== strpos( $margin_left, '%' ) && false !== strpos( $margin_right, '%' ) ) {

							if ( 'Boxed' === $fusion_settings->get( 'layout' ) ) {
								$margin_unit = '%';

								$main_padding_unitless = filter_var( $fusion_settings->get( 'hundredp_padding' ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
								$main_padding_unit     = str_replace( $main_padding_unitless, '', $fusion_settings->get( 'hundredp_padding' ) );

								$margin_left_negative  = 'calc( ( 100% - ' . ( 2 * $main_padding_unitless ) . $main_padding_unit . ' ) * ' . ( -1 / 100 ) * $margin_left_unitless_scaled . ' )';
								$margin_right_negative = 'calc( ( 100% - ' . ( 2 * $main_padding_unitless ) . $main_padding_unit . ' ) * ' . ( -1 / 100 ) * $margin_right_unitless_scaled . ' )';
							} else {
								$margin_unit = 'vw';
								$margin_sum  = ' - ' . ( $margin_left_unitless + $margin_right_unitless ) . $margin_unit;

								$margin_left_negative  = 'calc( (' . $viewport_width . ' - 100% ' . $margin_sum . ') / -2 - ' . $margin_left_unitless . $margin_unit . ' )';
								$margin_right_negative = 'calc( (' . $viewport_width . ' - 100% ' . $margin_sum . ') / -2  - ' . $margin_right_unitless . $margin_unit . ' )';

								$attr['class'] .= ' fusion-section-separator-with-offset';
							}
						} else {
							// Mixed container padding units.
							$margin_left_final = $margin_left;
							if ( false !== strpos( $margin_left, '%' ) && 'Boxed' !== $fusion_settings->get( 'layout' ) ) {
								$margin_left_final = $margin_left_unitless . 'vw';
							}

							$margin_right_final = $margin_right;
							if ( false !== strpos( $margin_right, '%' ) && 'Boxed' !== $fusion_settings->get( 'layout' ) ) {
								$margin_right_final = $margin_right_unitless . 'vw';
							}

							$margin_left_negative  = 'calc( (' . $viewport_width . ' - 100% - ' . $margin_left . ' - ' . $margin_right . ') / -2 - ' . $margin_left_final . ' )';
							$margin_right_negative = 'calc( (' . $viewport_width . ' - 100% - ' . $margin_left . ' - ' . $margin_right . ') / -2 - ' . $margin_right_final . ' )';
						}
					} else {
						// Non 100% width template.
						if ( false !== strpos( $margin_left, '%' ) ) {
							$margin_left = $margin_left_unitless_scaled . '%';
							if ( false !== strpos( $margin_right, '%' ) ) {
								$margin_right = $margin_right_unitless_scaled . '%';
							}

							$margin_left_negative = 'calc( (100% + ' . $margin_left . ' + ' . $margin_right . ') * ' . $margin_left_unitless . ' / -100 )';
						} else {
							$margin_left_negative = '-' . $margin_left;
						}

						if ( false !== strpos( $margin_right, '%' ) ) {
							$margin_right = $margin_right_unitless_scaled . '%';
							if ( false !== strpos( $margin_left, '%' ) ) {
								$margin_left = $margin_left_unitless_scaled . '%';
							}

							$margin_right_negative = 'calc( (100% + ' . $margin_left . ' + ' . $margin_right . ') * ' . $margin_right_unitless . ' / -100 )';
						} else {
							$margin_right_negative = '-' . $margin_right;
						}
					}

					$attr['style'] .= 'margin-left:' . $margin_left_negative . ';';
					$attr['style'] .= 'margin-right:' . $margin_right_negative . ';';
				}

				return $attr;

			}

			/**
			 * Builds the rounded split attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function divider_svg_attr() {
				$attr = array();

				if ( 'bigtriangle' === $this->args['divider_type'] || 'slant' === $this->args['divider_type'] || 'big-half-circle' === $this->args['divider_type'] || 'clouds' === $this->args['divider_type'] || 'curved' === $this->args['divider_type'] ) {
					$attr['style'] = sprintf( 'fill:%s;padding:0;', $this->args['backgroundcolor'] );
				}
				if ( 'slant' === $this->args['divider_type'] && 'bottom' === $this->args['divider_candy'] ) {
					$attr['style'] = sprintf( 'fill:%s;padding:0;margin-bottom:-3px;display:block', $this->args['backgroundcolor'] );
				}

				return $attr;
			}

			/**
			 * Builds the rounded split attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function divider_rounded_split_attr() {
				return array(
					'class' => 'rounded-split ' . $this->args['divider_candy'],
					'style' => 'background-color:' . $this->args['backgroundcolor'] . ';',
				);
			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {

				$attr = array(
					'class' => 'section-separator-icon icon ' . FusionBuilder::font_awesome_name_handler( $this->args['icon'] ),
					'style' => 'color:' . $this->args['icon_color'] . ';',
				);

				if ( FusionBuilder::strip_unit( $this->args['bordersize'] ) > 1 ) {
					$divider_candy = $this->args['divider_candy'];
					if ( 'bottom' === $divider_candy ) {
						$attr['style'] .= 'bottom:-' . ( FusionBuilder::strip_unit( $this->args['bordersize'] ) + 10 ) . 'px;top:auto;';
					} elseif ( 'top' === $divider_candy ) {
						$attr['style'] .= 'top:-' . ( FusionBuilder::strip_unit( $this->args['bordersize'] ) + 10 ) . 'px;';
					}
				}
				return $attr;

			}

			/**
			 * Builds the divider attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function divider_candy_attr( $args ) {

				$attr = array(
					'class' => 'divider-candy',
				);

				$divider_candy = ( $args ) ? $args['divider_candy'] : $this->args['divider_candy'];

				if ( 'bottom' === $divider_candy ) {
					$attr['class'] .= ' bottom';
					$attr['style']  = 'bottom:-' . ( FusionBuilder::strip_unit( $this->args['bordersize'] ) + 20 ) . 'px;border-bottom:1px solid ' . $this->args['bordercolor'] . ';border-left:1px solid ' . $this->args['bordercolor'] . ';';
				} elseif ( 'top' === $divider_candy ) {
					$attr['class'] .= ' top';
					$attr['style']  = 'top:-' . ( FusionBuilder::strip_unit( $this->args['bordersize'] ) + 20 ) . 'px;border-bottom:1px solid ' . $this->args['bordercolor'] . ';border-left:1px solid ' . $this->args['bordercolor'] . ';';
					// Modern setup, that won't work in IE8.
				} elseif ( false !== strpos( $this->args['divider_candy'], 'top' ) && false !== strpos( $this->args['divider_candy'], 'bottom' ) ) {
					$attr['class'] .= ' both';
					$attr['style']  = 'background-color:' . $this->args['backgroundcolor'] . ';border:1px solid ' . $this->args['bordercolor'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the divider-arrow attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function divider_candy_arrow_attr( $args ) {

				$attr = array(
					'class' => 'divider-candy-arrow',
				);

				$divider_candy = ( $args ) ? $args['divider_candy'] : $this->args['divider_candy'];

				// For borders of size 1, we need to hide the border line on the arrow, thus we set it to 0.
				$arrow_position = FusionBuilder::strip_unit( $this->args['bordersize'] );
				if ( '1' == $arrow_position ) {
					$arrow_position = 0;
				}

				if ( 'bottom' === $divider_candy ) {
					$attr['class'] .= ' bottom';
					$attr['style']  = 'top:' . $arrow_position . 'px;border-top-color: ' . $this->args['backgroundcolor'] . ';';
				} elseif ( 'top' === $divider_candy ) {
					$attr['class'] .= ' top';
					$attr['style']  = 'bottom:' . $arrow_position . 'px;border-bottom-color: ' . $this->args['backgroundcolor'] . ';';
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Section Separator settings.
			 */
			public function add_options() {

				return array(
					'section_separator_shortcode_section' => array(
						'label'       => esc_html__( 'Section Separator Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'sectionseparator_shortcode_section',
						'type'        => 'accordion',
						'fields'      => array(
							'section_sep_border_size'  => array(
								'label'       => esc_html__( 'Section Separator Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the section separator.', 'fusion-builder' ),
								'id'          => 'section_sep_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'choices'     => array(
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								),
							),
							'section_sep_bg'           => array(
								'label'       => esc_html__( 'Section Separator Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the section separator style.', 'fusion-builder' ),
								'id'          => 'section_sep_bg',
								'default'     => '#f6f6f6',
								'type'        => 'color-alpha',
							),
							'section_sep_border_color' => array(
								'label'       => esc_html__( 'Section Separator Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the separator.', 'fusion-builder' ),
								'id'          => 'section_sep_border_color',
								'default'     => '#f6f6f6',
								'type'        => 'color-alpha',
							),
						),
					),
				);
			}
		}
	}

	new FusionSC_SectionSeparator();

}

/**
 * Map shortcode to Fusion Builder.
 *
 * @since 1.0
 */
function fusion_element_section_separator() {

	global $fusion_settings;

	fusion_builder_map(
		array(
			'name'      => esc_attr__( 'Section Separator', 'fusion-builder' ),
			'shortcode' => 'fusion_section_separator',
			'icon'      => 'fusiona-ellipsis',
			'params'    => array(
				array(
					'type'        => 'select',
					'heading'     => esc_attr__( 'Section Separator Style', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the type of the section separator', 'fusion-builder' ),
					'param_name'  => 'divider_type',
					'value'       => array(
						'triangle'        => esc_attr__( 'Triangle', 'fusion-builder' ),
						'slant'           => esc_attr__( 'Slant', 'fusion-builder' ),
						'bigtriangle'     => esc_attr__( 'Big Triangle', 'fusion-builder' ),
						'rounded-split'   => esc_attr__( 'Rounded Split', 'fusion-builder' ),
						'curved'          => esc_attr__( 'Curved', 'fusion-builder' ),
						'big-half-circle' => esc_attr__( 'Big Half Circle', 'fusion-builder' ),
						'clouds'          => esc_attr__( 'Clouds', 'fusion-builder' ),
						'horizon'         => esc_attr__( 'Horizon', 'fusion-builder' ),
						'waves'           => esc_attr__( 'Waves', 'fusion-builder' ),
						'waves_opacity'   => esc_attr__( 'Waves Opacity', 'fusion-builder' ),
						'hills'           => esc_attr__( 'Hills', 'fusion-builder' ),
						'hills_opacity'   => esc_attr__( 'Hills Opacity', 'fusion-builder' ),
					),
					'default'     => 'triangle',
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Horizontal Position of the Section Separator', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the horizontal position of the section separator.', 'fusion-builder' ),
					'param_name'  => 'divider_position',
					'value'       => array(
						'left'   => esc_attr__( 'Left', 'fusion-builder' ),
						'center' => esc_attr__( 'Center', 'fusion-builder' ),
						'right'  => esc_attr__( 'Right', 'fusion-builder' ),
					),
					'default'     => 'center',
					'dependency'  => array(
						array(
							'element'  => 'divider_type',
							'value'    => 'triangle',
							'operator' => '!=',
						),
						array(
							'element'  => 'divider_type',
							'value'    => 'rounded-split',
							'operator' => '!=',
						),
						array(
							'element'  => 'divider_type',
							'value'    => 'big-half-circle',
							'operator' => '!=',
						),
						array(
							'element'  => 'divider_type',
							'value'    => 'clouds',
							'operator' => '!=',
						),
						array(
							'element'  => 'divider_type',
							'value'    => 'horizon',
							'operator' => '!=',
						),
						array(
							'element'  => 'divider_type',
							'value'    => 'hills',
							'operator' => '!=',
						),
						array(
							'element'  => 'divider_type',
							'value'    => 'hills_opacity',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Vertical Position of the Section Separator', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the vertical position of the section separator.', 'fusion-builder' ),
					'param_name'  => 'divider_candy',
					'value'       => array(
						'top'        => esc_attr__( 'Top', 'fusion-builder' ),
						'bottom'     => esc_attr__( 'Bottom', 'fusion-builder' ),
						'bottom,top' => esc_attr__( 'Top and Bottom', 'fusion-builder' ),
					),
					'default'     => 'top',
					'dependency'  => array(
						array(
							'element'  => 'divider_type',
							'value'    => 'clouds',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'iconpicker',
					'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
					'param_name'  => 'icon',
					'value'       => '',
					'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					'dependency'  => array(
						array(
							'element'  => 'divider_type',
							'value'    => 'triangle',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
					'description' => '',
					'param_name'  => 'icon_color',
					'value'       => '',
					'default'     => $fusion_settings->get( 'icon_color' ),
					'dependency'  => array(
						array(
							'element'  => 'divider_type',
							'value'    => 'triangle',
							'operator' => '==',
						),
						array(
							'element'  => 'icon',
							'value'    => '',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'range',
					'heading'     => __( 'Border', 'fusion-builder' ),
					'heading'     => esc_attr__( 'Border', 'fusion-builder' ),
					'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
					'param_name'  => 'bordersize',
					'value'       => '',
					'min'         => '0',
					'max'         => '50',
					'step'        => '1',
					'default'     => $fusion_settings->get( 'section_sep_border_size' ),
					'dependency'  => array(
						array(
							'element'  => 'divider_type',
							'value'    => 'triangle',
							'operator' => '==',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => __( 'Border Color', 'fusion-builder' ),
					'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the border color. ', 'fusion-builder' ),
					'param_name'  => 'bordercolor',
					'value'       => '',
					'default'     => $fusion_settings->get( 'section_sep_border_color' ),
					'dependency'  => array(
						array(
							'element'  => 'divider_type',
							'value'    => 'triangle',
							'operator' => '==',
						),
						array(
							'element'  => 'bordersize',
							'value'    => '0',
							'operator' => '!=',
						),
					),
				),
				array(
					'type'        => 'colorpickeralpha',
					'heading'     => esc_attr__( 'Background Color of the Section Separator', 'fusion-builder' ),
					'description' => esc_attr__( 'Controls the background color of the section separator style.', 'fusion-builder' ),
					'param_name'  => 'backgroundcolor',
					'value'       => '',
					'default'     => $fusion_settings->get( 'section_sep_bg' ),
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
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
					'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => '',
				),
			),
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_section_separator' );
