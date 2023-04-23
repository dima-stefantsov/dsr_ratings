<?php

$GLOBALS['rating_calculators']['memoria_rating'] = [
	'description' => '
		<a href="/player/125980">Memoria\'s</a> rating, v1.0

		Based on the main ratings.
		- Starting rating is 2500 instead of 2000, minimum is capped at 100, and rating change ranges from 0 to 50, or 100 in case of being afk.
		- Factor is changed from 4000 to 2000, meaning that at 4500 you have a 90% prediction to win against a 2500.
		- Games under 3m40s are not counted towards rating.
		- Only games from Standard and (Heroic) Commanders gamemodes are included.
		- Team rating is based on the highest rated player and the lowest rated player.
		  - Generally team rating is top player\'s rating.
		  - If the lowest rated player on a team has rating lower than 33% of the highest rated player, team rating is lowered slightly.
		  - This is done to make games with extremely worse players or known trolls slightly less punishing.

		Please give feedback on <a target="_blank" href="https://discord.gg/KXKw8HqKKK">Discord</a> (You can tag me using "<@826548928509902868>"). ^^
		',
	'default_rating' => 2500,
	'fields' => [
		'g.duration',
		'g.mode',
	],
	'main' => 'dsr_memoria_rating__main',
	];

function dsr_memoria_rating__main(&$teams) {
	$game_duration_seconds = floor($teams[0][0]['duration']/22.4);
	if ($game_duration_seconds < 220) {
		return;
	}
	if (($teams[0][0]['mode'] !== 'heroic_commanders' && $teams[0][0]['mode'] !== 'commanders') && $teams[0][0]['mode'] !== 'standard') {
		return;
    }

	$team_rating = [];
	$team_rating[0] = from($teams[0])->max('$v["rating"]');
	$team_rating[1] = from($teams[1])->max('$v["rating"]');
	$team_rating_low = [];
	$team_rating_low[0] = from($teams[0])->min('$v["rating"]');
	$team_rating_low[1] = from($teams[1])->min('$v["rating"]');
	if ($team_rating_low[0] * 3 < $team_rating[0]) {
		$team_rating[0] -= ceil(sqrt($team_rating[0]**2 - $team_rating_low[0]**2)**0.75);
	}
	if ($team_rating_low[1] * 3 < $team_rating[1]) {
		$team_rating[1] -= ceil(sqrt($team_rating[1]**2 - $team_rating_low[1]**2)**0.75);
	}

	$factor = 2000;

	$reward = [];
	$reward[0] = round(50 / (1 + 10**(($team_rating[0] - $team_rating[1])/$factor)));
	$reward[1] = 50 - $reward[0];

	foreach ($teams as &$team) {
		foreach ($team as &$player) {
			$multiplier = dsr_memoria_rating__get_multiplier($player['status']);
			$diff = ($multiplier*$reward[$player['winner_team']]);
			$player['rating'] = max($player['rating'] + $diff, 100);
		}
	}
}

function dsr_memoria_rating__get_multiplier($status) {
	if ($status === 'win') {
		return 1;
	}

	if ($status === 'afk') {
		return -2;
	}

	return -1;
}
