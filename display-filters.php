<?php

// Query filters for reclinks

add_filter( 'pre_get_posts', 'gad_reclinks_sortby' );

function gad_reclinks_sortby( $query ) {

	if ( !is_post_type_archive('reclink') )
		return $query;

	// default: order by vote total
	$query->set( 'meta_key', '_vote_score' );
	$query->set( 'orderby', 'meta_value_num' );
	$query->set( 'order', 'DESC' );

//	var_dump( $query ); die();


}


//add_filter( 'the_title', 'gad_reclinks_show_votelinks' );

function gad_reclinks_show_votelinks( $title, $post_ID ) {
	$post = get_post( $post_ID );
	if ( $post->post_type !== 'reclink' )
		return $title;

	$voteform = '<span class="votestotal">'.$link->totalvotes.' VOTES<br />';
	$voteform .= '<form><input type="hidden" name="promote-link" value="'.$link->id.'"><input type="image" src="'.get_bloginfo('wpurl') .'/'. PLUGINDIR . '/gad-link-recommendations/images/recommend.png" alt="Recommend this link!"/></form>';

//	remove_filter( 'the_title', 'gad_reclinks_show_votelinks' );
	return $voteform . $title;
}

//add_filter( 'the_permalink', 'gad_reclinks_permalink' );

