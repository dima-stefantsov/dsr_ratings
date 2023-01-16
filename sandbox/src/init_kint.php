<?php
Kint::$mode_default_cli = Kint::MODE_TEXT;

Kint::$aliases[] = 'ddd';
function ddd(...$vars) {
    Kint::dump(...$vars);
    exit;
}

// ddd(Kint::$plugins);
Kint\Renderer\TextRenderer::$parser_plugin_whitelist = array_diff(Kint\Renderer\TextRenderer::$parser_plugin_whitelist, [
    'Kint\Parser\ArrayLimitPlugin',
    'Kint\Parser\TracePlugin',
    ]);
