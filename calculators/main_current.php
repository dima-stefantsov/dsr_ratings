<?php

$GLOBALS['rating_calculators']['main_current'] = [
    'description' => '
        DSR rating vCurrent

        default rating algorithm, used for most DSR rating tasks

        calculates player rating according to DSR v2 rating algorithm, <a href="/changelog/rating-rules-ds-loading-screen-multiple-changes/">changelog post</a>
        an improvement over <a href="/rating/main_v1">main_v1</a>
        ',
    'default_rating' => 2000,
    'fields' => [],
    'main' => 'dsr_main_current__main',
    ];

function dsr_main_current__main(&$teams) {
    $players_max = max(count($teams[0]), count($teams[1]));
    $q = $players_max**2;

    $team_rating = [];
    $team_rating[0] = round((from($teams[0])->aggregate(fn($a, $v) => $a + $v['rating']**$q, 0) / count($teams[0]))**(1/$q));
    $team_rating[1] = round((from($teams[1])->aggregate(fn($a, $v) => $a + $v['rating']**$q, 0) / count($teams[1]))**(1/$q));
    $factor = 4000;
    if ($team_rating[0] < 2000 && $team_rating[1] < 2000) {
        $factor = 400;
    }

    $reward = [];
    $reward[0] = max(floor(100 / (1 + 10**(($team_rating[0] - $team_rating[1])/$factor))), 1);
    $reward[1] = 100 - $reward[0];

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $multiplier = dsr_main_current__get_multiplier($player['status']);
            $diff = intval($multiplier*$reward[$player['winner_team']]);
            if ($player['rating'] < 2000) {
                $diff = $diff / 10;
                if ($diff < 0) {
                    $diff = min(-1, intval(ceil($diff)));
                }
                else {
                    $diff = max(1, intval(floor($diff)));
                }
            }
            $player['rating'] = max($player['rating'] + $diff, 1);
        }
    }
}

function dsr_main_current__get_multiplier($status) {
    if ($status === 'lose') {
        return -1;
    }

    if ($status === 'win') {
        return 1;
    }

    if ($status === 'leaver') {
        return -3;
    }

    if ($status === 'afk') {
        return -5;
    }

    return 0;
}
