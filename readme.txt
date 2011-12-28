=== Recommended Links for WordPress ===
Contributors: goldenapples
Donate link: http://goldenapplesdesign.com/projects/recommended-links-plugin-for-wordpress/
Tags: social bookmarking, sharing, voting, reddit, digg
Requires at least: 3.2.1
Tested up to: 3.3
Stable tag: 0.2

A sort of "Reddit clone" that allows users to post links, up- or down-vote them, and comment on them.

== Description ==

This is the beginnings of a much more ambitious project. At the moment, there is no admin options screen and functionality is a bit limited. However, if you want to try this out, please give me feedback. 

I will guarantee backwards compatibility with all data saved by the plugin, so it should be safe to install and activate, and as I add features, your existing posts and links will work with them just fine.

== Installation ==

1. Upload the entire `recommended-links/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Include the 'Reclinks Add Link Form' in a sidebar (or anywhere).
1. Any links added through this plugin show up in the archive for post type "Recommended Links". If you have permalinks enabled, that will be displayed at `http://yoursite.tld/reclinks`; otherwise it will be at `http://yoursite.com?post_type=reclinks`
1. The plugin tries to intelligently filter markup so that it can work with most themes out of the box. If the archive or single link display doesn't work in your theme, you may have to create an `archive-reclink.php` or `single-reclink.php` template file. _(See the FAQ for examples of markup for these template files)_.

== Frequently Asked Questions ==

= What markup does the plugin offer? =

This plugin filters `the_permalink` to display the link submitted. A typical archive page, where the post title is wrapped in markup like
`<a href=<?php the_permalink(); ?>"><?php the_title(); ?></a>`
will display the title of the submitted link, linking to that link itself.

If you want to access the permalink of the comments page on your site, use `get_permalink()` instead.

By default, this plugin filters both `the_content` and `comment_text` to add vote buttons and current score box above the post/comment content. If you want to display these in a different position, you can unhook those filters and use the template tag `reclinks_votebox()` in your template files wherever you want the vote box to display.

There are not many special display features yet. 

`reclink_domain()` echoes the host of the link submitted - give people a chance to know what they're getting themselves into before they follow a link. 

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

= 0.2 = 
This is the first initial public release. This uses a custom post type, rather than trying to do everything through custom tables and functions. 

= 0.1 =
I built a version 0.1 back in 2010 for a personal project, which was only half complete (most of the functions necessary to run the plugin were mixed in with theme files, and functionality was very limited.)

