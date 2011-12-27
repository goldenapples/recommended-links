<?php 
/* AJAX responses for adding links, comments, tags, etc */

if ( !is_admin() )
	add_action('init', 'reclink_frontend_entries');

/**
 * @function	reclink_frontend_entries()
 *
 * Form handler for visitors with Javascript disabled. Should detect the presence of POST
 * data to either add link or vote/comment on link. This function should not be called if
 * Javascript is enabled, because the form handler functions in reclinks-scripts.js
 * should prevent default submission.
 *
 * @uses	gad_add_reclink()
 * @uses	gad_add_reclink_vote()
 */
function reclink_frontend_entries() {
	if ( !isset( $_GET['action'] ) )
		return;

	if ( 'reclink-add' === $_GET['action'] ) {
		$reclink = array(
			'reclink_url' => esc_url( $_POST['reclink_URL'] ),
			'reclink_title' => sanitize_text_field( $_POST['reclink_title'] ),
			'reclink_description' => wp_filter_post_kses( $_POST['reclink_description'] )
		);
		gad_add_reclink( $reclink );
	} 

	if ( 'reclink-vote' === $_GET['action'] ) {		
		global $current_user;
		get_currentuserinfo();

		$comment = ( isset( $_POST['comment'] ) ) ? intval( $_POST['comment'] ) : 0;
		$vote = intval( $_POST['vote'] );

		$votesuccess = gad_add_reclink_vote( $_POST['reclink'], $comment, $vote, $current_user->ID, $_SERVER['REMOTE_ADDR'] );
	}

}


add_action( 'wp_ajax_add_reclink', 'gad_reclinks_ajax_add' );

function gad_reclinks_ajax_add() {
	if ( !current_user_can( 'add_reclink' ) )
		die( json_encode( array( 'exception' => 'Current user is not authorized to add links' ) ) );
	$reclink = array(
		'reclink_url' => esc_url( $_POST['reclink_URL'] ),
		'reclink_title' => sanitize_text_field( $_POST['reclink_title'] ),
		'reclink_description' => wp_filter_post_kses( $_POST['reclink_description'] )
	);
	$link = gad_add_reclink( $reclink );
	echo json_encode( get_post( $link ) );
	die();

}

add_action( 'wp_ajax_vote_reclink', 'gad_reclinks_ajax_vote' );

function gad_reclinks_ajax_vote() {
	if ( !current_user_can( 'vote_reclink' ) )
		die( json_encode( array( 'exception' => 'Current user is not authorized to add links' ) ) );

	if ( !isset( $_POST['vote'] ) )
		die( print_r( $_REQUEST ) );

	global $current_user;
	get_currentuserinfo();

	$comment = ( isset( $_POST['comment'] ) ) ? intval( $_POST['comment'] ) : 0;
	$vote = intval( $_POST['vote'] );

	$votesuccess = gad_add_reclink_vote( $_POST['reclink'], $comment, $vote, $current_user->ID, $_SERVER['REMOTE_ADDR'] );

	die( json_encode( array( 'newCount' => $votesuccess ) ) );

}


?>
