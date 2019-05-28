<?php

/**
 * Handles all database updates for the plugin
 *
 * Class Tvo_Database_Manager
 */
class Tvo_Database_Manager {
	/**
	 * @var string version as xx.xx
	 */
	protected static $current_db_version;

	protected static $last_db_error = '';

	protected static $option_name = 'thrive_ovation_db_version';

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
		if ( is_admin() && ! empty( $_REQUEST['tvo_db_reset'] ) ) {
			self::reset_option();
		}

		if ( version_compare( self::db_version(), TVO_DB_VERSION, '<' ) ) {

			$scripts = self::get_scripts( self::db_version(), TVO_DB_VERSION );

			if ( ! empty( $scripts ) ) {
				define( 'TVO_DB_UPGRADE', true );
			}

			global $wpdb;

			/**
			 * we only want to hide the errors not suppress them
			 * in case we need to log them somewhere
			 */
			$wpdb->hide_errors();

			foreach ( $scripts as $file_path ) {
				$result = require_once $file_path;
				if ( $result === false ) {
					/* ERROR: we don't change the DB version option and notify the user about the last error */
					$has_error = true;
					break;
				}
			}
			if ( isset( $has_error ) ) {
				self::$last_db_error = $wpdb->last_error;
				add_action( 'admin_notices', array( 'Tvo_Database_Manager', 'display_admin_error' ) );

				return;
			}

			self::update_option( TVO_DB_VERSION );
		}
	}

	/**
	 * get all DB update scripts from $from_version to $to_version
	 *
	 * @param $from_version
	 * @param $to_version
	 *
	 * @return array
	 */
	protected static function get_scripts( $from_version, $to_version ) {
		$scripts = array();
		$dir     = new DirectoryIterator( dirname( __FILE__ ) . '/migrations' );
		foreach ( $dir as $file ) {
			/**
			 * @var $file DirectoryIterator
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
		 * sort the scripts in the correct version order
		 */
		uksort( $scripts, 'version_compare' );

		return $scripts;
	}

	/**
	 * Parse the script_name and return the version
	 *
	 * @param string $script_name in the following format {name}-{[\d+].[\d+]}.php
	 *
	 * @return string
	 */
	protected static function get_script_version( $script_name ) {
		if ( ! preg_match( '/(.+?)-(\d+)\.(\d+)(.\d+)?\.php/', $script_name, $m ) ) {
			return false;
		}

		return $m[2] . '.' . $m[3] . ( ! empty( $m[4] ) ? $m[4] : '' );
	}

	protected static function get_option( $default = '0.0' ) {
		if ( empty( $default ) ) {
			$default = '0.0';
		}

		return get_option( self::$option_name, $default );
	}

	protected static function update_option( $value ) {
		if ( $value === self::db_version() ) {
			return true;
		}

		return update_option( self::$option_name, $value );
	}

	protected static function reset_option() {
		return delete_option( self::$option_name );
	}

	/**
	 * display a error message in the admin panel notifying the user that the DB update script was not successful
	 */
	public static function display_admin_error() {
		if ( ! self::$last_db_error ) {
			return;
		}

		echo '<div class="notice notice-error is-dismissible"><p>' .
		     sprintf(
			     __( 'There was an error while updating the database tables needed by Thrive Ovation. Detailed error message: %s. If you continue seeing this message, please contact %s', TVO_TRANSLATE_DOMAIN ),
			     '<strong>' . self::$last_db_error . '</strong>',
			     '<a target="_blank" href="https://thrivethemes.com/forums/">' . __( 'Thrive Themes Support', TVO_TRANSLATE_DOMAIN ) . '</a>'
		     ) .
		     '</p></div>';
	}
}
