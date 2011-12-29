<?php 
/*
Plugin Name: Recommended Links
Plugin URI: http://goldenapplesdesign.com/projects/recommended-links-plugin/
Description: A "reddit clone" that runs in Wordpress.
Author: Nathaniel Taintor
Version: 0.2.1
Author URI: http://goldenapplesdesign.com
*/

// Variable definitions first
$reclinks_theme_options = get_option('reclinks_plugin_options');

global $wpdb;

// This is a hack to get around symlink resolving issues, see 
// http://wordpress.stackexchange.com/questions/15202/plugins-in-symlinked-directories
// Hopefully a better solution will be found in future versions of WordPress.
if ( isset( $plugin ) )
	define( 'RECLINKS_DIRECTORY', plugin_dir_url( $plugin ) );
else define( 'RECLINKS_DIRECTORY', RECLINKS_DIRECTORY );

// Required files
require_once( plugin_dir_path( __FILE__ ) . '/user-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/widgets.php' );
require_once( plugin_dir_path( __FILE__ ) . '/ajax-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/display-filters.php' );

// Register reclinks_votes table so it can be used with $wpdb class
global $wpdb;
$wpdb->reclinkvotes = $wpdb->prefix . 'reclinks_votes';

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
        'has_archive' => 'reclinks',
        'query_var' => 'link',
        'can_export' => true,
        'rewrite' => array( 
            'slug' => 'link'
        )
    );

    register_post_type( 'reclink', $args );


}

// Activation / deactivation
include_once( plugin_dir_path( __FILE__ ) . 'plugin-activation.php' );
register_activation_hook( __FILE__, 'reclinks_install' );
register_deactivation_hook( __FILE__, 'reclinks_uninstall');

// Enqueue javascript and CSS on front end
add_action( 'wp_enqueue_scripts', 'gad_reclinks_enqueues' );

function gad_reclinks_enqueues() {
	if ( is_admin() )
		return;
	wp_enqueue_script( 'reclinks-scripts', RECLINKS_DIRECTORY . 'js/reclinks-scripts.js', array( 'jquery' ), false, true );
	wp_localize_script( 'reclinks-scripts', 'reclinks', 
		array( 
			'ajaxUrl' => admin_url( 'admin-ajax.php' ), 
			'loginUrl' => wp_login_url( ( !empty( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ) .$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ) 
		) 
	);
	wp_enqueue_style( 'reclinks', RECLINKS_DIRECTORY . 'reclinks-styles.css' );
}

/**
 * @function	gad_add_reclink
 *
 * Used to insert a new post of type "reclink". Can be called through
 * AJAX or directly on init with POST data; has to be able to handle both.
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
 * @return	int		New vote total, used for updating page when called through Ajax	
 */
function gad_add_reclink_vote( $reclink, $comment = 0, $vote, $user, $userip ) {
	global $wpdb;

	if ( isset( $user ) ) {
		$alreadyvoted = $wpdb->get_row( "
			SELECT * FROM {$wpdb->reclinkvotes}
			WHERE post_id = $reclink
			AND comment_id = $comment
			AND voter_id = $user", OBJECT );
	} else {
		$alreadyvoted = $wpdb->get_row( "
			SELECT * FROM {$wpdb->reclinkvotes}
			WHERE post_id = $reclink
			AND comment_id = $comment
			AND voter_ip = $user_ip", OBJECT );
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
			'voter_id'		=> $user,
			'voter_ip'		=> $userip
		) );
	}

	// Update vote count (count everything over again)
	$new_vote_total = $wpdb->get_var("
		SELECT SUM(vote) FROM {$wpdb->reclinkvotes}
		WHERE post_id = $reclink AND comment_id = $comment");

	if ( 0 === $comment) {
		// vote on post, update post meta value
		update_post_meta( $reclink, '_vote_score', $new_vote_total+1 );
	} else {
		// vote on comment, update comment karma value
		wp_update_comment( array(
			'comment_ID' => $comment,
			'comment_karma' => $new_vote_total+1
		) );	
	}

	return $new_vote_total + 1;
}

