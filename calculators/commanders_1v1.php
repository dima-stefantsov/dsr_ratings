<?php

$fields = $GLOBALS['rating_calculators']['main_current']['fields']??[];
$fields[] = 'g.mode';
$fields[] = 'g.players_per_team';


$GLOBALS['rating_calculators']['commanders_1v1'] = [
    'description' => '
        only commanders 1v1 games

        1v1 is very different to 2v2 and 3v3, because stacks can be really hard to beat, since you are having just one set of units every wave. Bunker and base-stack plays are very important in 1v1, not having afk-limiter helps as well, sometimes it may be a good idea to not spend all the minerals you have, and be weaker than you could be, to prevent opponent base stack.

        not every commander is strong in 1v1, sometimes the answer to "what should I have done to win this?" is "sorry, no way to win", experiment with picks and strategies!

        no teammates, just you
        real StarCraft spirit

        this rating is built using default DSR <a href="/rating/main_current">main_current</a> algorithm
        ',
    'default_rating' => $GLOBALS['rating_calculators']['main_current']['default_rating'],
    'fields' => $fields,
    'main' => 'dsr_commanders_1v1__main',
    ];

function dsr_commanders_1v1__main(&$teams) {
    if ($teams[0][0]['mode'] !== 'commanders' || $teams[0][0]['players_per_team'] !== '1v1') {
        return;
    }

    $GLOBALS['rating_calculators']['main_current']['main']($teams);
}
