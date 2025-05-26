<?php

$GLOBALS['rating_calculators']['_memoria_matchups'] = [
    'description' => '
        by <a href="/player/125980">Memoria</a>: per-matchup calculation v4
        
        
        - games under 30 seconds don\'t count, games under 4 minutes have lowered rating change (x0.5 >> x1)
        - players start at 2500 rating, 4000 rating difference ≈ 90% win prediction, prediction gets more exagerated the more onesided the matchup is
        - max 250 rating per match, decreases if teammate rating difference for either of the teams is more than 2500
        - win prediction is calculated for each matchup (each 1v1), then it\'s combined into a final prediction
        - in case of an uneven game (NvM), each possible matchup is calculated, and rating change is adjusted to the player count ratio
        - if someone crashes before the game starts, that player loses some rating, but is not considered for matchup calculations (as if the player was not in the game)
        - afterwards, if there are leavers, respective team\'s rating gain from winning will be lower; if two people from opposite teams leave within 10 seconds, or if the first leave is within 1 minute of the game ending, rating gain stays the same
        ',
    'default_rating' => 2500,
    'fields' => [
        'g.duration', 'gp.status_timing'
    ],
    'main' => '_memoria_matchups__main',
    ];

function _memoria_matchups__main(&$teams) {

    $game_duration_seconds = floor($teams[0][0]['duration'] / 22.4);
    if ($game_duration_seconds < 30) {
        return;
    }

    //removes crashed players from calculations, flags leavers
    $team_shifted = [[]];
    $leavers = [[]];
    $j = 0;
    $i = 0;
    foreach ($teams as &$team) {
        $k = 0;
        foreach ($team as &$player) {
            if ($player['status_timing'] != '0') {
                $team_shifted[$j][$k] = $player['rating'];
                $k++;
            }
            if ($player['status_timing'] > '0') {
                $leavers[$i][0] = $player['status_timing'];
                $leavers[$i][1] = $player['team'];
                $i++;
            }
        }
        $j++;
    }

    //adjust teams' rating gain based on first 1-2 leavers
    $team_leaver_multiplier = [0, 1];
    if ($i != 0) {
        array_multisort($leavers, SORT_ASC, $leavers);
        $apply = true;
        if(count($leavers) >= 2) {
            if (($leavers[0][0] + 224 > $leavers[1][0] && $leavers[0][1] !== $leavers[1][1]) || ($leavers[1][0] + 1344 >= $teams[0][0]['duration'])) {
                $apply = false;
            }
        }
        if ($apply) {
            $team_leaver_multiplier[0] = $leavers[0][1];
            $team_leaver_multiplier[1] = (count($team_shifted[$leavers[0][1]]) - 1) / count($team_shifted[$leavers[0][1]]);    
        }
    }

    //adjusts rating gain based on player count difference and match result
    $team_ratio_multiplier = [0, 1];
    if (count($team_shifted[0]) > count($team_shifted[1])) {
        $team_ratio_multiplier[0] = 1;
        $team_ratio_multiplier[1] = count($team_shifted[1]) / count($team_shifted[0]);
    }
    elseif (count($team_shifted[0]) < count($team_shifted[1])) {
        $team_ratio_multiplier[0] = 0;
        $team_ratio_multiplier[1] = count($team_shifted[0]) / count($team_shifted[1]);
    }

    $factor = 4000;
    $matchup_predictions = [];
    $matchup_predictions_weight = [];
    $matchup_predictions_weight_total = 0;
    $matchup_counter = 0;
    if (count($team_shifted[0]) === count($team_shifted[1])) {
        for ($i = 0; $i < count($team_shifted[0]); $i++) {
            //modified elo equation, making it 90%/1:9 instead of 1:10 per n factor
            $abs_team_shifted = abs($team_shifted[0][$i] - $team_shifted[1][$i]);
            $elo_part = abs(($team_shifted[0][$i] - $team_shifted[1][$i]) / (($factor + $abs_team_shifted / (20 ** ($abs_team_shifted / $factor)))));
            $matchup_predictions[$i] = floor(1000 / (1 + 10 ** $elo_part)) / 1000;
            //percentage exageration in even team games
            if (count($teams[0]) > 1) {
                $matchup_predictions[$i] -= $matchup_predictions[$i] ** (2 / (0.02 + $elo_part));
                $matchup_predictions[$i] = max(0, $matchup_predictions[$i]);
            }

            if ($team_shifted[0][$i] > $team_shifted[1][$i]) {
                $matchup_predictions[$i] = 1 - $matchup_predictions[$i];
            }
            //assigns matchup prediction weight in the final prediction
            $matchup_predictions_weight[$i] = ((abs(0.5 - $matchup_predictions[$i]) + 1.8) * 1.5) ** ($elo_part + max($team_shifted[0][$i], $team_shifted[1][$i]) / $factor);
            $matchup_predictions_weight_total += $matchup_predictions_weight[$i];
            $matchup_counter++;
        }
    }
    else {
        for ($team2_player = 0; $team2_player < count($team_shifted[1]); $team2_player++) {
            for ($team1_player = 0; $team1_player < count($team_shifted[0]); $team1_player++) {
                $abs_team_shifted = abs($team_shifted[0][$team1_player] - $team_shifted[1][$team2_player]);
                $elo_part = abs(($team_shifted[0][$team1_player] - $team_shifted[1][$team2_player]) / (($factor + $abs_team_shifted / (20 ** ($abs_team_shifted / $factor)))));
                $matchup_predictions[$matchup_counter] = floor(1000 / (1 + 10 ** $elo_part)) / 1000;
                $matchup_predictions[$matchup_counter] += $matchup_predictions[$matchup_counter] ** (1.9 / (0.02 + $elo_part));
                $matchup_predictions[$matchup_counter] = min(0.5, $matchup_predictions[$matchup_counter]);

                if ($team_shifted[0][$team1_player] > $team_shifted[1][$team2_player]) {
                    $matchup_predictions[$matchup_counter] = 1 - $matchup_predictions[$matchup_counter];
                }

                $matchup_predictions_weight[$matchup_counter] = ((abs(0.5 - $matchup_predictions[$matchup_counter]) + 1.5) * 1.85) ** ($elo_part + max($team_shifted[0][$team1_player], $team_shifted[1][$team2_player]) / $factor);
                $matchup_predictions_weight_total += $matchup_predictions_weight[$matchup_counter];
                $matchup_counter++;
            }
        }
    }

    $prediction = 0;
    for ($i = 0; $i < $matchup_counter; $i++) {
        $normalised_weight_mult = $matchup_predictions_weight[$i] / $matchup_predictions_weight_total;
        $prediction += $matchup_predictions[$i] * $normalised_weight_mult;
    }

    //modifies maximum rating change based on biggest rating difference between team members once over 2500, and game length below 4 minutes
    $max_reward = 250 / max(1, max((max($team_shifted[0]) - min($team_shifted[0])), (max($team_shifted[1]) - min($team_shifted[1]))) / ($factor * 0.875) +  0.2856);
    $gametime_multiplier = max(1, (420 - ($game_duration_seconds - 30)) / 210);

    $reward = [];
    $reward[0] = round($max_reward * (1 - $prediction));
    $reward[1] = round($max_reward * $prediction);

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $status_multiplier = $player['status'] === 'win' ? ($player['team'] === $team_leaver_multiplier[0] ? $team_leaver_multiplier[1] : 1) : -1;
            $team_multiplier = $player['status'] === 'win' ? ($player['team'] === $team_ratio_multiplier[0] ? 1 : $team_ratio_multiplier[1]) : ($player['team'] === $team_ratio_multiplier[0] ? -1 : $team_ratio_multiplier[1]);
            $diff = $status_multiplier * $team_multiplier / $gametime_multiplier * $reward[$player['winner_team']];
            if ($player['status_timing'] === '0') $diff = $max_reward * -0.8;
            $player['rating'] = max(1, $player['rating'] + intval($diff));
        }
    }
}
