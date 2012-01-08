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

/**
 * Display the terms (of any taxonomy) associated with a given link.
 *
 * A wrapper around the_terms, which loops through all taxonomies, displaying
 * All the terms in each category. Accepts the same parameters as the_terms.
 *
 * @param	string	Text to display before the list of terms
 * @param	string	Text to display in between each term
 * @param	string	Text to display after the list of terms
 *
 */
function reclink_terms( $before = '<span class="terms-%s">[', $sep = ', ', $after = ']</span> ' ) {
	global $post;
	$obj = get_post_type_object( $post->post_type );
	$taxes = $obj->taxonomies;
	if ( $taxes ) {
		foreach ( $taxes as $tax ) {
			$tax_before = str_replace( '%s', $tax, $before );
			$tax_sep 	= str_replace( '%s', $tax, $sep );
			$tax_after 	= str_replace( '%s', $tax, $after );
			the_terms( $post->ID, $tax, $tax_before, $tax_sep, $tax_after );
		}
	}
}
