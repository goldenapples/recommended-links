=== Recommended Links for WordPress ===
Contributors: goldenapples
Donate link: http://goldenapplesdesign.com/projects/recommended-links-plugin-for-wordpress/
Tags: social bookmarking, sharing, voting, reddit, digg
Requires at least: 3.2.1
Tested up to: 3.3
Stable tag: 0.2.3

A sort of "Reddit clone" that allows users to post links, up- or down-vote them, and comment on them.

== Description ==

This is the beginnings of a much more ambitious project. At the moment, there is no admin options screen and functionality is a bit limited. However, if you want to try this out, please give me feedback.

I will guarantee backwards compatibility with all data saved by the plugin, so it should be safe to install and activate, and as I add features, your existing posts and links will work with them just fine.

== Installation ==

1. Upload the entire `recommended-links/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Include the 'Reclinks Add Link Form' widget in a sidebar (or anywhere).
1. Any links added through this widget show up in the archive for the custom post type "Recommended Links". 
1. If you have permalinks enabled, the archive will be displayed at `http://yoursite.tld/reclinks`; otherwise it will be at `http://yoursite.tld?post_type=reclink`. __(Note: on first activating the plugin, you may have to "flush your rewrite rules" to make these permalinks work - this can be done by visiting the Settings &raquo; Permalinks page in your WordPress admin once.)__
1. The plugin tries to intelligently filter markup so that it can work with most themes out of the box. If the archive or single link display doesn't work in your theme, you may have to create an `archive-reclink.php` or `single-reclink.php` template file. _(See the FAQ for examples of markup for these template files)_.

== Frequently Asked Questions ==

= What sorting options are there? =

Currently this plugin supports sorting of archive pages by a "sort" parameter passed via query string. The options allowed are:

* **newest** Sort links by posted time, most recently first
* **hot** Sort links by votes over the past day
* **current** Sort by votes over the past week
* **score** Sort by total vote score over time

So for example, with permalinks enabled, the URL `yoursite.tld/reclinks/?sort=hot` would display a page of the 25 links with the highest vote score over the past day.

At this point, implementing these sorting options is fully up to you - a user cookie would make sense here, or a drop down at the top of the archive page would work. I will provide a template tag and a widget to change sorting once I flesh out these options some more.

= What markup does the plugin offer? =

This plugin filters `the_permalink` to display the link submitted. A typical archive page, where the post title is wrapped in markup like
`<a href=<?php the_permalink(); ?>"><?php the_title(); ?></a>`
will display the title of the submitted link, linking to that link itself.

If you want to access the permalink of the comments page on your site, use `get_permalink()` instead.

By default, this plugin filters both `the_content` and `comment_text` to add vote buttons and current score box above the post/comment content. If you want to display these in a different position, you can unhook those filters and use the template tag `reclinks_votebox()` in your template files wherever you want the vote box to display.

There are not many special display features yet. A partial listing:

* `reclink_domain()` echoes the host of the link submitted - give people a chance to know what they're getting themselves into before they follow a link. 
* `reclink_votebox()` echoes a div with +/- vote buttons, the current score, author, and human time diff'd post date

= Sample markup =

Sample markup for the loop on an `archive-reclinks.php` template. This will output a list very similar to [Hacker News](http://news.ycombinator.com)'s frontpage:

	<?php if ( have_posts() ) : ?>
	<ol>
	<?php while ( have_posts() ) : the_post(); ?>
		<li><strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong> <small><?php reclink_domain(); ?><small>
			<br /><?php reclinks_votebox(); ?>
		</li>
	<?php endwhile; ?>
	</ol>
	<?php endif; ?>

That's all there is to it!


== Changelog ==

= 0.2.2 =
Added a widget to display the most recently posted links in the sidebar. Also, fixed some minor javascript and css errors.

= 0.2.1 =
Implemented four new sorting options to the recommended links archive page: "newest", "hot", "current", and "score". Also fixed minor bug in resolving symlinked plugin directory

= 0.2 = 
This is the first initial public release. This uses a custom post type, rather than trying to do everything through custom tables and functions. 

= 0.1 =
I built a version 0.1 back in 2010 for a personal project, which was only half complete (most of the functions necessary to run the plugin were mixed in with theme files, and functionality was very limited.)


== Upgrade Notice ==

= 0.2.2 =
Added a widget to display the most recently posted links in the sidebar. Also, fixed some minor javascript and css errors.

= 0.2.1 =
Implemented four new sorting options to the recommended links archive page: "newest", "hot", "current", and "score".
