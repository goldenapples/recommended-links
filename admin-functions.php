<?php

add_action('admin_menu','reclinks_admin_pages');

function reclinks_admin_pages() {
	add_submenu_page('edit.php?post_type=reclink',__( 'Recommended Links Plugin Settings', 'gad_reclinks' ),'Plugin Settings','activate_plugins','reclinks_plugin_settings','reclinks_plugin_settings');
}

function reclinks_plugin_settings() {
	$tabs = array(
		'general' => __( 'General Settings', 'reclinks' ),
		'archives' => __( 'Archive Pages', 'reclinks' ),
		'bookmarklet' => __( 'Bookmarklet', 'reclinks' ),
   		'about' => __( 'About this plugin', 'reclinks' )
	);
	$page = ( isset($_GET['tab']) && in_array( $_GET['tab'], array_keys( $tabs ) ) ) ? $_GET['tab'] : 'general';

	if ( !empty( $_POST ) && check_admin_referer( 'gad-reclinks-settings', '_wpnonce') )
		update_reclinks_settings( $page );
	$current_settings = get_option( 'reclinks_plugin_options' );
?>
	<div class="wrap">
<h2><?php _e( 'Recommended Links Plugin Settings', 'reclinks' ); ?></h2>

<div id="icon-themes" class="icon32"><br /></div>
<h3 class="nav-tab-wrapper">
<?php foreach ( $tabs as $tab => $title ) {
	$tab_active_class = ( $tab == $page ) ? ' nav-tab-active' : '';
	echo '<a class="nav-tab'.$tab_active_class.'" href="?post_type=reclink&page=reclinks_plugin_settings&tab='.$tab.'">';
	echo $title;
	echo '</a>';
} ?>
</h3>
		<?php if ( 'about' == $page ) {
				require( 'admin/options-about.php' ); 
			} else { ?>
		<form method="post">
			<table class="form-table">
			<?php require( "admin/options-$page.php" ); ?>
			</table>
		</form>
	<?php } ?>
	</div>

<?php
}

function update_reclinks_settings( $page ) {

	$settings = get_option( 'reclinks_plugin_options' );

	switch ( $page ) :
		case 'general':
			$settings['page_for_reclinks'] = intval( $_POST['page_for_reclinks'] );
			$settings['sort_order'] = $_POST['sort_order'];
			$settings['tax'] = ( isset( $_POST['tax'] ) ) ? $_POST['tax'] : array();
			$settings['allow-unregistered-vote'] = (isset($_POST['allow-unregistered-vote']) && true == $_POST['allow-unregistered-vote']);
			$settings['allow-unregistered-post'] = (isset($_POST['allow-unregistered-post']) && true == $_POST['allow-unregistered-post']);
			$settings['anonymous-links-author'] = intval($_POST['anonymous-links-author']); 
			$settings['vote-on-comments'] = (isset($_POST['vote-on-comments']) && true == $_POST['vote-on-comments']);

			// no UI for this yet, but its gotta be in there
			$settings['vote-values'] = array(
				'minus' => array( 'value' => -1, 'text' => '-' ),
				'plus' => array( 'value' => 1, 'text' => '+' )
			);
			break;
		case 'bookmarklet':
			$settings['bookmarklet_text'] = sanitize_text_field( $_POST['bookmarklet_text'] );
			$settings['bookmarklet_class'] = sanitize_text_field( $_POST['bookmarklet_class'] );
			$settings['bookmarklet_header'] = wp_kses_post( $_POST['bookmarklet_header'] );
			break;
		default:
			break;
	endswitch;

	update_option( 'reclinks_plugin_options', $settings );

	echo '<div id="message" class="messages updated"><p>Plugin settings updated!</p></div>';
}

/**
 * Settings for edit.php and post.php pages
 *
 **/

add_filter( 'manage_edit-reclink_columns', 'reclinks_votes_column_register' );

function reclinks_votes_column_register( $columns ) {
	$columns['vote-score'] = __( 'Votes', 'gad_reclinks' );
	return $columns;
}

add_action( 'manage_posts_custom_column', 'reclinks_votes_column_display', 10, 2 );

function reclinks_votes_column_display( $column_name, $post_id ) {
	if ( 'vote-score' != $column_name )
		return;
	$total_score = get_post_meta( $post_id, '_vote_score', true );
	echo '<b>' . __( 'Score:', 'gad_reclinks' ) . ' ' . $total_score . '</b><br>';

	global $wpdb;
	$plus = absint( $wpdb->get_var( "SELECT SUM(vote) FROM {$wpdb->reclinkvotes} WHERE post_id={$post_id} AND vote>0" ) );
	$minus = absint( $wpdb->get_var( "SELECT SUM(vote) FROM {$wpdb->reclinkvotes} WHERE post_id={$post_id} AND vote<0" ) );

	echo '<span class="description">' . "( + $plus / - $minus )";

}

add_filter( 'manage_edit-reclink_sortable_columns', 'reclink_column_register_sortable' );

function reclink_column_register_sortable( $columns ) {
	$columns['vote-score'] = 'vote-score';
 
	return $columns;
}

add_filter( 'request', 'votescore_column_orderby' );

function votescore_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'vote-score' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_vote_score',
			'orderby' => 'meta_value_num'
		) );
	}
 
	return $vars;
}

/**
 * Added meta boxes for edit-post?post_type=reclink screen
 *
 */
function reclinks_edit_screen_metaboxes() {
	add_meta_box( 'reclinkurl', __( 'Link URL', 'reclinks'), 'reclinks_URL_metabox', 'reclink', 'normal', 'core' );
}

function reclinks_URL_metabox() {
	global $post;
	$href = get_post_meta( $post->ID, '_href', true );
	wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );
	echo '<input name="_href" type="url" class="regular-text" style="width: 98%" value="'.$href.'">';
}

add_action( 'save_post', 'save_edited_reclink_href' );

function save_edited_reclink_href( $post_ID ) {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return;

	if ( isset( $_POST['post_type'] ) && 'reclink' === $_POST['post_type'] )
		update_post_meta( $post_ID, '_href', $_POST['_href'] );

}
