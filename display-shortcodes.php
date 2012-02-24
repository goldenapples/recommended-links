<?php

/**
 * @function	output_addlink_form
 *
 * Outputs or returns html markup for the "Add Link" form.
 * Called by the shortcode `reclink_addform` as well as by the Add Link widget,
 * and can be called directly by theme or bookmarklet.
 *
 * @param	bool	Whether to echo (true) or return (false).
 *
 */
add_shortcode( 'reclink_addform', 'output_addlink_form' );

function output_addlink_form( $echo = false ) {
	$plugin_settings = get_option( 'reclinks_plugin_options' );
	ob_start();
?>
	<form class="reclinks_addlink" action="<?php echo add_query_arg( 'action', 'reclink-add' ); ?>" method="POST">
		<div class="reclink_form_field">
			<label for="reclink_URL"><?php _e('Link URL', 'gad_reclinks'); ?></label>
			<input type="text" name="reclink_URL" id="reclink_URL" />
		</div>
		<div class="reclink_form_field">
			<label for="reclink_title"><?php _e('Link Title', 'gad_reclinks'); ?></label>
			<input type="text" name="reclink_title" id="reclink_title" />
		</div>
		<div class="reclink_form_field">
			<label for="reclink_description"><?php _e('Link Description', 'gad_reclinks'); ?></label>
			<textarea id="reclink_description" name="reclink_description" rows="10" cols="30" ></textarea>
		</div>
<?php if ( isset( $plugin_settings['tax'] ) && is_array( $plugin_settings['tax'] ) ) {
	foreach ( $plugin_settings['tax'] as $tax => $on ) {
		$t = get_taxonomy( $tax );
		echo '<div class="reclink_form_field">';
		echo '<label for="reclink_taxes['.$tax.']">'.$t->labels->name.'</label>';
		wp_dropdown_categories( 
			array( 
				'taxonomy' => $tax,
				'id' => 'tax-select-'.$tax,
				'name' => 'reclink_taxes['.$tax.']',
				'show_option_none' => sprintf( __( 'No %s', 'gad_reclinks' ), $t->labels->singular_name ),
			    'hide_empty' => 0
			)
		);	
		echo '</div>';
	}
} ?>
		<p><button type="submit" id="reclink_submit"><?php _e( 'Submit Link', 'gad_reclinks' ); ?></button></p>
	</form>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	if ( $echo === true ) echo $output; else return $output;
}


/**
 * @function	reclinks_bookmarklet
 *
 * Displays a button for the Add Link bookmarklet and outputs the javascript 
 * necessary for the bookmarklet.
 *
 * Based loosely on the internals of the Press This bookmarklet, but all of the work
 * is done front-end so there's no need to load admin scripts, etc.
 *
 * @param	bool	true: echoes bookmarklet, false: returns it.
 */

add_shortcode( 'reclink_bookmarklet', 'reclinks_bookmarklet' );

function reclinks_bookmarklet( $echo = false ) {
	$plugin_settings = get_option( 'reclinks_plugin_options' );
	$submit_link_url = add_query_arg( array( 'action' => 'submitlink' ), home_url() );
	$button_text = isset( $plugin_settings['bookmarklet_text'] ) ? $plugin_settings['bookmarklet_text'] : get_bloginfo('title') .' bookmarklet';
	$button_class = isset( $plugin_settings['bookmarklet_class'] ) ? $plugin_settings['bookmarklet_class'] : 'reclinks-bookmarklet';
	if ( !empty( $plugin_settings['button-class'] ) ) $button_class .= " " . trim( $plugin_settings['button-class'] );
	$button = <<<HTML
<p class="{$button_class}">
<a onclick="return false;" oncontextmenu="if(window.navigator.userAgent.indexOf('WebKit')!=-1||window.navigator.userAgent.indexOf('MSIE')!=-1)jQuery('.reclinks-bookmarklet-code').show().find('textarea').focus().select();return false;" href="javascript:var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='{$submit_link_url}',l=d.location,e=encodeURIComponent,u=f+'&u='+e(l.href)+'&amp;t='+e(d.title)+'&amp;s='+e(s)+'&amp;v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'))l.href=u;};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)"><span>$button_text</span></a>
</p>
HTML;
	if ( $echo ) echo $button;
	else return $button;
}


/**
 * @function	reclinks_votebox
 *
 * Returns or outputs the div with vote buttons and current points score.
 * Called by default by the filters above on the_content and comment_text,
 * but you can remove those filters and include this function in your themes.
 *
 * @param	bool	true: echoes votebox, false: returns it as text.
 */

function reclinks_votebox ( $echo = true ) {
	global $post, $comment, $current_user, $wpdb;

	if ( $post->post_type !== 'reclink' )
		return;

	if ( !isset( $comment ) ) {

		// votebox on recommended link itself
		$current_score = get_post_meta( $post->ID, '_vote_score', true );
		$comments_number = get_comments_number();
		if ( $comments_number > 0 )
			$comments_text = _n( 'One comment', sprintf( '%s comments', get_comments_number() ) ,get_comments_number(), 'gad_reclinks' );
		else 
			$comments_text = __( 'No comments yet', 'gad_reclinks' );
		
		$comments_link_text = '- <a href="' . get_comments_link() . '" title="' . the_title_attribute( 'echo=0' ) . '">' . $comments_text . '</a>';
		$author_link = ( get_the_author() ) 
			?  '<a href="' . get_author_posts_url( $post->post_author ) . '">' . get_the_author() . '</a>' 
			: "Anonymous";
		$submit_time = '<a href="'.get_permalink( $post->ID ).'">'.human_time_diff( mysql2date( 'U', $post->post_date ), current_time('timestamp') ) . ' ago</a>';

		$comment_ID = 0;

	} else {

		// fields relevant to comments
		$current_score = $comment->comment_karma;
		$comments_link_text = '';
		$author_link = get_comment_author_link();
		$submit_time = '<a href="'.get_comment_link( $comment ).'">'.human_time_diff( mysql2date( 'U', $comment->comment_date ) ) . ' ago</a>';

		$comment_ID = $comment->comment_ID;
	}

	$reclinks_options = get_option( 'reclinks_plugin_options' );

	get_currentuserinfo();

	if ( is_user_logged_in() ) {
		$current_vote = $wpdb->get_var( "
			SELECT vote FROM {$wpdb->reclinkvotes}
			WHERE post_id = {$post->ID} AND comment_id = $comment_ID
			AND voter_id = {$current_user->ID}" );
	} else {
		$current_vote = $wpdb->get_var( "
			SELECT vote FROM {$wpdb->reclinkvotes}
			WHERE post_id = {$post->ID} AND comment_id = $comment_ID
			AND voter_id = 0 AND voter_ip = '{$_SERVER['REMOTE_ADDR']}'" );
	}

	$vote_options = "\r\n" . '<form class="reclinks_vote" method="post" action="'.add_query_arg( 'action', 'reclink-vote' ).'" style="display:inline;">';
	$vote_options .= '<input type="hidden" name="reclink" value="'.$post->ID.'" >';

	if ( isset( $comment ) )
		$vote_options .= '<input type="hidden" name="comment" value="'.$comment->comment_ID.'">';

	foreach( $reclinks_options['vote-values'] as $vote => $values ) {
		$class = 'votelink-' . $vote;
		if ( $current_vote == $values['value'] )
			$class .= ' current-vote';
		$vote_options .= '<button class="votelink '.$class.'" name="vote" value="'.$values['value'].'" data-vote="'.$values['value'].'">';
		$vote_options .= $values['text'] . '</button>';
	}


	$votebox = <<<VOTEBOX
<div class="votebox">$vote_options | <span class="votescore">$current_score</span> points by $author_link $submit_time $comments_link_text</form></div>
VOTEBOX;

	if ( $echo === true )
		echo $votebox;
	else
		return $votebox;
}


/**
 * Display the terms (of any taxonomy) associated with a given link.
 *
 * A wrapper around the_terms, which loops through all taxonomies, displaying
 * All the terms in each category. Accepts the same parameters as the_terms.
 *
 * @param	string	Text to display before the list of terms
 * @param	string	Text to display in between each term
 * @param	string	Text to display after the list of terms
 *
 */
function reclink_terms( $before = '<span class="terms-%s">[', $sep = ', ', $after = ']</span> ' ) {
	global $post;
	$obj = get_post_type_object( $post->post_type );
	$taxes = $obj->taxonomies;
	if ( $taxes ) {
		foreach ( $taxes as $tax ) {
			$tax_before = str_replace( '%s', $tax, $before );
			$tax_sep 	= str_replace( '%s', $tax, $sep );
			$tax_after 	= str_replace( '%s', $tax, $after );
			the_terms( $post->ID, $tax, $tax_before, $tax_sep, $tax_after );
		}
	}
}

/**
 * Shortcode to display buttons for different sort options
 *
 * Usage: 	[reclink_sort_options] 
 * 				to show buttons for all four sorting options, with default text
 *			[reclink_sort_options show="newest,current,score" score="All time best"]
 *				will show only buttons for newest, current, and score, and will use
 *				"All time best" as the text for the "score" button rather than "Top Score"
 */
add_shortcode( 'reclink_sort_options', 'reclink_sort_options' );

function reclink_sort_options( $args = null ) {
	$defaults = array(
		'base_url'		=> false,
		'show' 			=> 'hot,current,newest,controversial,score',
		'hot' 			=> __( 'Hot', 'reclinks' ),
		'current' 		=> __( 'Current', 'reclinks' ),
		'newest' 		=> __( 'Newest', 'reclinks' ),
		'controversial' => __( 'Controversial', 'reclinks' ),
		'score' 		=> __( 'Top Score', 'reclinks' )
	);
	$args = wp_parse_args( $args, $defaults );
	$links = explode( ',' , $args['show'] );
	
	$plugin_settings = get_option( 'reclinks_plugin_options' );
	$archive_page = ( isset( $plugin_settings['page_for_reclinks'] ) ) ?
		get_permalink( $plugin_settings['page_for_reclinks'] ) : get_post_type_archive_link( 'reclink' );
	if ( $args['base_url'] ) 
		$archive_page= $args['base_url'];

	$return = '<form class="reclinks-sort-options" action="'.$archive_page.'" method="get" >';
	foreach ( $links as $link )
		$return .= '<button class="reclinks-sort reclinks-sort-'.$link.'" name="sort" value="'.$link.'">'.$args[$link].'</button>';
	$return .= '</form>';

	return $return;
}
