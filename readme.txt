=== Simply Strava ===
Plugin URI: http://www.njcyclist.com/simply_strava/
Author: Doug Junkins
Contributors: junkins
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F4NW5JBTC4WBA
Tags: strava, widget, cycling, running, gps
License: GPLv3
Version: 1.0.4
Requires at least: 3.4.2
Tested up to: 3.5
Stable Tag: trunk

A simple Strava widget for Wordpress

== Description ==

A widget to display the last several weeks of Strava activity (mileage) in a
sidebar.

== Installation ==

1. Unzip the zip file in your plugins directory (usually wp-content/plugins)
2. Make sure the simplystrava directory and files are owned by the user your
   webserver runs as. (e.g. chown -R www:www simplysrava)
3. In WordPress admin go to 'Plugins' -> 'Installed Plugins' and activate the
   Simply Strava plugin
4. Go to the Simply Strava settings page and populate your strava number id
   and authorization token. You can retrieve those by entering your strava
   email address and password and clicking the AutoPopulate ID & Token button.
   Don't forget to save the settings after you've autopopulated the id and
   token.
5. Go to the 'Appearance' -> 'Widgets' page and drag the Simply Strava widget
   into the sidebar of your choice. You will be able to enter a title for the
   widget there.
6. Enjoy!

== Frequently Asked Questions ==

= Is my strava username and password stored =

No. The Simply Strava settings page uses the username and password once to
obtain an access id and token from the strava webservice. The username and
password are not stored by the server, but your browser may save them.

= The graph stopped displaying in the sidebar. What do I do? =

The Strava access id and token are suppose to be valid forever, but in some
cases they may time out. If that happens, go back to the settings page and
enter your username and password again and autopoulate a new id and token.
Don't forget to save the settings after you autopopulate.

= Why do I need to enter my Strava timezone =

Strava's API returns times in your local timezone, but currently there is 
no way to identify programatically what that timezone is.

== Changelog ==

= 1.0.4 =
* Fixed DateTimeZone support for PHP 5.2

= 1.0.3 =
* Fixed path issue for settings page

= 1.0.2 =
* Added settings for timezone and units (imperial vs. metric)
* Fixed maximum scale to improve rendering

= 1.0.1 =
* Improved webservice timeout handling

= 1.0.0 =
* Initial Release
