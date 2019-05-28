<?php
/**
 * All plugin constants should be defined here
 * Use this file to define only constants
 */

/**
 * TO plugin version
 */
defined( 'TVO_VERSION' ) || define( 'TVO_VERSION', '1.0.7' );
defined( 'TVO_MIN_REQUIRED_WP_VERSION' ) || define( 'TVO_MIN_REQUIRED_WP_VERSION', '4.4' );

/**
 * Full path to the plugin folder (!includes a trailing slash)
 */
defined( 'TVO_PATH' ) || define( 'TVO_PATH', plugin_dir_path( __FILE__ ) );
defined( 'TVO_URL' ) || define( 'TVO_URL', plugin_dir_url( __FILE__ ) );

/**
 * THT plugin admin directory
 */
define( 'TVO_ADMIN_PATH', plugin_dir_path( __FILE__ ) . 'admin/' );
define( 'TVO_ADMIN_URL', plugin_dir_url( __FILE__ ) . 'admin/' );

define( 'TVO_UPDATE_URL', 'http://service-api.thrivethemes.com/plugin/update' );
define( 'TVO_CAPTCHA_URL', 'https://www.google.com/recaptcha/api/siteverify' );

/**
 * Database prefix for all TO tables
 */
defined( 'TVO_DB_PREFIX' ) || define( 'TVO_DB_PREFIX', 'tvo_' );

/**
 * Database version for current TO version
 */
defined( 'TVO_DB_VERSION' ) || define( 'TVO_DB_VERSION', '1.0' );

/**
 * Translate domain
 */
defined( 'TVO_TRANSLATE_DOMAIN' ) || define( 'TVO_TRANSLATE_DOMAIN', 'thrive-ovation' );

/**
 * Define the option name from options table
 */
defined( 'TVO_SETTINGS_OPTION' ) || define( 'TVO_SETTINGS_OPTION', 'tvo_testimonial_settings' );
defined( 'TVO_LANDING_PAGE_SETTINGS_OPTION' ) || define( 'TVO_LANDING_PAGE_SETTINGS_OPTION', 'tvo_landing_page_settings' );
defined( 'TVO_EMAIL_TEMPLATE_SUBJECT_OPTION' ) || define( 'TVO_EMAIL_TEMPLATE_SUBJECT_OPTION', 'tvo_confirmation_email_template_subject' );
defined( 'TVO_EMAIL_TEMPLATE_OPTION' ) || define( 'TVO_EMAIL_TEMPLATE_OPTION', 'tvo_confirmation_email_template' );
defined( 'TVO_FILTERS_OPTION' ) || define( 'TVO_FILTERS_OPTION', 'tvo_filters_settings' );
defined( 'TVO_DEFAULT_PLACEHOLDER' ) || define( 'TVO_DEFAULT_PLACEHOLDER', 'tvo_default_placeholder' );
/**
 * Define namespace for the rest endpoints
 */
defined( 'TVO_REST_NAMESPACE' ) || define( 'TVO_REST_NAMESPACE', 'tvo/v1' );

/**
 * Custom post type
 */
defined( 'TVO_TESTIMONIAL_POST_TYPE' ) || define( 'TVO_TESTIMONIAL_POST_TYPE', 'tvo_testimonials' );
defined( 'TVO_SHORTCODE_POST_TYPE' ) || define( 'TVO_SHORTCODE_POST_TYPE', 'tvo_shortcode' );

/**
 * Define the testimonial taxonomies
 */
defined( 'TVO_TESTIMONIAL_TAG_TAXONOMY' ) || define( 'TVO_TESTIMONIAL_TAG_TAXONOMY', 'tvo_tags' );

/**
 * Define the testimonial post/status/source meta key
 */
defined( 'TVO_POST_META_KEY' ) || define( 'TVO_POST_META_KEY', '_tvo_testimonial_attributes' );
defined( 'TVO_STATUS_META_KEY' ) || define( 'TVO_STATUS_META_KEY', '_tvo_testimonial_status' );
defined( 'TVO_SOURCE_META_KEY' ) || define( 'TVO_SOURCE_META_KEY', '_tvo_testimonial_source' );

/**
 * Define testimonial statuses
 */
defined( 'TVO_STATUS_READY_FOR_DISPLAY' ) || define( 'TVO_STATUS_READY_FOR_DISPLAY', '0' );
defined( 'TVO_STATUS_AWAITING_APPROVAL' ) || define( 'TVO_STATUS_AWAITING_APPROVAL', '1' );
defined( 'TVO_STATUS_AWAITING_REVIEW' ) || define( 'TVO_STATUS_AWAITING_REVIEW', '2' );
defined( 'TVO_STATUS_REJECTED' ) || define( 'TVO_STATUS_REJECTED', '3' );

/**
 * Define testimonial source
 */
defined( 'TVO_SOURCE_COMMENTS' ) || define( 'TVO_SOURCE_COMMENTS', '0' );
defined( 'TVO_SOURCE_SOCIAL_MEDIA' ) || define( 'TVO_SOURCE_SOCIAL_MEDIA', '1' );
defined( 'TVO_SOURCE_DIRECT_CAPTURE' ) || define( 'TVO_SOURCE_DIRECT_CAPTURE', '2' );
defined( 'TVO_SOURCE_PLUGIN' ) || define( 'TVO_SOURCE_PLUGIN', '3' );
defined( 'TVO_SOURCE_COPY' ) || define( 'TVO_SOURCE_COPY', '4' );

/**
 * Define notification toast timeout
 */
defined( 'TVO_TOAST_TIMEOUT' ) || define( 'TVO_TOAST_TIMEOUT', 4000 );

/**
 * Define the summary content character limit in testimonial list view
 */
defined( 'TVO_TESTIMONIAL_CONTENT_SUMMARY_LIMIT' ) || define( 'TVO_TESTIMONIAL_CONTENT_SUMMARY_LIMIT', 50 );
defined( 'TVO_TESTIMONIAL_CONTENT_WORDS_LIMIT' ) || define( 'TVO_TESTIMONIAL_CONTENT_WORDS_LIMIT', 15 );
/**
 * Define the testimonial activity log type constants
 */
defined( 'TVO_LOG_SOURCE_CAPTURE_FORM' ) || define( 'TVO_LOG_SOURCE_CAPTURE_FORM', 'source_capture_form' );
defined( 'TVO_LOG_SOURCE_WORDPRESS_COMMENTS' ) || define( 'TVO_LOG_SOURCE_WORDPRESS_COMMENTS', 'source_wordpress_comments' );
defined( 'TVO_LOG_SOURCE_IMPORT_SOCIAL_MEDIA' ) || define( 'TVO_LOG_SOURCE_IMPORT_SOCIAL_MEDIA', 'source_import_media' );
defined( 'TVO_LOG_SOURCE_PLUGIN' ) || define( 'TVO_LOG_SOURCE_PLUGIN', 'source_tvo_ovation' );
defined( 'TVO_LOG_SOURCE_COPY' ) || define( 'TVO_LOG_SOURCE_COPY', 'source_tvo_copy' );

defined( 'TVO_LOG_CONTENT_CHANGED_BY_STAFF' ) || define( 'TVO_LOG_CONTENT_CHANGED_BY_STAFF', 'content_changed_by_staff' );
defined( 'TVO_LOG_EMAIL_SENT' ) || define( 'TVO_LOG_EMAIL_SENT', 'email_sent' );
defined( 'TVO_LOG_CHANGED_STATUS' ) || define( 'TVO_LOG_CHANGED_STATUS', 'changed_status' );
defined( 'TVO_LOG_CHANGED_PICTURE' ) || define( 'TVO_LOG_CHANGED_PICTURE', 'changed_picture' );
defined( 'TVO_LOG_EMAIL_CONFIRMED' ) || define( 'TVO_LOG_EMAIL_CONFIRMED', 'email_confirmed' );

/**
 * define email template constants, these probably shouldn't be here
 */
defined( 'TVO_DEFAULT_EMAIL_TEMPLATE' ) || define( 'TVO_DEFAULT_EMAIL_TEMPLATE', 'Hello [tvo_full_name],

We noticed that you recently wrote the following on our site:-

“[tvo_testimonial_text]”

First of all, we’re delighted that you’ve had a positive experience using our product and hope that we can continue to improve our product over time so that you remain a happy customer.

Secondly, we’d really love to be able to use your comment as a testimonial on our site, however we want to ask your approval first.  Your testimonial would be featured in our sales material for prospects who are not yet customers to get an insight into the kind of feedback we’ve been receiving.  Of course, if you have reservations about this, or would rather that we didn’t use your comment, then that’s fine also.  Simply click one of the options below to let us know whether you approve of this or not:-

[tvo_approval_buttons yes="Yes, I approve" no="No, I don\'t approve"]

Thanks for taking the time to do this for us!' );

defined( 'TVO_DEFAULT_EMAIL_TEMPLATE_NAME' ) || define( 'TVO_DEFAULT_EMAIL_TEMPLATE_NAME', 'John' );
defined( 'TVO_DEFAULT_EMAIL_TEMPLATE_TEXT' ) || define( 'TVO_DEFAULT_EMAIL_TEMPLATE_TEXT', 'Your product is the best!' );
defined( 'TVO_DEFAULT_EMAIL_TEMPLATE_SUBJECT' ) || define( 'TVO_DEFAULT_EMAIL_TEMPLATE_SUBJECT', 'Testimonial approval request' );

/**
 * Menu video URLs
 */
defined( 'TVO_CAPTURE_USING_LANDING_PAGE_ACTIVE' ) || define( 'TVO_CAPTURE_USING_LANDING_PAGE_ACTIVE', '//fast.wistia.net/embed/iframe/mv9an37krm?popover=true' );
defined( 'TVO_CAPTURE_USING_LANDING_PAGE_INACTIVE' ) || define( 'TVO_CAPTURE_USING_LANDING_PAGE_INACTIVE', '//fast.wistia.net/embed/iframe/agm7q743cx?popover=true' );

defined( 'TVO_CAPTURE_USING_CONTENT_BUILDER_ACTIVE' ) || define( 'TVO_CAPTURE_USING_CONTENT_BUILDER_ACTIVE', '//fast.wistia.net/embed/iframe/pkx5cjdj9x?popover=true' );
defined( 'TVO_CAPTURE_USING_CONTENT_BUILDER_INACTIVE' ) || define( 'TVO_CAPTURE_USING_CONTENT_BUILDER_INACTIVE', '//fast.wistia.net/embed/iframe/agm7q743cx?popover=true' );

defined( 'TVO_CAPTURE_USING_LEADS_ACTIVE' ) || define( 'TVO_CAPTURE_USING_LEADS_ACTIVE', '//fast.wistia.net/embed/iframe/9yibse0p7g?popover=true' );
defined( 'TVO_CAPTURE_USING_LEADS_INACTIVE' ) || define( 'TVO_CAPTURE_USING_LEADS_INACTIVE', '//fast.wistia.net/embed/iframe/agm7q743cx?popover=true' );

defined( 'TVO_DISPLAY_USING_CONTENT_BUILDER_ACTIVE' ) || define( 'TVO_DISPLAY_USING_CONTENT_BUILDER_ACTIVE', '//fast.wistia.net/embed/iframe/fw7sllzgrc?popover=true' );
defined( 'TVO_DISPLAY_USING_CONTENT_BUILDER_INACTIVE' ) || define( 'TVO_DISPLAY_USING_CONTENT_BUILDER_INACTIVE', '//fast.wistia.net/embed/iframe/agm7q743cx?popover=true' );

defined( 'TVO_DISPLAY_USING_LEADS_ACTIVE' ) || define( 'TVO_DISPLAY_USING_LEADS_ACTIVE', '//fast.wistia.net/embed/iframe/mv9an37krm?popover=true' );
defined( 'TVO_DISPLAY_USING_LEADS_INACTIVE' ) || define( 'TVO_DISPLAY_USING_LEADS_INACTIVE', '//fast.wistia.net/embed/iframe/agm7q743cx?popover=true' );

