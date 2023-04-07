<?php

$GLOBALS['rating_calculators']['dsr_rush'] = [
    'description' => '
        DSR Rush is for hotheads.
        Any time you win a game under 13 minutes you gain 8 rating.
        Any time you win/lose a game over 13 minutes you lose 3 rating.
        The maximum rank is 3000 and if you reach 3000 you are named 5508 (BOSS)
        Rank is reset on the first date each month
        ',
    'default_rating' => 1500,
    'fields' => [ // always available: game_id, player_id, team, winner_team, status
        'g.duration',
        'g.date_played',
        ],
    'main' => 'dsr_rush__main',
    ];

function dsr_rush__main(&$teams) {
    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            //Increases rating if game is won under 13 minutes (22.4*60*13) and decreases if not won under 13
            $diff = 0;
            if ($player['duration'] <= 17472 && $player['winner_team'] === $player['team']) {
                $diff = 8;
            }
            else {
                $diff = -3;
            }

            $player['rating'] = max(0, $player['rating'] + $diff);

            // Puts anyone above 3k to 5508 (BOSS)
            if ($player['rating'] >= 3000) {
            $player['rating'] = 5508;
            }

            // Reset 1st date of month
            $last_reset = date('Y-m-d', strtotime('last day of previous month'));
            $now = $player['date_played'];
            if ($now < $last_reset) {
            return;
            }
        }
    }
}
