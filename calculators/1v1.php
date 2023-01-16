<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['1v1'] = [
    'description' => '
        only 1v1 games

        no teammates
        just you
        real StarCraft spirit

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_1v1__main',
    ];

function dsr_1v1__main(&$teams) {
    if ($teams[0][0]['players_per_team'] !== '1v1') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
