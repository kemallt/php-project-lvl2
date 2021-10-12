<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getDataFromFile(string $pathToFile): object
{
    $pathParts = pathinfo($pathToFile);
    $extension = $pathParts['extension'];
    if ($extension === 'json') {
        $data = json_decode(file_get_contents($pathToFile));
    } elseif ($extension === 'yml' || $extension === 'yaml') {
        $data = Yaml::parse(file_get_contents($pathToFile), Yaml::PARSE_OBJECT_FOR_MAP);
    }
    return $data;
}
