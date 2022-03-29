<?php

namespace FootballSimulator;

class ProbabilityCalculator
{
    private array $allVariations = [];
    private array $winProbabilities = [];
    private $scheduler = [];

    private array $remainingMatches = []; // Array<WP_Post>
    private array $teamsInfo;

    private array $set = ['W', 'D', 'L'];

	public const START_CALC_WEEK = 4;

	function __construct($teamsInfo, $currentWeek) {
        $this->teamsInfo = $teamsInfo; // Current table info array
	    $this->scheduler = new Scheduler();
        $weeksRemaining = fs_get_remaining_weeks($currentWeek);

        // Getting remaining matches
        $metaQuery = ['relation' => 'OR'];
        for ($i = $currentWeek + 1; $i <= $currentWeek + $weeksRemaining; $i++) {
            $metaQuery[] = [
                'key' => 'match_week',
                'compare' => '=',
                'value' => $i,
            ];            
        }
        $this->remainingMatches = get_posts([
            'numberposts' => -1,
            'post_type' => 'matches',
            'meta_query' => $metaQuery
        ]);
    }

    public function getWinProbabilities() {
        $this->getAllVariations(count($this->remainingMatches));

        foreach ($this->allVariations as $variation) {
            $this->getVariationChampion($variation);
        }

        foreach ($this->winProbabilities as $teamID => $probableWin) {
            $this->winProbabilities[$teamID] = $probableWin / count($this->allVariations) * 100;
        }
        
        return $this->winProbabilities;
    }

    // May be multiple
    private function getVariationChampion($variation)
    {
        $variationTeamsInfo = $this->teamsInfo;
        $variationArr = str_split($variation);

        for ($i = 0; $i < count($this->remainingMatches); $i++) {
            switch ($variationArr[$i]) {
                case 'W':
                    $variationTeamsInfo[$this->remainingMatches[$i]->match_home_team]['pts'] += 3;
                    break;
                case 'D':
                    $variationTeamsInfo[$this->remainingMatches[$i]->match_home_team]['pts'] += 1;
                    $variationTeamsInfo[$this->remainingMatches[$i]->match_away_team]['pts'] += 1;
                    break;
                case 'L':
                    $variationTeamsInfo[$this->remainingMatches[$i]->match_away_team]['pts'] += 3;
                    break;
            }
        }

        $variationTeamsInfo = $this->scheduler->getSortedTableInfo($variationTeamsInfo);

        if (empty($this->winProbabilities[$variationTeamsInfo[0]['post']->ID])) {
            $this->winProbabilities[$variationTeamsInfo[0]['post']->ID] = 0;
        }
        $this->winProbabilities[$variationTeamsInfo[0]['post']->ID] += 1;
    }

    private function getAllVariations($matchesLeft, $prefix = '')
    {
        $n = count($this->set);
        
        if ($matchesLeft == 0) {
            $this->allVariations[] = $prefix;
            return;
        }
        
        for ($i = 0; $i < $n; $i++) {

            // Next character of input added
            $newPrefix = $prefix . $this->set[$i];
            
            $this->getAllVariations($matchesLeft - 1, $newPrefix);
        }
    }
}