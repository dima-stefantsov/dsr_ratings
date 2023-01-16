<?php

$GLOBALS['rating_calculators']['main_v1'] = [
    'description' => '
        DSR rating v1

        legacy

        original DSR rating as it was released, <a href="/changelog/rating-released-in-depth-explained/">changelog post</a>
        ',
    'default_rating' => 2000,
    'fields' => [],
    'main' => 'dsr_main_v1__main',
    ];

function dsr_main_v1__main(&$teams) {
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
            $multiplier = dsr_main_v1__get_multiplier($player['status']);
            $diff = intval($multiplier*$reward[$player['winner_team']]);
            $player['rating'] = max($player['rating'] + $diff, 1);
        }
    }
}

function dsr_main_v1__get_multiplier($status) {
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
