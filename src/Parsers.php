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
    if ($fileContent === false) {
        return new \stdClass();
    }
    switch ($extension) {
        case 'json':
            $data = json_decode($fileContent);
            break;
        case 'yml':
        case 'yaml':
            $data = Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
            break;
        default:
            $data = new \stdClass();
    }
    return $data;
}
