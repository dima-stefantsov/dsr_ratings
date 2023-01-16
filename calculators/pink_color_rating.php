<?php

$GLOBALS['rating_calculators']['pink_color_rating'] = [
    'description' => '
        example pink color rating

        you start with 0 rating
        you have pink color = you get +1
        you don\'t have pink color = you get -3

        contribute your own rating ideas to <a href="https://discord.gg/KXKw8HqKKK" target="_blank">discord</a>
        source is available <a href="https://github.com/dima-stefantsov/dsr_ratings/blob/master/calculators/pink_color_rating.php" target="_blank">here</a>
        ',
    'default_rating' => 0,
    'fields' => [ // always available: game_id, player_id, team, winner_team, status
        // 'g.duration',
        // 'g.sc2_version',
        // 'g.map',
        // 'g.date_played',
        // 'g.region',
        // 'g.mode',
        // 'g.players_per_team',
        // 'g.bunker_destroyed_timings',
        // 'g.players_count',
        // 'g.mid_control_first_timing',
        // 'g.mid_control_timings',
        // 'g.parsed_in',

        // 'gp.name',
        // 'gp.clan',
        'gp.color',
        // 'gp.apm',
        // 'gp.race',
        // 'gp.gas_timings',
        // 'gp.gas_count',
        // 'gp.tier_timings',
        // 'gp.tier',
        // 'gp.mineral_value_killed',
        // 'gp.status_timing',
        // 'gp.recorder',
        ],
    'main' => 'dsr_pink_color_rating__main',
    ];

function dsr_pink_color_rating__main(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $diff = 0;
            if ($player['color'] === 'e55bb0') { // pink color
                $diff = 1;
            }
            else {
                $diff = -3;
            }

            $player['rating'] = max(0, $player['rating'] + $diff);
        }
    }
}
