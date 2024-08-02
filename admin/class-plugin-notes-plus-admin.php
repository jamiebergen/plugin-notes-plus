<?php

/**
 * The admin-specific functionality of the plugin.
 * Defines the plugin name, version, and icon options.
 * Enqueues admin-specific styles and scripts.
 * Defines functions for adding a custom column and rendering plugin notes.
 * Defines ajax handlers for adding and deleting notes.
 *
 * @link       https://github.com/jamiebergen
 * @since      1.0.0
 *
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/admin
 * @author     Jamie Bergen <jamie.bergen@gmail.com>
 */

class Plugin_Notes_Plus_Admin {

	/**
	 * A reference to the Plugin_Notes_Plus object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Plugin_Notes_Plus    $plugin    References the Plugin_Notes_Plus object for this plugin.
	 */
	private $plugin;

	/**
	 * A list of icon options. See definition of $icon_options after
	 * the end of the class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $icon_options
	 */
	public static $icon_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Notes_Plus_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Notes_Plus_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin->get_plugin_name(), plugin_dir_url( __FILE__ ) . 'css/plugin-notes-plus-admin.css', array(), $this->plugin->get_version(), 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Notes_Plus_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Notes_Plus_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$params = array (
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'pnp_add_plugin_note_form_nonce' ), // this is a unique token to prevent form hijacking
			'edit_text' => esc_html__( 'Edit', $this->plugin->get_plugin_name() ),
			'delete_text' => esc_html__( 'Delete', $this->plugin->get_plugin_name() ),
			'confirm_delete' => esc_html__( 'Are you sure you want to delete this note?', $this->plugin->get_plugin_name() ),
			'needs_content' => esc_html__( 'The note must contain content.', $this->plugin->get_plugin_name() ),
		);
		wp_enqueue_script( 'pnp_ajax_handle', plugin_dir_url( __FILE__ ) . 'js/plugin-notes-plus-admin.js', array( 'jquery' ), $this->plugin->get_version(), false );
		wp_localize_script( 'pnp_ajax_handle', 'params', $params );

		/**
		 * Retrieve notes for plugins on updates page and send to JavaScript file
		 *
		 * @since    1.2.0
		 */

		// Only run this code on update-core.php admin page
		global $hook_suffix;

		if ( $hook_suffix == 'update-core.php' ) {
			$updates = $this->get_notes_for_plugin_updates_page();

			if ( $updates ) {
				$updates_json_str = array( json_encode( $updates ) );
				$labels = array (
					'col_title' => esc_html__( 'Plugin Notes', $this->plugin->get_plugin_name() ),
					'no_note' => esc_html__( 'No Plugin Notes', $this->plugin->get_plugin_name() ),
				);
				wp_enqueue_script( 'pnp_updates_script', plugin_dir_url( __FILE__ ) . 'js/plugin-notes-plus-updates.js', array( 'jquery' ), $this->plugin->get_version(), false );
				wp_localize_script( 'pnp_updates_script', 'updates', $updates_json_str );
				wp_localize_script( 'pnp_updates_script', 'labels', $labels );
			}
		}
	}

	/**
	 * Add a custom column on the plugins page for notes.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_notes_column( $columns ) {

		$pnp_hide_notes = FALSE;

		$pnp_hide_notes = apply_filters( 'plugin-notes-plus_hide_notes', $pnp_hide_notes );

		if ( ! $pnp_hide_notes ) {
			$columns['pnp_plugin_notes_col'] =  esc_html__( 'Plugin Notes', $this->plugin->get_plugin_name() );
		}

		return $columns;
	}

	/**
	 * Generate a unique id for a plugin based on the plugin's filepath.
	 * Fixed to account for backslash in Windows paths
	 *
	 * @since    1.0.0
	 */
	public function get_plugin_unique_id( $plugin_file ) {
		return wp_normalize_path( $plugin_file );
	}


	/**
	 * Display the plugin note(s) for a given plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_note( $column_name, $plugin_file, $plugin_data ) {

		if ( 'pnp_plugin_notes_col' == $column_name ) {

			$plugin_unique_id = $this->get_plugin_unique_id( $plugin_file );
			$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $plugin_unique_id );

			$the_plugin_notes = $plugin_note_obj->get_plugin_notes();
			ksort($the_plugin_notes);

			$icon_options_array = apply_filters( 'plugin-notes-plus_icon_options', self::$icon_options );
			$plugin_unique_id_sanitized = preg_replace( '/[^a-zA-Z0-9_-]/', '-', 'pnp_' . $plugin_unique_id );

			$allowed_tags = $plugin_note_obj->get_allowed_tags();

			include( 'partials/plugin-note-markup.php' );

		}
	}

	/**
	 * Display the plugin note(s) for a given plugin beneath the plugin description
	 *
	 * @since    1.2.4
	 */
	public function display_plugin_note_desc( $plugin_meta, $plugin_file, $plugin_data, $context ) {

		$pnp_hide_notes = FALSE;

		$pnp_hide_notes = apply_filters( 'plugin-notes-plus_hide_notes', $pnp_hide_notes );

		if ( $pnp_hide_notes ) {
			return $plugin_meta;
		}

		$plugin_unique_id = $this->get_plugin_unique_id( $plugin_file );
		$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $plugin_unique_id );

		$the_plugin_notes = $plugin_note_obj->get_plugin_notes();
		ksort($the_plugin_notes);

		$icon_options_array = apply_filters( 'plugin-notes-plus_icon_options', self::$icon_options );
		$plugin_unique_id_sanitized = preg_replace( '/[^a-zA-Z0-9_-]/', '-', 'pnp_' . $plugin_unique_id );

		$allowed_tags = $plugin_note_obj->get_allowed_tags();

		include( 'partials/plugin-note-markup.php' );

		return $plugin_meta;

	}

	/**
	 * Ajax handler for adding a plugin note.
	 *
	 * @since    1.0.0
	 */
	public function pnp_add_response() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {

			// Check that user has correct permissions
			if ( ! current_user_can( 'activate_plugins' ) ) {
				die();
			}

			// Check nonce and die if any funny business is detected
			check_ajax_referer( 'pnp_add_plugin_note_form_nonce', 'security' );

			$note = $_REQUEST['note'];
			$icon = $_REQUEST['icon'];
			$pluginId = $_REQUEST['pluginId'];

			$noteId = $_REQUEST['noteId'];

			$user = wp_get_current_user()->display_name;

			// Create object and create_plugin_note
			$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $pluginId );

			if ( $noteId !== '' ) {
				$new_note_id = $plugin_note_obj->edit_plugin_note( $note, $icon, $user, $noteId );
			} else {
				$new_note_id = $plugin_note_obj->add_plugin_note( $note, $icon, $user );
			}

			$processed_note = $plugin_note_obj->get_plugin_note_by_id( $new_note_id );

			$return = array(
				'new_note_id'     => $new_note_id,
				'note_icon'       => $processed_note['icon'],
				'processed_note'  => $processed_note['note'],
				'note_user'       => $processed_note['user'],
				'note_time'       => $processed_note['time']
			);
			wp_send_json($return);

		}
		// Always die in functions echoing ajax content
		die();

	}

	/**
	 * Delete the plugin note by ID.
	 *
	 * @since    1.1.0
	 */
	public function delete_plugin_note_by_id( $index ) {

		global $wpdb;
		$table_name = $wpdb->prefix . $this->plugin->get_table_name();

		$wpdb->delete( $table_name, array( 'id' => $index ) ); //db call ok; no-cache ok

	}

	/**
	 * Ajax handler for deleting a plugin note.
	 *
	 * @since    1.0.0
	 */
	public function pnp_delete_response() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {

			// Check that user has correct permissions
			if ( ! current_user_can( 'activate_plugins' ) ) {
				die();
			}

			// Check nonce and die if any funny business is detected
			check_ajax_referer( 'pnp_add_plugin_note_form_nonce', 'security' );

			$note_id = $_REQUEST['noteId'];
			$this->delete_plugin_note_by_id( $note_id );
		}
		// Always die in functions echoing ajax content
		die();
	}

	/**
	 * Function that retrieves plugin notes for plugins that need updating
	 *
	 * @since    1.2.0
	 */
	public function get_notes_for_plugin_updates_page() {

		$notes_array = array();

		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		$plugins = get_plugin_updates();

		$pnp_hide_notes = FALSE;

		$pnp_hide_notes = apply_filters( 'plugin-notes-plus_hide_notes', $pnp_hide_notes );

		if ( empty( $plugins ) || $pnp_hide_notes ) {
			return $notes_array;
		}

		foreach ( $plugins as $plugin_file => $plugin_data ) {

			$plugin_unique_id = $this->get_plugin_unique_id( $plugin_file );
			$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $plugin_unique_id );
			$the_plugin_notes = $plugin_note_obj->get_plugin_notes();

			array_push( $notes_array, $the_plugin_notes );
		}

		return $notes_array;
	}
}

Plugin_Notes_Plus_Admin::$icon_options = array(
	'dashicons-clipboard' => esc_html__( 'Note', Plugin_Notes_Plus::get_plugin_name() ),
	'dashicons-info' => esc_html__( 'Info', Plugin_Notes_Plus::get_plugin_name() ),
	'dashicons-admin-links' => esc_html__( 'Link', Plugin_Notes_Plus::get_plugin_name() ),
	'dashicons-warning' => esc_html__( 'Warning', Plugin_Notes_Plus::get_plugin_name() ),
	'dashicons-admin-network' => esc_html__( 'Key', Plugin_Notes_Plus::get_plugin_name() ),
	'dashicons-yes' => esc_html__( 'Checkmark', Plugin_Notes_Plus::get_plugin_name() ),
	'dashicons-money' => esc_html__( 'Money', Plugin_Notes_Plus::get_plugin_name() ),
);
