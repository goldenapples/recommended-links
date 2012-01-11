<?php

add_action('admin_menu','reclinks_admin_pages');

function reclinks_admin_pages() {
//	add_menu_page('Recommended Links Plugin Settings','RecLinks','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings',RECLINKS_DIRECTORY.'/images/icon16.png');
	add_submenu_page('edit.php?post_type=reclink',__( 'Recommended Links Plugin Settings', 'gad_reclinks' ),'Plugin Settings','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings');
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
						<label for="page_for_reclinks"><?php _e( 'Page for Recommended Links Archive:', 'gad_reclinks' ); ?></label>
					</th>
					<td>
						<?php wp_dropdown_pages(
							array(
								'show_option_none' => __('None (use default archive)', 'gad_reclinks'),
								'exclude' => array( get_option('page_for_posts') ),
								'selected' => $current_settings['page_for_reclinks']
							)
						); ?>
	<p class="description"><?php _e( 'Note: if you choose a custom page to hold your archive, you can add content above the archive.<br>The page template you select will be used for styling purposes.', 'gad_reclinks' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="page_for_reclinks"><?php _e( 'Default Sort Order:', 'gad_reclinks' ); ?></label>
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
								echo '<option value="'.$opt.'" '.selected( $opt, $current_settings['sort_order'], 0 ).'>'.$descrip.'</option>';
							} ?>
						</select>
					</td>
				</tr>
		<tr>
			<th scope="row">
				<label for="taxonomies"><?php _e( 'Taxonomies to enable for recommended links:', 'gad_reclinks' ); ?></label>
			</th>
			<td>
			<?php $taxes = get_taxonomies( null, 'objects' );
					foreach ($taxes as $name => $tax) 
						if (!in_array( $name, array( 'nav_menu', 'link_category', 'post_format' ) ) )
							echo '<p><input type="checkbox" name="tax['.$name.']" '.checked( ( isset( $current_settings['tax'][$name] ) && $current_settings['tax'][$name] ), true, false ).'> '.$tax->labels->name .'</p>';
			?>
			</td>
		</tr>
			<tr>
				<th scope="row">
					<label><?php _e( 'User registration options:', 'gad_reclinks' ); ?></label>
				</th>
				<td>
					<p>
						<input type="checkbox" name="allow-unregistered-vote" <?php checked( $current_settings['allow-unregistered-vote'] ); ?>/>
						<label for="allow-unregistered-vote"><?php _e( 'Allow unregistered users to vote?', 'gad_reclinks' ); ?></label>
						<br><span class="description"><?php _e('(Votes will be logged by IP address.)', 'gad_reclinks' ); ?></span>
					</p>
<!---
					<p>
						<input type="checkbox" name="allow-unregistered-post" <?php checked( $current_settings['allow-unregistered-post'] ); ?>/>
						<label for="allow-unregistered-post"><?php _e( 'Allow unregistered users to post new links?', 'gad_reclinks' ); ?></label>
					</p>
-->
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
	// this is just bare minimum
	update_option( 'reclinks_plugin_options', 
		array(
			'page_for_reclinks' => intval( $_POST['page_id'] ),
			'sort_order' => $_POST['sort_order'],
			'tax' => ( isset( $_POST['tax'] ) ) ? $_POST['tax'] : array(),
			'allow-unregistered-vote' => (isset($_POST['allow-unregistered-vote']) && true == $_POST['allow-unregistered-vote']),
			'allow-unregistered-post' => (isset($_POST['allow-unregistered-post']) &&true == $_POST['allow-unregistered-post']),

			// no UI for this yet, but its gotta be in there
			'vote-values' => array(
				'minus' => array( 'value' => -1, 'text' => '-' ),
				'plus' => array( 'value' => 1, 'text' => '+' )
			),
		)
	);
	echo '<div id="message" class="messages updated"><p>Plugin settings updated!</p></div>';
}
