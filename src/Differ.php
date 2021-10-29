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
    $data1Vals = (array)$data1;
    $data2Vals = (array)$data2 ?? [];
    return array_reduce(
        array_keys($data1Vals),
        function ($acc, $itemName) use ($data1, $data2, $data2Vals) {
            return array_merge($acc, processItem($itemName, $data1, $data2, $data2Vals));
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
            if (is_object($data2->$itemName)) {
                $acc[$itemName] = convertObject(new \StdClass(), $data2->$itemName);
            } else {
                $acc[$itemName][NEWVALUENAME] = $data2->$itemName;
            }
            $acc[$itemName]['status'] = ADDED;
            return $acc;
        },
        $diffData
    );
}

function processItem(string $itemName, object $data1, object $data2, array $data2Vals): array
{
    if (!in_array($itemName, array_keys($data2Vals), true)) {
        return [$itemName => createNode($itemName, VALUENAME, DELETED, $data1)];
    }
    if ($data1->$itemName === $data2->$itemName) {
        return [$itemName => createNode($itemName, VALUENAME, UNCHANGED, $data1)];
    }
    if (!is_object($data1->$itemName) && !is_object($data2->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return [$itemName => updateNode($node, $itemName, $data2, NEWVALUENAME)];
    }
    if (!is_object($data1->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return [$itemName => updateNode($node, $itemName, $data2, NEWVALUENAME)];
    }
    if (!is_object($data2->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return [$itemName => updateNode($node, $itemName, $data2, NEWVALUENAME)];
    }
    return [$itemName => convertObject($data1->$itemName, $data2->$itemName)];
}

function createNode(string $itemName, string $valueName, string $status, object $data1): array
{
    if (is_object($data1->$itemName)) {
        $itemValue = convertObject($data1->$itemName, new \StdClass());
    } else {
        $itemValue = [$valueName => $data1->$itemName];
    }
    $res = array_merge($itemValue, ['status' => $status]);
    return $res;
}

function updateNode(array $node, string $itemName, object $data, string $valueName): array
{
    if (is_object($data->$itemName)) {
        $itemValue = convertObject(new \StdClass(), $data->$itemName);
    } else {
        $itemValue = [$valueName => $data->$itemName];
    }
    return array_merge($itemValue, $node);
}

function sortDiffArr($diffArr)
{
    $iter = function ($curArr) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        foreach ($curArr as $itemName => $itemValue) {
            $curArr[$itemName] = sortDiffArr($itemValue);
        }
        ksort($curArr);
        return $curArr;
    };
    return $iter($diffArr);
}
