<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';


$GLOBALS['rating_calculators']['commanders'] = [
    'description' => '
        only commanders games

        most popular Direct Strike game mode

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_commanders__main',
    ];

function dsr_commanders__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'commanders') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
