<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getDataFromFile(string $pathToFile): object
{
    $pathParts = pathinfo($pathToFile);
    if (array_key_exists('extension', $pathParts)) {
        $extension = $pathParts['extension'];
    } else {
        throw new \Exception("could not get file extension from {$pathToFile}");
    }

    $fileContent = file_get_contents($pathToFile);
    if ($fileContent === false) {
        throw new \Exception("could not get file content from {$pathToFile}");
    }
    return parseData($extension, $fileContent);
}

function parseData(string $extension, mixed $data): mixed
{
    switch ($extension) {
        case 'json':
            $result = json_decode($data);
            break;
        case 'yml':
        case 'yaml':
            $result = Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
            break;
        default:
            throw new \Exception('unknown extension');
    }
    return $result;
}
