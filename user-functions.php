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
}
