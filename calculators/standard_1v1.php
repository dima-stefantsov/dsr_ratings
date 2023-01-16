<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['standard_1v1'] = [
    'description' => '
        only standard 1v1 games

        the ultimate mode for showing off personal skill

        3 races
        no teammates
        real StarCraft spirit

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_standard_1v1__main',
    ];

function dsr_standard_1v1__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'standard' || $teams[0][0]['players_per_team'] !== '1v1') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
