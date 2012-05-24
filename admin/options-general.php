<?php
	
/**
 * HTML markup for the "general" tab under plugin settings
 *
 * Called from admin-functions.php
 *
 */

?>
	<tr>
		<th scope="row">
			<label for="page_for_reclinks"><?php _e( 'Page for Recommended Links Archive:', 'gad_reclinks' ); ?></label>
		</th>
		<td>
			<?php wp_dropdown_pages(
				array(
					'name' => 'page_for_reclinks',
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
			<label for="sort_order"><?php _e( 'Default Sort Order:', 'gad_reclinks' ); ?></label>
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
			<p>
				<input type="checkbox" name="allow-unregistered-post" <?php checked( $current_settings['allow-unregistered-post'] ); ?>/>
				<label for="allow-unregistered-post"><?php _e( 'Allow unregistered users to post new links?', 'gad_reclinks' ); ?></label>
			</p>
			<p class="description"><?php _e( 'If unregistered users are allowed to post links, choose an author to assign to those links:', 'gad_reclinks' ); ?></p>
			<?php wp_dropdown_users( array(
				'name' => 'anonymous-links-author',
				'selected' => ( isset( $current_settings['anonymous-links-author'] ) ) ? $current_settings['anonymous-links-author'] : null,
				)
			); ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label><?php _e( 'Comments:', 'gad_reclinks' ); ?></label>
		</th>
		<td>
			<p>
				<input type="checkbox" name="vote-on-comments" <?php checked( $current_settings['vote-on-comments'] ); ?> />
				<label for="vote-on-comments"><?php _e( 'Enable voting / points tally on comments?', 'gad_reclinks' ); ?></label>
			</p>
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
