<?php 
/* AJAX responses for adding links, comments, tags, etc */

if ( !is_admin() )
	add_action('init', 'reclink_frontend_entries');

/**
 * @function	reclink_frontend_entries()
 *
 * Form handler for visitors with Javascript disabled. Should detect the presence of POST
 * data to either add link or vote/comment on link. This function should not be called if
 * Javascript is enabled, because the form handler functions in reclinks-scripts.js
 * should prevent default submission.
 *
 * @uses	gad_add_reclink()
 * @uses	gad_add_reclink_vote()
 */
function reclink_frontend_entries() {
	if ( !isset( $_GET['action'] ) )
		return;

	if ( 'reclink-add' === $_GET['action'] && !empty( $_POST['reclink_URL'] ) ) {
		$reclink = array(
			'reclink_url' => esc_url( $_POST['reclink_URL'] ),
			'reclink_title' => sanitize_text_field( $_POST['reclink_title'] ),
			'reclink_description' => wp_filter_post_kses( $_POST['reclink_description'] ),
			'reclink_taxes' => ( isset( $_POST['reclink_taxes'] ) ) ? $_POST['reclink_taxes'] : null
		);
		gad_add_reclink( $reclink );
	} 

	if ( 'reclink-vote' === $_GET['action'] && !empty( $_POST['reclink'] ) ) {		
		global $current_user;
		get_currentuserinfo();

		$comment = ( isset( $_POST['comment'] ) ) ? intval( $_POST['comment'] ) : 0;
		$vote = intval( $_POST['vote'] );

		$votesuccess = gad_add_reclink_vote( $_POST['reclink'], $comment, $vote, $current_user->ID, $_SERVER['REMOTE_ADDR'] );
	}

	if ( 'submitlink' === $_GET['action'] ) {
		add_filter( 'show_admin_bar', '__return_false' );
		reclinks_bookmarklet_request();
		exit;
	}

}


add_action( 'wp_ajax_nopriv_add_reclink', 'gad_reclinks_ajax_add' );
add_action( 'wp_ajax_add_reclink', 'gad_reclinks_ajax_add' );

function gad_reclinks_ajax_add() {
	$plugin_settings = get_option( 'reclinks_plugin_options' );

	if ( !$plugin_settings['allow-unregistered-post'] && !current_user_can( 'add_reclink' ) )
		die( json_encode( array( 'exception' => 'Current user is not authorized to add links' ) ) );

	$reclink = array(
		'reclink_url' => esc_url( $_POST['reclink_URL'] ),
		'reclink_title' => sanitize_text_field( $_POST['reclink_title'] ),
		'reclink_description' => wp_filter_post_kses( $_POST['reclink_description'] ),
		'reclink_taxes' => isset( $_POST['reclink_taxes'] ) ? $_POST['reclink_taxes'] : null
	);
	
	$link = gad_add_reclink( $reclink );
	echo json_encode( get_post( $link ) );
	die();

}

add_action( 'wp_ajax_vote_reclink', 'gad_reclinks_ajax_vote' );
add_action( 'wp_ajax_nopriv_vote_reclink', 'gad_reclinks_ajax_vote' );

function gad_reclinks_ajax_vote() {
	$plugin_settings = get_option( 'reclinks_plugin_options' );

	global $current_user;
	get_currentuserinfo();

	if ( !$plugin_settings['allow-unregistered-vote'] && !current_user_can( 'vote_reclink' ) )
		die( json_encode( array( 'exception' => 'Current user is not authorized to add links' ) ) );

	$comment = ( isset( $_POST['comment'] ) ) ? intval( $_POST['comment'] ) : 0;
	$vote = intval( $_POST['vote'] );

	$votesuccess = gad_add_reclink_vote( $_POST['reclink'], $comment, $vote, $current_user->ID, $_SERVER['REMOTE_ADDR'] );

	die( json_encode( array( 'newCount' => $votesuccess ) ) );

}

// Not currently used; but here in case the YQL solution proves too slow, or unreliable
add_action( 'wp_ajax_check_reclink_title', 'gad_reclinks_check_link_title' );
add_action( 'wp_ajax_nopriv_check_reclink_title', 'gad_reclinks_check_link_title' );

function gad_reclinks_check_link_title() {
	$link = esc_url( $_POST['url'] );
	if ( !$link ) {
		$return['exception'] = 'Invalid URL';
	}
	$response = wp_remote_get( $link );
	if ( $response ) {
		$doc = new DOMDocument();
		$doc->strictErrorChecking = FALSE;
		$doc->loadHTML( $response['body'] );
		$xml = simplexml_import_dom($doc);
		$title = $xml->head->title;
		$return['title'] = (string)$title;
	}
	die( json_encode( $return ) );

}


/**
 * On template redirect, checks for the presence of the "action=submitlink" query
 * arg (IE, a request originating from the bookmarklet), and, if present, skips the
 * usual template hierarchy and only displays the "Add Link" form.
 * 
 */


function reclinks_bookmarklet_request() {

	define( 'IFRAME_REQUEST', true );

	if ( !is_user_logged_in() ) {
		$current_request = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];
		wp_redirect( add_query_arg( 
			array( 'msg' => 'bookmarklets-login' ),
			wp_login_url( $current_request ) )
		);
		exit;
	}
	
	header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<html>
	<head>
		<title><?php wp_title('Submit Recommended Link'); ?></title>
		<?php wp_head(); ?>
		<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_uri(); ?>" />
		<script type="text/javascript">
			jQuery(document).ready(function($){
				reclink = {
					url: '<?php echo $_GET['u']; ?>',
					title: '<?php echo $_GET['t']; ?>',
					description: '<?php echo $_GET['s']; ?>'
				}
				$('#reclink_URL').val(reclink.url);
				$('#reclink_title').val(reclink.title);
				$('#reclink_description').val(reclink.description);
			});
		</script>
	</head>
	<body>
		<div class="widget widget_addLink_form">
			<?php output_addlink_form( true ); ?>
		</div>
	</body>
</html>
<?php
	die(0);
}
