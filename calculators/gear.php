<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';


$GLOBALS['rating_calculators']['gear'] = [
    'description' => '
        only gear games

        gear mode is fun advanced standard mode, you could have additional powerups for your terran, zerg and protoss units

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_gear__main',
    ];

function dsr_gear__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'gear') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
