=== DiscPress ===
Contributors: imbeard
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FV2P4BFDDUTFJ
Tags: discogs, discogs.com
Requires at least: 3.0.1
Tested up to: 4.7.3
Stable tag: 1.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sync your Discogs collection with your WordPress website.

== Description ==

This plugin lets you replicate your Discogs collection into your WordPress website.
It creates a new custom post Records, and then creates a post for every record you have in your collection on Discogs.

In order to download images from Discogs you have to create a Discogs app within your account at this link:
[https://www.discogs.com/settings/developers](https://www.discogs.com/settings/developers)

After the creation insert your Consumer Key and Conusmer Secret, then authenticate.
After that you can sync your Discogs collection images with your WordPress website.

**DiscPress PRO**

DiscPress is also available in a professional version which includes:

* Marketplace sync
* Images sync in higher resolution
* Shortcode to display records on your site

Get it here: [http://www.imbeard.com/discpress](http://www.imbeard.com/discpress)

**Made by** [imbeard](http://www.imbeard.com/)

== Installation ==

1. Install the plugin (Upload the `discpress` folder manually to the `/wp-content/plugins/` directory, or upload the zip with the WordPress installer)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the instructions on the DiscPress page in the WordPress settings

== Frequently Asked Questions ==

= Can I use this without violating Discogs policy? =

Yes. Discogs states the following: *Database data is made available under the CC0 No Rights Reserved license, which means there are no restrictions on what you can do with it.*
You can read more [here](https://www.discogs.com/developers/).
We still suggest you read this about fee avoidance: [Fee Avoidance Policy](https://www.discogs.com/help/doc/fee-avoidance)

= What about rate limiting? =

Read [here](https://www.discogs.com/developers/#page:home,header:home-rate-limiting).

== Screenshots ==

1. screenshot-1.jpg

== Changelog ==

= 1.2.4 =
* Plugin cleanup when uninstalled.

= 1.2.3 =
* Better image download management, to prevent duplicate images.

= 1.2.2 =
* Fixed a bug in records deletion
* Now you can sync without authorization (but you won't be able to download images)
* Minor fixes and changes

= 1.2.1 =
* Images downloads back at one at a time
* Better management of images placeholders
* Minor fixes and changes

= 1.2.0 =
* Import can now handle multiple values in one field (for example multiple labels)
* Added import fields: id, year
* Removed import field: entity-type
* Minor fixes and changes

= 1.1.0 =
* Improved collection syncing: now managed by AJAX, new records fetched from Discogs are added, existing ones are updated, old ones are deleted
* Improved images syncing: now grabbing 10 images at a time
* Fixed CSS conflicting with other WordPress elements
* Minor fixes and changes

= 1.0.0 =
* First version released

== Upgrade Notice ==

You should always use the latest version, to get the best out of the plugin.
