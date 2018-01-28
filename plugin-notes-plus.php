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
 * @package           Plugin_Notes_Plus
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Notes Plus
 * Plugin URI:        https://jamiebergen.com/
 * Description:       Adds a column for plugin notes.
 * Version:           1.0.0
 * Author:            Jamie Bergen
 * Author URI:        https://jamiebergen.com/
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
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-notes-plus-deactivator.php
 */
function deactivate_plugin_notes_plus() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-notes-plus-deactivator.php';
	Plugin_Notes_Plus_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_plugin_notes_plus' );

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
