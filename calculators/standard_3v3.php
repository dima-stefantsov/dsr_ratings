<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['standard_3v3'] = [
    'description' => '
        only standard 3v3 games

        a game mode with a very developed pro-scene
        even if you feel like winning in pub games is easy, there\'s always a sky beyond the sky

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_standard_3v3__main',
    ];

function dsr_standard_3v3__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'standard' || $teams[0][0]['players_per_team'] !== '3v3') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
