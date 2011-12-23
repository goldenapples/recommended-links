<?php

$custom_capabilities = array(
	'add_reclink',
	'comment_reclink',
	'vote_reclink' );

global $wp_roles;

foreach ( $wp_roles->get_roles as $role => $role_display )
	foreach ( $custom_capabilities as $cap )
		$wp_roles->add_cap( $role, $cap );
