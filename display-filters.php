<?php

// Query filters for reclinks

add_filter( 'pre_get_posts', 'gad_reclinks_sortby' );

function gad_reclinks_sortby( $query ) {

	if ( !is_post_type_archive('reclink') )
		return $query;

	$query->set( 'posts_per_page', 25 );

	$sortby = 'current'; // default sort

	if ( isset( $_GET['sort'] ) && in_array(
			$_GET['sort'], 
			array( 'newest', 'hot', 'current', 'score' ) ) )
		$sortby = $_GET['sort'];

	switch ( $sortby ) :
		case 'score':
			// default: order by vote total
			$query->set( 'meta_key', '_vote_score' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;
		case 'current':
			add_filter( 'posts_fields', 'gad_reclinks_posts_fields' );
			add_filter( 'posts_join', 'gad_reclinks_votes_join_current' );
			add_filter( 'posts_groupby', 'gad_reclinks_groupby' );
			add_filter( 'posts_orderby', 'gad_reclinks_orderby' );
			break;
		case 'hot':
			add_filter( 'posts_fields', 'gad_reclinks_posts_fields' );
			add_filter( 'posts_join', 'gad_reclinks_votes_join_hot' );
			add_filter( 'posts_groupby', 'gad_reclinks_groupby' );
			add_filter( 'posts_orderby', 'gad_reclinks_orderby' );
			break;
		case 'newest':
			break;
	endswitch;

	return $query;

}

function gad_reclinks_posts_fields( $fields ) {
	global $wpdb;
	$fields = str_replace( "{$wpdb->posts}.*", "{$wpdb->posts}.*, SUM( {$wpdb->reclinkvotes}.vote ) AS post_vote ", $fields );
	return $fields;
}

function gad_reclinks_votes_join_hot() {
	return gad_reclinks_votes_join( '1 DAY' );
}

function gad_reclinks_votes_join_current() {
	return gad_reclinks_votes_join( '1 WEEK' );
}

function gad_reclinks_votes_join( $interval ) {
	global $wpdb;
	$join_sql = "LEFT JOIN {$wpdb->reclinkvotes} ON ( {$wpdb->reclinkvotes}.post_id = {$wpdb->posts}.ID AND DATE_ADD( {$wpdb->reclinkvotes}.vote_time, INTERVAL $interval ) > NOW() )";
	return $join_sql;
}

function gad_reclinks_groupby( $groupby ) {
	global $wpdb;
	$groupby = "{$wpdb->posts}.ID";
	return $groupby;
}

function gad_reclinks_orderby( $orderby ) {
	global $wpdb;
	$orderby = "post_vote DESC, {$wpdb->posts}.post_date DESC";
	return $orderby;
}

add_filter( 'the_content', 'gad_reclinks_show_votelinks' );

function gad_reclinks_show_votelinks( $content ) {
	global $post;
	if ( $post->post_type !== 'reclink' )
		return $content;

	$content = reclinks_votebox( false ) . $content;
	return $content;
}

add_filter( 'comment_text', 'reclinks_comment_show_votelinks' );

function reclinks_comment_show_votelinks( $comment_text, $comment ) {
	global $post;
	if ( $post->post_type !== 'reclink' )
		return $comment_text;

	$comment_text = reclinks_votebox( false ) . $comment_text;
	return $comment_text;
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
			$comments_text = _n( 'One comment', "%s comments", get_comments_number(), 'gad_reclinks' );
		else 
			$comments_text = __( 'No comments yet', 'gad_reclinks' );
		
		$comments_link_text = '<a href="' . get_comments_link() . '" title="' . the_title_attribute( 'echo=0' ) . '">' . $comments_text . '</a>';
		$author_link = ( get_the_author() ) 
			?  '<a href="' . get_author_posts_url( $post->post_author ) . '">' . get_the_author() . '</a>' 
			: "Anonymous";
		$submit_time = '<a href="'.get_permalink( $post->ID ).'">'.human_time_diff( mysql2date( 'U', $post->post_date ) ) . ' ago</a>';

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
		$class = ( $current_vote === $values['value'] ) ? 'current_vote' : '';
		$vote_options .= '<button class="votelink '.$class.'" name="vote" value="'.$values['value'].'" data-vote="'.$values['value'].'">';
		$vote_options .= $values['text'] . '</button>';
	}

	$vote_options .= '</form>';

	$votebox = <<<VOTEBOX
<div class="votebox">$vote_options | <span class="votescore">$current_score</span> points by $author_link $submit_time - $comments_link_text</div>
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

function reclink_domain( $echo = true ) {
	global $post;
	if ( $href = get_post_meta( $post->ID, '_href', true ) )
		$host = parse_url( $href, PHP_URL_HOST );
	if ( $echo )
		echo $host;
	else 
		return $host;
}

