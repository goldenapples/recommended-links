<?php

/**
 * An all-purpose upgrade/install handler for this plugin.
 *
 * Handles creating new options when upgrading from one version of the plugin to the next.
 *
 * @uses	reclinks_install	If $from is false, calls reclinks_install() to create initial
 * 								database tables
 *
 * @param	int|false			the db version being upgraded from
 * 								// ie get_option( 'reclinks_db_version' )
 *
 **/
function reclinks_db_option_upgrade( $from ) {

	$current_version = 5;

	if ( $from === $current_version ) return;

	if ( $from === false )
		reclinks_install();


	$old_settings = ( $from ) ? get_option( 'reclinks_plugin_options' ) : array();

	/* DB version 5, reflects plugin version 0.4early. Introduces bookmarklet settings options.
	/* DB version 4, reflects plugin version 0.4early. Includes 'vote-on-comments' settings. */
	/* DB version 3, reflects plugin version 0.3. Includes 'tax' string. */
	$reclinks_plugin_defaults = array(
		'vote-values' => array(
			'minus' => array( 'value' => -1, 'text' => '-' ),
			'plus' => array( 'value' => 1, 'text' => '+' )
		),
		'page_for_reclinks' => false,
		'sort_order' => 'current',
		'allow-unregistered-vote' => false,
		'allow-unregistered-post' => false,
		'vote-on-comments' => true,
		'tax' => array(),
		'bookmarklet_text' => sprintf( __( 'Post to %s', 'reclinks' ), get_option( 'blogname' ) ),
		'bookmarklet_class' => 'reclinks-bookmarklet',
		'bookmarklet_header' => ''
	);

	$options_to_set = wp_parse_args( $old_settings, $reclinks_plugin_defaults );

	update_option( 'reclinks_plugin_options', $options_to_set );
	update_option( 'reclinks_db_version', 5 );

}


/**
 * Should run on initial plugin activation. Checks for existence of tables from previous
 * version of plugin, and if present, imports all old link and vote entries into new 
 * custom post type and comments. Also should set default options for the plugin, if 
 * they have not been set.
 *
 */
function reclinks_install() {
	global $wp_version;

	if (version_compare($wp_version, "3.0", "<")) {
		deactivate_plugins(basename(__FILE__));
		wp_die("This plugin requires Wordpress version 3.0 or higher.");
	}

	global $wpdb;
	$reclinks_old_table = $wpdb->prefix . "reclinks";
	$reclinks_new_table = $wpdb->prefix . "reclinks_votes";

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$reclinks_old_table'" ) === $reclinks_old_table )
		add_action( 'shutdown', 'reclinks_import_old_links');

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$reclinks_new_table'") !== $reclinks_new_table ) {
		$sql = "CREATE TABLE $reclinks_new_table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				post_id mediumint(9) NOT NULL,
				comment_id mediumint(9) NOT NULL,
				vote tinyint(1) NOT NULL,
				voter_id mediumint(9) DEFAULT '0' NOT NULL,
				voter_ip varchar(55) NOT NULL,
				vote_time TIMESTAMP NOT NULL,
				UNIQUE KEY id (id)
				);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

}


/**
 * @function	reclinks_import_old_links
 *
 * Run on shutdown hook, so as not to block activation. This means that all links may not show up
 * immediately, but its better than waiting for minutes to be able to activate the plugin.
 *
 */
function reclinks_import_old_links() {
	ignore_user_abort(true);
	set_time_limit(0);

	global $wpdb;

	// Old table values: if these tables exist, should export data from them into wp_posts table
	// and delete old tables to clean up.
	$reclinks_old_table = $wpdb->prefix . "reclinks";
	$reclinks_old_votes_table = $wpdb->prefix . "reclink_votes";

	// Old plugin data exists, export it into custom posts.
	$old_links = $wpdb->get_results( "SELECT * FROM $reclinks_old_table" );

	foreach ( $old_links as $old_link ) {
		$new_post = wp_insert_post( array( 
			'post_type' 	=> 'reclink',
			'post_author' 	=> $old_link->link_addedby,
			'post_title' 	=> $old_link->link_title,
			'post_content'	=> $old_link->link_description,
			'post_date'		=> $old_link->link_addtime,
			'post_date_gmt'	=> $old_link->link_addtime,
			'post_status'	=> 'publish'
		) );

		update_post_meta( $new_post, '_href', $old_link->link_href );

		$old_comments = $wpdb->get_results( "SELECT * FROM $reclinks_old_votes_table WHERE link_id = {$old_link->id}" );
		
		if ( $old_comments ) {
			$vote_tally = 0;
			foreach ( $old_comments as $old_comment ) {
				$vote_tally += $old_comment->vote;
				$wpdb->insert( $wpdb->prefix . 'reclinks_votes', 
					array( 	'post_id' 	=> $new_post,
							'comment_id'=>	0,
							'vote'		=> $old_comment->vote,
							'voter_id'	=> $old_comment->voter_id,
							'voter_ip'	=> $old_comment->voter_ip,
							'vote_time'	=> $old_comment->vote_time )
					);
				if ( !empty( $old_comment->vote_text ) ) {
					wp_insert_comment( array(
						'comment_post_ID'	=> $new_post,
						'comment_author_IP'	=> $old_comment->voter_ip,
						'comment_date'		=> $old_comment->vote_time,
						'user_id'			=> $old_comment->voter_id,
						'comment_content'	=> $old_comment->vote_text
					) );
				}
			}
			update_post_meta( $new_post, '_vote_score', $vote_tally );
		}

	}

	// Delete old tables
	$wpdb->query( "DROP TABLE IF EXISTS $reclinks_old_votes_table" );
	$wpdb->query( "DROP TABLE IF EXISTS $reclinks_old_table;" );

}

/**
 * @function	reclinks_uninstall
 *
 * Function called on deactivating plugin. Should eventually remove all filters, and offer the option 
 * to delete all links of custom post type 'reclink', as well as all comments on them.
 */
function reclinks_uninstall() {
//	wp_die( 'Uninstalling plugin.' );
	// deactivate plugin
	
}	
