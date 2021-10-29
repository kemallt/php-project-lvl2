<?php

namespace Differ\Differ;

use function Differ\Parsers\getDataFromFile;
use function Differ\Formatters\getFormattedDiff;

const ADDED = 'added';
const MODIFIED = 'modified';
const DELETED = 'deleted';
const UNCHANGED = 'unchanged';
const NESTED = 'nested';
const STATUSNAME = 'status';
const VALUENAME = 'value';
const NEWVALUENAME = 'newValue';

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

    $diffData = convertObject($data1, $data2);
    $sortedDiffData = sortDiffArr($diffData);
    return getFormattedDiff($sortedDiffData, $formatter);
}

function convertObject(object $data1, object $data2): array
{
    $diffData = processData($data1, $data2);
    return processData2($data2, $diffData);
}

function processData(object $data1, object $data2): array
{
    return array_reduce(
        array_keys((array)$data1),
        function ($acc, $itemName) use ($data1, $data2): array {
            return array_merge($acc, [$itemName => processItem($itemName, $data1, $data2)]);
        },
        ['status' => NESTED]
    );
}

function processData2(object $data2, array $diffData): array
{
    $data2Vals = (array)$data2;
    return array_reduce(
        array_keys($data2Vals),
        function ($acc, $itemName) use ($data2, $diffData) {
            if (array_key_exists($itemName, $diffData)) {
                return $acc;
            }
            return array_merge(
                $acc,
                [$itemName => addData2ToNode(['status' => ADDED], $itemName, $data2, NEWVALUENAME)]
            );
        },
        $diffData
    );
}

function processItem(string $itemName, object $data1, object $data2): array
{
    if (!array_key_exists($itemName, (array)$data2)) {
        return createNode($itemName, VALUENAME, DELETED, $data1);
    }
    if ($data1->$itemName === $data2->$itemName) {
        return createNode($itemName, VALUENAME, UNCHANGED, $data1);
    }
    if (!is_object($data1->$itemName) && !is_object($data2->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return addData2ToNode($node, $itemName, $data2, NEWVALUENAME);
    }
    if (!is_object($data1->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return addData2ToNode($node, $itemName, $data2, NEWVALUENAME);
    }
    if (!is_object($data2->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return addData2ToNode($node, $itemName, $data2, NEWVALUENAME);
    }
    return convertObject($data1->$itemName, $data2->$itemName);
}

function createNode(string $itemName, string $valueName, string $status, object $data1): array
{
    if (is_object($data1->$itemName)) {
        return array_merge(convertObject($data1->$itemName, new \StdClass()), ['status' => $status]);
    }
    return array_merge([$valueName => $data1->$itemName], ['status' => $status]);
}

function addData2ToNode(array $node, string $itemName, object $data, string $valueName): array
{
    if (is_object($data->$itemName)) {
        return array_merge(convertObject(new \StdClass(), $data->$itemName), $node);
    }
    return array_merge([$valueName => $data->$itemName], $node);
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
