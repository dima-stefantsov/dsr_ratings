<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';


$GLOBALS['rating_calculators']['standard'] = [
    'description' => '
        only standard games

        standard is well known to everyone, just default StarCraft II units, just 3 races
        yet there\'s a lot of depth to it, standard competitive scene is very active

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_standard__main',
    ];

function dsr_standard__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'standard') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
