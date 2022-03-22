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

    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'fs-styles', $plugin_url . 'assets/css/fs-styles.css' );
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

    $tournament_post = fs_get_post();
    update_post_meta($tournament_post->ID, 'tour_status', 'not_started');
    update_post_meta($tournament_post->ID, 'tour_current_week', 0);

    $response['tournament_status'] = 'not_started';
    $response['current_week'] = 0;

    ob_start();
    $all_teams = get_posts([
        'post_type'   => 'teams',
        'post_status' => 'publish'
    ]);
    require __DIR__ . '/template-parts/content-page-tournament-team-select.php';
    $response['content'] = ob_get_contents();
    ob_end_clean();

    wp_send_json($response);
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

    $tournament_post = fs_get_post();
    update_post_meta($tournament_post->ID, 'tour_status', 'in_progress');
    update_post_meta($tournament_post->ID, 'tour_current_week', 0);

    $response = ['tournament_status' => 'in_progress'];
    ob_start();
    // Data for content-page-tournament-table.php
    $current_week = 0;
    $teams_info = fs_get_teams_info($current_week);
    require __DIR__ . '/template-parts/content-page-tournament-table.php';
    $table = ob_get_contents();
    ob_end_clean();

    $response['content'] = $table;
    $response['current_week'] = 0;

    wp_send_json($response);
}

function fs_get_remaining_weeks($current_week)
{
    $weekly_matches = get_posts([
        'numberposts'   => -1,
        'post_type'     => 'matches',
        'meta_key'      => 'match_week',
        'meta_value'    => 1
    ]);
    return ((count($weekly_matches) * 2) - 1) * 2 - $current_week;
}

add_action( 'wp_ajax_fs_play_all_games', 'fs_play_all_games' );
add_action( 'wp_ajax_nopriv_fs_play_all_games', 'fs_play_all_games' );
function fs_play_all_games() {
    $response = [];
    $weeks_remaining = fs_get_remaining_weeks($_POST['current_week']);
    for ($i = 1; $i <= $weeks_remaining; $i++) {
        fs_simulate_matches($_POST['current_week'] + $i);
        $response[$i] = fs_get_updated_table_response($_POST['current_week'] + $i);
    }
    wp_send_json($response);
}

add_action( 'wp_ajax_fs_start_week', 'fs_start_week' );
add_action( 'wp_ajax_nopriv_fs_start_week', 'fs_start_week' );
function fs_start_week() {
    fs_simulate_matches($_POST['current_week'] + 1);
    $table_response = fs_get_updated_table_response($_POST['current_week'] + 1);
    wp_send_json($table_response);
}

function fs_get_post() {
    $url = wp_get_referer();
    $post_id = url_to_postid($url);
    return get_post($post_id);
}

function fs_get_updated_table_response($week) {
    $table_response = ['tournament_status' => 'in_progress'];

    ob_start();
    $post = fs_get_post();

    // Data for content-page-tournament-table.php
    $current_week = $week;
    $teams_info = fs_get_teams_info($current_week);

    // Calculate winning probabilities
    if ($current_week >= 4 && $current_week < (count($teams_info) - 1) * 2) {
        require_once('includes/ProbabilityCalculator.php');
        $pr = new ProbabilityCalculator($teams_info, $current_week);
        $winning_probabilities = $pr->getWinProbabilities();
    }

    $teams_info = fs_get_sorted_table_info($teams_info);

    require __DIR__ . '/template-parts/content-page-tournament-table.php';

    $table = ob_get_contents();
    ob_end_clean();

    $table_response['content'] = $table;
    $table_response['current_week'] = $week;
    if (fs_get_remaining_weeks($week) == 0) {
        $table_response['tournament_status'] = 'completed';
    }

    return $table_response;
}

function fs_get_sorted_table_info($teams_info) {
    array_multisort(
        array_column($teams_info, 'pts'), SORT_DESC,
        array_column($teams_info, 'goad_diff'), SORT_DESC,
        array_column($teams_info, 'scored'), SORT_DESC,
        array_column($teams_info, 'conceded'), SORT_DESC,
        $teams_info
    );

    return $teams_info;
}

function fs_simulate_matches($week) {
    $matches = get_posts([
        'numberposts'   => -1,
        'post_type'     => 'matches',
        'meta_key'      => 'match_week',
        'meta_value'    => $week
    ]);

    foreach ($matches as $match) {
        fs_simulate_match($match);
    }

    $tournament_post = fs_get_post();
    if (fs_get_remaining_weeks($week) == 0) {
        update_post_meta($tournament_post->ID, 'tour_status', 'completed');
    }
    update_post_meta($tournament_post->ID, 'tour_current_week', $week);
}

function fs_simulate_match($match) {
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
        if (fs_try_to_score($home_team_lvl)) {
            $home_team_goals++;
        }
        if (fs_try_to_score($away_team_lvl)) {
            $away_team_goals++;
        }
        $home_team_lvl -= 20;
        $away_team_lvl -= 20;
    }

    update_post_meta($match->ID, 'match_home_team_goals', $home_team_goals);
    update_post_meta($match->ID, 'match_away_team_goals', $away_team_goals);
}

function fs_try_to_score($skill_level)
{
    $goal = false;

    if (rand(0, 100) <= $skill_level) {
        $goal = true;
    }

    return $goal;
}

add_action( 'wp_ajax_fs_edit_score', 'fs_edit_score' );
add_action( 'wp_ajax_nopriv_fs_edit_score', 'fs_edit_score' );
function fs_edit_score()
{
    if ($_POST['team'] == 'home') {
        update_post_meta($_POST['match_id'], 'match_home_team_goals', $_POST['goals']);
    } else if ($_POST['team'] == 'away') {
        update_post_meta($_POST['match_id'], 'match_away_team_goals', $_POST['goals']);
    }

    $tournament_post = fs_get_post();
    $response = [];
    for ($week = 1; $week <= $tournament_post->tour_current_week; $week++) {
        $response[$week] = fs_get_updated_table_response($week);
    }
    wp_send_json($response);
}

add_action( 'wp_ajax_fs_show_tables', 'fs_show_tables' );
add_action( 'wp_ajax_nopriv_fs_show_tables', 'fs_show_tables' );
function fs_show_tables()
{
    $tournament_post = fs_get_post();
    $response = [];
    for ($week = 1; $week <= $tournament_post->tour_current_week; $week++) {
        $response[$week] = fs_get_updated_table_response($week);
    }
    wp_send_json($response);
}
