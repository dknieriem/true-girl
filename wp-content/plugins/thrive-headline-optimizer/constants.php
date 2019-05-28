<?php
/**
 * All plugin constants should be defined here
 * Use this file to define only constants
 */

/**
 * THT plugin version
 */
defined( 'THO_VERSION' ) || define( 'THO_VERSION', '1.1.6' );
defined( 'THO_MIN_REQUIRED_WP_VERSION' ) || define( 'THO_MIN_REQUIRED_WP_VERSION', '4.4' );

/**
 * Full path to the plugin folder (!includes a trailing slash)
 */
defined( 'THO_PATH' ) || define( 'THO_PATH', plugin_dir_path( __FILE__ ) );
defined( 'THO_URL' ) || define( 'THO_URL', plugin_dir_url( __FILE__ ) );

/**
 * THT plugin admin directory
 */
define( 'THO_ADMIN_PATH', plugin_dir_path( __FILE__ ) . "admin" );
define( 'THO_ADMIN_URL', plugin_dir_url( __FILE__ ) . "admin" );

/* plugin update url */
//define( 'THO_UPDATE_URL', 'http://members.thrivethemes.com/plugin_versions/thrive_headline_optimizer/update.json' );
define( 'THO_UPDATE_URL', 'http://service-api.thrivethemes.com/plugin/update' );

/**
 * Database prefix for all TU tables
 */
defined( 'THO_DB_PREFIX' ) || define( 'THO_DB_PREFIX', 'tho_' );

/**
 * Database version for current TU version
 */
defined( 'THO_DB_VERSION' ) || define( 'THO_DB_VERSION', '1.1' );

/**
 * Translate domain
 */
defined( 'THO_TRANSLATE_DOMAIN' ) || define( 'THO_TRANSLATE_DOMAIN', 'thrive-headline' );

/**
 * Define namespace for the rest endpoints
 */
defined( 'THO_REST_NAMESPACE' ) || define( 'THO_REST_NAMESPACE', 'tho/v1' );

/**
 * Define the option name from options table
 */
defined( 'THO_SETTINGS_OPTION' ) || define( 'THO_SETTINGS_OPTION', 'tho_test_criteria_settings' );
defined( 'THO_INCONCLUSIVE_TESTS_OPTION' ) || define( 'THO_INCONCLUSIVE_TESTS_OPTION', 'tho_inconclusive_tests' );
/**
 * TAG and ID of the element that will trigger comes in viewport and end of document
 */
defined( 'THO_HEADLINE_TAG' ) || define( 'THO_HEADLINE_TAG', 'thrive_headline' );
defined( 'THO_WOO_TAG' ) || define( 'THO_WOO_TAG', 'tho_woo' );
defined( 'THO_END_OF_CONTENT_ID' ) || define( 'THO_END_OF_CONTENT_ID', 'tho-end-content' );
defined( 'THO_CHANGE_MENU_ITEMS_TITLE' ) || define( 'THO_CHANGE_MENU_ITEMS_TITLE', 'tho-change-menu-title-item' );

/**
 * Define the test status values
 */
defined( 'THO_TEST_STATUS_ACTIVE' ) || define( 'THO_TEST_STATUS_ACTIVE', '1' );
defined( 'THO_TEST_STATUS_COMPLETED' ) || define( 'THO_TEST_STATUS_COMPLETED', '2' );
defined( 'THO_TEST_STATUS_ARCHIVED' ) || define( 'THO_TEST_STATUS_ARCHIVED', '3' );

/**
 * Date interval options
 */
defined( 'THO_LAST_7_DAYS' ) || define( 'THO_LAST_7_DAYS', 1 );
defined( 'THO_LAST_30_DAYS' ) || define( 'THO_LAST_30_DAYS', 2 );
defined( 'THO_THIS_MONTH' ) || define( 'THO_THIS_MONTH', 3 );
defined( 'THO_LAST_MONTH' ) || define( 'THO_LAST_MONTH', 4 );
defined( 'THO_THIS_YEAR' ) || define( 'THO_THIS_YEAR', 5 );
defined( 'THO_LAST_YEAR' ) || define( 'THO_LAST_YEAR', 6 );
defined( 'THO_LAST_12_MONTHS' ) || define( 'THO_LAST_12_MONTHS', 7 );
defined( 'THO_CUSTOM_DATE_RANGE' ) || define( 'THO_CUSTOM_DATE_RANGE', 8 );


/**
 * Engagement types
 */
defined( 'THO_CLICK_FLAG' ) || define( 'THO_CLICK_FLAG', 'tho_click' );
defined( 'THO_CLICK_ENGAGEMENT' ) || define( 'THO_CLICK_ENGAGEMENT', 1 );
defined( 'THO_SCROLL_ENGAGEMENT' ) || define( 'THO_SCROLL_ENGAGEMENT', 2 );
defined( 'THO_TIME_ENGAGEMENT' ) || define( 'THO_TIME_ENGAGEMENT', 3 );

defined( 'THO_VIEWPORT_TRIGGER' ) || define( 'THO_VIEWPORT_TRIGGER', 4 );

defined( 'THO_LOG_IMPRESSION' ) || define( 'THO_LOG_IMPRESSION', 1 );
defined( 'THO_LOG_ENGAGEMENT' ) || define( 'THO_LOG_ENGAGEMENT', 2 );

/*
 * Toast timeout in milliseconds
 */
defined( 'THO_TOAST_TIMEOUT' ) || define( 'THO_TOAST_TIMEOUT', 4000 );

/*
 * Reports
 */
defined( 'THO_ENGAGEMENT_REPORT' ) || define( 'THO_ENGAGEMENT_REPORT', 'engagement_report' );
defined( 'THO_ENGAGEMENT_RATE_REPORT' ) || define( 'THO_ENGAGEMENT_RATE_REPORT', 'engagement_rate_report' );
defined( 'THO_CUMULATIVE_ENGAGEMENT_REPORT' ) || define( 'THO_CUMULATIVE_ENGAGEMENT_REPORT', 'cumulative_engagement_report' );

/**
 * Report types
 */
defined( 'THO_CLICK_THROUGH_RATE_REPORT' ) || define( 'THO_CLICK_THROUGH_RATE_REPORT', 'click_through_rate_report' );
defined( 'THO_TIME_ON_CONTENT_REPORT' ) || define( 'THO_TIME_ON_CONTENT_REPORT', 'time_on_content_report' );
defined( 'THO_SCROLL_REPORT' ) || define( 'THO_SCROLL_REPORT', 'scroll_report' );

/**
 * Report for all sources
 */
defined( 'THO_SOURCE_REPORT_ALL' ) || define( 'THO_SOURCE_REPORT_ALL', 'all' );

/**
 * Define an hook that triggers when a test is set as winner
 */
defined( 'THO_ACTION_SET_TEST_ITEM_WINNER' ) || define( 'THO_ACTION_SET_TEST_ITEM_WINNER', 'tho_action_set_test_item_winner' );

