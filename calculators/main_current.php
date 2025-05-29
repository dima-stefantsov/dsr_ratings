<?php

$GLOBALS['rating_calculators']['main_current'] = [
    'description' => '
        DSR rating v4 (current)

        default rating algorithm, used for most DSR rating tasks

        compared to <a href="/rating/main_v3">main_v3</a> it has following changes:
        - proper rounding, now wins get you +0 when rating difference is larger than 7982; previously this threshold was at 9195 rating difference
        - proper mirror detection, now 3v3 requires exact mirror, this would mostly affect standard; mirror matches give x2 rating change
        - first 25 games now give x10->x1 rating change, diminishing linearly every game; this would mostly affect new accounts; given not everyone is uploader, this would assign more suitable rating for non-uploaders sooner, resulting in more correct ratings rewards when playing against non-uploaders; and this change especially boosts <a href="/ratings">Ratings</a> such as <a href="/rating/commanders_1v1_abathur">commanders_1v1_abathur</a>, which is rarely played, which resulted in your opponents being considered ~2000 most of the time. 25 wins in a row on a new account against ~2000-rated opponents would put you at ~5500 rating.
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
                $player['rating'] = intval($player['rating']/10);
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

    $team_ratings_diff = $team_ratings[0] - $team_ratings[1];
    $team_ratings_diff_is_negative = $team_ratings_diff < 0;
    if ($team_ratings_diff_is_negative) {
        $team_ratings_diff *= -1;
    }
    $rewards = [];
    $rewards[0] = $team_ratings_diff_is_negative ?
        100 - floor(100 / (1 + 10**($team_ratings_diff/4000))) :
        floor(100 / (1 + 10**($team_ratings_diff/4000)));
    $rewards[1] = 100 - $rewards[0];
    $rewards[2] = $rewards[0];

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $games_played_multiplier = dsr_main_current__get_games_played_multiplier($player);
            $status_multiplier = dsr_main_current__get_status_multiplier($player);

            $reward = $rewards[$player['winner_team']];
            if ($player['team'] === $player['winner_team'] && $player['status'] !== 'win') {
                $reward = $rewards[$player['winner_team']+1];
            }

            $diff = $reward*$full_mirror_multiplier*$games_played_multiplier*$status_multiplier;
            if ($player['rating'] + $diff < 10000) {
                $diff = ceil($reward*$full_mirror_multiplier*$games_played_multiplier/10)*10 * $status_multiplier;
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
    $count_team0 = count($teams[0]);
    if ($count_team0 !== count($teams[1])) {
        return 1;
    }

    $races0 = [];
    foreach ($teams[0] as $player) {
        $races0[] = $player['race'];
    }
    $races1 = [];
    foreach ($teams[1] as $player) {
        $races1[] = $player['race'];
    }

    if ($races0[0] === $races1[0] && ($races0[1]??false) === ($races1[1]??false) && ($races0[2]??false) === ($races1[2]??false)) {
        return 2;
    }
    if ($count_team0 === 2) {
        if ($races0[0] === $races1[1] && $races0[1] === $races1[0]) {
            return 2;
        }
    }

    return 1;
}

function dsr_main_current__get_games_played_multiplier(&$player) {
    $player_games_count = &$GLOBALS['rating_calculators']['main_current']['data']['players_games_count'][$player['player_id']];
    $player_games_count++;
    if ($player_games_count > 25) {
        return 1;
    }

    // $games_limit = 25;
    // $max_multiplier = 10;
    // $games_played_multiplier = 1 + ($max_multiplier-1)*($games_limit + 1 - $player_games_count)/$games_limit;
    return 10.36 - 0.36*$player_games_count;
}

function dsr_main_current__get_status_multiplier(&$player) {
    if ($player['status'] === 'win') {
        return 1;
    }

    return -1;
}
