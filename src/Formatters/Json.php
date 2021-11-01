<?php

namespace Differ\Formatters\Json;

function generateDiff(mixed $diffData): string
{
    $diffString = json_encode($diffData);
    if ($diffString === false) {
        throw new \Exception('error with formatting diff to json');
    }
    return $diffString;
}
