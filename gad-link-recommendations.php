<?php 
/*
Plugin Name: Recommended Links
Plugin URI: http://goldenapplesdesign.com/projects/recommended-links-plugin/
Description: A "reddit clone" that runs in Wordpress.
Author: Nathaniel Taintor
Version: 0.2
Author URI: http://goldenapplesdesign.com
*/

// Variable definitions first
$reclinks_theme_options = get_option('reclinks_plugin_options');

global $wpdb;


// Constants, should be useful
//define("WP_RECLINKS_PLUGIN_DIR", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )));
//define("WP_RECLINKS_PLUGIN_PATH", path_join(ABSPATH.'wp-content/plugins', basename( dirname( __FILE__ ) )));
//define("WP_RECLINKS_THEME_DIR", WP_RECLINKS_PLUGIN_DIR.'/themes/'.$reclinks_theme_options['theme']);
//define("WP_RECLINKS_THEME_PATH", WP_RECLINKS_PLUGIN_PATH.'/themes/'.$reclinks_theme_options['theme']);

// Required files
require_once( plugin_dir_path( __FILE__ ) . '/user-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/widgets.php' );
require_once( plugin_dir_path( __FILE__ ) . '/ajax-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/display-filters.php' );


// Register custom post type required for this work
add_action( 'init', 'register_cpt_reclink' );

function register_cpt_reclink() {

	// Code generated by: http://themergency.com/generators/wordpress-custom-post-types/
    $labels = array( 
        'name' => _x( 'Recommended Links', 'reclink' ),
        'singular_name' => _x( 'Recommended Link', 'reclink' ),
        'add_new' => _x( 'Add New', 'reclink' ),
        'add_new_item' => _x( 'Add New Recommended Link', 'reclink' ),
        'edit_item' => _x( 'Edit Recommended Link', 'reclink' ),
        'new_item' => _x( 'New Recommended Link', 'reclink' ),
        'view_item' => _x( 'View Recommended Link', 'reclink' ),
        'search_items' => _x( 'Search Recommended Links', 'reclink' ),
        'not_found' => _x( 'No recommended links found', 'reclink' ),
        'not_found_in_trash' => _x( 'No recommended links found in Trash', 'reclink' ),
        'parent_item_colon' => _x( 'Parent Recommended Link:', 'reclink' ),
        'menu_name' => _x( 'RecLinks', 'reclink' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array( 'title', 'editor', 'excerpt', 'author', 'custom-fields', 'comments' ),
        'taxonomies' => array( 'category' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
		'menu_position' => 40,
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => 'link',
        'can_export' => true,
        'rewrite' => array( 
            'slug' => 'link'
        )
    );

    register_post_type( 'reclink', $args );

	// register reclinks_votes table so it can be used with $wpdb class
	global $wpdb;
	$wpdb->reclinkvotes = $wpdb->prefix . 'reclinks_votes';

}

// Activation / deactivation
include_once( plugin_dir_path( __FILE__ ) . 'plugin-activation.php' );
register_activation_hook( __FILE__, 'reclinks_install' );
register_deactivation_hook( __FILE__, 'reclinks_uninstall');


add_action('admin_menu','reclinks_admin_pages');

function reclinks_admin_pages() {
	include( plugin_dir_path( __FILE__ ) . '/admin-functions.php');
	add_menu_page( 'Recommended Links Plugin Settings', 'RecLinks',
		'activate_plugins', 'reclinks_plugin_settings', 
		'reclinks_plugin_settings', plugin_dir_url( __FILE__ ) . '/images/icon16.png' );
	add_submenu_page( 'reclinks_plugin_settings', 'Recommended Links Plugin Settings', 'Plugin Settings',
		'activate_plugins', 'reclinks_plugin_settings', 'reclinks_plugin_settings' );
}


function reclink_collect_votes($reclink) {
	if ( !$reclink = absint($reclink) ) return false;
	$numericvote = 1;
	$votetext = '';
	$count = 0;
	global $wpdb;
	$table_name = $wpdb->prefix.'linkvotes';
	$linkvotes = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".$table_name."` WHERE `link_id` = '".$reclink."'") );
	foreach ($linkvotes as $linkvote) {
		$numericvote += $linkvote->vote;
		if ($linkvote->vote_text) {
			$count++;
			$alt = ($count % 2) ? ' alt' : '';
			$votetext .= '<li id="vote-'.$linkvote->vote.'" class="vote'.$alt.'">';
			$votetext .= '<span class="vote">'.$linkvote->vote.'</span>'.$linkvote->vote_text.'<span class="voter">';
			if ($voter = get_userdata($linkvote->voter_id)) $votetext .= $voter->display_name;
			else $votetext .= $linkvote->voter_ip;
			$votetext .= '</span></li>';
		}
	}
	return $votetext;
}
/**
 * @function	gad_add_reclink
 *
 * Used to insert a new post of type "reclink". Can be called through
 * AJAX or directly on init with POST data; has to handle both.
 *
 * @param array 	Form data submitted, of the format: array( 
 * 						'reclink_title' => $reclink_title,
 * 						'reclink_url'	=> $reclink_url,
 * 						'reclink_description' 	=> $reclink_description )
 *
 * @return 	False on error or exception, post ID of the new reclink if successful
 */
function gad_add_reclink( $reclink ) {
	// Check to see that user is authorized to add link
	if ( !current_user_can( 'add_reclink' ) )
		return false;

	// Check to see if that link already exists
	$link_exists = get_posts( array(
		'post_type' => 'reclink',
		'meta_name' => '_href',
		'meta_value' => $reclink['reclink_url']
	) );
	if ( $link_exists )
		return false;
	
	$link_ID = wp_insert_post( array(
		'post_type' 	=> 'reclink',
		'post_author' 	=> $current_user->user_id,
		'post_title' 	=> $reclink['reclink_title'],
		'post_content'	=> $reclink['reclink_description'],
		'post_status'	=> 'publish'
	) );
	update_post_meta( $link_ID, '_href', $reclink['reclink_url'] );
	update_post_meta( $link_ID, '_vote_score', 1 );

	return $link_ID;
}

/**
 * @function	gad_add_reclink_vote
 *
 * Used to record a vote or comment on any recommended link
 *
 * @param	int 			ID or object of link to vote / comment on
 * @param	int				comment ID (or zero for vote on link itself)
 * @param	int				Vote value (acceptable range of values is set in plugin settings)
 * @param	int				User ID or WP_User object
 * @param	string			Current user's IP adddress
 *
 * @return	TODO: don't know yet
 */
function gad_add_reclink_vote( $reclink, $comment = 0, $vote, $user, $userip ) {
	global $wpdb;

	if ( isset( $user ) ) {
		$alreadyvoted = $wpdb->get_row( "
			SELECT * FROM {$wpdb->reclinkvotes}
			WHERE post_id = $reclink
			AND comment_id = $comment
			AND user_id = $user", OBJECT );
	} else {
		$alreadyvoted = $wpdb->get_row( "
			SELECT * FROM {$wpdb->reclinkvotes}
			WHERE post_id = $reclink
			AND comment_id = $comment
			AND user_ip = $user_ip", OBJECT );
	}
			
	if ($alreadyvoted) {
		$wpdb->update( $wpdb->reclinkvotes,
			array( 'vote' => $vote ),
			array( 'id' => $alreadyvoted->id )
		);
	} else {
		$wpdb->insert( $wpdb->reclinkvotes,
			array(
			'post_id' 		=> $reclink,
			'comment_id'	=> $comment,
			'vote'			=> $vote,
			'user_id'		=> $user,
			'user_ip'		=> $userip
		) );
	}

	// Update vote count (count everything over again)
	$new_vote_total = $wpdb->get_var("
		SELECT SUM(vote) FROM {$wpdb->reclinkvotes}
		WHERE post_id = $reclink AND comment_id = $comment");

	if ( 0 === $comment) {
		// vote on post, update post meta value
		update_post_meta( $reclink, '_vote_score', $new_vote_total );
	} else {
		// vote on comment, update comment karma value
		wp_update_comment( array(
			'comment_ID' => $comment,
		   	'comment_karma' => $new_vote_total
		) );	
	}
}

function reclink_show_top_voted( $number='8', $time='', $tag='' ) {
	$response = '';
	$timestamp = '';
	switch ($time):
		case 'thisweek':
			$timestamp = "AND votes.vote_time > '".date("Y-m-d H:i:s", strtotime('-1 week'))."' ";
			break;
	endswitch;
	global $wpdb;
	$sql = "SELECT links.*,SUM(votes.vote) AS totalvotes,COUNT(votes.vote_text) AS commentcount FROM ".WP_RECLINKS_TABLE." AS links, ".WP_RECLINKS_VOTES_TABLE." AS votes WHERE links.id=votes.link_id ".$timestamp."GROUP BY links.id ORDER BY SUM(votes.vote) DESC";
	if ($number > 0) $sql .= " LIMIT ".$number;
	//$tablename =$linkstable;
	$reclinks = $wpdb->get_results($wpdb->prepare($sql));
	//print_r($links);
	if ($reclinks) {
		global $current_user;
		get_currentuserinfo();
		
		include(WP_RECLINKS_THEME_PATH.'/links.php');
	}
	//return $response;
}

function reclink_get_links($args) {
	$defaults = array ('dateadded'=>'','daterated'=>'','show_comments'=>true,'show_votes'=>false,'numberposts'=>5,'paged'=>false);
	$args = wp_parse_args( $args, $defaults );
	
}

function reclink_show_link($link,$showcomments,$showvotes) {
	$votestext = reclink_collect_votes($link->id,$showvotes);
	$response .= '<li id="link-'.$link->id.'" class="reclink"><span class="votestotal">'.$link->totalvotes.' VOTES<br />';
	$response .= '<form><input type="hidden" name="promote-link" value="'.$link->id.'"><input type="image" src="'.get_bloginfo('wpurl') .'/'. PLUGINDIR . '/gad-link-recommendations/images/recommend.png" alt="Recommend this link!"/></form>';
	$response .= '</span><span class="reclink_linktitle"><a target="_blank" href="'.$link->link_href.'">'.apply_filters('the_content',stripslashes($link->link_title)).'</a></span>';
	$response .= '<span class="reclink_linkhref">'.stripslashes($link->link_href).'</span>';
	
	$response .= '<span class="reclink_description">'.apply_filters('the_content',stripslashes($link->link_description)).'</span><span class="reclink_addedby">Added by ';
		$userinfo = get_userdata($link->link_addedby);
		$response .=  $userinfo->display_name.' on '.$link->link_addtime.'</span>';
	if ($votestext[1] && $showcomments) $response .= '<ul class="reclinks_votes">'.$votestext[1].'</ul>';
	$response .= '</li>';
	return $response;
}



