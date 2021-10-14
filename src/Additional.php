<?php

namespace Differ\Additional;

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

function getStatus(array $curArr, bool $fixChildrenStatus = false): string
{
    return $fixChildrenStatus ? 'unchanged' : $curArr['_status'];
}
