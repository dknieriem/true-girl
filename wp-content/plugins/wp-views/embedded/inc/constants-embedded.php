<?php 

/**
* constants-embedded.php
*
*�Set some constants used along the whole embedded plugin
*
* @package Views
*
* @since 1.7.0
*/

/**
 * General space char
 */

if ( ! defined( 'WPV_MESSAGE_SPACE_CHAR' ) ) {
	define( 'WPV_MESSAGE_SPACE_CHAR', '&nbsp;' );
}

/**
 * Listing screens contants
 */

define( 'WPV_ITEMS_PER_PAGE', 20 );


/**
 * Documentation links
 */

if ( ! defined( 'WPV_LINK_CREATE_PAGINATED_LISTINGS' ) ) {
	define( 'WPV_LINK_CREATE_PAGINATED_LISTINGS', 'https://toolset.com/documentation/user-guides/views-pagination/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-paginated-listing-helpbox&utm_term=Creating paginated listings with Views' );
}

if ( ! defined( 'WPV_LINK_CREATE_SLIDERS' ) ) {
	define( 'WPV_LINK_CREATE_SLIDERS', 'https://toolset.com/documentation/user-guides/creating-sliders-with-types-and-views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-sliders-helpbox&utm_term=Creating sliders with Views' );
}

if ( ! defined( 'WPV_LINK_CREATE_PARAMETRIC_SEARCH' ) ) {
	define( 'WPV_LINK_CREATE_PARAMETRIC_SEARCH', 'https://toolset.com/documentation/user-guides/front-page-filters/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-custom-search-helpbox&utm_term=Creating custom searches with Views' );
}

if ( ! defined( 'WPV_LINK_DESIGN_SLIDER_TRANSITIONS' ) ) {
	define( 'WPV_LINK_DESIGN_SLIDER_TRANSITIONS', 'https://toolset.com/documentation/user-guides/creating-sliders-with-types-and-views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-sliders-transitions-helpbox&utm_term=Creating sliders with Views' );
}


if ( ! defined( 'WPV_LINK_LOOP_DOCUMENTATION' ) ) {
	define( 'WPV_LINK_LOOP_DOCUMENTATION', 'https://toolset.com/documentation/user-guides/digging-into-view-outputs/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-edit-layouts-helpbox&utm_term=Learn more by reading the Views Loop documentation.' );
}

if ( ! defined( 'WPV_LINK_CONTENT_TEMPLATE_DOCUMENTATION' ) ) {
	define( 'WPV_LINK_CONTENT_TEMPLATE_DOCUMENTATION', 'https://toolset.com/documentation/user-guides/view-templates/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-content-template-page&utm_term=Content Template documentation#tutorial' );
}

if ( ! defined( 'WPV_LINK_WORDPRESS_ARCHIVE_DOCUMENTATION') ) {
	define( 'WPV_LINK_WORDPRESS_ARCHIVE_DOCUMENTATION', 'https://toolset.com/documentation/user-guides/normal-vs-archive-views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-wordpress-archive-page&utm_term=WordPress Archive documentation' );
}

if ( ! defined( 'WPV_LINK_FRAMEWORK_INTEGRATION_DOCUMENTATION') ) {
	define( 'WPV_LINK_FRAMEWORK_INTEGRATION_DOCUMENTATION', 'https://toolset.com/documentation/user-guides/theme-frameworks-integration/?utm_source=viewsplugin&utm_campaign=views&utm_medium=theme-framework-integration-page&utm_term=theme framework integration documentation page' );
}