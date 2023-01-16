<?php

// $GLOBALS['rating_calculators']['main_current'] = [
$GLOBALS['rating_calculators']['main_next'] = [
    'description' => '
        DSR rating vNext candidate

        compared to <a href="/rating/main_current">main_current</a> it has following changes:
        - no extra penalties for leave/afk
        - team rating = max player rating
        - uncompressing before comparisons
        - fix 2k-border gravity bug
        - improved rewards calculations

        please give feedback on it in <a target="_blank" href="https://discord.gg/KXKw8HqKKK">discord</a>
        ',
    'default_rating' => 2000,
    'fields' => [],
    'main' => 'dsr_main_next__main',
    ];

function dsr_main_next__main(&$teams) {
    dsr_main_next__uncompress_rating($teams);
    dsr_main_next__update_ratings($teams);
    dsr_main_next__compress_rating($teams);
}


function dsr_main_next__uncompress_rating(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            if ($player['rating'] > 2000) {
                $player['rating'] = $player['rating'] + 18000;
            }
            else {
                $player['rating'] = intval($player['rating']*10);
            }
        }
    }
}

function dsr_main_next__compress_rating(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            if ($player['rating'] > 20000) {
                $player['rating'] = $player['rating'] - 18000;
            }
            else {
                $player['rating'] = intval(round($player['rating']/10));
            }
        }
    }
}

function dsr_main_next__update_ratings(&$teams) {
    $factor = 4000;

    $team_ratings = [];
    $team_ratings[0] = from($teams[0])->max('$v["rating"]');
    $team_ratings[1] = from($teams[1])->max('$v["rating"]');

    $rewards = [];
    $rewards[0] = max(1, floor(100 / (1 + 10**(($team_ratings[0] - $team_ratings[1])/$factor))));
    $rewards[1] = 100 - $rewards[0];
    $rewards[2] = $rewards[0];

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $multiplier = dsr_main_next__get_multiplier($player['status']);

            $reward = $rewards[$player['winner_team']];
            if ($player['team'] === $player['winner_team'] && $player['status'] !== 'win') {
                $reward = $rewards[$player['winner_team']+1];
            }

            $diff = intval($multiplier*$reward);
            if ($player['rating'] + $diff < 20000) {
                $diff = intval($multiplier*max(10, min(90, $reward)));
            }

            $player['rating'] = max(1, $player['rating'] + $diff);
        }
    }
}

function dsr_main_next__get_multiplier($status) {
    if ($status === 'win') {
        return 1;
    }

    return -1;
}
