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
	 * Convert plugin name to lowercase string with dashes
	 *
	 * @since    1.0.0
	 */
//	protected function get_sanitized_plugin_name() {
//
//		return sanitize_title( $this->plugin_ref_name );
//	}

	/**
	 * Generate unique options key for plugin note
	 *
	 * @since    1.0.0
	 */
//	public function get_plugin_note_key() {
//
//		return '_aaa_plugin_note_' . $this->get_sanitized_plugin_name();
//	}

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
		$note_with_icon = $this->set_up_plugin_note_string( $note_array['note'], $note_array['icon'] );
		return $note_with_icon;
	}

	/**
	 * Get the plugin note or notes.
	 *
	 * @since    1.0.0
	 */
	public function get_plugin_notes() {

		$notes_array = get_option( $this->plugin_unique_id );
		$notes_string_array = array();

		if ( is_array($notes_array) ) {
			foreach( $notes_array as $note_array ) {
				array_push( $notes_string_array, $this->set_up_plugin_note_string( $note_array['note'], $note_array['icon'] ) );
			}
		}

		return $notes_string_array;
	}

	/**
	 * Set up a string with the plugin note and meta info.
	 *
	 * @since    1.0.0
	 */
	protected function set_up_plugin_note_string( $note_text, $icon_class ) {
		$processed_note = $this->process_plugin_note( $note_text );
		$note_with_icon = '<span class="dashicons '. $icon_class .'"></span>' . $processed_note;
		return $note_with_icon;
	}


	/**
	 * Create a new plugin note.
	 *
	 * @since    1.0.0
	 */
	public function create_plugin_note( $note_text, $icon_class ) {

		$notes_array = array();
		$single_note = $this->set_up_plugin_note_array( $note_text, $icon_class );
		array_push( $notes_array, $single_note );
		add_option( $this->plugin_unique_id, $notes_array );
	}

	/**
	 * Update the plugin note.
	 *
	 * @since    1.0.0
	 */
	public function update_plugin_note( $note_text, $icon_class ) {

		$notes_array = get_option( $this->plugin_unique_id );
		$new_note_array = $this->set_up_plugin_note_array( $note_text, $icon_class );
		array_push( $notes_array, $new_note_array );

		update_option( $this->plugin_unique_id, $notes_array );
	}

	/**
	 * Set up array with the plugin note and meta info.
	 *
	 * @since    1.0.0
	 */
	protected function set_up_plugin_note_array( $note_text, $icon_class ) {
		$note_array = array();
		$processed_note = $this->process_plugin_note( $note_text );

		$note_array['note'] = $processed_note;
		$note_array['icon'] = $icon_class; // e.g., dashicons-info

		return $note_array;
	}

	/**
	 * Delete the plugin note.
	 *
	 * @since    1.0.0
	 */
	public function delete_plugin_note() {

		delete_option( $this->plugin_unique_id );
	}

	/**
	 * Sanitize the plugin note and convert any urls to links.
	 *
	 * @since    1.0.0
	 */
	protected function process_plugin_note( $note ) {

		$sanitized_note = force_balance_tags( wp_kses( $note, $this->allowed_tags ) );

		//$formatted_note = wpautop( $sanitized_note );

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

		// Insert all link
		return preg_replace_callback( '/<(\d+)>/', function ( $match ) use ( &$links ) {
			return $links[ $match[1] - 1 ];
		}, $value );
	}
}