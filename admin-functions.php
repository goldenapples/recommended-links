<?php

add_action('admin_menu','reclinks_admin_pages');

function reclinks_admin_pages() {
	add_menu_page('Recommended Links Plugin Settings','RecLinks','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings',RECLINKS_DIRECTORY.'/images/icon16.png');
	add_submenu_page('reclinks_plugin_settings','Recommended Links Plugin Settings','Plugin Settings','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings');
//	add_submenu_page('reclinks_plugin_settings','Recommended Links - View / Edit Links','Edit Links','activate_plugins','reclinks_edit_links','reclinks_edit_links');
}

function reclinks_plugin_settings() {
	if ( !empty( $_POST ) && check_admin_referer( 'gad-reclinks-settings', '_wpnonce') )
		update_reclinks_settings();
	$current_settings = get_option( 'reclinks_plugin_options' );
?>
	<div class="wrap">
		<h2><?php _e( 'Recommended Links Plugin Settings', 'gad_reclinks' ); ?></h2>
		<form method="post">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="page_for_reclinks"><?php _e( 'Page for Recommended Links Archive', 'gad_reclinks' ); ?></label>
					</th>
					<td>
						<?php wp_dropdown_pages(
							array(
								'show_option_none' => __('None (use default archive)', 'gad_reclinks'),
								'exclude' => array( get_option('page_for_posts') ),
								'selected' => get_option( 'page_for_reclinks' )
							)
						); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="page_for_reclinks"><?php _e( 'Default Sort Order', 'gad_reclinks' ); ?></label>
					</th>
					<td>
						<select id="sort_order" name="sort_order">
							<?php $options = array( 
								'current' => __( 'Current: Top score this week', 'gad_reclinks' ),
								'hot' => __( 'Hot: Top score in the past 24 hours', 'gad_reclinks' ),
								'score' => __( 'Highest overall score', 'gad_reclinks' ),
								'newest' => __( 'Most recently submitted', 'gad_reclinks' )
							);
							foreach ( $options as $opt => $descrip ) {
								echo '<option value="'.$opt.'" '.selected( $opt, $reclinks_settings['sort_order'], 0 ).'>'.$descrip.'</option>';
							} ?>
						</select>
					</td>
				</tr>

		<tr>
			<th></th>
			<td>
				<?php wp_nonce_field( 'gad-reclinks-settings' ); ?>
				<p>
					<input type="submit" class="button-primary" value="Save changes"/>
				</p>
			</td>
		</tr>
			</table>
		</form>
	</div>

<?php
}

function update_reclinks_settings() {
	// needs sanitization and whitelisting, of course
	update_option( 'page_for_reclinks', intval( $_POST['page_id'] ) );
	update_option( 'reclinks_settings', 
		array(
			'sort_order' => $_POST['sort_order']
		)
	);
	echo '<div id="message" class="messages updated"><p>Plugin settings updated!</p></div>';
}
