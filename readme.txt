=== Nomore404 404 Redirection and Firewall ===
Contributors: devoutpro
Tags: 404, 301, redirection, URL, URI, host, block, blacklist, firewall
Donate link: https://devoutpro.com/nomore404/
Requires at least: 4.0
Tested up to: 5.5
Requires PHP: 5.3
Stable tag: 2.1    
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

NoMore404 is a free WordPress plugin for redirection of 404 pages and simple firewall to block malicious hosts and URLs.

== Description ==
NoMore404 is a free WordPress plugin for redirection of 404 pages and simple firewall to block malicious hosts and URLs.
All redirections are done via 301 redirection.
You can mark any hosts and URLs to be blocked as malicious as well.

== Installation ==
Installation is easy, just install and activate, the plugin will work straight away with default settings.

== Frequently Asked Questions ==
Can I submit the feature request? Or a bug report?
Please come to our [forum] (https://devoutpro.com/forums/forum/nomore404-forum/)

== Screenshots ==
1. URLs list
2. Hosts/callers list
3. Settings

== Changelog ==
2.1 Removed session use and migrated to cookie use, thus improving scalability 
    and avoiding race conditions with sessions.
    Fixed bug of the counter reset button in the dashboard.
2.0 Upload of data to backend is fully functional and is in the crob job.
    It has to be manually switched on by user via "Share suspicious and malicious"
    setting, otherwise plugin will not upload anything. 
1.15 Bug fix of plugin update process and new bug in Callers list, callers list 
     was empty when filtered by URI
1.14 Manual upload of data to the backend system for analysis is done
1.13 Fixed activation and db upgrade errors. 
     Uploading whitelists, suspects and  blacklists to the backend
     Changed SQL data to GMT
     Fixed bug when uri and caller count did not work on uri/caller filter.
1.12 fixed whitespace issue in dashboard widget file
1.11 adding api calls to the plugin to upload/download data to/from backend
1.10 linux lookup bugfix
1.09 Bugfix saving URI, optimising host name lookup for windows and linux
1.08 Callers have new field hostname and new bulk action created to find hostnames.
     Added button to reset statistics and additional stat value.
     Added comment field for URI.
1.07 Bugfix - fixed counting of blocked malicious for the widget
1.06 Whitelist functionality to callers is added, minor UI adjustments to URI table and Callers table. 
     Link to abuseipdb is created from Callers malicious column.
     Added dashboard widget to show statistics.
1.05 Bug fix of import function: it was removing all previous data before import, now all data is kept and new added.
1.04 Initial release

== Upgrade Notice ==
Just replace plugin, deactivate and activate. All upgrades will be done automatically.
