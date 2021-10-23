<?php

namespace Differ\Additional;

use const Differ\Differ\NESTED;
use const Differ\Differ\NEWVALUENAME;
use const Differ\Differ\STATUSNAME;
use const Differ\Differ\UNCHANGED;
use const Differ\Differ\VALUENAME;

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
