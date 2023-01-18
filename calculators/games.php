<?php

$GLOBALS['rating_calculators']['games'] = [
    'description' => '
        games count

        we all heard stories of "on my lost HDD I had 50000 games"
        here are the real non-imaginary numbers
        ',
    'default_rating' => 0,
    'fields' => [],
    'main' => 'dsr_games__main',
    ];

function dsr_games__main(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $player['rating']++;
        }
    }
}
