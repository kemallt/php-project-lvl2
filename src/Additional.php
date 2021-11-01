<?php

namespace Differ\Additional;

use const Differ\TreeBuilder\NESTED;
use const Differ\TreeBuilder\NEWVALUENAME;
use const Differ\TreeBuilder\STATUSNAME;
use const Differ\TreeBuilder\UNCHANGED;
use const Differ\TreeBuilder\VALUENAME;

function stringifyItem(mixed $item): mixed
{
    if ($item === true) {
        $res = 'true';
    } elseif ($item === false) {
        $res = 'false';
    } elseif ($item === null) {
        $res = 'null';
    } else {
        $res = $item;
    }
    return $res;
}

function getKeyNames(): array
{
    return [STATUSNAME, VALUENAME, NEWVALUENAME];
}
