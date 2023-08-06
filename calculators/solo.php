<?php

$GLOBALS['rating_calculators']['solo'] = [
    'description' => '
        solo main_current

        same as <a href="/rating/main_current">main_current</a>, but only for solo games with random teammates or alone
        games with teammates you often play with do not affect this rating
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $GLOBALS['rating_calculators']['main_current']['fields'],
    'main' => 'dsr_solo__main',
    ];

function dsr_solo__main(&$teams) {
    $teams_with_solo_team_rating = dsr_solo__get_teams_with_solo_team_rating($teams);

    $GLOBALS['rating_calculators']['main_current']['main']($teams_with_solo_team_rating);

    dsr_solo__assign_ratings($teams, $teams_with_solo_team_rating);
}

function dsr_solo__get_teams_with_solo_team_rating(&$teams) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['solo']['data']['ratings_solo_team'];
    $teams_with_solo_team_rating = $teams;

    foreach ($teams_with_solo_team_rating as &$team) {
        foreach ($team as &$player) {
            $player['is_solo'] = !dsr__have_teammates_in_game($player, $team);
            $player['rating'] =
                $ratings_solo_team[$player['is_solo']][$player['player_id']] ??
                $GLOBALS['rating_calculators']['solo']['default_rating'];
        }
    }

    return $teams_with_solo_team_rating;
}

function dsr_solo__assign_ratings(&$teams, &$teams_with_solo_team_rating) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['solo']['data']['ratings_solo_team'];

    foreach ($teams_with_solo_team_rating as $team_index => $team) {
        foreach ($team as $player_index => $player) {
            $ratings_solo_team[$player['is_solo']][$player['player_id']] = $player['rating'];

            if ($player['is_solo']) {
                $teams[$team_index][$player_index]['rating'] = $player['rating'];
            }
        }
    }
}
