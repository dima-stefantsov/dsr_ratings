<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';
$fields[] = 'gp.race';


$GLOBALS['rating_calculators']['commanders_1v1_stukov'] = [
    'description' => '
        commanders 1v1 stukov

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_commanders_1v1_stukov__main',
    ];

function dsr_commanders_1v1_stukov__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'commanders' || $teams[0][0]['players_per_team'] !== '1v1') {
        return;
    }

    $teams_with_race_ratings = dsr_commanders_1v1_stukov__get_teams_with_race_ratings($teams);
    $GLOBALS['rating_calculators']['main_current']['main']($teams_with_race_ratings);
    dsr_commanders_1v1_stukov__assign_ratings($teams, $teams_with_race_ratings);
}

function dsr_commanders_1v1_stukov__get_teams_with_race_ratings(&$teams) {
    $race_ratings = &$GLOBALS['rating_calculators']['commanders_1v1_stukov']['data']['race_ratings'];
    $teams_with_race_ratings = $teams;
    foreach ($teams_with_race_ratings as &$team) {
        foreach ($team as &$player) {
            $player['rating'] = $race_ratings[$player['race']][$player['player_id']] ?? $GLOBALS['rating_calculators']['commanders_1v1_stukov']['default_rating'];
            $GLOBALS['rating_calculators']['main_current']['data']['players_games_count'][$player['player_id']] = &$GLOBALS['rating_calculators']['commanders_1v1_stukov']['data']['race_players_games_count'][$player['race']][$player['player_id']];
        }
    }

    return $teams_with_race_ratings;
}

function dsr_commanders_1v1_stukov__assign_ratings(&$teams, &$teams_with_race_ratings) {
    $race_ratings = &$GLOBALS['rating_calculators']['commanders_1v1_stukov']['data']['race_ratings'];
    foreach ($teams as $team_index => &$team) {
        foreach ($team as $player_index => &$player) {
            $race_ratings[$player['race']][$player['player_id']] = $teams_with_race_ratings[$team_index][$player_index]['rating'];

            if ($player['race'] === 'stukov') {
                $player['rating'] = $teams_with_race_ratings[$team_index][$player_index]['rating'];
            }
        }
    }
}
