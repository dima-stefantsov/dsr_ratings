<?php $start = microtime(true); echo "started dsr_ratings sandbox\n";
require_once __DIR__.'/init.php';

$rating_name = $argv[1] ?? 'pink_color_rating';
echo "using $rating_name\n";

$rating_calculator = get_rating_calculator($rating_name);
// ddd($rating_calculator);

$games = get_games($rating_calculator);
echo "got ".count($games)." games in ".number_format(microtime(true)-$start, 3)."\n";
// ddd(from($games)->first());

$players = get_players($games);
echo "got ".count($players)." players in ".number_format(microtime(true)-$start, 3)."\n";
// ddd(from($players)->take(10)->toarray());

$ratings = calculate_ratings($games, $rating_calculator);
echo "got ".count($ratings)." ratings in ".number_format(microtime(true)-$start, 3)."\n";
// ddd($ratings);
// print_ratings($ratings, $players); die;

$ratings_changed = from($ratings)->where(fn($v) => $v !== $rating_calculator['default_rating'])->toarray();
echo "got ".count($ratings_changed)." changed ratings in ".number_format(microtime(true)-$start, 3)."\n";
// ddd($ratings_changed);
// print_ratings($ratings_changed, $players); die;

$top_count = 10;
$ratings_top = from($ratings)->orderbydescending('$v')->take($top_count)->toarray();
echo "got ".count($ratings_top)." top ratings in ".number_format(microtime(true)-$start, 3)."\n";
print_ratings($ratings_top, $players);
