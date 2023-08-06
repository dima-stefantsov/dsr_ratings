<?php

$GLOBALS['rating_calculators']['main_current'] = [
    'description' => '
        DSR rating v3 (current)

        default rating algorithm, used for most DSR rating tasks

        compared to <a href="/rating/main_v2">main_v2</a> it has following changes:
        - no extra penalties for leave/afk
        - first player to leave/afk forfeits the win for his team
        - team rating = max player rating
        - uncompressing ratings before comparisons
        - fixed 2k-border gravity bug
        - compression now starts below 1000 rating
        - improved rewards calculations
        - fixed team1 and team2 rewards rounded differently
        - teams mirror matchup gives x2 rating changes
        ',
    'default_rating' => 2000,
    'fields' => ['gp.race', 'gp.status_timing'],
    'main' => 'dsr_main_current__main',
    ];

function dsr_main_current__main(&$teams) {
    dsr_main_current__uncompress_rating($teams);
    dsr_main_current__update_ratings($teams);
    dsr_main_current__compress_rating($teams);
}

function dsr_main_current__uncompress_rating(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            if ($player['rating'] > 1000) {
                $player['rating'] = $player['rating'] + 9000;
            }
            else {
                $player['rating'] = intval($player['rating']*10);
            }
        }
    }
}

function dsr_main_current__compress_rating(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            if ($player['rating'] > 10000) {
                $player['rating'] = $player['rating'] - 9000;
            }
            else {
                $player['rating'] = intval(round($player['rating']/10));
            }
        }
    }
}

function dsr_main_current__update_ratings(&$teams) {
    $full_mirror_multiplier = dsr_main_current__get_full_mirror_multiplier($teams);

    $team_ratings = [];
    $team_ratings[0] = from($teams[0])->max('$v["rating"]');
    $team_ratings[1] = from($teams[1])->max('$v["rating"]');

    $team_forfeit =
        from($teams)->
        selectmany()->
        where('$v["status_timing"] != -1')->
        orderby('$v["status_timing"]')->
        select('$v["team"]')->
        firstordefault(false);

    $rewards = [];
    $rewards[0] = round(100 / (1 + 10**(($team_ratings[0] - $team_ratings[1])/4000)));
    $rewards[1] = 100 - $rewards[0];
    $rewards[2] = $rewards[0];

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $status_multiplier = dsr_main_current__get_status_multiplier($player['status']);

            $reward = $rewards[$player['winner_team']];
            if ($player['team'] === $player['winner_team'] && $player['status'] !== 'win') {
                $reward = $rewards[$player['winner_team']+1];
            }

            $diff = $reward*$full_mirror_multiplier*$status_multiplier;
            if ($player['rating'] + $diff < 10000) {
                $diff = max(1, floor($reward*$full_mirror_multiplier/10))*10 * $status_multiplier;
            }

            // once a game was forfeited,
            // members of forfeited team can't get more than 0, but can lose full
            // members of remaining team can't lose more than 0, but can win full
            if ($team_forfeit !== false) {
                if ($player['team'] === $team_forfeit) {
                    $diff = min(0, $diff);
                }
                else {
                    $diff = max(0, $diff);
                }
            }


            $player['rating'] = max(10, $player['rating'] + intval($diff));
        }
    }
}

function dsr_main_current__get_full_mirror_multiplier(&$teams) {
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

function dsr_main_current__get_status_multiplier($status) {
    if ($status === 'win') {
        return 1;
    }

    return -1;
}
