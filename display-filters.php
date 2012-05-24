<?php

/**
 * Query filters for reclinks
 * 
 *
 */


add_filter( 'query_vars', 'gad_reclinks_add_query_vars' );

function gad_reclinks_add_query_vars( $vars ) {
    array_push( $vars, 'reclinks_sort' );
    return $vars;
}

// flush_rules() if our rules are not yet included
add_action( 'wp_loaded','gad_reclinks_flush_rules' );

function gad_reclinks_flush_rules() {
	global $wp_rewrite;

	if ( !$wp_rewrite->using_permalinks() )
		return;

	$rules = get_option( 'rewrite_rules' );
	$plugin_settings = get_option( 'reclinks_plugin_options' );
	$archive_page = $plugin_settings['page_for_reclinks'];
	if ( !$archive_page )
		return;

	$archive_page_name = get_post( $archive_page )->post_name;
	if ( !isset( $rules["({$archive_page_name})/(newest|hot|current|score|controversial)/?"] ) ) {
		$wp_rewrite->flush_rules();
	}
}

add_filter( 'rewrite_rules_array', 'gad_reclinks_sortorder_rewrite' );

function gad_reclinks_sortorder_rewrite( $rules ) {

	$plugin_settings = get_option( 'reclinks_plugin_options' );

	if ( $archive_page = $plugin_settings['page_for_reclinks'] ) {

		$archive_page_name = get_post( $archive_page )->post_name;
		$new_rules = array( 
			"({$archive_page_name})/(newest|hot|current|score|controversial)/page/([0-9]+)/?" => 'index.php?pagename=$matches[1]&reclinks_sort=$matches[2]&paged=$matches[3]',
			"({$archive_page_name})/(newest|hot|current|score|controversial)/?" => 'index.php?pagename=$matches[1]&reclinks_sort=$matches[2]'
		);
		$rules = $new_rules + $rules;

		return $rules;
	}
}


add_filter( 'pre_get_posts', 'gad_reclinks_sortby' );

function gad_reclinks_sortby( $query ) {
	$plugin_settings = get_option( 'reclinks_plugin_options' );

	global $wp_the_query;

	if ( !isset( $query->query_vars['post_type'] ) || $query->query_vars['post_type'] !== 'reclink')
		return $query;
	
	if ( is_admin() )
		return $query;

	if ( $query === $wp_the_query ) {
		$posts_per_page = ( isset( $plugin_settings['posts_per_page'] ) ) ? $plugin_settings['posts_per_page'] : 25;
		$query->set( 'posts_per_page', $posts_per_page );
	}

	// if any taxonomies are enabled for recommended links post type (in plugin settings), they can be used to 
	// filter archive pages. If a taxonomy term is passed in query string, use that to modify the query

	if ( $taxonomies = $plugin_settings['tax'] ) {
		$tax_query = array();
		foreach ( $taxonomies as $tax => $on ) {
			if ( isset( $_GET[ $tax ] ) )
				$tax_query[] = array(
					'taxonomy' 	=> $tax,
					'terms'		=> (array)$_GET[ $tax ],
					'field'		=> 'slug'
				);
		}
		$query->set( 'tax_query', $tax_query );
	}

	// Sort order is determined by plugin defaults, and can be 
	// overriden by query parameter "reclinks_sort" or query string argument "sort"
	
	$sort_order = ( isset( $plugin_settings['sort_order'] ) ) ? $plugin_settings['sort_order'] : 'current';

	if ( isset( $query->query_vars['reclinks_sort'] ) && in_array(
			$query->query_vars['reclinks_sort'], 
			array( 'newest', 'hot', 'current', 'score', 'controversial' ) ) )
		$sort_order = $query->query_vars['reclinks_sort'];

	if ( isset( $_GET['sort'] ) && in_array(
			$_GET['sort'], 
			array( 'newest', 'hot', 'current', 'score', 'controversial' ) ) )
		$sort_order = $_GET['sort'];

	switch ( $sort_order ) :
		case 'score':
			// default: order by vote total
			$query->set( 'meta_key', '_vote_score' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;
		case 'current':
			add_filter( 'posts_fields', 'gad_reclinks_posts_fields' );
			add_filter( 'posts_join', 'gad_reclinks_votes_join_current' );
			add_filter( 'posts_groupby', 'gad_reclinks_groupby' );
			add_filter( 'posts_orderby', 'gad_reclinks_orderby' );
			add_action( 'the_posts', 'gad_remove_custom_filters' );
			break;
		case 'hot':
			add_filter( 'posts_fields', 'gad_reclinks_posts_fields' );
			add_filter( 'posts_join', 'gad_reclinks_votes_join_hot' );
			add_filter( 'posts_groupby', 'gad_reclinks_groupby' );
			add_filter( 'posts_orderby', 'gad_reclinks_orderby' );
			add_action( 'the_posts', 'gad_remove_custom_filters' );
			break;
		case 'controversial':
			add_filter( 'posts_fields', 'gad_reclinks_posts_fields_absval' );
			add_filter( 'posts_join', 'gad_reclinks_votes_join_current' );
			add_filter( 'posts_groupby', 'gad_reclinks_groupby' );
			add_filter( 'posts_orderby', 'gad_reclinks_orderby' );
			add_action( 'the_posts', 'gad_remove_custom_filters' );
			break;
		case 'newest':
			break;
	endswitch;

	return $query;

}

function gad_remove_custom_filters( $posts ) {
	remove_filter( 'posts_fields', 'gad_reclinks_posts_fields' );
	remove_filter( 'posts_join', 'gad_reclinks_votes_join_hot' );
	remove_filter( 'posts_join', 'gad_reclinks_votes_join_current' );
	remove_filter( 'posts_groupby', 'gad_reclinks_groupby' );
	remove_filter( 'posts_orderby', 'gad_reclinks_orderby' );
	return $posts;
}


function gad_reclinks_posts_fields( $fields ) {
	global $wpdb;
	$fields = str_replace( "{$wpdb->posts}.*", "{$wpdb->posts}.*, SUM( {$wpdb->reclinkvotes}.vote ) AS post_vote ", $fields );
	return $fields;
}

function gad_reclinks_posts_fields_absval( $fields ) {
	global $wpdb;
	$fields = str_replace( "{$wpdb->posts}.*", "{$wpdb->posts}.*, SUM( ABS( {$wpdb->reclinkvotes}.vote ) ) AS post_vote ", $fields );
	return $fields;
}

function gad_reclinks_votes_join_hot( $join ) {
	return $join . gad_reclinks_votes_join( '1 DAY' );
}

function gad_reclinks_votes_join_current( $join ) {
	return $join . gad_reclinks_votes_join( '1 WEEK' );
}

function gad_reclinks_votes_join( $interval ) {
	global $wpdb;
	$join_sql = "LEFT JOIN {$wpdb->reclinkvotes} ON ( {$wpdb->reclinkvotes}.post_id = {$wpdb->posts}.ID AND DATE_ADD( {$wpdb->reclinkvotes}.vote_time, INTERVAL $interval ) > NOW() )";
	return $join_sql;
}

function gad_reclinks_groupby( $groupby ) {
	global $wpdb;
	$groupby = "{$wpdb->posts}.ID";
	return $groupby;
}

function gad_reclinks_orderby( $orderby ) {
	global $wpdb;
	$orderby = "post_vote DESC, {$wpdb->posts}.post_date DESC";
	return $orderby;
}


/**
 * By default, filters the_content to add the vote box above the content 
 * (the link description). If you would like to add the vote box in a different 
 * location, you can remove this filter and include the template tag
 * reclinks_votebox() in your theme files.
 *
 */
add_filter( 'the_content', 'gad_reclinks_show_votelinks' );

function gad_reclinks_show_votelinks( $content ) {
	if ( is_admin() )
		return $content;

	global $post;
	if ( $post->post_type !== 'reclink' )
		return $content;

	$content = reclinks_votebox( false ) . $content;
	return $content;
}


/**
 * By default, this function filters comment_text to add the vote box above the 
 * comment text IF the setting "Enable voting / points tally on comments" is on.
 * If you would like to add the vote box in a different location, you can remove 
 * this filter and include the template tag reclinks_votebox() in your comment
 * callback function.
 * 
 */
add_filter( 'comment_text', 'reclinks_comment_show_votelinks' );

function reclinks_comment_show_votelinks( $comment_text, $comment = null ) {
	$plugin_settings = get_option( 'reclinks_plugin_options' );

	if ( false == $plugin_settings['vote-on-comments'] )
		return $comment_text;

	if ( is_admin() )
		return $comment_text;
	
	global $post;
	if ( $post->post_type !== 'reclink' )
		return $comment_text;

	$comment_text = reclinks_votebox( false ) . $comment_text;
	return $comment_text;
}



/**
 * For recommended links, the_permalink is filtered to echo the link submitted,
 * not the permalink of the comments page on your site. To get the discussion page
 * permalink instead, use get_permalink() or another similar function.
 *
 */
add_filter( 'the_permalink', 'gad_reclinks_permalink' );

function gad_reclinks_permalink( $permalink ) {
	global $post;
	if ( $post->post_type === 'reclink' && $href = get_post_meta( $post->ID, '_href', true ) )
		return $href;
	return $permalink;
}

function reclink_domain( $echo = true ) { return reclinks_domain( $echo ); } // the price you pay for typos in documentation

function reclinks_domain( $echo = true, $before = '(', $after = ')' ) {
	global $post;
	if ( $href = get_post_meta( $post->ID, '_href', true ) )
		$host = parse_url( $href, PHP_URL_HOST );
	
	if ( empty( $host ) ) return;

	if ( $echo )
		echo $before . $host . $after;
	else 
		return $before . $host . $after;
}

function reclinks_favicon( $echo = true ) {
	$domain = reclinks_domain( false, '', '' );
	if ( empty( $domain ) ) return false;
	$favicon = '<img class="reclink-favicon" src="http://www.google.com/s2/favicons?domain='.$domain.'" alt="'.$domain.'">';
	if ( $echo )
		echo $favicon;
	else
		return $favicon;
}

/**
 * A "pseudo-loop" for the page designated as "Page for Recommended Links Archive"
 *
 * Uses the WP_Query object to retrieve posts, the loop-reclinks.php template to display
 * them, and the WordPress functions get_previous_posts_page and get_next_posts_page.
 * In short, it basically functions just like a regular archive page, except for the 
 * template and the WordPress conditional tags, (ie. `is_archive()` will return false).
 */
add_filter( 'the_content', 'gad_reclinks_page' );

function gad_reclinks_page( $content ) {
	$plugin_settings = get_option( 'reclinks_plugin_options' );

	if ( !$plugin_settings['page_for_reclinks'] || !is_page( $plugin_settings['page_for_reclinks'] ) )
		return $content;	

	global $wp_the_query, $wp_query, $paged;

	if ( $wp_query !== $wp_the_query )
		return $content;

	$links_paged = ( isset( $wp_query->query['paged'] ) ) ? $wp_query->query['paged'] : 1;
	$links_sort = ( isset( $wp_query->query_vars['reclinks_sort'] ) ) ? $wp_query->query_vars['reclinks_sort'] : $plugin_settings['sort_order'];
	$posts_per_page = ( isset( $plugin_settings['posts_per_page'] ) ) ? $plugin_settings['posts_per_page'] : 25;

	// Backup old query, so it doesn't throw off conditionals elsewhere
	$old_query = $wp_query;
	$old_paged = $paged;

	$wp_query = new WP_Query( array(
		'post_type' => 'reclink',
		'reclinks_sort' => $links_sort,
		'posts_per_page' => $posts_per_page,
		'paged' => $links_paged
	) );
	$paged = $links_paged;

	/*
	 * Basic structure for prev/next links,
	 * should be built out a little more in future releases.
	 */
	$found_posts = $wp_query->found_posts;

	$links_navigation = '<div class="links-navigation">' ;

	if ( $paged > 1 ) 
		$links_navigation .= '<div class="nav-previous">' . get_previous_posts_link() . '</div>';
	
	if ( $found_posts > $posts_per_page * $paged )
		$links_navigation .= '<div class="nav-next">' . get_next_posts_link() . '</div>';

	$links_navigation .= '</div>';

	ob_start();
	if ( '' === locate_template( 'loop-reclinks.php', true, false ) )
		include( 'loop-reclinks.php' );
	$links_archive = ob_get_clean();

	$wp_query = $old_query;
	$paged = $old_paged;
	wp_reset_query();

	return $content . $links_archive . $links_navigation;

}

