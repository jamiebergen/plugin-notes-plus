<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/jamiebergen
 * @since             1.0.0
 * @package           Plugin_Notes_Plus
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Notes Plus
 * Plugin URI:        https://github.com/jamiebergen/plugin-notes-plus
 * Description:       Adds a column for plugin notes.
 * Version:           1.2.8
 * Author:            Jamie Bergen
 * Author URI:        https://github.com/jamiebergen
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-notes-plus
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'PLUGIN_NOTES_PLUS_VERSION', '1.2.8' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
global $plugin_notes_plus_db_version;
$plugin_notes_plus_db_version = '1.1';

function activate_plugin_notes_plus() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-notes-plus-activator.php';
	Plugin_Notes_Plus_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_plugin_notes_plus' );

/**
 * The code that runs on plugin update.
 * Migrates data out of _options and into new custom table if upgrading from 1.0.0
 * Removes old notes from options after migration is complete
 */
function plugin_notes_plus_migrate_to_table() {
	if ( !get_option( 'plugin_notes_plus_db_version' ) || get_option( 'plugin_notes_plus_db_version' ) == 1.0 ) {
		activate_plugin_notes_plus();
	}
}
add_action( 'plugins_loaded', 'plugin_notes_plus_migrate_to_table' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin-notes-plus.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_notes_plus() {

	$plugin = new Plugin_Notes_Plus();
	$plugin->run();

}
run_plugin_notes_plus();
