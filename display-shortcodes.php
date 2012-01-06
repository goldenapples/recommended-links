<?php

add_shortcode( 'reclink_addform', 'output_addlink_form' );

function output_addlink_form( $echo = false ) {
	$plugin_settings = get_option( 'reclinks_plugin_options' );
	ob_start();
?>
	<form class="reclinks_addlink" action="<?php echo add_query_arg( 'action', 'reclink-add' ); ?>" method="POST">
		<label for="reclink_URL"><?php _e('Link URL', 'gad_reclinks'); ?></label>
		<input type="text" name="reclink_URL" id="reclink_URL" />
		<label for="reclink_title"><?php _e('Link Title', 'gad_reclinks'); ?></label>
		<input type="text" name="reclink_title" id="reclink_title" />
		<label for="reclink_description"><?php _e('Link Description', 'gad_reclinks'); ?></label>
		<textarea id="reclink_description" name="reclink_description" rows="10" cols="30" ></textarea>
<?php if ( isset( $plugin_settings['tax'] ) && is_array( $plugin_settings['tax'] ) ) {
	foreach ( $plugin_settings['tax'] as $tax => $on ) {
		$t = get_taxonomy( $tax );
		echo '<p><label for="reclink_taxes['.$tax.']">'.$t->labels->name.'</label>';
		wp_dropdown_categories( 
			array( 
				'taxonomy' => $tax,
				'id' => 'tax-select-'.$tax,
				'name' => 'reclink_taxes['.$tax.']',
				'show_option_none' => sprintf( __( 'No %s', 'gad_reclinks' ), $t->labels->singular_name ),
			    'hide_empty' => 0
			)
		);	
		echo '</p>';
	}
} ?>
		<p><button type="submit" id="reclink_submit"><?php _e( 'Submit Link', 'gad_reclinks' ); ?></button></p>
	</form>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	if ( $echo === true ) echo $output; else return $output;
}


