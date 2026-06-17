<?php
/**
 * Plugin Name:       GatherPress Seasons
 * Plugin URI:        https://github.com/carstingaxion/gatherpress-seasons
 * Description:
 * Version:           0.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires plugins:  gatherpress
 * Author:            carstenbach
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatherpress-seasons
 * Domain Path:       /languages
 *
 * @package GatherPress_Seasons
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

// Constants.
define( 'GATHERPRESS_SEASONS_VERSION', current( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
define( 'GATHERPRESS_SEASONS_CORE_PATH', __DIR__ );


/**
 * Adds the GatherPress_Seasons namespace to the autoloader.
 *
 * This function hooks into the 'gatherpress_autoloader' filter and adds the
 * GatherPress_Seasons namespace to the list of namespaces with its core path.
 *
 * @param array<string, string> $namespaces An associative array of namespaces and their paths.
 * @return array<string, string> Modified array of namespaces and their paths.
 */
function gatherpress_seasons_autoloader( array $namespaces ): array {
	$namespaces['GatherPress_Seasons'] = GATHERPRESS_SEASONS_CORE_PATH;

	return $namespaces;
}
add_filter( 'gatherpress_autoloader', 'gatherpress_seasons_autoloader' );

/**
 * Initialize the plugin.
 *
 * Bootstrap function that starts the plugin by initializing the main class.
 *
 * This function hooks into the 'plugins_loaded' action to ensure that
 * the instances are created once all plugins are loaded,
 * only if the GatherPress plugin is active.
 *
 * @since 0.1.0
 * @return void
 */
function gatherpress_seasons_setup(): void {
	if ( defined( 'GATHERPRESS_VERSION' ) ) {
		\GatherPress_Seasons\Setup::get_instance();
		\GatherPress_Seasons\Block::get_instance();
	}
}
add_action( 'plugins_loaded', 'gatherpress_seasons_setup' );
