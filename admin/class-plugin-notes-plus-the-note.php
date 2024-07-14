<?php

/**
 * A class that handles the notes for each plugin.
 * Defines functions to add, edit, and delete plugin notes.
 * Also defines functions to retrieve note(s) for a specific plugin.
 *
 * @link       https://github.com/jamiebergen
 * @since      1.0.0
 *
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/admin
 * @author     Jamie Bergen <jamie.bergen@gmail.com>
*/

class Plugin_Notes_Plus_The_Note {

	/**
	 * The unique ID of the plugin associated with the note.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_unique_id    The unique ID of the plugin associated with the note.
	 */
	private $plugin_unique_id;

	/**
	 * A list of allowed tags.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $allowed_tags
	 */
	private $allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'target' => array(),
		),
		'br' => array(),
		'p' => array(),
		'b' => array(),
		'strong' => array(),
		'i' => array(),
		'em' => array(),
		'u' => array(),
		'hr' => array(),
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_unique_id      The name of the plugin associated with the note.
	 */
	public function __construct( $plugin_unique_id ) {

		$this->plugin_unique_id = $plugin_unique_id;
		$this->allowed_tags = apply_filters( 'plugin-notes-plus_allowed_html', $this->allowed_tags );
	}

	public function get_allowed_tags() {
		return $this->allowed_tags;
	}

	/**
	 * Retrieve the plugin note or notes from the options table
	 * Used for migration from options to custom table
	 *
	 * @since    1.1.0
	 */
	public function retrieve_notes_from_options() {
		$notes_array = get_option( $this->plugin_unique_id );

		$notes_output_array = array();

		if ( is_array($notes_array) ) {
			foreach( $notes_array as $index => $note_array ) {
				$notes_output_array[$index]['note'] = $this->process_plugin_note( $note_array['note'] );
				$notes_output_array[$index]['icon'] = $note_array['icon'];
				$notes_output_array[$index]['user'] = $note_array['user'];
				$notes_output_array[$index]['time'] = $note_array['time'];
			}
		}

		return $notes_output_array;
	}

	/**
	 * Get a specific plugin note by id.
	 *
	 * @since    1.1.0
	 *
	 */
	public function get_plugin_note_by_id( $note_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . Plugin_Notes_Plus::get_table_name();

		$result = $wpdb->get_row( $wpdb->prepare( //db call ok; no-cache ok
			"SELECT * FROM %i WHERE ID = %d;",
			$table_name,
			$note_id
		) );

		$note_array = array();
		$note_array['note'] = $this->process_plugin_note( $result->note_content );
		$note_array['icon'] = $result->note_icon;
		$note_array['user'] = $result->user_name;
		$note_array['time'] = $result->time;

		return $note_array;
	}

	/**
	 * Get the plugin note or notes from the custom db table
	 *
	 * @since    1.1.0
	 */
	public function get_plugin_notes() {

		$notes_output_array = array();

		global $wpdb;
		$table_name = $wpdb->prefix . Plugin_Notes_Plus::get_table_name();

		$results = $wpdb->get_results( $wpdb->prepare(  //db call ok; no-cache ok
			"SELECT * FROM %i WHERE plugin_id LIKE %s;",
			$table_name,
			$this->plugin_unique_id
		) );

		foreach( $results as $key => $row ) {

			$index = $row->id;
			$note = $row->note_content;
			$icon = $row->note_icon;
			$user = $row->user_name;
			$time = $row->time;

			$notes_output_array[$index]['note'] = $this->process_plugin_note( $note );
			$notes_output_array[$index]['icon'] = $icon;
			$notes_output_array[$index]['user'] = $user;
			$notes_output_array[$index]['time'] = $time;
		}

		return $notes_output_array;
	}

	/**
	 * Migrate plugin note from options to new database table.
	 * Assign a new id based on normalized filepath
	 *
	 * @since    1.1.0
	 */
	public function migrate_plugin_note( $new_id, $note_text, $icon_class, $username, $note_time ) {

		global $wpdb;
		$table_name = $wpdb->prefix . Plugin_Notes_Plus::get_table_name();

		$wpdb->insert(  //db call ok
			$table_name,
			array(
				'plugin_id' => $new_id,
				'note_content' => $this->process_plugin_note( $note_text ),
				'note_icon' => $icon_class,
				'user_name' => $username,
				'time' => $note_time,
			)
		);

		return $wpdb->insert_id;
	}

	/**
	 * Create a new database entry for a plugin note.
	 *
	 * @since    1.1.0
	 */
	public function add_plugin_note( $note_text, $icon_class, $username ) {

		$note_time = time();

		$processed_note = $this->process_plugin_note( $note_text );

		global $wpdb;
		$table_name = $wpdb->prefix . Plugin_Notes_Plus::get_table_name();

		$wpdb->insert(  //db call ok
			$table_name,
			array(
				'plugin_id' => $this->plugin_unique_id,
				'note_content' => $processed_note,
				'note_icon' => $icon_class,
				'user_name' => $username,
				'time' => $note_time,
			)
		);

		return $wpdb->insert_id;
	}

	/**
	 * Edit an existing plugin note.
	 *
	 * @since    1.1.0
	 */
	public function edit_plugin_note( $note_text, $icon_class, $username, $note_id ) {

		$note_time = time(); // update time for edited note

		$processed_note = $this->process_plugin_note( $note_text );

		global $wpdb;
		$table_name = $wpdb->prefix . Plugin_Notes_Plus::get_table_name();

		$wpdb->update( //db call ok; no-cache ok
			$table_name,
			array(
				'note_content'  => $processed_note,	// string
				'note_icon'     => $icon_class, // string
				'user_name'     => $username,	// string
				'time'          => $note_time,	// int
			),
			array( 'id' => $note_id ),
			array(
				'%s',	// note_content
				'%s',	// note_icon
				'%s',	// user_name
				'%d',	// time
			),
			array( '%d' )
		);

		return $note_id;
	}

	/**
	 * Sanitize the plugin note and convert any urls to links.
	 *
	 * @since    1.0.0
	 */
	protected function process_plugin_note( $note ) {

		$sanitized_note = stripslashes( force_balance_tags( wp_kses( $note, $this->allowed_tags ) ) );
		$note_with_links = $this->convert_urls_to_links( $sanitized_note );

		return $note_with_links;
	}

	/**
	 * Turn all URLs in clickable links.
	 *
	 * @param string $input
	 * @return string
	 *
	 * @since    1.0.0
	 */
	protected function convert_urls_to_links( $input ) {

		$url_without_tags_regex = "/<a.*?<\/a>(*SKIP)(*F)|https?:\/\/\S*[^\s`!()\[\]{};:'\".,<>?«»“”‘’]/";
		$replacement_pattern = '<a href="$0">$0</a>';

		return preg_replace( $url_without_tags_regex, $replacement_pattern, $input );
	}
}