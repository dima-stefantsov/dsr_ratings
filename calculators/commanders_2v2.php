<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['commanders_2v2'] = [
    'description' => '
        only commanders 2v2 games

        2v2 is a very enjoyable mode, balance is close to 3v3, and yet it has more action, your wave spawns more often, it is also easier to find a teammate to enjoy proper teamplay

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_commanders_2v2__main',
    ];

function dsr_commanders_2v2__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'commanders' || $teams[0][0]['players_per_team'] !== '2v2') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
