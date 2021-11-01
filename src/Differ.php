<?php

namespace Differ\Differ;

use function Differ\Formatters\getFormattedDiff;
use function Differ\Parsers\parseData;
use function Differ\TreeBuilder\generateDiffOfTwoObjects;

function genDiff(string $pathToFile1, string $pathToFile2, string $formatter = "stylish"): string
{
    if ($pathToFile1 === '') {
        throw new \Exception('no first file path');
    }
    if ($pathToFile2 === '') {
        throw new \Exception('no second file path');
    }
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);

    $diffData = generateDiffOfTwoObjects($data1, $data2);
    $sortedDiffData = sortDiffArr($diffData);
    return getFormattedDiff($sortedDiffData, $formatter);
}

function getDataFromFile(string $pathToFile): object
{
    $pathParts = pathinfo($pathToFile);
    if (!array_key_exists('extension', $pathParts)) {
        throw new \Exception("could not get file extension from {$pathToFile}");
    }

    $extension = $pathParts['extension'];
    $fileContent = file_get_contents($pathToFile);
    if ($fileContent === false) {
        throw new \Exception("could not get file content from {$pathToFile}");
    }
    return parseData($extension, $fileContent);
}

function sortDiffArr(mixed $diffArr): mixed
{
    if (!is_array($diffArr)) {
        return $diffArr;
    }
    $sortedCurArr = array_reduce(
        array_keys($diffArr),
        function ($acc, $itemName) use ($diffArr) {
            return array_merge($acc, [$itemName => sortDiffArr($diffArr[$itemName])]);
        },
        []
    );
    return collect($sortedCurArr)->sortKeys()->all();
}
