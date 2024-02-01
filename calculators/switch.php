<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';


$GLOBALS['rating_calculators']['switch'] = [
    'description' => '
        only switch games

        switch is one of the least played DS game modes
        but it is fun to play and compete in!

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_switch__main',
    ];

function dsr_switch__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'switch') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
