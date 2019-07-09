<?php
defined( 'ABSPATH' ) or exit;

/** @var MC4WP_Styles_Builder $builder */
$builder = $this;
$selector = $selector_prefix . $form_selector;

printf( "/* form #%d */\n", $form_id );
echo "$selector label, \n";
echo "$selector input, \n";
echo "$selector textarea, \n";
echo "$selector select, \n";
echo "$selector button {\n";
	echo "\t-webkit-box-sizing: border-box;\n";
	echo "\t-moz-box-sizing: border-box;\n";
	echo "\tbox-sizing: border-box;\n";
echo "}\n\n";


/**
 * Form Elements
 */
if( $builder->form_has_rules_for_element( $form_id, 'form' ) ) {

	echo "$selector {\n";
		echo "\tdisplay: block;\n";
		$builder->maybe_echo( "\tborder-color: %s; \n", $form_border_color );
		$builder->maybe_echo( "\tborder-style: solid; border-width: %dpx;\n", $form_border_width );
		$builder->maybe_echo( "\tpadding: %dpx;\n", $form_padding );
		$builder->maybe_echo( "\tbackground-color: %s !important;\n", $form_background_color );
		$builder->maybe_echo( "\tcolor: %s !important;\n", $form_font_color );
		$builder->maybe_echo( "\ttext-align: %s;\n", $form_text_align );
		$builder->maybe_echo( "\twidth: 100%%; max-width: %s !important;\n", $form_width );
		$builder->maybe_echo( "\tbackground-image: url('%s');\n", $form_background_image );

		if( in_array( $form_background_repeat, array( 'cover' ) ) ) {
			echo sprintf( "\tbackground-size: %s;\n", $form_background_repeat );
		} else {
			$builder->maybe_echo( "\tbackground-repeat: %s;\n", $form_background_repeat);
		}
		
	echo "}\n\n";

}

/**
 * Label Elements
 */
if( $builder->form_has_rules_for_element( $form_id, 'labels' ) ) {
	echo "$selector label {\n";
		echo "\tvertical-align: top;\n";
		echo "\tmargin-bottom: 6px;\n";
		$builder->maybe_echo( "\twidth: %s;\n", $labels_width );
		$builder->maybe_echo( "\tcolor: %s;\n", $labels_font_color );
		$builder->maybe_echo( "\tfont-size: %dpx;\n", $labels_font_size );
		$builder->maybe_echo( "\tdisplay: %s;\n", $labels_display );

		if( ! empty( $labels_font_style ) ) {
			if( $labels_font_style === 'italic' || $labels_font_style === 'bolditalic') {
				echo "\tfont-style: italic;\n";
			} else {
				echo "\tfont-style: normal;\n";
			}

			if( $labels_font_style === 'bold' || $labels_font_style === 'bolditalic') {
				echo "\tfont-weight: bold;\n";
			} else {
				echo "\tfont-weight: normal;\n";
			}
		}
	echo "}\n\n";

	// reset <span> elements inside <label> tag (choice HTML)
	if( $labels_font_style === 'bold' || $labels_font_style === 'bolditalic') {
		echo "$selector label span { font-weight: initial; }\n\n";
	}

	if( $labels_font_style === 'italic' || $labels_font_style === 'bolditalic') {
		echo "$selector label span { font-style: initial; }\n\n";
	}
}


/**
 * Input, Select & Textarea Elements
 */
if( $builder->form_has_rules_for_element( $form_id, 'fields' ) ) :


	echo "$selector input[type='text'],\n";
	echo "$selector input[type='email'],\n";
	echo "$selector input[type='url'],\n";
	echo "$selector input[type='tel'],\n";
	echo "$selector input[type='number'],\n";
	echo "$selector input[type='date'],\n";
	echo "$selector select,\n";
	echo "$selector textarea {\n";

		// start field rules
		echo "\tvertical-align: top;\n";
		echo "\tmargin-bottom: 6px;\n";
		echo "\tpadding: 6px 12px;\n";
		$builder->maybe_echo( "\twidth: 100%%; max-width: %s;\n", $fields_width );
		$builder->maybe_echo( "\tborder-color: %s !important;\n", $fields_border_color );
		$builder->maybe_echo( "\tborder-width: %dpx; border-style: solid;\n", $fields_border_width );
		$builder->maybe_echo( "\tdisplay: %s;\n", $fields_display );
		if( ! empty($fields_border_radius)) {
			echo "\t-webkit-border-radius: {$fields_border_radius}px;\n";
			echo "\t-moz-border-radius: {$fields_border_radius}px;\n";
			echo "\tborder-radius: {$fields_border_radius}px;\n";
		}
		if( ! empty($fields_height)) {
			echo "\theight: {$fields_height}px;\n";
		}

	echo "}\n\n";

endif;

/**
 * Input, Select & Textarea Elements (focus)
 */
if( $builder->form_has_rules_for_element( $form_id, 'fields_focus' ) ) :
	echo "$selector input[type='text']:focus,\n";
	echo "$selector input[type='email']:focus,\n";
	echo "$selector input[type='url']:focus,\n";
	echo "$selector input[type='tel']:focus,\n";
	echo "$selector input[type='number']:focus,\n";
	echo "$selector input[type='date']:focus,\n";
	echo "$selector select:focus,\n";
	echo "$selector textarea:focus {\n";
		$builder->maybe_echo( "\toutline: 2px solid %s;\n", $fields_focus_outline_color );
	echo "}\n\n";
endif;


/**
 * Choice Elements
 */
echo "$selector input[type='radio'],\n";
echo "$selector input[type='checkbox'] {\n";
	echo "\tmargin-right: 6px;\n";
	echo "\tdisplay: inline-block\n";
echo "}\n\n";


/**
 * Button Elements
 */
if( $builder->form_has_rules_for_element( $form_id, 'buttons' ) ) {

	echo "$selector input[type='submit'],\n";
	echo "$selector input[type='button'],\n";
	echo "$selector input[type='reset'],\n";
	echo "$selector button {\n";
		echo "\tvertical-align: top;\n";
	    echo "\ttext-shadow: none;\n";
	    echo "\tpadding: 6px 12px;\n";
	    echo "\tcursor: pointer;\n";
	    echo "\ttext-align: center;\n";
	    echo "\tline-height: normal;\n";
		echo "\tdisplay: inline-block;\n";
		$builder->maybe_echo( "\tbackground:none; filter: none; background-color: %s !important;\n", $buttons_background_color );
		$builder->maybe_echo( "\tcolor: %s !important;\n", $buttons_font_color );
		$builder->maybe_echo( "\tfont-size: %dpx !important;\n", $buttons_font_size );
		$builder->maybe_echo( "\tborder-color: %s !important;\n", $buttons_border_color );
		$builder->maybe_echo( "\twidth: 100%%; max-width: %s;\n", $buttons_width );
		$builder->maybe_echo( "\theight: %dpx;\n", $buttons_height );

		if( ! empty($buttons_border_width)) {
			echo "\tborder-style: solid;\n";
			echo "\tborder-width: {$buttons_border_width}px;\n";
		}
		if( ! empty($buttons_border_radius)) {
			echo "\t-webkit-border-radius: {$buttons_border_radius}px;\n";
			echo "\t-moz-border-radius: {$buttons_border_radius}px;\n";
			echo "\tborder-radius: {$buttons_border_radius}px;\n";
		}

		// to fix IOS Mobile Safari adding rounded corners & gradient.
        echo "\t-webkit-appearance: none;\n";

	echo "}\n\n";
}

/**
 * Button Elements (hover & focus)
 */
if( $builder->form_has_rules_for_element( $form_id, 'buttons_hover' ) ) {
	echo "$selector input[type='submit']:focus,\n";
	echo "$selector input[type='button']:focus,\n";
	echo "$selector input[type='reset']:focus,\n";
	echo "$selector button:focus,\n";
	echo "$selector input[type='submit']:hover,\n";
	echo "$selector input[type='button']:hover,\n";
	echo "$selector input[type='reset']:hover,\n";
	echo "$selector button:hover {\n";
		$builder->maybe_echo( "\tbackground:none; filter: none; background-color: %s !important;\n", $buttons_hover_background_color );
		$builder->maybe_echo( "\tborder-color: %s !important;\n", $buttons_hover_border_color );
	echo "}";
}


/**
 * Form Messages
 */
if( $builder->form_has_rules_for_element( $form_id, 'messages' ) ) {
    $builder->maybe_echo("$selector .mc4wp-success p{\n\tcolor: %s;\n}", $messages_font_color_success);
    $builder->maybe_echo("$selector .mc4wp-notice p{\n\tcolor: %s;\n}", $messages_font_color_notice);
    $builder->maybe_echo("$selector .mc4wp-error p{\n\tcolor: %s;\n}", $messages_font_color_error);
}

/**
 * Manual CSS
 */
echo $manual;

