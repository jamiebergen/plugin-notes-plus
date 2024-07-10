<?php

/**
 * The file that defines the core plugin class
 *
 * This is used to define internationalization and admin-specific hooks.
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://github.com/jamiebergen
 * @since      1.0.0
 *
 * @package    Plugin_Notes_Plus
 * @subpackage Plugin_Notes_Plus/includes
 * @author     Jamie Bergen <jamie.bergen@gmail.com>
 */

class Plugin_Notes_Plus {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Notes_Plus_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected static $plugin_name = 'plugin-notes-plus';

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The db table name.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $version    The db table name without the prefix.
	 */
	protected static $table_name = 'plugin_notes_plus';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NOTES_PLUS_VERSION' ) ) {
			$this->version = PLUGIN_NOTES_PLUS_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Notes_Plus_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Notes_Plus_i18n. Defines internationalization functionality.
	 * - Plugin_Notes_Plus_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plugin-notes-plus-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plugin-notes-plus-i18n.php';

		/**
		 * The classes responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-plugin-notes-plus-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-plugin-notes-plus-the-note.php';

		$this->loader = new Plugin_Notes_Plus_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Notes_Plus_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Plugin_Notes_Plus_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Plugin_Notes_Plus_Admin( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Decision point for where to place notes
		$this->loader->add_action( 'after_setup_theme', $this, 'get_note_placement_option' );

		$pnp_note_placement = get_option( 'plugin_notes_plus_note_placement' );

		if ( 'description' === $pnp_note_placement ) {
			// Option to display plugin notes beneath description
			$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'display_plugin_note_desc', 10, 4 );
		} else { // 'column' by default
			// Custom column on plugins page in admin
			$this->loader->add_filter( 'manage_plugins_columns', $plugin_admin, 'add_plugin_notes_column' );
			$this->loader->add_action( 'manage_plugins_custom_column', $plugin_admin, 'display_plugin_note', 10, 3 );

			// Separate hook for multisite admin plugins page (only required for column option)
			$this->loader->add_filter( 'manage_plugins-network_columns', $plugin_admin, 'add_plugin_notes_column' );
		}

		// Ajax responses for adding and deleting notes
		$this->loader->add_action( 'wp_ajax_pnp_add_response', $plugin_admin, 'pnp_add_response');
		$this->loader->add_action( 'wp_ajax_pnp_delete_response', $plugin_admin, 'pnp_delete_response');
	}

	/**
	 * Helper function to get and set the note placement option in the database
	 *
	 * @since    1.2.4
	 */
	public function get_note_placement_option() {

		$pnp_note_placement_options = array( 'column', 'description' );

		$pnp_note_placement = 'column'; // set default

		$pnp_note_placement = apply_filters( 'plugin-notes-plus_note_placement', $pnp_note_placement );

		// Protect against unexpected options 
		if ( ! in_array( $pnp_note_placement, $pnp_note_placement_options ) ) {
			$pnp_note_placement = 'column';
		}

		update_option( 'plugin_notes_plus_note_placement', $pnp_note_placement );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Notes_Plus_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the db table name.
	 *
	 * @since     1.1.0
	 * @return    string    The db table name without the prefix.
	 */
	public static function get_table_name() {
		return self::$table_name;
	}

}
