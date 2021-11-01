<?php

namespace Differ\Formatters\Json;

function generateDiff(mixed $diffData): string
{
    return json_encode($diffData);
}
