<?php

$GLOBALS['rating_calculators']['time_played'] = [
    'description' => '
        hours spent in games

        this only includes actual game time, as displayed above the map in SC2

        it doesn\'t include anything else:
        - staying online in battle.net
        - staying in SC2 menu
        - waiting in lobbies
        - looking at DS loading screen
        - waiting for a player disconnect countdown
        - waiting while game is paused
        - having game slowed because of lags: even if 3 ingame minutes of a laggy game took you 10 real life minutes, it will be counted as 3 minutes

        fun math: divide your hours played by <code>5.12</code>,
        that would be how many work-days equivalents you have spent ingame

        all those who have more than 1869 hours played - congratulations, you have spent more than a work-year in DS!
        <blockquote class="mb-0">365 days in a year
            365 - 28 = 337 days in a year excluding one month paid leave
            337 - 10 = 327 days in a year excluding holidays as well
            327 / 7 * 5 = 234 days in a year excluding weekends
            234 * 8 = 1869 hours spent at work per year
            1869 / 365 = 5.12 hours spent at work per day

            (365-28-10)/7*5*8/365=5.12
        </blockquote>
        ',
    'default_rating' => 0,
    'fields' => [
        'g.duration',
        ],
    'main' => 'dsr_time_played__main',
    ];

function dsr_time_played__main(&$teams) {
    $durations_loops = &$GLOBALS['rating_calculators']['time_played']['data']['durations_loops'];
    foreach ($teams as &$team) {
        foreach ($team as &$player) { $player_id = $player['player_id'];
            $durations_loops[$player_id] = ($durations_loops[$player_id]??0) + $player['duration'];
            $player['rating'] = (int)($durations_loops[$player_id] / 80640); // 22.4 * 60 * 60
        }
    }
}
