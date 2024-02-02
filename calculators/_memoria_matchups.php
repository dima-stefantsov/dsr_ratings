<?php

$GLOBALS['rating_calculators']['_memori_matchups'] = [
    'description' => '
        by <a href="/player/125980">Memoria</a>: per-matchup calculation v1
        
        - only 1v1, 2v2, and 3v3
        - games under 4 minutes don\'t count
        - players start at 2000 rating, 4000 rating difference = 90% win prediction, max 100 rating per match
        - win prediction is calculated for each matchup (1v1), then it\'s combined into a final prediction 
        ',
    'default_rating' => 2000,
    'fields' => [
        'g.duration',
    ],
    'main' => '_memori_matchups__main',
    ];

function _memori_matchups__main(&$teams) {
    if (count($teams[0]) != count($teams[1])) {
        return;
    }
    $game_duration_seconds = floor($teams[0][0]['duration']/22.4);
    if ($game_duration_seconds < 240) {
        return;
    }

    $player_count = count($teams[0]);
    $factor = 4000;
    $matchup_predictions = [];
    $matchup_predictions_weight = [];
    $matchup_predictions_weight_total = 0;
    for ($i = 0; $i < $player_count; $i++) {
        $matchup_predictions[$i] = 1 - (round(100 / (1 + 10 ** (($teams[0][$i]['rating'] - $teams[1][$i]['rating']) / $factor)))) / 100;
        $matchup_predictions_weight[$i] = ((abs(0.5 - $matchup_predictions[$i]) + 0.03 ) * 2.5) ** 1.6;
        $matchup_predictions_weight_total += $matchup_predictions_weight[$i];
    }
    array_multisort($matchup_predictions_weight, SORT_DESC, $matchup_predictions);

    $prediction = 0;
    $normalised_weight_mult_total = 0;
    for ($i = 0; $i < $player_count; $i++) {
        if ($matchup_predictions_weight_total == 0) {
            $prediction = 0.5;
            break;
        }
        if ($matchup_predictions[$i] == 0) {
            if ($player_count == 2) { 
                $normalised_weight_mult = $matchup_predictions_weight_total - $normalised_weight_mult_total; 
            }
            else {
            $normalised_weight_mult = ($matchup_predictions_weight_total - $normalised_weight_mult_total) / (3 - $i);
            }
        }
        else {
            $normalised_weight_mult = $matchup_predictions_weight[$i] / $matchup_predictions_weight_total;
        }
        $normalised_weight_mult_total += $normalised_weight_mult;
        $prediction += $matchup_predictions[$i] * $normalised_weight_mult;
    }

    $reward = [];
    $reward[0] = intval(100 * (1 - ($prediction)));
    $reward[1] = 100 - $reward[0];

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $multiplier = ($player['status'] === 'win' ? 1 : -1);
            $diff = ($multiplier*$reward[$player['winner_team']]);
            $player['rating'] = max(1, $player['rating'] + intval($diff));
        }
    }
}
