<?php
/**
 * Plugin Name:       Football simulator
 * Description:       Adds Football simulator.
 * Author:            Oleg Vartanov
 * Text Domain:       text-domain
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require 'includes/Scheduler.php';
require 'includes/ProbabilityCalculator.php';
require 'includes/AjaxHandler.php';
require 'includes/Simulator.php';
include 'includes/fs_constants.php';

use FootballSimulator\Scheduler;
use FootballSimulator\ProbabilityCalculator;

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

function fs_remove_old_matches() {
    $old_matches = get_posts([
        'post_type' => 'matches',
        'numberposts' => -1
    ]);
    foreach ($old_matches as $old_match) {
        wp_delete_post( $old_match->ID, true );
    }
}

function fs_get_remaining_weeks($current_week)
{
	$weekly_matches = get_posts([
		'numberposts'   => -1,
		'post_type'     => 'matches',
		'meta_key'      => 'match_week',
		'meta_value'    => 1 // Take a first week
	]);
	return ((count($weekly_matches) * 2) - 1) * 2 - $current_week;
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
    $current_week_matches = get_posts([
        'numberposts'   => -1,
        'post_type'     => 'matches',
        'meta_key'      => 'match_week',
        'meta_value'    => $current_week
    ]);
	$scheduler = new Scheduler();
	$teams_info = $scheduler->getTeamsInfo($current_week_matches, $current_week);

    // Calculate winning probabilities
    if ($current_week >= 4 && $current_week < (count($teams_info) - 1) * 2) {
        $pr = new ProbabilityCalculator($teams_info, $current_week);
        $winning_probabilities = $pr->getWinProbabilities();
    }

    $teams_info = $scheduler->getSortedTableInfo($teams_info);

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

register_activation_hook( __FILE__, 'fs_plugin_activate' );
function fs_plugin_activate() {
    $teams = get_posts([
        'post_type'   => 'teams',
        'numberposts' => -1
    ]);

    if (empty(count($teams))) {
        foreach (FS_INIT_TEAMS as $title => $skill_lvl)
        wp_insert_post([
            'post_title'  => $title,
            'post_type'   => 'teams',
            'post_status' => 'publish',
            'meta_input'  => [
                'team_level' => $skill_lvl
            ]
        ]);
    }
}

register_uninstall_hook( __FILE__, 'fs_plugin_uninstall' );
function fs_plugin_uninstall() {
    $teams = get_posts([
        'post_type'   => 'teams',
        'numberposts' => -1
    ]);

    foreach ($teams as $team) {
        wp_delete_post( $team->ID, true );
    }
}

add_filter( 'ocdi/import_files', 'ocdi_import_files' );
function ocdi_import_files() {
    return [
        [
            'import_file_name'             => 'Premier League Demo',
            'local_import_file'            => __DIR__ . '/ocdi/demo-content.xml',
        ]
    ];
}

new \FootballSimulator\AjaxHandler();