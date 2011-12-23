<?php 
/* AJAX responses for adding links, comments, tags, etc */

add_action('init', 'reclink_entries');

/**
 * @function	reclink_entries()
 *
 * Form handler for visitors with Javascript disabled. Should detect the presence of POST
 * data to either add link or vote/comment on link. This function should not be called if
 * Javascript is enabled, because the form handler functions in reclinks-scripts.js
 * should prevent default submission.
 *
 * @uses	gad_add_reclink()
 * @uses	gad_add_reclink_vote()
 */
function reclink_entries() {
	if ( !isset( $_POST['reclink_linkhref'] ) && !isset( $_POST['reclink_promote'] ) )
		return;

	if ( !isset( $_POST['reclink_linkhref'] ) ) {
		$reclink = array(
			'reclink_url' => esc_url( $_POST['reclink_URL'] ),
			'reclink_title' => sanitize_text_field( $_POST['reclink_title'] ),
			'reclink_desciption' => wp_filter_post_kses( $_POST['reclink_description'] )
		);
		gad_add_reclink( $reclink );
	} 

	if ( !isset( $_POST['reclink_promote'] ) ) {		
		global $current_user;
		get_currentuserinfo();
		$votesuccess = reclink_add_vote($_REQUEST['promote-link'],$_REQUEST['rating'],$_REQUEST['comment'],$current_user->ID,$_SERVER['REMOTE_ADDR']);
	}

}


add_action( 'wp_ajax_add_reclink', 'gad_reclinks_ajax_add' );

function gad_reclinks_ajax_add() {
	if ( !current_user_can( 'add_reclink' ) )
		return false;
	$reclink = array(
		'reclink_url' => esc_url( $_POST['reclink_URL'] ),
		'reclink_title' => sanitize_text_field( $_POST['reclink_title'] ),
		'reclink_desciption' => wp_filter_post_kses( $_POST['reclink_description'] )
	);
	gad_add_reclink( $reclink );
	die( 0 );

}


?>
