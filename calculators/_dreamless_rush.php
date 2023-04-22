<?php

$GLOBALS['rating_calculators']['_dreamless_rush'] = [
    'description' => '
        by <a href="/player/31532">MFDreamless</a>: rush is for hotheads v1
        released 2023-04-22

        Any time you win a game under 13 minutes you gain 8 rating.
        Any time you win/lose a game over 13 minutes you lose 3 rating.

        The maximum rating is 3000 and if you reach 3000 you are named 5508 (BOSS).

        Rating is reset to 1500 on the first date each month.
        ',
    'logo' => [
        'relative_url' => '/wp-content/uploads/2023/04/rating_dreamless_rush.png',
        'width' => 500,
        'height' => 500,
        ],
    'default_rating' => 1500,
    'fields' => [
        'g.duration',
        'g.date_played',
        ],
    'main' => '_dreamless_rush__main',
    ];

function _dreamless_rush__main(&$teams) {
    // Reset 1st date of month.
    if ($teams[0][0]['date_played'] < strtotime('first day of this month, midnight, UTC')) {
        return;
    }

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            // Increases rating if game is won under 13 minutes (22.4*60*13)
            // and decreases if not won under 13.
            $diff = 0;
            if ($player['duration'] < 17472 && $player['winner_team'] === $player['team']) {
                $diff = 8;
            }
            else {
                $diff = -3;
            }

            $player['rating'] = max(0, $player['rating'] + $diff);

            // Puts anyone above 3k to 5508 (BOSS).
            if ($player['rating'] >= 3000) {
                $player['rating'] = 5508;
            }
        }
    }
}
