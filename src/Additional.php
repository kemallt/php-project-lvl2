<?php

namespace Differ\Additional;

use const Differ\Differ\STATUSNAME;
use const Differ\Differ\UNCHANGED;

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

function getStatus(array $data, bool $fixChildrenStatus = false): string
{
    return $fixChildrenStatus ? UNCHANGED : $data[STATUSNAME];
}
