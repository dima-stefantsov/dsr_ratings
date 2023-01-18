<?php

function get_rating_calculator($rating_name) {
    $rating_calculator = $GLOBALS['rating_calculators'][$rating_name] ?? false;
    if ($rating_calculator === false) {
        echo "ERROR: rating_calculator $rating_name was not found\n";
        die;
    }

    return $rating_calculator;
}

define('DEFAULT_PLAYER_FIELDS', [
    'game_id',
    'player_id',
    'team',
    'winner_team',
    'status',

    'name',
    'clan',
    ]);
function get_games(&$rating_calculator) {
    $games = include __DIR__.'/example_games.php';

    $rating_calculator_fields_to_keep =
        from($rating_calculator['fields']??[])->
        select('mb_ereg_replace("^(g|gp)\\.", "", $v)')->
        tolist();
    $fields_to_keep = array_merge(DEFAULT_PLAYER_FIELDS, $rating_calculator_fields_to_keep);

    return
    from($games)->
    select(fn($v) => from($v)->select(function($player) use (&$fields_to_keep) {
        $keys_to_remove = array_diff(array_keys($player), $fields_to_keep);
        foreach ($keys_to_remove as $key_to_remove) {
            unset($player[$key_to_remove]);
        }
        return $player;
    })->tolist())->
    tolist();
}

function get_players(&$games) {
    return
    from($games)->
    orderbydescending('$k')->
    selectmany('$v', function($v) {
        if ($v['clan'] !== '') {
            return '<'.$v['clan'].'> '.$v['name'];
        }
        return $v['name'];
    }, '$v["player_id"]')->
    distinct('$k')->
    orderby('$k')->
    toarray();
}

function calculate_ratings(&$games, &$rating_calculator) {
    $rating_calculator['data'] = [];

    $ratings = [];
    foreach ($games as $game) {
        process_game_rating($game, $rating_calculator, $ratings);
    }

    unset($rating_calculator['data']);
    return $ratings;
}

function process_game_rating(&$game, &$rating_calculator, &$ratings) {
    $teams =
        from($game)->
        select(function($v) use (&$ratings, &$rating_calculator) {
            $v['rating'] = $ratings[$v['player_id']] ?? $rating_calculator['default_rating'] ?? 2000;
            return $v;
        })->
        groupby('$v["team"]')->
        orderby('$k')->
        toarray();

    $rating_calculator['main']($teams);

    foreach ($teams as &$team) {
        foreach ($team as &$player) {
            $ratings[$player['player_id']] = $player['rating'];
        }
    }
}

function print_ratings(&$ratings, &$players) {
    $ratings_ordered =
        from($ratings)->
        orderbydescending('$v')->
        toarray();

    echo "\n";
    echo "  rank |                  name | rating\n";
    echo str_repeat('—', 39)."\n";
    $position = 0;
    foreach ($ratings_ordered as $player_id => $rating) { $position++;
        echo
        mb_str_pad_left('#'.$position, 6, ' ', STR_PAD_LEFT).' | '.
        mb_str_pad_left($players[$player_id], 21, ' ', STR_PAD_LEFT).' | '.
        mb_str_pad_left($rating, 6, ' ', STR_PAD_LEFT)."\n";
    }
    echo "\n";
}

function mb_str_pad_left($string, $length, $pad_string = ' ') {
    $pad = $length - mb_strlen($string);
    if ($pad > 0) {
        return str_repeat($pad_string, $pad).$string;
    }
    return $string;
}

