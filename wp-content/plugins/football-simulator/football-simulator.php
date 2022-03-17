<?php
/**
 * Plugin Name:       Football simulator
 * Description:       Adds Football simulator.
 * Author:            Oleg Vartanov
 * Text Domain:       text-domain
 * Domain Path:       /languages
 */

add_action( 'wp_print_styles', 'fs_stylesheet' );
function fs_stylesheet()
{
    wp_register_style('mypluginstylesheet', '/wp-content/plugins/postgrid/style.css');
    wp_enqueue_style('mypluginstylesheet');
    wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');
}

add_action('wp_print_scripts','fs_plugin_js');
function fs_plugin_js()
{
    wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('prefix_bootstrap');
}

add_action( 'wp_enqueue_scripts', 'load_stylesheets' );
function load_stylesheets() {
    wp_enqueue_script("jquery");
};

/*
 * Adds a custom page template
 */
add_filter('theme_page_templates', 'fs_add_page_template');
function fs_add_page_template($templates)
{
    $templates['tournament.php'] = 'Tournament';
    return $templates;
}

/*
 * Redirects page to plugin's custom template
 */
add_filter('page_template', 'fs_redirect_page_template');
function fs_redirect_page_template($template)
{
    $post = get_post();
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);
    if ('tournament.php' == basename($page_template))
        $template = dirname(__FILE__) . '/tournament.php';
    return $template;
}

add_action( 'init', 'fs_register_post_type_teams' );
function fs_register_post_type_teams() {
    $labels = [
        "name" => __( "Teams", "textdomain" ),
        "singular_name" => __( "Team", "textdomain" ),
    ];
    $args = [
        "labels" => $labels,
        "public" => true,
        "rest_base" => "",
        "capability_type" => "post",
        "map_meta_cap" => true,
        "supports" => [ "title" ],
        "has_archive" => true,
        "rewrite" => [ "slug" => "teams", "with_front" => true ],
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "query_var" => true,
        "delete_with_user" => false,
        "menu_icon" => "dashicons-admin-site-alt3",
    ];

    register_post_type( "teams", $args );
}

add_action( 'init', 'fs_register_post_type_matches' );
function fs_register_post_type_matches() {
    $labels = [
        "name" => __( "Matches", "textdomain" ),
        "singular_name" => __( "Match", "textdomain" ),
    ];
    $args = [
        "labels" => $labels,
        "public" => true,
        "rest_base" => "",
        "capability_type" => "post",
        "map_meta_cap" => true,
        "supports" => [ "title" ],
        "has_archive" => true,
        "rewrite" => [ "slug" => "matches", "with_front" => true ],
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "query_var" => true,
        "delete_with_user" => false,
        "menu_icon" => "dashicons-admin-site-alt3",
    ];

    register_post_type( "matches", $args );
}

require __DIR__ . '/includes/fs-schedule-functions.php';

function fs_remove_old_matches() {
    $old_matches = get_posts([
        'post_type' => 'matches',
        'numberposts' => -1
    ]);
    foreach ($old_matches as $old_match) {
        wp_delete_post( $old_match->ID, true );
    }
}

add_action( 'wp_ajax_fs_reset_tournament', 'fs_reset_tournament' );
add_action( 'wp_ajax_nopriv_fs_reset_tournament', 'fs_reset_tournament' );
function fs_reset_tournament() {
    fs_remove_old_matches();

    $url = wp_get_referer();
    $tournament_post_id = url_to_postid($url);
    update_post_meta($tournament_post_id, 'tour_status', 'not_started');
    update_post_meta($tournament_post_id, 'tour_current_week', 0);
}

add_action( 'wp_ajax_fs_start_tournament', 'fs_start_tournament' );
add_action( 'wp_ajax_nopriv_fs_start_tournament', 'fs_start_tournament' );
function fs_start_tournament() {

    fs_remove_old_matches();

    // Creating new matches
    $args = [
        'post_type' => 'teams',
        'post__in' => $_POST['team_ids']
    ];
    $teams = get_posts($args);
    fs_create_matches($teams);

    $url = wp_get_referer();
    $tournament_post_id = url_to_postid($url);
    update_post_meta($tournament_post_id, 'tour_status', 'in_progress');
    update_post_meta($tournament_post_id, 'tour_current_week', 0);

    wp_die();
}

add_action( 'wp_ajax_fs_start_week', 'fs_start_week' );
add_action( 'wp_ajax_nopriv_fs_start_week', 'fs_start_week' );
function fs_start_week() {
    simulate_matches($_POST['current_week'] + 1);
}

function simulate_matches($week) {
    $url = wp_get_referer();
    $tournament_post_id = url_to_postid($url);
    $tournament_post = get_post($tournament_post_id);

    $matches = get_posts([
        'numberposts'   => -1,
        'post_type'     => 'matches',
        'meta_key'      => 'match_week',
        'meta_value'    => $week
    ]);

    foreach ($matches as $match) {
        simulate_match($match);
    }

    update_post_meta($tournament_post_id, 'tour_current_week', $tournament_post->tour_current_week + 1);
}

function simulate_match($match) {
    $home_team = get_post($match->match_home_team);
    $away_team = get_post($match->match_away_team);

    if (!empty($match->match_home_team_goals) && !empty($match->match_away_team_goals)) {
        return false;
    }

    $home_team_goals = 0;
    $away_team_goals = 0;
    $home_team_lvl = $home_team->team_level;
    $away_team_lvl = $away_team->team_level;

    for ($i = 1; $i < 6; $i++) {
        if (try_to_score($home_team_lvl)) {
            $home_team_goals++;
        }
        if (try_to_score($away_team_lvl)) {
            $away_team_goals++;
        }
        $home_team_lvl -= 20;
        $away_team_lvl -= 20;
    }

    update_post_meta($match->ID, 'match_home_team_goals', $home_team_goals);
    update_post_meta($match->ID, 'match_away_team_goals', $away_team_goals);
}

function try_to_score($skill_level) {
    $goal = false;

    if (rand(0, 100) <= $skill_level) {
        $goal = true;
    }

    return $goal;
}
