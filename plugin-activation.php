<?php

/**
 * @function	reclinks_install()
 *
 * Runs on initial plugin activation. Checks for existence of tables from previous
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

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$reclinks_old_table'" ) === $reclinks_old_table )
		add_action( 'shutdown', 'reclinks_import_old_links');

	if (!get_option('reclinks_plugin_options')) {
		$reclinks_plugin_defaults = array(
					'theme'=>'roadsigns',
					'display'=>'stars',
					'stars-1-value'=>-1,
					'stars-1-text'=>'Off topic or irrelevant',
					'stars-2-value'=>0,
					'stars-2-text'=>'Wasn\'t all that impressed',
					'stars-3-value'=>1,
					'stars-3-text'=>'Liked it',
					'stars-4-value'=>2,
					'stars-4-text'=>'Very interesting',
					'stars-5-value'=>3,
					'stars-5-text'=>'A+++++',
					'commenting-enabled'=>true,
					'tagging-enabled'=>true);
		
		update_option('reclinks_plugin_options',$reclinks_plugin_defaults);
	}
}

/**
 * @function	reclinks_import_old_links
 *
 * Run on cron, so as not to block activation...
 *
 */
function reclinks_import_old_links() {
	error_log( 'Importing old posts...' );
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
			'post_date_gmt'	=> $old_link->link_addtime,
			'post_status'	=> 'publish'
		) );

		update_post_meta( $new_post, '_href', $old_link->link_href );

		$old_comments = $wpdb->get_results( "SELECT * FROM $reclinks_old_votes_table WHERE link_id = {$old_link->id}" );
		
		if ( $old_comments ) {
			$vote_tally = 0;
			foreach ( $old_comments as $old_comment ) {
				$vote_tally += $old_comment->vote;
				$vote_comment = wp_insert_comment( array(
					'comment_post_ID' 	=> $new_post,
					'comment_type'		=> 'reclinks_vote',
					'comment_author_IP'	=> $old_comment->voter_ip,
					'comment_date'		=> $old_comment->vote_time,
					'user_id'			=> $old_comment->voter_id,
					'comment_content'	=> md5( $new_post . 'reclinks_vote' . $old_comment->vote_time ), 
											// necessary to prevent "duplicate/empty comment" warnings
					'comment_karma'		=> $old_comment->vote,
					'comment_approved'	=> 1
				) );
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
