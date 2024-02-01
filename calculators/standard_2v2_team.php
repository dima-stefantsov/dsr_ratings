<?php

$GLOBALS['rating_calculators']['standard_2v2_team'] = [
    'description' => '
        team standard_2v2

        same as <a href="/rating/standard_2v2">standard_2v2</a>, but only for team games with a teammate you often play with
        games you played with a random pub do not affect this rating

        now you can play some occasional solo without having to fear losing rating: your team rating is safe here!
        ',
    'default_rating' => $GLOBALS['rating_calculators']['standard_2v2']['default_rating'],
    'fields' => $GLOBALS['rating_calculators']['standard_2v2']['fields'],
    'main' => 'dsr_standard_2v2_team__main',
    ];

function dsr_standard_2v2_team__main(&$teams) {
    $teams_with_solo_team_rating = dsr_standard_2v2_team__get_teams_with_solo_team_rating($teams);

    $GLOBALS['rating_calculators']['standard_2v2']['main']($teams_with_solo_team_rating);

    dsr_standard_2v2_team__assign_ratings($teams, $teams_with_solo_team_rating);
}

function dsr_standard_2v2_team__get_teams_with_solo_team_rating(&$teams) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['standard_2v2_team']['data']['ratings_solo_team'];
    $teams_with_solo_team_rating = $teams;

    foreach ($teams_with_solo_team_rating as &$team) {
        foreach ($team as &$player) {
            $player['is_solo'] = !dsr__have_teammates_in_game($player, $team);
            $player['rating'] =
                $ratings_solo_team[$player['is_solo']][$player['player_id']] ??
                $GLOBALS['rating_calculators']['standard_2v2_team']['default_rating'];
        }
    }

    return $teams_with_solo_team_rating;
}

function dsr_standard_2v2_team__assign_ratings(&$teams, &$teams_with_solo_team_rating) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['standard_2v2_team']['data']['ratings_solo_team'];

    foreach ($teams_with_solo_team_rating as $team_index => $team) {
        foreach ($team as $player_index => $player) {
            $ratings_solo_team[$player['is_solo']][$player['player_id']] = $player['rating'];

            if (!$player['is_solo']) {
                $teams[$team_index][$player_index]['rating'] = $player['rating'];
            }
        }
    }
}
