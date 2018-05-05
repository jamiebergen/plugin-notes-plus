<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.1.0
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/includes
 * @author     Jamie Bergen <jamie.bergen@gmail.com>
 */
class Plugin_Notes_Plus_Activator {

	/**
	 * Create a custom database table to hold plugin notes.
	 *
	 * Migrate existing notes from options table
	 *
	 * @since    1.1.0
	 */

	public static function migrate_old_notes( $old_id, $new_id ) {

		$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $old_id );
		$plugin_notes = $plugin_note_obj->retrieve_notes_from_options();

		if ( empty( $plugin_notes ) ) {
			return;
		}

		ksort( $plugin_notes );

		foreach ( $plugin_notes as $note_index => $note ) {
			$note_text = $note[ 'note' ];
			$icon_class = $note[ 'icon' ];
			$username = $note[ 'user' ];
			$note_time = $note[ 'time' ];

			$plugin_note_obj->migrate_plugin_note( $new_id, $note_text, $icon_class, $username, $note_time );
		}
	}

	public static function activate() {

		global $wpdb;
		global $plugin_notes_plus_db_version;

		$table_name = $wpdb->prefix . Plugin_Notes_Plus::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				plugin_id varchar(1024) NOT NULL,
				note_content longtext NOT NULL,
				note_icon varchar(255) NOT NULL,
				user_name varchar(255) NOT NULL,
				time bigint(20) NOT NULL,
				PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Migrate notes from _options to new table if upgrading from 1.0.0
		if ( !get_option( 'plugin_notes_plus_db_version' ) ) {

			$plugins_array = get_plugins();

			$plugin_keys = array_keys($plugins_array);

			foreach ( $plugin_keys as $key ) {

				// Look for notes under Linux version of plugin id
				$key_linux = str_replace( '\\', '/', $key );
				$plugin_unique_id_linux = 'plugin_notes_plus_' . sanitize_title( $key_linux );

				$new_id = wp_normalize_path( $key );

				self::migrate_old_notes( $plugin_unique_id_linux, $new_id );

				// Look for notes under Windows version of plugin id
				$key_windows = str_replace( '/', '\\', $key );
				$plugin_unique_id_windows = 'plugin_notes_plus_' . sanitize_title( $key_windows );

				if ( $plugin_unique_id_windows != $plugin_unique_id_linux ) {
					self::migrate_old_notes( $plugin_unique_id_windows, $new_id );
				}
			}
			add_option( 'plugin_notes_plus_db_version', 1.0 );
		}

		// Clean up old options entries after migration is complete
		if ( get_option( 'plugin_notes_plus_db_version' ) == 1.0 ) {
			$all_options = wp_load_alloptions();
			$pnp_options  = array();

			foreach ( $all_options as $name => $value ) {
				if ( stristr( $name, 'plugin_notes_plus_' ) && $name !== 'plugin_notes_plus_db_version' ) {
					$pnp_options[] = $name;
				}
			}

			// Remove notes from options table
			foreach ( $pnp_options as $pnp_option ) {
				delete_option( $pnp_option );
			}

			update_option( 'plugin_notes_plus_db_version', $plugin_notes_plus_db_version );
		}
	}
}
