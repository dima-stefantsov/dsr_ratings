<?php

$GLOBALS['rating_calculators']['_mignoubou_gas_king'] = [
    'description' => '
        by <a href="/player/22267">PewPewPew</a>: gas king rating v2
        only objective: gas as much and as soon as you can, but do not lose !
        you start with 2000 rating
        you lose 50 rating per minute for each gas not taken (without compression) (only when winning)
        you win 1, 2, 3, 4 rating per minute for gas 1, 2, 3, 4 (without compression)
        you are punished if your gases do not pay themselves back (only when losing)
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
    $min_timings = [0, 1.5, 3, 10];
    $good_timings = [5, 7.5, 10, 12.5];
    $number_of_frames_in_a_min = 22.4 * 60;
    $duration_of_the_game_in_minutes = $teams[0][0]['duration'] / 22.4 / 60;

    foreach($teams as &$team)
    {
        foreach($team as &$player)
        {
            $diff = 0;

            if ($player['status'] == 'win')
            {
                for ($i = 0; $i < $player['gas_count']; $i++)
                {
                    $gas_timing_in_minutes = intval($player['gas_timings'][$i]) / $number_of_frames_in_a_min; 
                    $diff -= 50 * max(0, ($gas_timing_in_minutes - $min_timings[$i]));
                    $diff += ($i + 1) * ($duration_of_the_game_in_minutes - $gas_timing_in_minutes);
                }
            }

            else
            {
                if ($player['gas_count'] == 0)
                {
                    $diff -= 4 * 5 * $duration_of_the_game_in_minutes;
                }

                else
                {
                    for ($i = 0; $i < $player['gas_count']; $i++)
                    {
                        $gas_timing_in_minutes = intval($player['gas_timings'][$i]) / $number_of_frames_in_a_min;

                        $punishing_for_gassing_too_soon = 50 * (($duration_of_the_game_in_minutes - $gas_timing_in_minutes) - $good_timings[$i]);
                        
                        $diff += min(0, $punishing_for_gassing_too_soon);
                    }
                }
            }

            if ($player['rating'] + $diff >= 0)
            {
                $dist_to_2000 = $player['rating'] - 2000;

                if ($dist_to_2000 >= 0)
                {
                    if (dsr_gas_king_rating__same_sign($dist_to_2000, $diff))
                    {
                        $diff *= exp(-($dist_to_2000) / 1700);
                    }

                    else
                    {
                        if ($diff + $dist_to_2000 < 0)
                        {
                            $diff = ($diff + $dist_to_2000) / 10 - $dist_to_2000;
                        }
                    }
                }

                else
                {
                    if (dsr_gas_king_rating__same_sign($dist_to_2000, $diff))
                    {
                        $diff /= 10;
                    }

                    else
                    {
                        if ($diff / 10 + $dist_to_2000 > 0)
                        {
                            $diff += $dist_to_2000 * 10 - $dist_to_2000;
                        }

                        else
                        {
                            $diff /= 10;
                        }
                    }
                }

                $player['rating'] += round($diff);
            }
        }
    }
    $player['rating'] = max(1, $player['rating']);
}

/*function dsr_gas_king_rating__main(&$teams)
{
    $min_timings = [0, 2016, 4032, 13440];
    $good_timings = [6720, 10080, 13440, 16800];
    $number_of_loops_in_a_minute = 22.4 * 60;

    foreach($teams as &$team)
    {
        foreach($team as &$player)
        {
            $diff = 0;

            if ($player['status'] == 'win')
            {
                for ($i = 0; $i < $player['gas_count']; $i++)
                {
                    $diff -= 50 / $number_of_loops_in_a_minute * (intval($player['gas_timings'][$i]) - $min_timings[$i]);
                    $diff += ($i + 1) / $number_of_loops_in_a_minute * ($teams[0][0]['duration'] - intval($player['gas_timings'][$i]));
                }
            }

            else
            {
                if ($player['gas_count'] == 0)
                {
                    $diff -= 4 * 5 / $number_of_loops_in_a_minute * $teams[0][0]['duration'];
                }

                else
                {
                    for ($i = 0; $i < $player['gas_count']; $i++)
                    {
                        $punishing_for_gassing_too_soon = 50 / $number_of_loops_in_a_minute * (($teams[0][0]['duration'] - intval($player['gas_timings'][$i])) - $good_timings[$i]);
                        
                        $diff += min(0, $punishing_for_gassing_too_soon);
                    }
                }
            }

            if ($player['rating'] + $diff >= 0)
            {
                $dist_to_2000 = $player['rating'] - 2000;

                if ($dist_to_2000 >= 0)
                {
                    if (dsr_gas_king_rating__same_sign($dist_to_2000, $diff))
                    {
                        $diff *= exp(-($dist_to_2000) / 2000);
                    }

                    else
                    {
                        if ($diff + $dist_to_2000 < 0)
                        {
                            $diff = ($diff + $dist_to_2000) / 10 - $dist_to_2000;
                        }
                    }
                }

                else
                {
                    if (dsr_gas_king_rating__same_sign($dist_to_2000, $diff))
                    {
                        $diff /= 10;
                    }

                    else
                    {
                        if ($diff / 10 + $dist_to_2000 > 0)
                        {
                            $diff += $dist_to_2000 * 10 - $dist_to_2000;
                        }

                        else
                        {
                            $diff /= 10;
                        }
                    }
                }

                $player['rating'] += round($diff);
            }
        }
    }
}*/

function dsr_gas_king_rating__same_sign($a, $b)
{
    return ($a >= 0 && $b >= 0) || ($a < 0 && $b < 0);
}
