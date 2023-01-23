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
        - mirror matchup gives x2 rating changes

        please give feedback on it in <a target="_blank" href="https://discord.gg/KXKw8HqKKK">discord</a>
        ',
    'default_rating' => 2000,
    'fields' => ['gp.race'],
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
    $full_mirror_multiplier = dsr_main_next__get_full_mirror_multiplier($teams);

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
            $status_multiplier = dsr_main_next__get_status_multiplier($player['status']);

            $reward = $rewards[$player['winner_team']];
            if ($player['team'] === $player['winner_team'] && $player['status'] !== 'win') {
                $reward = $rewards[$player['winner_team']+1];
            }

            $diff = $reward*$full_mirror_multiplier*$status_multiplier;
            if ($player['rating'] + $diff < 20000) {
                $diff = max(1, floor($reward*$full_mirror_multiplier/10))*10 * $status_multiplier;
            }

            $player['rating'] = max(1, $player['rating'] + intval($diff));
        }
    }
}

function dsr_main_next__get_full_mirror_multiplier(&$teams) {
    $races0 = [];
    foreach ($teams[0] as $player) {
        $races0[] = $player['race'];
    }
    $races1 = [];
    foreach ($teams[1] as $player) {
        $races1[] = $player['race'];
    }

    if (array_diff($races0, $races1) === []) {
        if ($races0[0] === $races1[0] && ($races0[1]??false) === ($races1[1]??false) && ($races0[2]??false) === ($races1[2]??false)) {
            return 2;
        }

        array_unshift($races1, array_pop($races1));
        if ($races0[0] === $races1[0] && ($races0[1]??false) === ($races1[1]??false) && ($races0[2]??false) === ($races1[2]??false)) {
            return 2;
        }

        array_unshift($races1, array_pop($races1));
        if ($races0[0] === $races1[0] && ($races0[1]??false) === ($races1[1]??false) && ($races0[2]??false) === ($races1[2]??false)) {
            return 2;
        }
    }

    return 1;
}

function dsr_main_next__get_status_multiplier($status) {
    if ($status === 'win') {
        return 1;
    }

    return -1;
}
