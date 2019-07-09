<?php

/**
 * Class WPV_Shortcode_Post_Excerpt_GUI
 *
 * @since 2.5.0
 */
class WPV_Shortcode_Post_Excerpt_GUI extends WPV_Shortcode_Base_GUI {
	
	/**
	 * Register the wpv-post-excerpt shortcode in the GUI API.
	 *
	 * @param $views_shortcodes
	 *
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function register_shortcode_data( $views_shortcodes ) {
		$views_shortcodes['wpv-post-excerpt'] = array(
			'callback' => array( $this, 'get_shortcode_data' )
		);
		return $views_shortcodes;
	}
	
	/*
	 * Get the wpv-post-excerpt shortcode attributes data.
	 *
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function get_shortcode_data() {
		$data = array(
			'name'           => __( 'Post excerpt', 'wpv-views' ),
			'label'          => __( 'Post excerpt', 'wpv-views' ),
			'post-selection' => true,
			'attributes'		=> array(
            'display-options' => array(
                'label'		=> __( 'Display options', 'wpv-views' ),
                'header'	=> __( 'Display options', 'wpv-views' ),
                'fields'	=> array(
	                'output'	=> array(
		                'label'			=> __( 'Display', 'wpv-views' ),
		                'type'			=> 'radio',
		                'options'		=> array(
			                'formatted'	=> __( 'Formatted based on the options below', 'wpv-views' ),
			                'raw'	    => __( 'As stored in database', 'wpv-views' ),
		                ),
		                'default'		=> 'formatted',
	                ),
					'length_combo'	=> array(
						'label'		=> __( 'Excerpt length', 'wpv-views' ),
						'type'		=> 'grouped',
						'fields'	=> array(
							'length'	=> array(
								'pseudolabel'	=> __( 'Length count', 'wpv-views'),
								'type'			=> 'number',
								'default'		=> '',
								'description'	=> __( 'This will shorten the excerpt to a specific length. Leave blank for default.', 'wpv-views' ),
								'placeholder'	=> __( 'Enter the excerpt length.', 'wpv-views' ),
							),
							'count'		=> array(
								'pseudolabel'	=> __( 'Count length by', 'wpv-views' ),
								'type'			=> 'radio',
								'options'		=> array(
									'char'		=> __( 'Characters', 'wpv-views' ),
									'word'		=> __( 'Words', 'wpv-views' ),
								),
								'default'		=> 'char',
								'description'	=> __( 'You can create an excerpt based on the number of words or characters.', 'wpv-views' ),
							),
						)
					),
                    'more'		=> array(
                        'label'			=> __( 'Ellipsis text', 'wpv-views' ),
                        'type'			=> 'text',
						'description'	=> __( 'This will be added after the excerpt, as an invitation to keep reading.', 'wpv-views' ),
                        'placeholder'	=> __( 'Read more...', 'wpv-views' ),
                    ),
					'format'	=> array(
						'label'			=> __( 'Formatting', 'wpv-views' ),
						'type'			=> 'radio',
						'options' 		=> array(
                            'autop'		=> __( 'Wrap the excerpt in a paragraph', 'wpv-views' ),
                            'noautop'	=> __( 'Do not wrap the excerpt in a paragraph', 'wpv-views' ),
                        ),
						'default'		=> 'autop',
						'description'	=> __( 'Whether the excerpt should be wrapped in paragraph tags.', 'wpv-views' ),
					)
                ),
            ),
        ),
		);
		return $data;
	}
	
	
}