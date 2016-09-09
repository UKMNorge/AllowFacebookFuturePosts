<?php
/* 
Plugin Name: AllowFacebookFuturePosts
Plugin URI: http://www.github.com/UKMNorge/AllowFacebookFuturePosts
Description: Allows Facebooks crawler to read posts that are scheduled, but not published. Heavily inspired by https://wordpress.org/plugins/public-post-preview/.
Author: UKM Norge / M Mandal / A Hustad
Version: 1.0 
Author URI: http://www.ukm.no
*/

add_filter( 'pre_get_posts', 'UKM_allow_facebook_peak' );

function is_facebook() {
    return !( strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit/") === false && strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") === false) ;
}

/**
 * Registers the filter to handle a public preview.
 *
 * Filter will be set if it's the main query, a preview, a singular page
 * and the query var `_ppp` exists.
 *
 * @since 2.0.0
 *
 * @param object $query The WP_Query object.
 * @return object The WP_Query object, unchanged.
 */
function UKM_allow_facebook_peak( $query ) {
    if (
        $query->is_main_query() &&
        $query->is_singular() &&
        is_facebook()
    ) {
        add_filter( 'posts_results', 'UKM_set_post_to_publish', 10, 2 );
    }

    return $query;
}

/**
 * Sets the post status of the first post to publish, so we don't have to do anything
 * *too* hacky to get it to load the preview.
 *
 * @since 2.0.0
 *
 * @param  array $posts The post to preview.
 * @return array The post that is being previewed.
 */
function UKM_set_post_to_publish( $posts ) {
    // Remove the filter again, otherwise it will be applied to other queries too.
    remove_filter( 'posts_results', 'UKM_set_post_to_publish', 10 );

    if ( empty( $posts ) ) {
        return;
    }

    // Only show posts that are scheduled, not drafts.
    if($posts[0]->post_status == 'future') {
    	// Set post status to publish so that it's visible.
	    $posts[0]->post_status = 'publish';

	    // Disable comments and pings for this post.
	    add_filter( 'comments_open', '__return_false' );
	    add_filter( 'pings_open', '__return_false' );
    }

    return $posts;
}