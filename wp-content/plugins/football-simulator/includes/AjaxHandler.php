<?php

namespace FootballSimulator;

class AjaxHandler {

	static function resetTournament() {
		fs_remove_old_matches();

		$tournament_post = fs_get_post();
		update_post_meta( $tournament_post->ID, 'tour_status', 'not_started' );
		update_post_meta( $tournament_post->ID, 'tour_current_week', 0 );

		$response['tournament_status'] = 'not_started';
		$response['current_week']      = 0;

		ob_start();
		$all_teams = get_posts( [
			'post_type'   => 'teams',
			'post_status' => 'publish'
		] );
		require WP_PLUGIN_DIR . '/football-simulator/template-parts/content-page-tournament-team-select.php';
		$response['content'] = ob_get_contents();
		ob_end_clean();

		wp_send_json( $response );
	}

	static function startTournament() {
		fs_remove_old_matches();

		// Creating new matches
		$args      = [
			'post_type' => 'teams',
			'post__in'  => $_POST['team_ids']
		];
		$teams     = get_posts( $args );
		$scheduler = new Scheduler();
		$scheduler->createMatches( $teams );

		$tournament_post = fs_get_post();
		update_post_meta( $tournament_post->ID, 'tour_status', 'in_progress' );
		update_post_meta( $tournament_post->ID, 'tour_current_week', 0 );

		$response = [ 'tournament_status' => 'in_progress' ];
		ob_start();
		// Data for content-page-tournament-table.php
		$current_week         = 0;
		$current_week_matches = get_posts( [
			'numberposts' => - 1,
			'post_type'   => 'matches',
			'meta_key'    => 'match_week',
			'meta_value'  => 1 // Take a first week
		] );
		$teams_info           = $scheduler->getTeamsInfo( $current_week_matches, $current_week );
		require WP_PLUGIN_DIR . '/football-simulator/template-parts/content-page-tournament-table.php';
		$table = ob_get_contents();
		ob_end_clean();

		$response['content']      = $table;
		$response['current_week'] = 0;

		wp_send_json( $response );
	}

	static function playAllGames() {
		$response        = [];
		$weeks_remaining = fs_get_remaining_weeks( $_POST['current_week'] );
		for ( $i = 1; $i <= $weeks_remaining; $i ++ ) {
			fs_simulate_matches( $_POST['current_week'] + $i );
			$response[ $i ] = fs_get_updated_table_response( $_POST['current_week'] + $i );
		}
		wp_send_json( $response );
	}

	static function startWeek() {
		fs_simulate_matches( $_POST['current_week'] + 1 );
		$table_response = fs_get_updated_table_response( $_POST['current_week'] + 1 );
		wp_send_json( $table_response );
	}

	static function editScore() {
		if ( $_POST['team'] == 'home' ) {
			update_post_meta( $_POST['match_id'], 'match_home_team_goals', $_POST['goals'] );
		} else if ( $_POST['team'] == 'away' ) {
			update_post_meta( $_POST['match_id'], 'match_away_team_goals', $_POST['goals'] );
		}

		$tournament_post = fs_get_post();
		$response        = [];
		for ( $week = 1; $week <= $tournament_post->tour_current_week; $week ++ ) {
			$response[ $week ] = fs_get_updated_table_response( $week );
		}
		wp_send_json( $response );
	}

	function showTables() {
		$tournament_post = fs_get_post();
		$response        = [];
		for ( $week = 1; $week <= $tournament_post->tour_current_week; $week ++ ) {
			$response[ $week ] = fs_get_updated_table_response( $week );
		}
		wp_send_json( $response );
	}
}