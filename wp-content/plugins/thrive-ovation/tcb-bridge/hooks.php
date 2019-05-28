<?php

add_action( 'tcb_advanced_elements_html', 'tvo_add_display_testimonial_element', 11 );

add_action( 'tcb_custom_menus_html', 'tvo_add_display_testimonial_menu', 11 );

add_action( 'tcb_custom_menus_html', 'tvo_add_capture_testimonial_menu', 11 );

add_action( 'tcb_add_elements_wrapper', 'tvo_add_testimonial_display_tcb_wrapper_element', 11 );

add_action( 'tcb_add_elements_wrapper', 'tvo_add_testimonial_capture_tcb_wrapper_element', 11 );

add_action( 'plugins_loaded', 'tvo_tcb_check' );

add_action( 'tcb_ajax_load', 'tvo_testimonial_lightbox' );

add_filter( 'wp', 'tvo_shortcode_post' );

add_shortcode( 'tvo_shortcode', 'tvo_render_shortcode' );
