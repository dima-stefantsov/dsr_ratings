<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['commanders_3v3'] = [
    'description' => '
        only commanders 3v3 games

        by far the most played Direct Strike game mode

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_commanders_3v3__main',
    ];

function dsr_commanders_3v3__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'commanders' || $teams[0][0]['players_per_team'] !== '3v3') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
