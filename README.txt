=== Plugin Notes Plus ===
Contributors: jamiebergen
Tags: plugins, plugin notes, memo
Donate link: https://jamiebergen.com/donate/
Requires at least: 4.0
Tested up to: 4.9.2
Requires PHP: 5.5.24
Stable tag: trunk
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

== Installation ==
1. You can either install the plugin via the Plugins directory from within your WordPress install, or you can upload the files manually to your server by extracting the .zip file and placing its contents in the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Add, edit, or delete notes in the Plugin Notes column on the Installed Plugins page.

== Frequently Asked Questions ==
= Can I modify which icons are available to display next to plugin notes? =

Yes, you can use the filter plugin-notes-plus_icon_options to modify the set of icons available. The icons must be selected from here: https://developer.wordpress.org/resource/dashicons/

Here is an example of a snippet that removes one icon and adds an additional icon to the list of options. It can be added to your the child theme's functions.php:

`function change_icon_options( $icon_options ) {

    // Remove key option
    unset( $icon_options['dashicons-admin-network'] );

    // Add smartphone option
    $icon_options['dashicons-smartphone'] = 'Smartphone';

    return $icon_options;
}
add_filter( 'plugin-notes-plus_icon_options', 'change_icon_options' );`

= Which HTML tags are permitted, and can that list be modified? =

You can use the following HTML tags: `a`, `br`, `p`, `b`, `strong`, `i`, `em`, `u`, `hr`.

To modify the list of available tags, use the filter plugin-notes-plus_allowed_html. Be careful, however, to avoid allowing tags that could leave the site vulnerable to an XSS attack.

`function change_allowed_html_tags( $allowed_tags ) {

    // Remove br from allowed tags
    unset( $allowed_tags['br'] );

    // Add img to allowed tags
    $allowed_tags['img'] = array();

    return $allowed_tags;
}
add_filter( 'plugin-notes-plus_allowed_html', 'change_allowed_html_tags' );`

= Where is the data stored? =

Plugin notes are stored in the options table. Each plugin with note(s) is given a separate entry that stores all of that plugin's notes and note meta.

= How does it work on multisite installs? =

Each site within a multisite install maintains its own plugin notes. Additionally, the superadmin can maintain his/her own plugin notes.