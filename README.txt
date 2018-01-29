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
Have you ever returned to a site that you built a while back and asked, \"Dude, why did I install this plugin?\" This plugin provides an extra column on the Plugins page that enables you to add, edit, or delete notes about the plugins you have installed on a particular site. These notes are intended to provide documentation regarding why a particular plugin was installed and how it\'s being used.

Features
* Add as many or as few notes as you need for each plugin.
* Edit or delete notes as desired.
* Select an icon to go with each note to quickly convey what type of note it is (e.g., info, warning, link, etc.)
* Format notes using basic HTML tags if desired.
* Any links included in the note will be automatically converted to `target=\"_blank\"`

== Installation ==
1. You can either install the plugin via the Plugins directory from within your WordPress install, or you can upload the files manually to your server by extracting the .zip file and placing its contents in the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Add, edit, or delete notes in the Plugin Notes column on the Installed Plugins page.

== Frequently Asked Questions ==
= Which HTML tags are permitted? =

You can use the following HTML tags: `a`, `br`, `p`, `b`, `strong`, `i`, `em`, `u`, `img`, `hr`.

= Where is the data stored? =

Plugin notes are stored in the options table. Each plugin has a separate entry to stores all of that plugin\'s notes and note meta.

= How does it work on multisite installs? =

Each site within a multisite install maintains its own plugin notes. Additionally, the superadmin can maintain his/her own plugin notes.