=== Events Manager Presenter Addon ===
Contributors: jeiseman
Tags: events manager, presenter, speaker, events, custom post type
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a Presenter Custom Post Type linked seamlessly into the Events Manager layout.


== Description ==

Adds a Presenter Custom Post Type with contact fields, linked seamlessly into the Events Manager layout with an auto-injector toggle.

This add-on allows you to easily create and manage speaker or presenter profiles independently, and attach them directly to your existing Events Manager events. By linking a presenter, you can choose to auto-inject their profile and contact information at the bottom of the event page, or use custom placeholders to place their details exactly where you want them.

= Features =
*   **Presenter Profiles:** Manage presenters completely separately from standard WordPress users via a dedicated Custom Post Type.
*   **AJAX Search Integration:** Quickly find and link presenters to events from the edit screen without slowing down the admin area.
*   **Custom Placeholders:** Use `#_PRESENTERNAME`, `#_PRESENTERBIO`, `#_PRESENTERPHOTO`, `#_PRESENTERWEBSITE`, `#_PRESENTEREMAIL`, and `#_PRESENTERPHONE` within your Events Manager formatting settings.
*   **Auto-Inject Option:** Automatically append a styled presenter profile box to the bottom of the linked event's content.

== Installation ==

1. Upload the `events-manager-presenter-addon` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the new "Presenters" menu in your admin dashboard to add your first profile.
4. Open an event in Events Manager and use the new meta box to search for and link your presenter.

== Frequently Asked Questions ==

= Does this require the Events Manager plugin? =
Yes, this is an add-on and requires the core Events Manager plugin to be installed and activated.

= Can I link more than one presenter to an event? =
Currently, the add-on supports a single primary presenter per event. 

== Changelog ==

= 1.1.0 =
* Initial release for the WordPress.org repository.
