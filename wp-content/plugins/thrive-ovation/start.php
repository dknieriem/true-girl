<?php

require_once dirname( __FILE__ ) . '/constants.php';
require_once dirname( __FILE__ ) . '/inc/data.php';
require_once dirname( __FILE__ ) . '/inc/functions.php';
require_once dirname( __FILE__ ) . '/inc/helpers.php';
require_once dirname( __FILE__ ) . '/inc/hooks.php';
/**
 * REST Routes
 */
require_once dirname( __FILE__ ) . '/inc/classes/class-tvo-rest-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-settings-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-testimonials-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-tags-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-social-media-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-comments-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-shortcodes-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-post-meta-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tvo-rest-filters-controller.php';

/**
 * TCB Bridge
 */
require_once dirname( __FILE__ ) . '/tcb-bridge/hooks.php';
require_once dirname( __FILE__ ) . '/tcb-bridge/functions.php';

/**
 * Database
 */
require_once dirname( __FILE__ ) . '/inc/classes/class-tvo-db.php';
