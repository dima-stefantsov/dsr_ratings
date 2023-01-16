<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['standard_2v2'] = [
    'description' => '
        only standard 2v2 games

        this game mode is currently not very popular, but there\'s a lot of depth to it

        less competition = easier to become #1!

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_standard_2v2__main',
    ];

function dsr_standard_2v2__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'standard' || $teams[0][0]['players_per_team'] !== '2v2') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
