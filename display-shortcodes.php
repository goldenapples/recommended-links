<?php

add_shortcode( 'reclink_addform', 'output_addlink_form' );

function output_addlink_form( $echo = false ) {
	ob_start();
?>
	<form class="reclinks_addlink" action="<?php echo add_query_arg( 'action', 'reclink-add' ); ?>" method="POST">
		<label for="reclink_URL"><?php _e('Link URL', 'gad_reclinks'); ?></label>
		<input type="text" name="reclink_URL" id="reclink_URL" />
		<label for="reclink_title"><?php _e('Link Title', 'gad_reclinks'); ?></label>
		<input type="text" name="reclink_title" id="reclink_title" />
		<label for="reclink_description"><?php _e('Link Description', 'gad_reclinks'); ?></label>
		<textarea id="reclink_description" name="reclink_description" rows="10" cols="30"></textarea>
		<button type="submit" id="reclink_submit"><?php _e( 'Submit Link', 'gad_reclinks' ); ?></button>
	</form>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	if ( $echo === true ) echo $output; else return $output;
}


?>
