=== To-Do Dashboard Widget ===
Contributors: catchmyfame
Donate link: https://paypal.me/catchmyfame
Tags: dashboard, widget, todo, to-do, todo list, to-do list, reminders
Requires at least: 4.0.0
Tested up to: 4.3.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An intuitive and easy-to-use to-do list for your WordPress dashboard.

== Description ==

Have you ever been working in WordPress and needed to jot down some notes for later? Of course you have! Now there's a dashboard widget that can manage your to-do items quickly and easily. You'll wonder how you ever got along without it.

Just type a to-do item and hit enter. That's it! You can enter HTML in your to-do items including links and images, or just plain text. Re-order items simply by dragging them. Edit an item by double clicking, making your changes, then clicking outside the item.

### Configuration Options

* Display age for each to-do item (e.g. 2 weeks ago)
* Display a completed option that adds a strike-through to the item and marks it as completed
* Limit the number of to-do items that can be entered 
* Restrict the HTML tags that can be used in the to-do items
* Automatically color code items based on their age
* Restrict the plugin by role
* Cleanup database upon plugin removal

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= The HTML tags I entered aren't working =

The format of your text in this field is critical to it working. There must be no spaces anywhere in the text. For example `em,i,b,strong,a(href/title)` will work while `em, i, b, strong, a(href/title)` wont. Also be sure that any attributes you enter (e.g. href, title, src) are separated by forward slashes and enclosed in parenthesis.

= Why isn't the administrator role listed with the other roles that an access the widget? =

Since dashboard widgets are configured from the widget itself, someone needs to always have access to it, hence the administrator is always included in the list of roles and can't accidentally be omitted.

== Screenshots ==

1. The to-do list showing single line, multi-line, and HTML entries.
2. The to-do list showing items that have automatic background colors applied based on their age.
3. The to-do configuration screen.

== Changelog ==

= 1.1 =
* Added the ability to edit any item.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1 =
Added ability to edit items.
