<?php

/** plugin compatibility check **/
require_once dirname( __FILE__ ) . '/inc/classes/class-tho-version-check.php';

require_once dirname( __FILE__ ) . '/inc/data.php';
require_once dirname( __FILE__ ) . '/inc/functions.php';
require_once dirname( __FILE__ ) . '/inc/helpers.php';
require_once dirname( __FILE__ ) . '/inc/hooks.php';

/**
 * REST Routes
 */
require_once dirname( __FILE__ ) . '/inc/classes/class-tho-rest-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tho-rest-logs-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tho-rest-posts-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tho-rest-settings-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tho-rest-tests-controller.php';
require_once dirname( __FILE__ ) . '/inc/classes/endpoints/class-tho-rest-variation-controller.php';


require_once dirname( __FILE__ ) . '/inc/classes/class-tho-trigger-manager.php';
require_once dirname( __FILE__ ) . '/inc/classes/class-tho-db.php';
