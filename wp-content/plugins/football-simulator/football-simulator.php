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

add_action( 'save_post_matches', 'fs_update_match_title' );
function fs_update_match_title($post_id) {
    $new_fields = get_fields($post_id);

    $home_team_title = $new_fields['home_team']->post_title;
    $away_team_title = $new_fields['away_team']->post_title;

    $post_update = array(
        'ID'         => $post_id,
        'post_title' => $home_team_title . ' - '. $away_team_title
    );
    remove_action('save_post_matches', 'fs_update_match_title');
    wp_update_post( $post_update );
    add_action('save_post_matches', 'fs_update_match_title');
}

add_action( 'wp_ajax_fs_select_teams', 'fs_select_teams' );
add_action( 'wp_ajax_nopriv_fs_select_teams', 'fs_select_teams' );
function fs_select_teams() {
    $args = array(
        'post_type' => 'teams',
        'post__in' => $_POST['team_ids']
    );
    $teams = get_posts($args);

//    get_post_meta($teams[0]->ID, 'level', true);

//    $post_id = wp_insert_post(array (
//        'post_type' => 'matches',
//        'post_title' => $your_title,
//        'post_content' => $your_content,
//        'post_status' => 'publish',
//        'comment_status' => 'closed',   // if you prefer
//        'ping_status' => 'closed',      // if you prefer
//    ));
//
//    if ($post_id) {
//        // insert post meta
//        add_post_meta($post_id, '_your_custom_1', $custom1);
//        add_post_meta($post_id, '_your_custom_2', $custom2);
//        add_post_meta($post_id, '_your_custom_3', $custom3);
//    }

    wp_die();
}