<?php

namespace FootballSimulator;

class Simulator {

	private const ATTEMPTS_TO_SCORE = 5;
	private const LVL_DECREASE_AFTER_ATTEMPT = 20;
	private const MIN_SKILL_LVL = 0;
	private const MAX_SKILL_LVL = 100;

	function simulateMatches( $week ) {
		$matches = get_posts( [
			'numberposts' => - 1,
			'post_type'   => 'matches',
			'meta_key'    => 'match_week',
			'meta_value'  => $week
		] );

		foreach ( $matches as $match ) {
			$this->simulateMatch( $match );
		}

		$tournament_post = fs_get_post();
		if ( fs_get_remaining_weeks( $week ) == 0 ) {
			update_post_meta( $tournament_post->ID, 'tour_status', 'completed' );
		}
		update_post_meta( $tournament_post->ID, 'tour_current_week', $week );
	}

	function simulateMatch( $match ) {
		$home_team = get_post( $match->match_home_team );
		$away_team = get_post( $match->match_away_team );

		if ( ! empty( $match->match_home_team_goals ) && ! empty( $match->match_away_team_goals ) ) {
			return false;
		}

		$home_team_goals = 0;
		$away_team_goals = 0;
		$home_team_lvl   = $home_team->team_level;
		$away_team_lvl   = $away_team->team_level;

		for ( $i = 1; $i < $this::ATTEMPTS_TO_SCORE; $i ++ ) {
			if ( $this->tryToScore( $home_team_lvl ) ) {
				$home_team_goals ++;
			}
			if ( $this->tryToScore( $away_team_lvl ) ) {
				$away_team_goals ++;
			}
			$home_team_lvl -= $this::LVL_DECREASE_AFTER_ATTEMPT;
			$away_team_lvl -= $this::LVL_DECREASE_AFTER_ATTEMPT;
		}

		update_post_meta( $match->ID, 'match_home_team_goals', $home_team_goals );
		update_post_meta( $match->ID, 'match_away_team_goals', $away_team_goals );
	}

	private function tryToScore( $skill_level ) {
		$goal = false;

		if ( rand( $this::MIN_SKILL_LVL, $this::MAX_SKILL_LVL ) <= $skill_level ) {
			$goal = true;
		}

		return $goal;
	}
}