<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * @wordpress-plugin
 * Plugin Name:       Learning Vault for Uncanny Automator
 * Plugin URI:        https://www.learningvault.io/
 * Description:       Learning Vault plugin for Uncanny Automator
 * Version:           1.0.0
 * Author:            Learning Vault
 * Author URI:        https://www.learningvault.io/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       LV
 * Domain Path:       /languages
 */
class Uncanny_Automator_LV {
	/**
	 * @var string
	 */
	public $integration_code = 'LV';
	/**
	 * @var string
	 */
	public $directory;

	/**
	 * Uncanny_Automator_LV constructor.
	 */
	public function __construct() {
		$this->directory = __DIR__ . DIRECTORY_SEPARATOR . $this->integration_code;
		/**
		 * Waiting for the Automator to load.
		 */
		add_action( 'automator_configuration_complete', array( $this, 'add_this_integration' ) );
	}

	/**
	 * Add integration and all related files to Automator so that it shows up under Triggers / Actions
	 *
	 * @return bool|null
	 * @throws \Uncanny_Automator\Automator_Exception
	 */
	public function add_this_integration() {
		if ( ! function_exists( 'automator_add_integration' ) ) {
			wp_die( 'automator_add_integration() function not found. Please upgrade Uncanny Automator to version 3.0+' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		}

		if ( empty( $this->integration_code ) || empty( $this->directory ) ) {
			return false;
		}

		automator_add_integration_directory( $this->integration_code, $this->directory );
	}
}

new Uncanny_Automator_LV();
