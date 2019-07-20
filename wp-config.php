<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME','truegirl');

/** MySQL database username */
define( 'DB_USER','admin');

/** MySQL database password */
define( 'DB_PASSWORD','live4JC');

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'P #}9&&6m~-X=i5JA-6a@&4?[nqT>uSnUWwu.iYd?Jr,W?%]o48RuyT7a~>R*ip|');
define('SECURE_AUTH_KEY',  ';<wrPpt[c5]`P5i9;P_E-af2nEyZ@CX_AC@xqR|f`A(H><Th:o IN3*TX%6{HQ4&');
define('LOGGED_IN_KEY',    'YwS#+&w|esU/pN 3gU`*))D5/A4{OR#mn{OhzGk|d(&]NaV~oy%1k!|H]yowQC|x');
define('NONCE_KEY',        'qf0C~yw-esJ_ivq$e#[P>hHy+*h~i/1?3.Jk`OI|wZE/g8uH|&uf$=]N6qZ^$o,]');
define('AUTH_SALT',        '3}|;4{qvs9`jDM2^1xC!HcYWKY0+vhQs~RgpMHYjOW-]d{lloNb:.)um`qhQ}w.1');
define('SECURE_AUTH_SALT', 'iO8bxcgHxfj,~o+&x9#MH<u,9A=c./-hMx~~qi_2+%1]aRi7A%MD!n+ap2)V_y$b');
define('LOGGED_IN_SALT',   'GhKh61$[2!W#66dcI0{PQs-G#*|NxBB0ZANC?rOXKft^(.*hbzApzNc0^)4|+.?$');
define('NONCE_SALT',       '9vJS:i-_Ui7AOS2]}3Ulm1~a)`3KmB%7wJ+9SWM5_1# Y6!?2w09j7:}%9W@e/+(');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
define('WP_CACHE_KEY_SALT', 'hPyvigavgEcXAtmW6VFdaQ');
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

# Disables all core updates. Added by SiteGround Autoupdate:
define( 'WP_AUTO_UPDATE_CORE', false );



@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system

