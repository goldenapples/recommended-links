<?php 

function reclinks_plugin_settings() {
	$reclinks_plugin_options = get_option('reclinks_plugin_options');
	?><div class="wrap">
    <div id="icon-options-general" class="icon32"></div><h2><?php _e('Recommended Links Plugin General Options','reclinks_plugin'); ?></h2>
    <?php if ($_POST['display']) {
		//update options
		foreach ($_POST as $key=>$value) {
			$reclinks_plugin_options[$key] = $value;
		}
		if (!$_POST['commenting-enabled']) $reclinks_plugin_options['commenting-enabled']=false;
		if (!$_POST['tagging-enabled']) $reclinks_plugin_options['tagging-enabled']=false;
		update_option('reclinks_plugin_options',$reclinks_plugin_options);
		$updated_text='<div class="updated"><p>Updated options saved.</p></div>';
	}
    echo $updated_text; ?>
    <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<h3>Theme and Display</h3>
    <div id="post-body-content"><p>Here, you can set the general options for display of the recommended links.</p>
    <table class="form-table"><tbody>
    <tr><th width="20%" valign="top"><label for="display">Ratings format: </label></th>
        	<td valign="top"><p><input id="display" type="radio" name="display" value="5stars" <?php
			if ($reclinks_plugin_options['display']=='5stars') echo 'checked="checked"'; ?>><strong>5 Star Rating System</strong><br />
			As used on Netflix, etc. (You will have the option to customize the values and text descriptions of each of the various ratings.)</p>
        	<p><input type="radio" name="display" value="updown" <?php
			if ($reclinks_plugin_options['display']=='updown') echo 'checked="checked"'; ?>><strong>Up/Down Voting</strong><br />
			As used on Reddit, Digg, etc.</p></td></tr>
	<tr><th width="20%"><label for="commenting-enabled">Enable commenting: </label></th>
      		<td valign="top"><input type="checkbox" name="commenting-enabled" <?php 
			if ($reclinks_plugin_options['commenting-enabled']) echo 'checked="checked"'; ?>></td></tr>
    <tr><th width="20%"><label for="tagging-enabled">Enable tagging: </label></th>
      		<td valign="top"><input type="checkbox" name="tagging-enabled" <?php 
			if ($reclinks_plugin_options['tagging-enabled']) echo 'checked="checked"'; ?>></td></tr>
	</tbody></table>
    <h3>5 Star Rating System - Rating Settings</h3>
    <table class="widefat"><tbody>
    <thead><tr><th>Rating</th><th>Rating Value</th><th>Rating Description</th></tr></thead>
    <?php for ($x=1;$x<=5;$x++) {
		echo '<tr><td valign="top" width="20%"><img src="'.WP_RECLINKS_PLUGIN_DIR.'/themes/'.$reclinks_plugin_options['theme'].'/images/'.$x.'-stars.png" alt="'.$x.' STARS"></td><td><input type="text" name="stars-'.$x.'-value" value="'.$reclinks_plugin_options['stars-'.$x.'-value'].'" size="20"></td><td><input type="text" name="stars-'.$x.'-text" value="'.stripslashes($reclinks_plugin_options['stars-'.$x.'-text']).'" size="80"></td></tr>';
	} ?>
	</tbody></table>
    <input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options'); ?>" id="submitbutton" style="margin-top:12px; float: right;" />
    </div></form>
	</div>
        <?php 
}

function reclinks_edit_links() {
	echo '<div class="wrap">';
	if ($_GET['action']) {
		switch ($_GET['action']) :
			case 'edit':
			global $wpdb;
			// first, check for submitted form data
				if ($_POST['link_title'] && $_POST['link_description']) {
					$wpdb->update( WP_RECLINKS_TABLE, array('link_title'=>$_POST['link_title'],
							'link_description'=>$_POST['link_description']),
							array('id'=>$_GET['link']));
					$update_message = '<div class="updated"><strong>Link updated.</strong></div>';
				}
			
			// edit an individual link
				
				$sql = "SELECT * FROM ".WP_RECLINKS_TABLE." WHERE id=".$_GET['link'];
				$link = $wpdb->get_row($wpdb->prepare($sql));
				echo '<div id="icon-edit" class="icon32"></div><h2>Edit Link: '.apply_filters('the_title',stripslashes($link->link_title)).'</h2>';
                echo $update_message;  ?>
                <form id="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                
                <div id="poststuff" class="metabox-holder has-right-sidebar">
                
                <div id="side-info-column" class="inner-sidebar">
                <div id="side-sortables" class="meta-box-sortables ui-sortable">
				
                <div id="submitdiv" class="postbox"><div class="handlediv" title="Click to toggle"><br /></div><h3 class="hndle"><span>Edit Link</span></h3>
                	<div class="inside">
                    	<div class="submitbox" id="editlink">
                        <p style="margin: 12px 6px;">This link was added (or last edited) at <?php echo $link->link_addtime; ?> by <strong><?php echo get_userdata($link->link_addedby)->display_name; ?></strong>.</p></div>
                    </div>
                    <div id="major-publishing-actions"><div id="delete-action"><a class="submitdelete deletion" href="admin.php?page=reclinks_edit_links&action=delete&link=<?php echo $link->id; ?>">Delete this link</a></div>
                    <div id="publishing-action"><input id="publish" class="button-primary" type="Submit" value="Update" /></div>
                    <div class="clear"></div>
                </div></div>
                
                </div></div> <!-- / side-info-column -->
                
                <div id="post-body"><div id="post-body-content">
                <div id="titlediv">
                <input id="title" name="link_title" type="text" size="30" value="<?php echo apply_filters('the_title',stripslashes($link->link_title)); ?>" />
                <div id="edit-slug-box"><span id="sample-permalink"><?php echo $link->link_href; ?></span>
                
                <a class="button-secondary" href="<?php echo $link->link_href; ?>" target="_blank" title="Visit Link">Visit Link</a></div></div>
                
                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                
                <div id="linkdescription" class="postbox"><h3 class="hndle">Link Description</h3>
                	<div class="inside"><textarea id="excerpt" name="link_description" cols="40" rows="8" style="height:200px;"><?php echo stripslashes($link->link_description); ?></textarea></div>
                    
                
                </div>
                
                <div id="commenthistory" class="postbox"><h3 class="hndle">Vote History</h3>
                	<div class="inside">
                    <table class="widefat"><thead><tr><th>Date</th><th>User</th><th>Vote</th><th>Comment</th><th>Action</th></tr></thead><tbody>
						<?php $linkvotes = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WP_RECLINKS_VOTES_TABLE."` WHERE `link_id` = '".$link->id."' ORDER BY vote_time ASC") );
						foreach ($linkvotes as $linkvote) { ?>
                        <tr><td><?php echo $linkvote->vote_time; ?></td><td><?php if ($linkvote->voter_id) {
									echo get_userdata($linkvote->voter_id)->display_name;
								} else echo "Anonymous (".$linkvote->voter_ip.")"; ?></td><td><?php echo $linkvote->vote; ?></td>
                                <td><?php echo $linkvote->vote_text; ?></td><td></td></tr>
                                <?php } ?></tbody></table>
                    </div>
                </div>
                </div></div> <!-- / post-body-content -->
        
				</div></div></form>
				<?php 
				

				break;
			case 'comments':
				//view link with all comments
				global $wpdb;
				$sql = "SELECT * FROM ".WP_RECLINKS_TABLE." WHERE id=".$_GET['link'];
				$link = $wpdb->get_row($wpdb->prepare($sql));
				echo '<div id="icon-edit" class="icon32"></div><h2>Edit Votes / Comments: '.apply_filters('the_title',stripslashes($link->link_title)).'</h2>'; ?>
                <form id="post" action="admin.php?page=reclinks_edit_links&action=edit-save">
                <div id="poststuff" class="metabox-holder has-right-sidebar"><div id="post-body"><div id="post-body-content">
                <div id="titlediv">
                <input id="title" name="link_title" type="text" size="30" value="<?php echo apply_filters('the_title',stripslashes($link->link_title)); ?>" />
                <div id="edit-slug-box"><span id="sample-permalink"><?php echo $link->link_href; ?></span>
                
                <a class="button-secondary" href="<?php echo $link->link_href; ?>" target="_blank" title="Visit Link">Visit Link</a></div></div>
                <div id="postdivrich" class="postarea">
                <div id="linkdescription" class="postbox"><h3 class="hndle">Link Description</h3>
                	<div class="inside"><p><?php echo stripslashes($link->link_description); ?></p></div>
                    <table id="post-status-info"><tr><td align="right">Added by <?php echo get_userdata($link->link_addedby)->display_name.' on '.$link->link_addtime; ?></td></tr></table></div>
                    
                
                </div><div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div id="commenthistory" class="postbox"><h3 class="hndle">Vote History</h3>
                	<div class="inside">
                    <table class="widefat"><thead><tr><th>Date</th><th>User</th><th>Vote</th><th>Comment</th><th>Action</th></tr></thead><tbody>
						<?php $linkvotes = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WP_RECLINKS_VOTES_TABLE."` WHERE `link_id` = '".$link->id."' ORDER BY vote_time ASC") );
						foreach ($linkvotes as $linkvote) { ?>
                        <tr><td><?php echo $linkvote->vote_time; ?></td><td><?php if ($linkvote->voter_id) {
									echo get_userdata($linkvote->voter_id)->display_name;
								} else echo "Anonymous (".$linkvote->voter_ip.")"; ?></td><td><?php echo $linkvote->vote; ?></td>
                                <td><?php echo $linkvote->vote_text; ?></td><td></td></tr>
                                <?php } ?></tbody></table>
                        
							
                    
                    </div>
                </div>
				
				</div></div></div></div></div></form>
				<?php 
				
				
				
				break;
			case 'delete':
				//delete a link
				if ($_REQUEST['confirm']) {
					global $wpdb;
					$wpdb->query("DELETE FROM ".WP_RECLINKS_TABLE." WHERE id=".$_GET['link']);
					$wpdb->query("DELETE FROM ".WP_RECLINKS_VOTES_TABLE." WHERE link_id=".$_GET['link']);
					die('Link #'.$_GET['link'].' has been deleted.');
				} else { 
					global $wpdb;
					$sql = "SELECT * FROM ".WP_RECLINKS_TABLE." WHERE id=".$_GET['link'];
					$link = $wpdb->get_row($wpdb->prepare($sql));
					echo '<div class="wrap">';
					echo '<div id="icon-edit" class="icon32"></div><h2>Really Delete Link: '.apply_filters('the_title',stripslashes($link->link_title)).'?</h2>'; ?>
					<div class="post-body-content"><form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                    <input type="hidden" name="confirm" value="true" />
                    <p>Are you sure you want to delete the link <strong><?php echo apply_filters('the_title',stripslashes($link->link_title)); ?></strong>?</p>
                    <p>All ratings and comments associated with this link will be deleted permanently.</p>
                    <input type="submit" class="button-primary" value="Confirm deletion" /><a class="button-secondary" href="admin.php?page=reclinks_edit_links&action=edit&link=<?php echo $_GET['link']; ?>">Cancel</a>
                    </form></div></div><?php
				break;
				}
		endswitch;
	} else {
	echo '<div id="icon-edit" class="icon32"></div><h2>'.__('Manage / Edit Recommended Links','reclinks_plugin').'</h2>';
	}

}

?>
