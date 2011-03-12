<?php 
/*
Package gad-link-recommendations
Subpackage roadsigns

Display Links template
*/
?>
<ul class="reclinkslist">

<?php global $reclinks_theme_options;

	foreach ($reclinks as $reclink) { ?>
	<li class="reclink">
    	<?php if ($reclinks_theme_options['commenting-enabled'] && $reclink->commentcount) { ?>
			<a href="#reclink-<?php echo $reclink->id; ?>-comments" title="Click to show comments" onclick="showReclinkComments(<?php echo $reclink->id; ?>);">
            <div class="reclink_commentcount"><?php echo $reclink->commentcount; ?></div></a>
        <?php } ?>
        	<div class="reclink_votecount"><?php echo $reclink->totalvotes; ?></div>
            <div class="reclinkvotesarea <?php echo ($reclink->currentuservoted) ? $reclink->currentuservoted : 'votesgray'; ?>">
				<?php for ($x=1;$x<=5;$x++) { ?>
                	<a href="?promote-link=<?php echo $reclink->id.'&rating='.$reclinks_theme_options['stars-'.$x.'-value']; ?>" class="reclink-vote reclink_vote_<?php echo $x; ?>"
                    title="Click to rate this link: <?php echo stripslashes($reclinks_theme_options['stars-'.$x.'-text']); ?>"></a>
                <?php } ?>
            </div>			
			
            <div class="reclink_body">
            <p class="reclink_title"><a href="<?php echo $reclink->link_href; ?>" title="Recommended Link: <?php echo apply_filters('the_title',stripslashes($reclink->link_title)); ?>"><?php echo apply_filters('the_title',stripslashes($reclink->link_title)); ?></a></p>
            <p class="reclink_href"><?php echo $reclink->link_href; ?></p>
            <?php echo apply_filters('the_content',stripslashes($reclink->link_description)); ?>
            <span class="link_addedby"><?php _e('added by','reclinks_plugin'); ?> <strong><?php echo get_userdata($reclink->link_addedby)->display_name; ?></strong></span></div>
            <div class="reclink_clear"></div>

	</li>
    <?php } ?>
</ul>