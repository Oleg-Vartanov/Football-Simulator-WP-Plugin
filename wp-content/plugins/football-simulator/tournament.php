<?php
/* Template Name: Tournament */
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

$all_teams = get_posts([
    'post_type'   => 'teams',
    'post_status' => 'publish'
]);
$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full')['0'];

if ($post->tour_status == 'in_progress') {
    $current_week = $post->tour_current_week;
    $current_week_matches = get_posts([
        'numberposts'   => -1,
        'post_type'     => 'matches',
        'meta_key'      => 'match_week',
        'meta_value'    => 1 // Take a first week
    ]);
    $teams_info = fs_get_teams_info($current_week_matches, $current_week);
}

/* Start the Loop */
while (have_posts()) :
    the_post();

    include __DIR__ . '/template-parts/content-page-tournament.php';

    // If comments are open or there is at least one comment, load up the comment template.
    if (comments_open() || get_comments_number()) {
        comments_template();
    }
endwhile; // End of the loop.

get_footer();
