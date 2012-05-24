=== Recommended Links for WordPress ===
Contributors: goldenapples
Donate link: http://goldenapplesdesign.com/projects/recommended-links-plugin-for-wordpress/
Tags: social bookmarking, sharing, voting, reddit, digg
Requires at least: 3.2.1
Tested up to: 3.4
Stable tag: 0.4.2

A sort of "Reddit clone" that allows users to post links, up- or down-vote them, and comment on them.

== Description ==

This plugin aims to support community link-sharing, social bookmarking, and discussion of links in the WordPress environment. 

This is still at an early stage of what I hope will be a much more ambitious project. If you want to try this out, please give me feedback.

I will guarantee backwards compatibility with all data saved by the plugin, so it should be safe to install and activate, and as I add features, your existing posts and links will work with them just fine.

See the plugin's [wiki page on github](https://github.com/goldenapples/recommended-links/wiki) for more up-to-date documentation (its hard to keep documentation updated in multiple places).

== Changelog ==

= 0.4.2 =

Fixes a couple of bugs relating to displaying the plugin's widgets on a page that includes a comments query (the author and the score of all links in the widget would be taken from the last comment displayed on the page.

Also, includes an option to allow unregistered users to post for the the first time.

= 0.4.1 =

Fixes pagination bug when reclinks archive is set to front page and bug in install hook function.

= 0.4 =

This update fleshes out a number of functions that were introduced in earlier versions, and introduces a bookmarklet that can be used for easier submission of links.

= 0.3.5 =

I fixed term archives so that your recommended links archive page can be sorted by term; if you have categories enabled for links, try adding `?category=yourcategoryname` to the URL for your archive page. Also fixed issue with adding non-hierarchical taxonomies to reclinks, and fixed the loop on archive pages (reset the query after the recommended loops link) so that it doesn't mess up any secondary loops on the page.

= 0.3.4 =

I broke posting of new links in the last update... this is just a quick fix to correct that. More changes to come soon.

= 0.3.3 =

Several bugfixes; added "vote on comments" option and styling for buttons.

= 0.3.2 =

Fixed a number of "missing index" bugs that caused error messages on first activation of the plugin.

= 0.3.1 =

Fixed bug relating to empty taxonomy array on first install.

= 0.3 =

Added multiple new features:

* Plugin settings page
* Link title now auto-populates from the link URL, so users don't have to enter it by hand
* Shortcode for link add form
* Allows you to set an existing page as the archive, rather than use the WP archive page
* Option to allow unregistered users to vote (tracks votes by IP address)
* User karma functions - not in UI yet; but roughed out. Use function `author_karma( $user )` to get a user's score.

Also, fixed a couple bugs and roughed out the plugin to prepare for a number of other features in the works.

= 0.2.2 =
Added a widget to display the most recently posted links in the sidebar. Also, fixed some minor javascript and css errors.

= 0.2.1 =
Implemented four new sorting options to the recommended links archive page: "newest", "hot", "current", and "score". Also fixed minor bug in resolving symlinked plugin directory

= 0.2 = 
This is the first initial public release. This uses a custom post type, rather than trying to do everything through custom tables and functions. 

= 0.1 =
I built a version 0.1 back in 2010 for a personal project, which was only half complete (most of the functions necessary to run the plugin were mixed in with theme files, and functionality was very limited.)


== Upgrade Notice ==

= 0.4.2
Mostly bugfixes; also includes an option to allow unregistered users to post links.
