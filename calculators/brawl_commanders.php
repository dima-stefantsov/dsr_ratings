<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';


$GLOBALS['rating_calculators']['brawl_commanders'] = [
    'description' => '
        only brawl_commanders games

        weekly brawl is a fun game mode, with different perks every week

        it is usually unbalanced and wild, and therefore there\'s a lot of space to discover and abuse OP strategies

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_brawl_commanders__main',
    ];

function dsr_brawl_commanders__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'brawl_commanders') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
