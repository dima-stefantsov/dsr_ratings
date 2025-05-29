<?php

$GLOBALS['rating_calculators']['commanders_3v3_team'] = [
    'description' => '
        team commanders_3v3

        same as <a href="/rating/commanders_3v3">commanders_3v3</a>, but only for team games with teammate(s) you often play with
        games you played with random pubs do not affect this rating

        now you can play some occasional solo without having to fear losing rating: your team rating is safe here!
        ',
    'default_rating' => $GLOBALS['rating_calculators']['commanders_3v3']['default_rating'],
    'fields' => $GLOBALS['rating_calculators']['commanders_3v3']['fields'],
    'main' => 'dsr_commanders_3v3_team__main',
    ];

function dsr_commanders_3v3_team__main(&$teams) {
    $teams_with_solo_team_rating = dsr_commanders_3v3_team__get_teams_with_solo_team_rating($teams);

    $GLOBALS['rating_calculators']['commanders_3v3']['main']($teams_with_solo_team_rating);

    dsr_commanders_3v3_team__assign_ratings($teams, $teams_with_solo_team_rating);
}

function dsr_commanders_3v3_team__get_teams_with_solo_team_rating(&$teams) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['commanders_3v3_team']['data']['ratings_solo_team'];
    $teams_with_solo_team_rating = $teams;

    foreach ($teams_with_solo_team_rating as &$team) {
        foreach ($team as &$player) {
            $player['is_solo'] = !dsr__have_teammates_in_game($player, $team);
            $player['rating'] =
                $ratings_solo_team[$player['is_solo']][$player['player_id']] ??
                $GLOBALS['rating_calculators']['commanders_3v3_team']['default_rating'];
            $GLOBALS['rating_calculators']['main_current']['data']['players_games_count'][$player['player_id']] = &$GLOBALS['rating_calculators']['commanders_3v3_team']['data']['solo_team_players_games_count'][$player['is_solo']][$player['player_id']];
        }
    }

    return $teams_with_solo_team_rating;
}

function dsr_commanders_3v3_team__assign_ratings(&$teams, &$teams_with_solo_team_rating) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['commanders_3v3_team']['data']['ratings_solo_team'];

    foreach ($teams_with_solo_team_rating as $team_index => $team) {
        foreach ($team as $player_index => $player) {
            $ratings_solo_team[$player['is_solo']][$player['player_id']] = $player['rating'];

            if (!$player['is_solo']) {
                $teams[$team_index][$player_index]['rating'] = $player['rating'];
            }
        }
    }
}
