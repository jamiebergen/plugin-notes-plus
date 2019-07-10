=== Plugin Notes Plus ===
Contributors: jamiebergen
Tags: plugins, plugin notes, memo
Donate link: https://jamiebergen.com/donate/
Requires at least: 4.0
Tested up to: 5.2.2
Requires PHP: 5.5.24
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a column to the Plugins page where you can add, edit, or delete notes about a plugin.

== Description ==
Have you ever returned to a site that you built a while back and asked, "Dude, why did I install this plugin?" This plugin provides an extra column on the Plugins page that enables you to add, edit, or delete notes about the plugins you have installed on a particular site. These notes are intended to provide documentation regarding why a particular plugin was installed and how or where it's being used.

Features

* Add as many or as few notes as you need for each plugin.
* Edit or delete notes as desired.
* Select an icon to go with each note to quickly convey what type of content it contains (e.g., info, warning, link, etc.)
* Format notes using basic HTML tags if desired.
* Any links included in the note will be automatically converted to `target="_blank"`
* Notes are added and updated via Ajax, avoiding slow page reloads.
* Notes also display on the WordPress Updates page for any plugins that need to be updated.

== Installation ==
1. You can either install the plugin via the Plugins directory from within your WordPress install, or you can upload the files manually to your server by extracting the .zip file and placing its contents in the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Add, edit, or delete notes in the Plugin Notes column on the Installed Plugins page.

== Frequently Asked Questions ==
= Can I modify which icons are available to display next to plugin notes? =

Yes, you can use the filter plugin-notes-plus_icon_options to modify the set of icons available. The icons must be selected from [the list of available WordPress dashicons](https://developer.wordpress.org/resource/dashicons/).

Here is an example of a snippet that removes one icon and adds an additional icon to the list of options. It can be added to your child theme's functions.php:

`function pnp_change_icon_options( $icon_options ) {

    // Remove key option
    unset( $icon_options['dashicons-admin-network'] );

    // Add smartphone option
    $icon_options['dashicons-smartphone'] = 'Smartphone';

    return $icon_options;
}
add_filter( 'plugin-notes-plus_icon_options', 'pnp_change_icon_options' );`

= Which HTML tags are permitted, and can that list be modified? =

You can use the following HTML tags: `a`, `br`, `p`, `b`, `strong`, `i`, `em`, `u`, `hr`.

To modify the list of available tags, use the filter plugin-notes-plus_allowed_html. Be careful, however, to avoid allowing tags that could leave the site vulnerable to an XSS attack.

`function pnp_change_allowed_html_tags( $allowed_tags ) {

    // Remove br from allowed tags
    unset( $allowed_tags['br'] );

    // Add img to allowed tags
    $allowed_tags['img'] = array();

    return $allowed_tags;
}
add_filter( 'plugin-notes-plus_allowed_html', 'pnp_change_allowed_html_tags' );`

= Where is the data stored? =

Plugin notes and note metadata are stored in a custom table whose name ends in `plugin_notes_plus`. In the initial version (1.0.0), notes were stored in the options table. Version 1.1.0 was released to migrate existing notes from the options table into the `plugin_notes_plus` table. Upgrading to version 1.1.1 will perform a cleanup, removing any notes from the options table.

= How does it work on multisite installs? =

Each site within a multisite install maintains its own plugin notes. Additionally, the superadmin can maintain their own plugin notes.

== Screenshots ==

1. Upon activating the plugin, you will see a new column on the Plugins page that enables you to add, edit, or delete notes about the plugins you have installed on a particular site.

== Changelog ==

= 1.2.0 =
* Added: Plugin notes now display in a read-only format on the WordPress Updates page (update-core.php). Thanks to @douglsmith for the suggestion.
* Fixed: Removed unnecessary multisite hook. Thanks to @foomagoo for pointing this out.

= 1.1.2 =
* Fixed: Bug that prevented user from adding or updating notes after an ajax response. Thanks to @anticosti for helping to identify this bug.
* Added: Spinning icon to indicate that a note is in the process of being deleted.

= 1.1.1 =
* Added: Cleanup routine to remove notes from the options table. (If upgrading from 1.0.0, notes will first be migrated into their own table.)

= 1.1.0 =
* Fixed: Bug that caused plugin notes to disappear on Windows servers due to discrepancies in the plugin file path related to forward vs. backslash. This update will recover missing notes. Thanks to @gwalsh66 for helping to identify this bug.
* Changed: Plugin notes will now be stored in a custom table called `$wpdb->prefix . 'plugin_notes_plus'`
* Added: Migration routine to move notes from the options table into their own table if upgrading from version 1.0.0
* Added: Entry in the options table called 'plugin_notes_plus_db_version' to track the custom database table version

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
This version adds a feature to display plugin notes on the WordPress Updates page.

= 1.1.2 =
This version fixes a bug where plugin notes couldn't be updated if the user had previously filtered the list of plugins.

= 1.1.1 =
This version does some behind-the-scenes cleanup to the options table to improve performance. It should not affect your existing notes or the functionality of the plugin.

= 1.1.0 =
This version migrates plugin notes into their own database table and fixes a bug with plugin notes disappearing on Windows servers.
