<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jamiebergen.com/
 * @since      1.0.0
 *
 * @package    Better_Plugin_Notes
 * @subpackage Better_Plugin_Notes/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Better_Plugin_Notes
 * @subpackage Better_Plugin_Notes/admin
 * @author     Jamie Bergen <jamie.bergen@gmail.com>
 */
class Better_Plugin_Notes_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Better_Plugin_Notes_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Better_Plugin_Notes_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/better-plugin-notes-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Better_Plugin_Notes_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Better_Plugin_Notes_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$params = array (
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'bpn_add_plugin_note_form_nonce' ), // this is a unique token to prevent form hijacking
			'edit_text' => esc_html__( 'edit', $this->plugin_name ),
			'delete_text' => esc_html__( 'delete', $this->plugin_name )
		);
		wp_enqueue_script( 'bpn_ajax_handle', plugin_dir_url( __FILE__ ) . 'js/better-plugin-notes-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( 'bpn_ajax_handle', 'params', $params );

	}
	
	public function add_plugin_notes_column( $columns ) {
		$columns['bpn_plugin_notes_col'] =  esc_html__('Plugin Notes', $this->plugin_name);
		return $columns;
	}

	public function get_plugin_unique_id( $plugin_name ) {
		return '_aaa_plugin_note_' . sanitize_title( $plugin_name );
	}

	public function display_plugin_note( $column_name, $plugin_file, $plugin_data ) { // !!! use $plugin_file since it's unique path

		$plugin_unique_id = $this->get_plugin_unique_id( $plugin_data['Name'] );
		$plugin_note_obj = new Better_Plugin_Notes_The_Note( $plugin_unique_id  );

		if ( 'bpn_plugin_notes_col' == $column_name ) {

			$the_plugin_notes = $plugin_note_obj->get_plugin_notes();
			ksort($the_plugin_notes);
			include( 'partials/plugin-note-markup.php' );

		}
	}

	public function bpn_add_response() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {

			// Check nonce and die if any funny business is detected
			check_ajax_referer( 'bpn_add_plugin_note_form_nonce', 'security' );

			$note = $_REQUEST['note'];
			$icon = $_REQUEST['icon'];
			$pluginId = $_REQUEST['pluginId'];

			$index = $_REQUEST['index'];

			// Create object and create_plugin_note
			$plugin_note_obj = new Better_Plugin_Notes_The_Note( $pluginId );

			if ($index !== '') {
				$new_note_index = $plugin_note_obj->edit_plugin_note( $note, $icon, $index );
			} elseif ( $plugin_note_obj->has_plugin_note() ) {
				$new_note_index = $plugin_note_obj->append_plugin_note( $note, $icon );
			} else {
				$new_note_index = $plugin_note_obj->initialize_plugin_notes( $note, $icon );
			}

			$processed_note = $plugin_note_obj->get_plugin_note( $new_note_index );

			$return = array(
				'new_note_index'  => $new_note_index,
				'processed_note'  => $processed_note
			);
			wp_send_json($return);

		}

		// Always die in functions echoing ajax content
		die();

	}

	public function bpn_delete_response() {

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {

			// !!!Check nonce here as well?

			$pluginId = $_REQUEST['pluginId'];
			$noteIndex = $_REQUEST['noteIndex'];

			$plugin_note_obj = new Better_Plugin_Notes_The_Note( $pluginId );

			if ( $plugin_note_obj->has_plugin_note() ) {
				$plugin_note_obj->delete_plugin_note( $noteIndex );
			}
		}

		// Always die in functions echoing ajax content
		die();

	}

}
