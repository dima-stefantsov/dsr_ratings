<?php
$GLOBALS['rating_calculators'] = [];

$calculators_path = dirname(__DIR__, 2).'/calculators';
$order = [
    'main_current.php',
    'main_next.php',
    ];
$calculators =
    from(scandir($calculators_path))->
    where(fn($v) => str_ends_with($v, '.php'))->
    orderby('$v', fn($a, $b) => in_array($b, $order) <=> in_array($a, $order))->
    thenby('$v', fn($a, $b) => array_search($a, $order) <=> array_search($b, $order))->
    tolist();
// ddd($calculators);

foreach ($calculators as $calculator) {
    require_once "$calculators_path/$calculator";
}
