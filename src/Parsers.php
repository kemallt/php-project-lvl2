<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getDataFromFile(string $pathToFile): object
{
    $pathParts = pathinfo($pathToFile);
    if (array_key_exists('extension', $pathParts)) {
        $extension = $pathParts['extension'];
    } else {
        return new \stdClass();
    }

    $fileContent = file_get_contents($pathToFile);
    if (!$fileContent) {
        return new \stdClass();
    }
    if ($extension === 'json') {
        $data = json_decode($fileContent);
    } elseif ($extension === 'yml' || $extension === 'yaml') {
        $data = Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
    }
    return $data;
}
