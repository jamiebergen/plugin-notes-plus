<?php

/*

*/

class Better_Plugin_Notes_The_Note {

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
		$this->allowed_tags = apply_filters('bpn_allowed_tags', $this->allowed_tags);
	}

	/**
	 * Check to see whether this plugin already has a note.
	 *
	 * @since    1.0.0
	 */
	public function has_plugin_note() {

		$the_note = get_option( $this->plugin_unique_id );

		$option_set_but_empty = ('' === $the_note);

		return ( $the_note || $option_set_but_empty );
	}

	/**
	 * Get a specific plugin note.
	 *
	 * @since    1.0.0
	 */
	public function get_plugin_note( $index ) {

		$note_array = get_option( $this->plugin_unique_id )[$index];

		$note_output_array = array();
		$note_output_array['note'] = $this->process_plugin_note( $note_array['note'] );
		$note_output_array['icon'] = $note_array['icon'];

		return $note_output_array;
	}

	/**
	 * Get the plugin note or notes.
	 *
	 * @since    1.0.0
	 */
	public function get_plugin_notes() {

		$notes_array = get_option( $this->plugin_unique_id );

		$notes_output_array = array();

		if ( is_array($notes_array) ) {
			foreach( $notes_array as $index => $note_array ) {
				$notes_output_array[$index]['note'] = $this->process_plugin_note( $note_array['note'] );
				$notes_output_array[$index]['icon'] = $note_array['icon'];
			}
		}
		return $notes_output_array;
	}

	/**
	 * Create a new database entry and add the plugin's first note.
	 *
	 * @since    1.0.0
	 */
	public function initialize_plugin_notes( $note_text, $icon_class, $username ) {

		$note_time = time();

		$single_note = $this->set_up_plugin_note_array( $note_text, $icon_class, $username, $note_time );

		// add random num at end of time to ensure that two entries at the same time won't overlap
		$note_index = $note_time . '_' . rand( 10, 99 );

		$notes_array = array();
		$notes_array[$note_index] = $single_note;

		add_option( $this->plugin_unique_id, $notes_array );
		return $note_index;
	}

	/**
	 * Append additional notes to a plugin's existing entry.
	 *
	 * @since    1.0.0
	 */
	public function append_plugin_note( $note_text, $icon_class, $username ) {

		$note_time = time();

		$new_note_array = $this->set_up_plugin_note_array( $note_text, $icon_class, $username, $note_time );

		$note_index = $note_time . '_' . rand( 10, 99 );

		$notes_array = get_option( $this->plugin_unique_id );
		$notes_array[$note_index] = $new_note_array;

		update_option( $this->plugin_unique_id, $notes_array );
		return $note_index;
	}

	/**
	 * Edit an existing plugin note.
	 *
	 * @since    1.0.0
	 */
	public function edit_plugin_note( $note_text, $icon_class, $note_index, $username ) {

		$note_time = substr( $note_index, 0, -3 );

		$edited_note_array = $this->set_up_plugin_note_array( $note_text, $icon_class, $username, $note_time );

		$notes_array = get_option( $this->plugin_unique_id );
		$notes_array[$note_index] = $edited_note_array;

		update_option( $this->plugin_unique_id, $notes_array );

		return $note_index;
	}

	/**
	 * Set up array with the plugin note and meta info.
	 *
	 * @since    1.0.0
	 */
	protected function set_up_plugin_note_array( $note_text, $icon_class, $username, $note_time ) {

		$processed_note = $this->process_plugin_note( $note_text );

		$note_array = array();
		$note_array['note'] = $processed_note;
		$note_array['icon'] = $icon_class; // e.g., dashicons-info
		$note_array['user'] = $username;
		$note_array['time'] = $note_time; // GMT

		return $note_array;
	}

	/**
	 * Delete the plugin note.
	 *
	 * @since    1.0.0
	 */
	public function delete_plugin_note( $index ) {

		$notes_array = get_option( $this->plugin_unique_id );
		unset( $notes_array[$index] );
		update_option( $this->plugin_unique_id, $notes_array );

		// Delete entire entry if the last note has been deleted
		if ( empty($notes_array) ) {
			delete_option( $this->plugin_unique_id );
		}

	}

	/**
	 * Sanitize the plugin note and convert any urls to links.
	 *
	 * @since    1.0.0
	 */
	protected function process_plugin_note( $note ) {

		$sanitized_note = stripslashes( force_balance_tags( wp_kses( $note, $this->allowed_tags ) ) );

		$note_with_links = $this->linkify( $sanitized_note );

		return $note_with_links;
	}


	/**
	 * Turn all URLs in clickable links.
	 * Source: https://gist.github.com/jasny/2000705
	 *
	 * @param string $value
	 * @param array  $protocols  http/https, ftp, mail, twitter
	 * @param array  $attributes
	 * @return string
	 *
	 */
	protected function linkify( $value, $protocols = array('http', 'mail'), array $attributes = array() ) {
		// Link attributes
		$attr = '';
		foreach ( $attributes as $key => $val ) {
			$attr = ' ' . $key . '="' . htmlentities( $val ) . '"';
		}

		$links = array();

		// Extract existing links and tags
		$value = preg_replace_callback( '~(<a .*?>.*?</a>|<.*?>)~i', function ( $match ) use ( &$links ) {
			return '<' . array_push( $links, $match[1] ) . '>';
		}, $value );

		// Extract text links for each protocol
		foreach ( (array) $protocols as $protocol ) {
			switch ( $protocol ) {
				case 'http':
				case 'https':
					$value = preg_replace_callback( '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ( $match ) use ( $protocol, &$links, $attr ) {
						if ( $match[1] ) {
							$protocol = $match[1];
						}
						$link = $match[2] ?: $match[3];

						return '<' . array_push( $links, "<a $attr href=\"$protocol://$link\">$link</a>" ) . '>';
					}, $value );
					break;
				case 'mail':
					$value = preg_replace_callback( '~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ( $match ) use ( &$links, $attr ) {
						return '<' . array_push( $links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>" ) . '>';
					}, $value );
					break;
				case 'twitter':
					$value = preg_replace_callback( '~(?<!\w)[@#](\w++)~', function ( $match ) use ( &$links, $attr ) {
						return '<' . array_push( $links, "<a $attr href=\"https://twitter.com/" . ( $match[0][0] == '@' ? '' : 'search/%23' ) . $match[1] . "\">{$match[0]}</a>" ) . '>';
					}, $value );
					break;
				default:
					$value = preg_replace_callback( '~' . preg_quote( $protocol, '~' ) . '://([^\s<]+?)(?<![\.,:])~i', function ( $match ) use ( $protocol, &$links, $attr ) {
						return '<' . array_push( $links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>" ) . '>';
					}, $value );
					break;
			}
		}

		// Insert all links
		return preg_replace_callback( '/<(\d+)>/', function ( $match ) use ( &$links ) {
			return $links[ $match[1] - 1 ];
		}, $value );
	}
}