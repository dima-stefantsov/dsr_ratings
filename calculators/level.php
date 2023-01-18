<?php

$GLOBALS['rating_calculators']['level'] = [
    'description' => '
        in-game level, uncapped

        it\'s a pity Direct Strike has level cap of 2400,
        let us fix that!

        now you know what your real uncapped level is!
        no more losing your level to the level-reset bug as well

        the calculation algorithm assumes that
        - you are efficiently spending mastery points when it is available every game
        - mastery points are being spent 50/50 into XP Multiplier and Passive Experience
        - you are not late with building your passive experience buildings, that you get at level 10, 30, 110, 130, etc.
        - there is no diminishing bugs, which could let you have less XP than you should, when you have more than 100 points in one mastery
        - you have no premium
        - mastery points and buildings are still capped at level 2400 as it is in game right now
        ',
    'default_rating' => 1,
    'fields' => [
        'g.duration',
        'gp.mineral_value_killed',
        ],
    'main' => 'dsr_level__main',
    ];

function dsr_level__main(&$teams) {
    $xps = &$GLOBALS['rating_calculators']['level']['data']['xps'];
    $game_duration_seconds = floor($teams[0][0]['duration']/22.4);
    foreach ($teams as &$team) {
        foreach ($team as &$player) { $player_id = $player['player_id'];
            // if you have left the game prematurely, you will not get the XP
            if ($player['status'] !== 'win' && $player['status'] !== 'lose') {
                continue;
            }

            $xp_multiplier = dsr_level__get_xp_multiplier($player['rating']);
            $xp_per_second = dsr_level__get_xp_per_second($player['rating']);

            $game_xp = ($player['mineral_value_killed'] + $game_duration_seconds*$xp_per_second) * $xp_multiplier;

            $xps[$player_id] = ($xps[$player_id]??0) + $game_xp;
            $player['rating'] = dsr_level__get_level($xps[$player_id]);
        }
    }
}

function dsr_level__get_xp_multiplier($level) {
    $level = min(2400, $level);
    $skill_level = floor(($level+2) / 6);
    $skill_diminished = min(100, $skill_level) + max(0, $skill_level-100)/5;

    $xp_multiplier = 1 + $skill_diminished*0.01;
    return $xp_multiplier;
}

function dsr_level__get_xp_per_second($level) {
    $level = min(2400, $level);
    $skill_level = floor(($level-1) / 6);
    $skill_diminished = min(100, $skill_level) + max(0, $skill_level-100)/5;

    $skill_buildings_bonus = floor($level/100)*2 + (($level%100)>=10?1:0) + (($level%100)>=30?1:0);

    $xp_per_second = $skill_diminished + $skill_buildings_bonus;
    return $xp_per_second;
}

// 1 => 2000,
// 2 => 3000,
// 3 => 5000,
// 4 => 10000,
// 5 => 15000,
// 6 => 20000,
// 7 => 30000,
// 8 => 40000,
// 9 => 55000,
// 10 => 70000,
// 11 => 95000,
// 12 => 110000,
// 13 => 125000,
// 14 => 140000,
// 15 => 160000,
// 16 => 180000,
// 17 => 200000,
// 18 => 220000,
// 19 => 240000,
// 20 => 260000,
// 21 => 280000,
// 22 => 300000,
// 23 => 325000,
// 24 => 350000,
// 25 => 375000,
function dsr_level__get_level($xp) {
    $level = 1;
    if ($xp > 3610000) {
        $level = 26 + floor(($xp-3610000)/400000);
    }
    else if ($xp > 3235000) { $level = 25; }
    else if ($xp > 2885000) { $level = 24; }
    else if ($xp > 2560000) { $level = 23; }
    else if ($xp > 2260000) { $level = 22; }
    else if ($xp > 1980000) { $level = 21; }
    else if ($xp > 1720000) { $level = 20; }
    else if ($xp > 1480000) { $level = 19; }
    else if ($xp > 1260000) { $level = 18; }
    else if ($xp > 1060000) { $level = 17; }
    else if ($xp >  880000) { $level = 16; }
    else if ($xp >  720000) { $level = 15; }
    else if ($xp >  580000) { $level = 14; }
    else if ($xp >  455000) { $level = 13; }
    else if ($xp >  345000) { $level = 12; }
    else if ($xp >  250000) { $level = 11; }
    else if ($xp >  180000) { $level = 10; }
    else if ($xp >  125000) { $level =  9; }
    else if ($xp >   85000) { $level =  8; }
    else if ($xp >   55000) { $level =  7; }
    else if ($xp >   35000) { $level =  6; }
    else if ($xp >   20000) { $level =  5; }
    else if ($xp >   10000) { $level =  4; }
    else if ($xp >    5000) { $level =  3; }
    else if ($xp >    2000) { $level =  2; }

    return (int)$level;
}
