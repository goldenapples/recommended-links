<?php
/*
Plugin Name: Recommended Links
Plugin URI: http://goldenapplesdesign.com/projects/recommended-links-plugin/
Description: A "reddit clone" that runs in Wordpress.
Author: Nathaniel Taintor
Version: 0.4.2
Author URI: http://goldenapplesdesign.com
*/

// Variable definitions first
$reclinks_options = get_option('reclinks_plugin_options');

// Register reclinks_votes table so it can be used with $wpdb class
global $wpdb;
$wpdb->reclinkvotes = $wpdb->prefix . 'reclinks_votes';

// This is a hack to get around symlink resolving issues, see
// http://wordpress.stackexchange.com/questions/15202/plugins-in-symlinked-directories
// Hopefully a better solution will be found in future versions of WordPress.
if ( isset( $plugin ) )
	define( 'RECLINKS_DIRECTORY', plugin_dir_url( $plugin ) );
else define( 'RECLINKS_DIRECTORY', plugin_dir_url( __FILE__ ) );

// Required files
require_once( plugin_dir_path( __FILE__ ) . '/admin-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/user-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/widgets.php' );
require_once( plugin_dir_path( __FILE__ ) . '/ajax-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/display-filters.php' );
require_once( plugin_dir_path( __FILE__ ) . '/display-shortcodes.php' );

// Register custom post type required for this work
add_action( 'init', 'register_cpt_reclink' );

function register_cpt_reclink() {

	$plugin_settings = get_option( 'reclinks_plugin_options' );

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

	$taxonomies = is_array( $plugin_settings['tax'] ) ? array_keys( $plugin_settings['tax'] ) : array();

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array( 'title', 'editor', 'excerpt', 'author', 'custom-fields', 'comments' ),
        'taxonomies' => $taxonomies,
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
		),
		'register_meta_box_cb' => 'reclinks_edit_screen_metaboxes'
    );

    register_post_type( 'reclink', $args );

}

// Activation / deactivation
add_action( 'admin_init', 'gad_check_db_tables' );

function gad_check_db_tables() {
	$v = get_option( 'reclinks_db_version' );
	if ( !$v || $v < 5 ) {
			include_once( plugin_dir_path( __FILE__ ) . 'plugin-activation.php' );
			reclinks_db_option_upgrade( $v );
	}
}


// Enqueue javascript and CSS on front end
add_action( 'wp_enqueue_scripts', 'gad_reclinks_enqueues' );

function gad_reclinks_enqueues() {
	if ( is_admin() )
		return;
	wp_enqueue_script( 'reclinks-scripts', RECLINKS_DIRECTORY . 'js/reclinks-scripts.js', array( 'jquery' ), false, true );
	wp_localize_script( 'reclinks-scripts', 'reclinks',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'loginUrl' => wp_login_url( ( !empty( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ) .$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ),
			'messages_linkSubmitted'	=> __( 'Link submitted successfully.', 'gad_reclinks' ),
			'messages_error404' 		=> __( 'The submitted link could not be found.', 'gad_reclinks' ),
			'messages_errorNoTitle' 	=> __( 'The document does not appear to have a title.', 'gad_reclinks' )
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
	global $current_user;
	get_currentuserinfo();

	$plugin_settings = get_option( 'reclinks_plugin_options' );

	// Check to see that user is authorized to add link
	if ( !$plugin_settings['allow-unregistered-post'] && !current_user_can( 'add_reclink' ) )
		{
			error_log ( 'unauthorixed user tried to post link: ' . print_r( $reclink, true ) );
			return false;
		}

	// Check to see if that link already exists
	$link_exists = get_posts( array(
		'post_type' => 'reclink',
		'meta_name' => '_href',
		'meta_value' => $reclink['reclink_url']
	) );
	if ( $link_exists )
		return false;


	function empty_taxonomy( $t ) {
		if ( is_array( $t ) ) {
			$t = array_filter( $t );
			return ( !empty( $t ) );
		}
		else return ( $t );
	}

	$author = ( is_user_logged_in() ) ? $current_user->ID : $plugin_settings['anonymous-links-author'];

	$link_ID = wp_insert_post( array(
		'post_type' 	=> 'reclink',
		'post_author' 	=> $author,
		'post_title' 	=> $reclink['reclink_title'],
		'post_content'	=> $reclink['reclink_description'],
		'post_status'	=> 'publish'
	) );

	// Set any taxonomy terms that were selected
	if ( isset( $reclink['reclink_taxes'] ) )
		foreach ( $reclink['reclink_taxes'] as $tax => $terms ) {
			$terms_array = array_map( 'intval', (array)$terms );
			$test = wp_set_object_terms( $link_ID, $terms_array, $tax );
//			error_log( 'Setting terms on '.$link_ID.': '.print_r( $test ) );
		}

	update_post_meta( $link_ID, '_href', $reclink['reclink_url'] );

	gad_add_reclink_vote( $link_ID, 0, 1, $current_user->ID, $_SERVER['REMOTE_ADDR'] );

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
			AND voter_ip = $userip", OBJECT );
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
		update_post_meta( $reclink, '_vote_score', $new_vote_total );
	} else {
		// vote on comment, update comment karma value
		wp_update_comment( array(
			'comment_ID' => $comment,
			'comment_karma' => $new_vote_total
		) );
	}

	do_action( 'reclink_add_vote', $reclink, $comment, $vote );

	return $new_vote_total;
}

