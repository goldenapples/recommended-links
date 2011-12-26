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

	foreach ( $wp_roles->get_names() as $rolename => $displayname )
		foreach ( $custom_capabilities as $cap )
			$wp_roles->add_cap( $rolename, $cap );

}
