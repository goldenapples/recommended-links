=== Recommended Links for WordPress ===
Contributors: goldenapples
Donate link: http://goldenapplesdesign.com/projects/recommended-links-plugin-for-wordpress/
Tags: social bookmarking, sharing, voting, reddit, digg
Requires at least: 3.2.1
Tested up to: 3.3.1
Stable tag: 0.3.3

A sort of "Reddit clone" that allows users to post links, up- or down-vote them, and comment on them.

== Description ==

This is the beginning of what I hope will be a much more ambitious project. If you want to try this out, please give me feedback.

I will guarantee backwards compatibility with all data saved by the plugin, so it should be safe to install and activate, and as I add features, your existing posts and links will work with them just fine.

== Installation ==

1. Upload the entire `recommended-links/` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. A plugin options page is included under the "Recommended Links" admin menu, where you can select some basic settings.
1. Include the 'Reclinks Add Link Form' widget in a sidebar (or anywhere). Alternately, you can use the shortcode `[reclinks_addlinkform]` in any page or post.
1. Any links added through this widget show up in the archive for the custom post type "Recommended Links". You can also create a new page and set that page to show your links on the plugin options page.
1. If you have permalinks enabled, the archive will be displayed at `http://yoursite.tld/reclinks`; otherwise it will be at `http://yoursite.tld?post_type=reclink`. 
1. The plugin tries to intelligently filter markup so that it can work with most themes out of the box. If the archive or single link display doesn't work in your theme, you may have to create an `archive-reclink.php` or `single-reclink.php` template file. _(See the FAQ for examples of markup for these template files)_.
1. See the "Frequently Asked Questions for questions on customizing the output or styling the plugin.

== Frequently Asked Questions ==

= How can I customize the markup of the list of "Recommended Links"? =

__If you are using the default post type archive__, WordPress will search in your theme directory for a file called `archive-reclinks.php`. If thats not found, it will fall back along the usual WordPress [template hierarchy](http://codex.wordpress.org/Template_Hierarchy). The easiest way to begin customizing your output is to copy your existing `archive.php` or `index.php` to a file called `archive-reclinks.php` and begin making changes to it.

__If you have selected an existing page to hold your archive__, WordPress will use the template assigned to that page for the page layout, and the loop defined in the `loop_reclinks.php` file in the plugin directory to mark up the list of recommended links. To make changes to the `loop-reclinks.php` (which also affects the output of the recommended links list widget), copy it to your theme directory and edit it there.

__Individual recommended link posts__ are handled by WordPress's normal template hierarchy in your theme. If you want to change their markup and layout, create a file in your theme directory called `single-reclink.php` (or copy your `single.php` file and rename it to `single-reclink.php`) and begin editing.

= What sorting options are there? =

Currently this plugin supports sorting of archive pages by a "sort" parameter passed via query string. The options allowed are:

* **newest** Sort links by posted time, most recently first
* **hot** Sort links by votes over the past day
* **current** Sort by votes over the past week
* **score** Sort by total vote score over time

So for example, with permalinks enabled, the URL `yoursite.tld/reclinks/?sort=hot` would display a page of the 25 links with the highest vote score over the past day.

You can also set a default sort order from the plugin settings page. The query string argument, if present, overrides the default value. So if your default sorting is **current**, you can still give your users a chance to view top voted links of all time by giving them a link to `yoursite.tld/reclinks/?sort=score`.

= What markup does the plugin offer? =

This plugin filters `the_permalink` to display the link submitted. A typical archive page, where the post title is wrapped in markup like
`<a href=<?php the_permalink(); ?>"><?php the_title(); ?></a>`
will display the title of the submitted link, linking to that link itself.

If you want to access the permalink of the comments page on your site, use `get_permalink()` instead.

By default, this plugin filters both `the_content` and `comment_text` to add vote buttons and current score box above the post/comment content. If you want to display these in a different position, you can unhook those filters and use the template tag `reclinks_votebox()` in your template files wherever you want the vote box to display.

There are not many special display features yet. A partial listing:

* `reclink_domain()` echoes the host of the link submitted - give people a chance to know what they're getting themselves into before they follow a link. 
* `reclink_votebox()` echoes a div with +/- vote buttons, the current score, author, and human time diff'd post date
* `author_karma( $user )` returns a user's total karma score



== Changelog ==

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

= 0.3.3 = 

This update fixes several bugs that were pointed out (time zone setting, posts showing up as from "anonymous", update messages not defined), and adds an option to disable voting on comments. It also styles the +1/-1 buttons a bit - skins for the style will be selectable and overridable by version 0.4, but just for now I was tired of seeing the unstyled buttons.

= 0.3 =

This version includes a plugin settings page, as well as multiple new features:

* Link title now auto-populates from the link URL, so users don't have to enter it by hand
* Shortcode for link add form
* Allows you to set an existing page as the archive, rather than use the WP archive page
* Option to allow unregistered users to vote (tracks votes by IP address)
* User karma functions - not in UI yet; but roughed out.

= 0.2.2 =
Added a widget to display the most recently posted links in the sidebar. Also, fixed some minor javascript and css errors.

= 0.2.1 =
Implemented four new sorting options to the recommended links archive page: "newest", "hot", "current", and "score".
