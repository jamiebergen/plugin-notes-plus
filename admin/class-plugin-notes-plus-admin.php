<?php

/**
 * The admin-specific functionality of the plugin.
 * Defines the plugin name, version, and icon options.
 * Enqueues admin-specific styles and scripts.
 * Defines functions for adding a custom column and rendering plugin notes.
 * Defines ajax handlers for adding and deleting notes.
 *
 * @link       https://jamiebergen.com/
 * @since      1.0.0
 *
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/admin
 * @author     Jamie Bergen <jamie.bergen@gmail.com>
 */

class Plugin_Notes_Plus_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The db table name.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $table_name    The db table name without the prefix.
	 */
	private $table_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version           The version of this plugin.
	 * @param      string    $table_name        The db table name.
	 */
	public function __construct( $plugin_name, $version, $table_name ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->table_name = $table_name;
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-notes-plus-admin.css', array(), $this->version, 'all' );

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
			'edit_text' => esc_html__( 'edit', $this->plugin_name ),
			'delete_text' => esc_html__( 'delete', $this->plugin_name ),
			'confirm_delete' => esc_html__( 'Are you sure you want to delete this note?', $this->plugin_name ),
			'needs_content' => esc_html__( 'The note must contain content.', $this->plugin_name ),
		);
		wp_enqueue_script( 'pnp_ajax_handle', plugin_dir_url( __FILE__ ) . 'js/plugin-notes-plus-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( 'pnp_ajax_handle', 'params', $params );

	}

	/**
	 * Add a custom column on the plugins page for notes.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_notes_column( $columns ) {
		$columns['pnp_plugin_notes_col'] =  esc_html__( 'Plugin Notes', $this->plugin_name );
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
			$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $plugin_unique_id, $this->table_name  );

			$the_plugin_notes = $plugin_note_obj->get_plugin_notes();
			ksort($the_plugin_notes);

			$icon_options_array = apply_filters( 'plugin-notes-plus_icon_options', self::$icon_options );
			$plugin_unique_id_sanitized = preg_replace( '/[^a-zA-Z0-9_-]/', '-', 'pnp_' . $plugin_unique_id );

			include( 'partials/plugin-note-markup.php' );

		}
	}

	/**
	 * Ajax handler for adding a plugin note.
	 *
	 * @since    1.0.0
	 */
	public function pnp_add_response() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {

			// Check nonce and die if any funny business is detected
			check_ajax_referer( 'pnp_add_plugin_note_form_nonce', 'security' );

			$note = $_REQUEST['note'];
			$icon = $_REQUEST['icon'];
			$pluginId = $_REQUEST['pluginId'];

			$noteId = $_REQUEST['noteId'];

			$user = wp_get_current_user()->display_name;

			// Create object and create_plugin_note
			$plugin_note_obj = new Plugin_Notes_Plus_The_Note( $pluginId, $this->table_name );

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
		$table_name = $wpdb->prefix . $this->table_name;

		$wpdb->delete( $table_name, array( 'id' => $index ) );

	}

	/**
	 * Ajax handler for deleting a plugin note.
	 *
	 * @since    1.0.0
	 */
	public function pnp_delete_response() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {

			// Check nonce and die if any funny business is detected
			check_ajax_referer( 'pnp_add_plugin_note_form_nonce', 'security' );

			$note_id = $_REQUEST['noteId'];
			$this->delete_plugin_note_by_id( $note_id );
		}
		// Always die in functions echoing ajax content
		die();
	}

}

Plugin_Notes_Plus_Admin::$icon_options = array(
	'dashicons-clipboard' => esc_html__( 'Note', $this->plugin_name ),
	'dashicons-info' => esc_html__( 'Info', $this->plugin_name ),
	'dashicons-admin-links' => esc_html__( 'Link', $this->plugin_name ),
	'dashicons-warning' => esc_html__( 'Warning', $this->plugin_name ),
	'dashicons-admin-network' => esc_html__( 'Key', $this->plugin_name ),
	'dashicons-yes' => esc_html__( 'Checkmark', $this->plugin_name ),
);