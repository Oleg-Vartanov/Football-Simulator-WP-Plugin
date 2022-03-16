<?php

function fs_create_matches($teams)
{
    foreach (fs_generate_matches_schedule($teams) as $i => $round) {
        foreach ($round as $match) {

            // Create a match post
            $post_id = wp_insert_post(array(
                'post_type' => 'matches',
                'post_title' => $match['home_team']->post_title . ' - ' . $match['away_team']->post_title,
                'post_content' => '',
                'post_status' => 'publish',
            ));

            // Insert a match meta
            if ($post_id) {
                add_post_meta($post_id, 'away_team', $match['away_team']->ID);
                add_post_meta($post_id, 'home_team', $match['home_team']->ID);
                add_post_meta($post_id, 'week', $i + 1);
            }
        }
    }
}

function fs_generate_matches_schedule($teams): array
{
    $rounds = [];
    $teams_count = count($teams);

    for($round = 0 ; $round < $teams_count-1 ; ++$round)
    {
        for($i = 0 ; $i < $teams_count/2 ; ++$i)
        {
            $opponent = $teams_count - 1 - $i;

            // Home match
            $rounds[$round][] = [
                'home_team' => $teams[$i],
                'away_team' => $teams[$opponent]
            ];

            // Away match
            $rounds[$round + $teams_count][] = [
                'home_team' => $teams[$opponent],
                'away_team' => $teams[$i]
            ];
        }
        $teams = fs_rotate_competitors($teams); // rotate all competitors but the first one
    }

    return $rounds;
}

function fs_rotate_competitors($teams): array
{
    $result = $teams ;

    $tmp = $result[ count($result) - 1 ] ;
    for($i = count($result)-1 ; $i > 1 ; --$i)
    {
        $result[$i] = $result[$i-1] ;
    }
    $result[1] = $tmp ;

    return $result ;
}