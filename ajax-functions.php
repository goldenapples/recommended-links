<?php 
/* AJAX responses for adding links, comments, tags, etc */

add_action('init', 'reclink_entries');

function reclink_entries() {
	if ($_POST['reclink_linkhref']) {
		global $current_user;
		get_currentuserinfo();
		list($linkid,$error,$success) = reclink_add_reclink($_POST['reclink_linktitle'],$_POST['reclink_linkhref'],$_POST['reclink_linkdescription'],$current_user->ID,$_SERVER['REMOTE_ADDR']);
		if ($success) echo '<div class="reclinks_response success">Thank you. Your link has been successfully added as #'.$linkid.'.</span>';
		else echo $error;
	} elseif ($_REQUEST['promote-link']) {
		global $current_user;
		get_currentuserinfo();
		$votesuccess = reclink_add_vote($_REQUEST['promote-link'],$_REQUEST['rating'],$_REQUEST['comment'],$current_user->ID,$_SERVER['REMOTE_ADDR']);
	}
}

?>