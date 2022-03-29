<?php

namespace FootballSimulator;

class Scheduler {

	private array $defaultInfo = [
		'pts'       => 0,
		'w'         => 0,
		'd'         => 0,
		'l'         => 0,
		'scored'    => 0,
		'conceded'  => 0,
		'goad_diff' => 0,
	];

	private const WIN = 3;
	private const DRAW = 1;

	public function getSortedTableInfo($teams_info) {
		array_multisort(
			array_column($teams_info, 'pts'), SORT_DESC,
			array_column($teams_info, 'goad_diff'), SORT_DESC,
			array_column($teams_info, 'scored'), SORT_DESC,
			array_column($teams_info, 'conceded'), SORT_DESC,
			$teams_info
		);

		return $teams_info;
	}

	public function getTeamsInfo( $current_week_matches, $current_week ) {
		$teams = [];
		foreach ( $current_week_matches as $current_week_match ) {
			$teams[] = get_post( $current_week_match->match_home_team );
			$teams[] = get_post( $current_week_match->match_away_team );
		}

		$teams_info = [];
		foreach ( $teams as $team ) {
			$teams_info[ $team->ID ] = $this->getTeamInfo( $team, $current_week );
		}

		return $teams_info;
	}

	private function getTeamInfo( $team, $current_week ) {
		$info = $this->defaultInfo;
		$info['post'] = $team;

		$matches = get_posts( [
			'numberposts' => - 1,
			'post_type'   => 'matches',
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => 'match_week',
					'compare' => '<=',
					'value'   => $current_week,
				],
				[
					'relation' => 'OR',
					[
						'key'     => 'match_home_team',
						'compare' => '=',
						'value'   => $team->ID,
					],
					[
						'key'     => 'match_away_team',
						'compare' => '=',
						'value'   => $team->ID,
					]

				]
			]
		] );

		foreach ( $matches as $match ) {

			// Counting goals considering it's home or away
			if ( $match->match_home_team == $team->ID ) {
				$scored   = $match->match_home_team_goals;
				$conceded = $match->match_away_team_goals;
			} else {
				$scored   = $match->match_away_team_goals;
				$conceded = $match->match_home_team_goals;
			}
			$info['scored']   += $scored;
			$info['conceded'] += $conceded;

			// Adding points
			if ( $scored > $conceded ) {
				$info['w'] ++;
				$info['pts'] += $this::WIN;
			} else if ( $scored == $conceded ) {
				$info['d'] ++;
				$info['pts'] += $this::DRAW;
			} else {
				$info['l'] ++;
			}
		}

		$info['goad_diff'] = $info['scored'] - $info['conceded'];

		return $info;
	}

	public function createMatches( $teams ) {
		foreach ( $this->generateMatchesSchedule( $teams ) as $i => $round ) {
			foreach ( $round as $match ) {

				// Create a match post
				$post_id = wp_insert_post( array(
					'post_type'    => 'matches',
					'post_title'   => $match['match_home_team']->post_title . ' - ' . $match['match_away_team']->post_title,
					'post_content' => '',
					'post_status'  => 'publish',
				) );

				// Insert a match meta
				if ( $post_id ) {
					update_post_meta( $post_id, 'match_away_team', $match['match_away_team']->ID );
					update_post_meta( $post_id, 'match_home_team', $match['match_home_team']->ID );
					update_post_meta( $post_id, 'match_week', $i );
				}
			}
		}
	}

	private function generateMatchesSchedule( $teams ): array {
		$rounds      = [];
		$teams_count = count( $teams );

		for ( $round = 1; $round < $teams_count; ++ $round ) {
			for ( $i = 0; $i < $teams_count / 2; ++ $i ) {
				$opponent = $teams_count - 1 - $i;

				// Home match
				$rounds[ $round ][] = [
					'match_home_team' => $teams[ $i ],
					'match_away_team' => $teams[ $opponent ]
				];

				// Away match
				$rounds[ $round + $teams_count - 1 ][] = [
					'match_home_team' => $teams[ $opponent ],
					'match_away_team' => $teams[ $i ]
				];
			}
			$teams = $this->rotateCompetitors( $teams ); // rotate all competitors but the first one
		}

		return $rounds;
	}

	private function rotateCompetitors( $teams ): array {
		$result = $teams;

		$tmp = $result[ count( $result ) - 1 ];
		for ( $i = count( $result ) - 1; $i > 1; -- $i ) {
			$result[ $i ] = $result[ $i - 1 ];
		}
		$result[1] = $tmp;

		return $result;
	}
}