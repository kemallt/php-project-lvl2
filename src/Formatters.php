<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\generateDiff as getStylishDiff;
use function Differ\Formatters\Plain\generateDiff as getPlainDiff;
use function Differ\Formatters\Json\generateDiff as getJsonDiff;

function getFormattedDiff(array $diffData, string $formatter): string
{
    switch ($formatter) {
        case "stylish":
            return getStylishDiff($diffData);
        case "plain":
            return getPlainDiff($diffData);
        case "json":
            return getJsonDiff($diffData);
        default:
            throw new \Exception('unknown format');
    }
}
