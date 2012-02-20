<?php
/**
 * Loop display to use for the listing of recommended links in the sidebar widget,
 * or if you have set a custom page to hold your recommended links archive.
 *
 * To modify this loop, copy this file to your theme directory and make any changes you want
 * to it there. Otherwise, all of your changes will be overwritten when the plugin is updated.
 *
 * If you did not set a page for your recommended links, and are using the default archive,
 * this loop will not be used. Instead, WordPress will look in your theme directory first for
 * a file titled archive-reclinks.php, then archive.php, then finally fall back on index.php
 *
 * For more information about the WordPress template hierarchy, see the Codex entry:
 * http://codex.wordpress.org/Template_Hierarchy
 *
 */

global $wp_query;
$plugin_settings = get_option( 'reclinks_plugin_settings' );
$start =  ( $wp_query->query_vars['paged'] ) ? 
	( ( $wp_query->query_vars['paged'] -1 ) * $wp_query->query_vars['posts_per_page'] + 1) : 1;
?>
	<?php if ( have_posts() ) : ?>
	<ol start="<?php echo $start; ?>">
	<?php while ( have_posts() ) : the_post(); ?>
		<li>
			<?php reclinks_favicon(); ?>
			<?php reclink_terms(); ?>
			<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong> 
<!--		<small><?php reclinks_domain(); ?><small>  -->
			<br /><?php reclinks_votebox(); ?>
		</li>
	<?php endwhile; ?>
	</ol>
	<?php else : ?>
	<p><?php _e( 'No recommended links yet. Add one?', 'gad_reclinks' ); ?></p>
	<?php endif; ?>
