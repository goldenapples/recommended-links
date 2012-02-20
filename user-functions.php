<?php

/**
 * Define capabilities for adding, voting, and commenting on recommended
 * links.
 *
 * TODO: This should be set through plugin settings, but for now, I'm just granting 
 * all capabilities to all logged-in users.
 *
 */
add_action( 'init', 'reclinks_define_capabilities' );

function reclinks_define_capabilities() {

	$custom_capabilities = array(
		'add_reclink',
		'comment_reclink',
		'vote_reclink' );

	global $wp_roles;

	if (!isset( $wp_roles ) )
		$wp_roles = new WP_Roles;

	foreach ( $wp_roles->get_names() as $rolename => $displayname )
		foreach ( $custom_capabilities as $cap )
			$wp_roles->add_cap( $rolename, $cap );

}


add_filter( 'login_message', 'reclinks_custom_login_message' );

function reclinks_custom_login_message() {
	if ( !isset( $_GET['msg'] ) )
		return;
	if ( 'reclinks-login' === $_GET['msg'] ) {
		$message = '<p class="message">'.__( 'You must be logged in to vote.', 'gad_reclinks' ) .'</p>';
		return $message;
	}
	if ( 'bookmarklets-login' === $_GET['msg'] ) {
		$message = '<p class="message">'.__( 'Please <b>log in</b> to submit this link.', 'gad_reclinks' ) . '</p>';
		return $message;
	}
}

add_action( 'reclink_add_vote', 'update_author_karma', 10, 2);

/**
 * After a vote is placed, update the post or comment author's total karma score.
 * Recounts votes from all posts and comments that author has written...
 * Could grow to be bad for performance, but in initial tests (scores < 5000)
 * it seemed acceptable...
 *
 * @param	int		post ID of link voted on
 * @param	int 	comment ID (null/zero if vote was on post)
 * @return 	none
 */
function update_author_karma( $post, $comment ) {
	$author = ( $comment ) ?  @get_comment( $comment )->user_id : @get_post( $post )->post_author;
	if ( !$author ) return;
	$new_karma = 0;

	$author_posts = get_posts( array( 'post_type' => 'reclink', 'author' => $author, 'numberposts' => -1 ) );
	foreach ( $author_posts as $p )
		$new_karma += get_post_meta( $p->ID, '_vote_score', true );
	
	$author_comments = get_comments( array( 'user_id' => $author ) );
	foreach ( $author_comments as $c )
		$new_karma += $c->comment_karma;
	
	$new_karma = apply_filters( 'author_karma', $new_karma );
	update_user_meta( $author, '_author_karma', $new_karma );
}

/**
 * Retrieve a user's comment score.
 *
 * @param	int|object	User ID, or the entire WP_User object
 * @return	int			the author's karma score
 */
function author_karma( $user ) {
	if ( is_object( $user ) )
		$user = $user->ID;
	$karma = get_user_meta( $user, '_author_karma', true );
	return $karma;
}
