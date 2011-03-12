<?php 
/*
Plugin Name: Recommended Links
Plugin URI: http://goldenapplesdesign.com/projects/recommended-links-plugin/
Description: A "reddit clone" that runs in Wordpress.
Author: Nathaniel Taintor
Version: 0.1
Author URI: http://goldenapplesdesign.com
*/

// Variable definitions first

$reclinks_theme_options = get_option('reclinks_plugin_options');

global $wpdb;
define("WP_RECLINKS_TABLE", $wpdb->prefix . "reclinks");
define("WP_RECLINKS_VOTES_TABLE", $wpdb->prefix . "reclink_votes");
define("WP_RECLINKS_PLUGIN_DIR", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )));
define("WP_RECLINKS_PLUGIN_PATH", path_join(ABSPATH.'wp-content/plugins', basename( dirname( __FILE__ ) )));
define("WP_RECLINKS_THEME_DIR", WP_RECLINKS_PLUGIN_DIR.'/themes/'.$reclinks_theme_options['theme']);
define("WP_RECLINKS_THEME_PATH", WP_RECLINKS_PLUGIN_PATH.'/themes/'.$reclinks_theme_options['theme']);

// Required files

require_once(WP_RECLINKS_PLUGIN_PATH.'/ajax-functions.php');


// Activation / deactivation

register_activation_hook(__FILE__,'reclinks_install');

function reclinks_install() {
	global $wp_version;
	if (version_compare($wp_version, "2.9", "<")) {
		deactivate_plugins(basename(__FILE__));
		wp_die("This plugin requires Wordpress version 2.9 or higher.");
	}
	global $wpdb;
	if ($wpdb->get_var("SHOW TABLES LIKE '".WP_RECLINKS_TABLE."'") != WP_RECLINKS_TABLE) {
		$sql .= "CREATE TABLE ".WP_RECLINKS_TABLE." (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				link_href varchar(255) NOT NULL,
				link_title varchar(255) NULL,
				link_description text NOT NULL,
				link_addedby TINYINT(4) NOT NULL,
				link_addedby_ip varchar(55) NOT NULL,
				link_addtime TIMESTAMP NOT NULL,
				UNIQUE KEY id (id)
			);";
		}
	if ($wpdb->get_var("SHOW TABLES LIKE '".WP_RECLINKS_VOTES_TABLE."'") != WP_RECLINKS_VOTES_TABLE) {
		$sql .= "CREATE TABLE ".WP_RECLINKS_VOTES_TABLE." (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				link_id mediumint(9) NOT NULL,
				vote tinyint(1) NOT NULL,
				vote_text text NOT NULL,
				voter_id mediumint(9) DEFAULT '0' NOT NULL,
				voter_ip varchar(55) NOT NULL,
				vote_time TIMESTAMP NOT NULL,
				UNIQUE KEY id (id)
			);";
		}
	if ($sql) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
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


register_deactivation_hook(__FILE__,'reclinks_uninstall');

function reclinks_uninstall() {
	
	// deactivate plugin
	
}	

add_action('admin_menu','reclinks_admin_pages');

function reclinks_admin_pages() {
	include(WP_RECLINKS_PLUGIN_PATH . '/admin-functions.php');
	add_menu_page('Recommended Links Plugin Settings','RecLinks','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings',WP_RECLINKS_PLUGIN_DIR.'/images/icon16.png');
	add_submenu_page('reclinks_plugin_settings','Recommended Links Plugin Settings','Plugin Settings','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings');
	add_submenu_page('reclinks_plugin_settings','Recommended Links - View / Edit Links','Edit Links','activate_plugins','reclinks_edit_links','reclinks_edit_links');
}


add_action('wp_print_styles','reclinks_styles');
add_action('init','reclinks_scripts');

function reclinks_styles() {
	wp_enqueue_style("reclinks-theme-".$reclinks_theme_options['theme'],WP_RECLINKS_THEME_DIR.'/style.css');
	/* elseif (file_exists(get_stylesheet_directory().'/plugins/gad-link-recommendations/gad-link-recommendations.css')){ 
		//Child Theme (or just theme)
		wp_enqueue_style( "gad-link-recommendations", get_stylesheet_directory_uri().'/plugins/gad-link-recommendations/gad-link-recommendations.css' );
	} elseif (file_exists(get_template_directory().'/plugins/gad-link-recommendations/gad-link-recommendations.css')) { 
		//Parent Theme (if parent exists)
		wp_enqueue_style( "gad-link-recommendations", get_template_directory_uri().'/plugins/gad-link-recommendations/gad-link-recommendations.css' );
	} else { 
		//Default file in plugin folder
		wp_enqueue_style( "gad-link-recommendations", WP_RECLINKS_PLUGIN_DIR.'/gad-link-recommendations.css' );
	}	*/
	
	//echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') .'/'. PLUGINDIR . '/gad-link-recommendations/gad-link-recommendations.css" />' . "\n";
}

function reclinks_scripts() {
	wp_enqueue_script('scriptaculous-effects',array('prototype'));
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

function reclink_add_reclink($linktitle,$linkhref,$linkdescription,$userID,$userip) {
	global $wpdb;
	if ($linkid = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".WP_RECLINKS_TABLE." WHERE link_href LIKE '".$linkhref."'"))) {
		return array($linkid,'Sorry, that link already exists.',false); 
	}
	$wpdb->insert(WP_RECLINKS_TABLE,array('link_href'=>$linkhref,'link_title'=>$linktitle,'link_description'=>$linkdescription,'link_addedby'=>$userID,'link_addedby_ip'=>$userip) );
	$linkid = $wpdb->insert_id;
	$wpdb->insert(WP_RECLINKS_VOTES_TABLE,array('link_id'=>$linkid,'vote'=>1,'voter_id'=>$userID,'voter_ip'=>$userip) );
 	return array($linkid,false,true);
}

function reclink_add_vote($linkid,$vote,$votetext,$user,$userip) {
	global $wpdb;
	if ($user) $alreadyvoted = $wpdb->get_var("SELECT `id` FROM ".WP_RECLINKS_VOTES_TABLE." WHERE `link_id` = '".$linkid."' AND `voter_id` = '".$user."'");
	else $alreadyvoted = $wpdb->get_var("SELECT `id` FROM ".WP_RECLINKS_VOTES_TABLE." WHERE `link_id` = '".$linkid."' AND `voter_ip` = '".$userip."'");
	if ($alreadyvoted) return "You cannot vote twice on the same link.";
	$wpdb->insert(WP_RECLINKS_VOTES_TABLE,array('link_id'=>$linkid,'vote'=>$vote,'vote_text'=>$votetext,'voter_id'=>$user,'voter_ip'=>$userip) );
	$voteid = $wpdb->insert_id;
	return $voteid;
}

function reclink_show_top_voted($number='8',$time='',$tag='') {
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

function reclink_addlink_form() {
	echo '<div id="reclink_addform">';
	if (!current_user_can('edit_posts')) {
		echo '<h3>Share your favorite unschooling links!</h3><p>You must be logged into to recommend links. You can register here.</p>';
		wp_register();
		echo '</div>';
		return;
	}
	echo '<form id="reclink_addlink" action="" method="post">';
	echo 'Got a link you want to recommend? Submit it here!';
	echo '<p><label for="reclink_linktitle">Title:</label><input type="text" name="reclink_linktitle"></p>';
	echo '<p><label for="reclink_linkhref">Address:</label><input type="text" name="reclink_linkhref"></p>';
	echo '<p><label for="reclink_linkdescription">Description:</label><textarea name="reclink_linkdescription"></textarea></p>';
	echo '<p><input type="submit" value="Submit link!"></p>';
	echo '</form></div>';
}
	
	
	