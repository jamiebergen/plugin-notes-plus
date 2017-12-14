<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://jamiebergen.com/
 * @since             1.0.0
 * @package           Better_Plugin_Notes
 *
 * @wordpress-plugin
 * Plugin Name:       Better Plugin Notes
 * Plugin URI:        https://jamiebergen.com/
 * Description:       Add a field for plugin notes.
 * Version:           1.0.0
 * Author:            Jamie Bergen
 * Author URI:        https://jamiebergen.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       better-plugin-notes
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-better-plugin-notes-activator.php
 */
function activate_better_plugin_notes() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-better-plugin-notes-activator.php';
	Better_Plugin_Notes_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-better-plugin-notes-deactivator.php
 */
function deactivate_better_plugin_notes() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-better-plugin-notes-deactivator.php';
	Better_Plugin_Notes_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_better_plugin_notes' );
register_deactivation_hook( __FILE__, 'deactivate_better_plugin_notes' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-better-plugin-notes.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_better_plugin_notes() {

	$plugin = new Better_Plugin_Notes();
	$plugin->run();

}
run_better_plugin_notes();
