<?php

$GLOBALS['rating_calculators']['_memoria_matchups'] = [
    'description' => '
        by <a href="/player/125980">Memoria</a>: per-matchup calculation v2
        
        - only 1v1, 2v2, and 3v3
        - games under 4 minutes don\'t count
        - players start at 2000 rating, 4000 rating difference ≈ 90% win prediction
        - max 250 rating per match, or less if players with 5000+ or 1500- rating are present
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
        $matchup_predictions[$i] = floor(1000 / (1 + 10 ** $elo_part)) / 1000;
        if (count($teams[0]) > 1) {
            $matchup_predictions[$i] -= $matchup_predictions[$i] ** (2 / (0.05 + $elo_part));
            $matchup_predictions[$i] = max(0, $matchup_predictions[$i]);
        }

        if ($teams[0][$i]['rating'] > $teams[1][$i]['rating']) {
            $matchup_predictions[$i] = 1 - $matchup_predictions[$i];
        }

        $matchup_predictions_weight[$i] = ((abs(0.5 - $matchup_predictions[$i]) + 1) * 3) ** ($elo_part/1.25 + max($teams[0][$i]['rating'], $teams[1][$i]['rating'])/$factor);
        $matchup_predictions_weight_total += $matchup_predictions_weight[$i];
    }

    $prediction = 0;
    for ($i = 0; $i < count($teams[0]); $i++) {
        $normalised_weight_mult = $matchup_predictions_weight[$i] / $matchup_predictions_weight_total;
        $prediction += $matchup_predictions[$i] * $normalised_weight_mult;
    }

    $max_reward = 250 / max(1, 
    max(1.25, from($teams[0])->max('$v["rating"]') / $factor, from($teams[1])->max('$v["rating"]') / $factor) - 0.25,
    max(1, $factor / (from($teams[0])->min('$v["rating"]') + 2500), $factor / (from($teams[1])->min('$v["rating"]') + 2500)));
    $reward = [];
    $reward[0] = round($max_reward * (1 - $prediction));
    $reward[1] = round($max_reward * $prediction);

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $multiplier = $player['status'] === 'win' ? 1 : -1;
            $diff = $multiplier * $reward[$player['winner_team']];
            $player['rating'] = max(1, $player['rating'] + intval($diff));
        }
    }
}
