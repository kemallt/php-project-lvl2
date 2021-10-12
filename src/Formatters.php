<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\generateDiff as getStylishDiff;
use function Differ\Formatters\Plain\generateDiff as getPlainDiff;
use function Differ\Formatters\Json\generateDiff as getJsonDiff;

function getFormattedDiff($diffData, $keyNames, $formatter)
{
    switch ($formatter) {
        case "stylish":
            return getStylishDiff($diffData, $keyNames);
        case "plain":
            return getPlainDiff($diffData, $keyNames);
        case "json":
            return getJsonDiff($diffData, $keyNames);
    }
}
