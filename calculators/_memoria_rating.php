<?php

$GLOBALS['rating_calculators']['_memoria_rating'] = [
	'description' => '
		by <a href="/player/125980">Memoria</a>: Rating with per-matchup rating calculation, v1
		
		Only for 1v1, 2v2, and 3v3. Games under 4 minutes don\'t count.
		
		',
	'default_rating' => 2000,
	'fields' => [
		'g.duration',
	],
	'main' => '_memoria_rating__main',
	];

function _memoria_rating__main(&$teams) {
	$game_duration_seconds = floor($teams[0][0]['duration']/22.4);
	if (count($teams[0]) != count($teams[1])) {
		return;
	}
	
	if ($game_duration_seconds < 240) {
		return;
	}
		
	$player_c = count($teams[0]);
	$factor = 4000;
	$ratings1 = [];
	foreach ($teams[0] as $player) {
        $ratings1[] = $player['rating'];
    }
	$ratings2 = [];
	foreach ($teams[1] as $player) {
        $ratings2[] = $player['rating'];
    }
	
	$matchup_predictions = [];
	$matchup_predictions_weight = [];
	$matchup_predictions_weight_total = 0;
	for ($i = 0; $i < $player_c; $i++) {
		$matchup_predictions[$i] = 1-(round(100/(1 + 10**(($ratings1[$i]-$ratings2[$i])/$factor))))/100;
		$matchup_predictions_weight[$i] = (abs(((0.5 - $matchup_predictions[$i])*3)))**1.6;
		$matchup_predictions_weight_total += $matchup_predictions_weight[$i];
	}
	array_multisort($matchup_predictions_weight, SORT_DESC, $matchup_predictions);
	
	$prediction = 0;
	$normalised_weight_mult_total = 0;
	$j = 1;
	for ($i = 0; $i < $player_c; $i++) {
		if ($matchup_predictions_weight_total == 0) {
			$normalised_weight_mult = (1/$player_c);
		}
		elseif ($matchup_predictions[$i] == 0) {
			$normalised_weight_mult = (1 / ($player_c - $i)) / (($matchup_predictions_weight_total - $normalised_weight_mult_total)/$j);
			$j++;
		}
		else {
			$normalised_weight_mult = ($matchup_predictions_weight[$i] / $matchup_predictions_weight_total);
			$normalised_weight_mult_total += $normalised_weight_mult;
		}
		$prediction += $matchup_predictions[$i] * $normalised_weight_mult;
	}
	
	$reward = [];
	$reward[0] = intval(100 * (1 - ($prediction)));
	$reward[1] = 100 - $reward[0];

	foreach ($teams as &$team) {
		foreach ($team as &$player) {
			$multiplier = _memoria_rating__get_multiplier($player['status']);
			$diff = ($multiplier*$reward[$player['winner_team']]);
			$player['rating'] = max(1, $player['rating'] + $diff);
		}
	}
}

function _memoria_rating__get_multiplier($status) {
	if ($status === 'win') {
		return 1;
	}

	return -1;
}
