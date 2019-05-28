<?php
/**
 * Created by PhpStorm.
 * User: Danut
 * Date: 6/23/2015
 * Time: 2:11 PM
 */
$thrive_setup = Thrive_Theme_Setup::getInstance();
add_action( 'thrive_theme_setup', array( $thrive_setup, 'run' ) );

class Thrive_Theme_Setup {
	const MAX_SETUP = "1.0";

	protected static $instance = null;

	protected $setup_version = null;

	protected $theme_name = null;

	protected $current_theme = null;

	protected function __construct() {
		$current_theme       = wp_get_theme();
		$this->theme_name    = $current_theme->name;
		$this->setup_version = get_option( $this->getSetupOptionName(), '0.0' );
	}

	protected function getSetupOptionName() {
		if ( empty( $this->theme_name ) ) {
			return "";
		}

		return "thrive_theme_" . strtolower( $this->theme_name ) . "_setup_version";
	}

	/**
	 * Get instance for this single ton
	 *
	 * @return null|Thrive_Theme_Setup
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new Thrive_Theme_Setup();
		}

		return self::$instance;
	}

	/**
	 * Run the setup
	 * Require once all the php files that have a greater version that current setup version in their file name
	 *
	 * @return bool
	 */
	public function run() {
		if ( empty( $this->setup_version ) ) {
			return;
		}

		if ( version_compare( self::MAX_SETUP, $this->setup_version, '=' ) ) {
			return;
		}

		$scripts = $this->getScripts();

		if ( empty( $scripts ) ) {
			return true;
		}

		defined( 'THRIVE_SETUP_FLAG' ) || define( 'THRIVE_SETUP_FLAG', true );

		foreach ( $scripts as $filePath ) {
			require_once $filePath;
		}

		end( $scripts ); //move the pointer at the end of the array
		$setup_name = key( $scripts ); //get the last key of the array

		return update_option( $this->getSetupOptionName(), $setup_name );
	}

	/**
	 * Returns all scripts that have version greater than current setup version
	 *
	 * @return array
	 */
	protected function getScripts() {
		if ( empty( $this->setup_version ) ) {
			return array();
		}

		$scripts = array();

		$dir = new DirectoryIterator( dirname( __FILE__ ) . "/setup" );

		/** @var $file DirectoryIterator */
		foreach ( $dir as $file ) {

			if ( $file->isDot() ) {
				continue;
			}

			$script_version = $this->getVersionFromScript( $file->getFilename() );

			if ( version_compare( $script_version, $this->setup_version, ">" ) ) {
				$scripts[ $script_version ] = $file->getPathname();
			}
		}

		uksort( $scripts, 'version_compare' );

		return $scripts;
	}

	protected function getVersionFromScript( $script_name ) {
		if ( ! preg_match( '/(.+?)-(\d+)\.(\d+)(.\d+)?\.php/', $script_name, $m ) ) {
			return false;
		}

		return $m[2] . '.' . $m[3] . ( ! empty( $m[4] ) ? $m[4] : '' );
	}
}
