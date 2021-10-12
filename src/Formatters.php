<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\generateDiff as getStylishDiff;
use function Differ\Formatters\Plain\generateDiff as getPlainDiff;
use function Differ\Formatters\Json\generateDiff as getJsonDiff;

function getFormattedDiff(array $diffData, array $keyNames, string $formatter): string
{
    switch ($formatter) {
        case "stylish":
            $diff = getStylishDiff($diffData, $keyNames);
            break;
        case "plain":
            $diff = getPlainDiff($diffData, $keyNames);
            break;
        case "json":
            $diff = getJsonDiff($diffData, $keyNames);
            break;
        default:
            $diff = '';
            break;
    }
    return $diff;
}
