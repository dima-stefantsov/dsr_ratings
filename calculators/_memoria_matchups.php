<?php

$GLOBALS['rating_calculators']['_memoria_matchups'] = [
    'description' => '
        by <a href="/player/125980">Memoria</a>: per-matchup calculation v1
        
        - only 1v1, 2v2, and 3v3
        - games under 4 minutes don\'t count
        - players start at 2000 rating, 4000 rating difference = 90% win prediction, max 250 rating per match
        - win prediction is calculated for each matchup (1v1), then it\'s combined into a final prediction 
        ',
    'default_rating' => 2000,
    'fields' => [
        'g.duration',
    ],
    'main' => '_memoria_matchups__main',
    ];

function _memoria_matchups__main(&$teams) {
    if (count($teams[0]) !== count($teams[1])) {
        return;
    }
    
    $game_duration_seconds = floor($teams[0][0]['duration'] / 22.4);
    if ($game_duration_seconds < 240) {
        return;
    }
    
    $factor = 4000;
    $matchup_predictions = [];
    $matchup_predictions_weight = [];
    $matchup_predictions_weight_total = 0;
    for ($i = 0; $i < count($teams[0]); $i++) {
        $elo_part = abs(($teams[0][$i]['rating'] - $teams[1][$i]['rating']) / $factor);
        $matchup_predictions[$i] = floor(1000 / (1 + 10 ** ($elo_part))) / 1000;
		
        if ($teams[0][$i]['rating'] > $teams[1][$i]['rating']) {
            $matchup_predictions[$i] = 1 - $matchup_predictions[$i];
        }
        
        $matchup_predictions_weight[$i] = ((abs(0.5 - $matchup_predictions[$i]) + 0.015) * 6) ** ($elo_part * 1.5);
        $matchup_predictions_weight_total += $matchup_predictions_weight[$i];
    }
    
    $prediction = 0;
    for ($i = 0; $i < count($teams[0]); $i++) {
        $normalised_weight_mult = $matchup_predictions_weight[$i] / $matchup_predictions_weight_total;
        $prediction += $matchup_predictions[$i] * $normalised_weight_mult;
    }
    
    $reward = [];
    $reward[0] = intval(250 * (1 - ($prediction)));
    $reward[1] = 250 - $reward[0];
    
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $multiplier = ($player['status'] === 'win' ? 1 : -1);
            $diff = ($multiplier * $reward[$player['winner_team']]);
            $player['rating'] = max(1, $player['rating'] + intval($diff));
        }
    }
}
