<?php

$GLOBALS['rating_calculators']['_mignoubou_gas_king'] = [
    'description' => '
        by <a href="/player/22267">PewPewPew</a>: gas king rating v1
        only objective: gas as much and as soon as you can

        you start with 2000 rating
        you lose 10 rating per minute for each gas not taken (without compression)
        you win 1, 2, 3, 4 rating per minute for gas 1, 2, 3, 4 (without compression)
        ',
    'default_rating' => 2000,
    'fields' => [
        'g.duration',
        'gp.gas_timings',
        'gp.gas_count',
        ],
    'main' => 'dsr_gas_king_rating__main',
    ];

function dsr_gas_king_rating__main(&$teams)
{
    $min_timings = [1.5, 3, 4.5, 10];
    $number_of_frames_in_a_min = 22.4 * 60;

    foreach($teams as &$team)
    {
        foreach($team as &$player)
        {
            $diff = 0;
            $duration_of_the_game_in_minutes = $teams[0][0]['duration'] / 22.4 / 60;

            if($player['gas_count'] == 0)
            {
                $player['rating'] -= 4 * 10 * $duration_of_the_game_in_minutes;
            }

            else
            {
                for ($i = 1; $i <= $player['gas_count']; $i++)
                {
                    $gas_timing_in_minutes = intval($player['gas_timings'][$i - 1]) / $number_of_frames_in_a_min;
                    $diff -= 10 * ($gas_timing_in_minutes - $min_timings[$i - 1]);
                    $diff += $i * ($duration_of_the_game_in_minutes - $gas_timing_in_minutes);
                }
            }

            if ($player['rating'] + $diff >= 0)
            {
                $dist_to_2000 = $player['rating'] - 2000;
                
                if ($player['rating'] > 2000)
                {
                    if (dsr_gas_king_rating__same_sign($dist_to_2000, $diff))
                    {
                        $diff = intval($diff * exp(-($player['rating'] - 2000) / 1500));
                    }

                    else
                    {
                        if (-$diff > $dist_to_2000)
                        {
                            $diff = intval(($diff + $dist_to_2000) / 10 - $dist_to_2000);
                        }

                        else
                        {
                            $diff = intval($diff);
                        }
                    }
                }

                else
                {
                    if (dsr_gas_king_rating__same_sign($dist_to_2000, $diff))
                    {
                        $diff = intval($diff / 10);
                    }

                    else
                    {
                        if (-$diff / 10 < $dist_to_2000)
                        {
                            $diff = intval($diff + $dist_to_2000 * 10);
                        }

                        else
                        {
                            $diff = intval($diff / 10);
                        }
                    }
                }

                $player['rating'] += $diff;
            }
        }
    }
}

function dsr_gas_king_rating__same_sign($a, $b)
{
    return ($a >= 0 && $b >= 0) || ($a < 0 && $b < 0);
}