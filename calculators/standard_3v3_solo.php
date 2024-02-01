<?php

$GLOBALS['rating_calculators']['standard_3v3_solo'] = [
    'description' => '
        solo standard_3v3

        same as <a href="/rating/standard_3v3">standard_3v3</a>, but only for solo games with random teammates
        games with teammates you often play with do not affect this rating

        if you are playing against a team/party, their team rating is used for calculations, therefore even if you lose while being solo, you will not lose much of your rating, and if you happen to win, you win even more!

        now we have answers for people saying "it\'s because you party up, try playing solo"!
        ',
    'default_rating' => $GLOBALS['rating_calculators']['standard_3v3']['default_rating'],
    'fields' => $GLOBALS['rating_calculators']['standard_3v3']['fields'],
    'main' => 'dsr_standard_3v3_solo__main',
    ];

function dsr_standard_3v3_solo__main(&$teams) {
    $teams_with_solo_team_rating = dsr_standard_3v3_solo__get_teams_with_solo_team_rating($teams);

    $GLOBALS['rating_calculators']['standard_3v3']['main']($teams_with_solo_team_rating);

    dsr_standard_3v3_solo__assign_ratings($teams, $teams_with_solo_team_rating);
}

function dsr_standard_3v3_solo__get_teams_with_solo_team_rating(&$teams) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['standard_3v3_solo']['data']['ratings_solo_team'];
    $teams_with_solo_team_rating = $teams;

    foreach ($teams_with_solo_team_rating as &$team) {
        foreach ($team as &$player) {
            $player['is_solo'] = !dsr__have_teammates_in_game($player, $team);
            $player['rating'] =
                $ratings_solo_team[$player['is_solo']][$player['player_id']] ??
                $GLOBALS['rating_calculators']['standard_3v3_solo']['default_rating'];
        }
    }

    return $teams_with_solo_team_rating;
}

function dsr_standard_3v3_solo__assign_ratings(&$teams, &$teams_with_solo_team_rating) {
    $ratings_solo_team = &$GLOBALS['rating_calculators']['standard_3v3_solo']['data']['ratings_solo_team'];

    foreach ($teams_with_solo_team_rating as $team_index => $team) {
        foreach ($team as $player_index => $player) {
            $ratings_solo_team[$player['is_solo']][$player['player_id']] = $player['rating'];

            if ($player['is_solo']) {
                $teams[$team_index][$player_index]['rating'] = $player['rating'];
            }
        }
    }
}
