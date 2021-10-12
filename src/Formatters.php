<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\generateDiff as getStylishDiff;
use function Differ\Formatters\Plain\generateDiff as getPlainDiff;

function getFormattedDiff($diffData, $formatter)
{
    switch ($formatter) {
        case "stylish":
            return getStylishDiff($diffData);
        case "plain":
            return getPlainDiff($diffData);
    }
}
