<?php

// Query filters for reclinks

add_filter( 'pre_get_posts', 'gad_reclinks_sortby' );

function gad_reclinks_sortby( $query ) {

	if ( !is_post_type_archive('reclink') )
		return $query;

	if ( isset( $_GET['newest'] ) )
		return $query;

	// default: order by vote total
	$query->set( 'meta_key', '_vote_score' );
	$query->set( 'orderby', 'meta_value_num' );
	$query->set( 'order', 'DESC' );

//	var_dump( $query ); die();

}

add_filter( 'the_content', 'gad_reclinks_show_votelinks' );

function gad_reclinks_show_votelinks( $content ) {
	global $post;
	if ( $post->post_type !== 'reclink' )
		return $content;

	$content = reclinks_votebox() . $content;
	return $content;
}

function reclinks_votebox ( $echo = true ) {
	global $post;
	if ( $post->post_type !== 'reclink' )
		return;

	$current_score = get_post_meta( $post->ID, '_vote_score', true );
	$comments_number = get_comments_number();
	if ( $comments_number > 0 )
		$comments_text = _n( 'One comment', "%s comments", get_comments_number(), 'gad_reclinks' );
	else 
		$comments_text = __( 'No comments yet', 'gad_reclinks' );

	$comments_link_text = '<a href="' . get_comments_link() . '" title="' . the_title_attribute( 'echo=0' ) . '">' . $comments_text . '</a>';
	$link_submitter = ( get_the_author() ) 
		?  '<a href="' . get_author_posts_url( $post->post_author ) . '">' . get_the_author() . '</a>' 
		: "Anonymous";
	$link_submit_time = '<a href="'.get_permalink( $post->ID ).'">'.human_time_diff( mysql2date( 'U', $post->post_date ) ) . ' ago</a>';

	$reclinks_options = get_option( 'reclinks_plugin_options' );

	$vote_options = "\r\n" . '<form method="post" action="'.add_query_arg( 'action', 'reclink-vote' ).'" style="display:inline;">';
	$vote_options .= "\r\n\t" . '<input type="hidden" name="reclink" value="'.$post->ID.'" >';

	foreach( $reclinks_options['vote-values'] as $vote => $values ) {
		$vote_options .= "\r\n\t" . '<button class="votelink" name="vote" value="'.$values['value'].'" data-vote="'.$values['value'].'">';
		$vote_options .= $values['text'] . '</button>';
	}

	$vote_options .= "\r\n".'</form>';

	$votebox = <<<VOTEBOX
<div class="votebox">
	$vote_options | $current_score points by $link_submitter $link_submit_time - $comments_link_text
</div>
VOTEBOX;

	if ( $echo === true )
		echo $votebox;
	else
		return $votebox;
}

add_filter( 'the_permalink', 'gad_reclinks_permalink' );

function gad_reclinks_permalink( $permalink ) {
	global $post;
	if ( $post->post_type === 'reclink' && $href = get_post_meta( $post->ID, '_href', true ) )
		return $href;
	return $permalink;
}

