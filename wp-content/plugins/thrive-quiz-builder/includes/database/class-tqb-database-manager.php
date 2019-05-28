<?php
/**
 * Database manager file.
 *
 * @package Thrive Quiz Builder
 */

/**
 * Handles all database updates for the plugin
 *
 * Class Thrive_Quiz_Builder_Database_Manager
 */
class Thrive_Quiz_Builder_Database_Manager {
	/**
	 * Database version
	 *
	 * @var string
	 */
	protected static $current_db_version;

	/**
	 * Last db error
	 *
	 * @var string
	 */
	protected static $last_db_error = '';

	/**
	 * Get the current version of database tables
	 * If there is no version saved 0.0 is returned
	 *
	 * @return string
	 */
	public static function db_version() {
		if ( empty( self::$current_db_version ) ) {
			self::$current_db_version = self::get_option();
		}

		return self::$current_db_version;
	}

	/**
	 * Compare db version with code version
	 * Runs all the scrips of old db version until the current code version
	 */
	public static function check() {
		if ( is_admin() && ! empty( $_REQUEST['tqb_db_reset'] ) ) {
			self::reset_option();
		}

		if ( version_compare( self::db_version(), Thrive_Quiz_Builder::DB, '<' ) ) {

			$scripts = self::get_scripts( self::db_version(), Thrive_Quiz_Builder::DB );

			if ( ! empty( $scripts ) ) {
				define( 'TQB_DB_UPGRADING', true );
			}

			global $wpdb;

			/**
			 * We only want to hide the errors not suppress them
			 * in case we need to log them somewhere
			 */
			$wpdb->hide_errors();

			foreach ( $scripts as $file_path ) {
				$result = require_once $file_path;
				if ( false === $result ) {
					/* ERROR: we don't change the DB version option and notify the user about the last error */
					$has_error = true;
					break;
				}
			}
			if ( isset( $has_error ) ) {
				self::$last_db_error = $wpdb->last_error;
				add_action( 'admin_notices', array( 'Thrive_Quiz_Builder_Database_Manager', 'display_admin_error' ) );

				return;
			}

			self::update_option( Thrive_Quiz_Builder::DB );
		}
	}

	/**
	 * Get all DB update scripts from $fromVersion to $toVersion.
	 *
	 * @param string $from_version from version.
	 * @param string $to_version to version.
	 *
	 * @return array
	 */
	protected static function get_scripts( $from_version, $to_version ) {
		$scripts = array();
		$dir     = new DirectoryIterator( dirname( __FILE__ ) . '/migrations' );
		foreach ( $dir as $file ) {
			/**
			 * DirectoryIterator
			 *
			 * @var $file
			 */
			if ( $file->isDot() ) {
				continue;
			}
			$script_version = self::get_script_version( $file->getFilename() );
			if ( empty( $script_version ) ) {
				continue;
			}
			if ( version_compare( $script_version, $from_version, '>' ) && version_compare( $script_version, $to_version, '<=' ) ) {
				$scripts[ $script_version ] = $file->getPathname();
			}
		}

		/**
		 * Sort the scripts in the correct version order
		 */
		uksort( $scripts, 'version_compare' );

		return $scripts;
	}

	/**
	 * Parse the scriptName and return the version
	 *
	 * @param string $script_name in the following format {name}-{[\d+].[\d+]}.php.
	 *
	 * @return string
	 */
	protected static function get_script_version( $script_name ) {
		if ( ! preg_match( '/(.+?)-(\d+)\.(\d+)(.\d+)?\.php/', $script_name, $m ) ) {
			return false;
		}

		return $m[2] . '.' . $m[3] . ( ! empty( $m[4] ) ? $m[4] : '' );
	}

	/**
	 * Gets the database option.
	 *
	 * @param string $default default value 0.0.
	 *
	 * @return mixed|void
	 */
	protected static function get_option( $default = '0.0' ) {
		if ( empty( $default ) ) {
			$default = '0.0';
		}

		return get_option( 'tqb_db_version', $default );
	}

	/**
	 * Sets the Thrive Quiz Builder database version.
	 *
	 * @param string $value value to be updated in database.
	 *
	 * @return bool
	 */
	protected static function update_option( $value ) {
		if ( self::db_version() === $value ) {
			return true;
		}

		return update_option( 'tqb_db_version', $value );
	}

	/**
	 * Resets the Thrive Quiz Builder database version.
	 *
	 * @return bool
	 */
	protected static function reset_option() {
		return delete_option( 'tqb_db_version' );
	}

	/**
	 * Display a error message in the admin panel notifying the user that the DB update script was not successful.
	 */
	public static function display_admin_error() {
		if ( ! self::$last_db_error ) {
			return;
		}

		echo '<div class="notice notice-error is-dismissible"><p>' .
		     sprintf(
			     __( 'There was an error while updating the database tables needed by Thrive Quiz Builder. Detailed error message: %s. If you continue seeing this message, please contact %s', Thrive_Quiz_Builder::T ),
			     '<strong>' . self::$last_db_error . '</strong>',
			     '<a target="_blank" href="https://thrivethemes.com/forums/">' . __( 'Thrive Themes Support', Thrive_Quiz_Builder::T ) . '</a>'
		     ) .
		     '</p></div>';
	}
}

Thrive_Quiz_Builder_Database_Manager::check();
