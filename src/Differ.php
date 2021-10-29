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
    $data2Vals = (array)$data2;
    return array_reduce(
        array_keys($data2Vals),
        function ($acc, $itemName) use ($data2) {
            if (is_object($data2->$itemName)) {
                $acc[$itemName] = convertObject(new \StdClass(), $data2->$itemName);
            } else {
                $acc[$itemName][NEWVALUENAME] = $data2->$itemName;
            }
            $acc[$itemName]['status'] = ADDED;
            unset($data2->$itemName);
            return $acc;
        },
        $diffData
    );
}

function processData($data1, $data2)
{
    $data1Vals = (array)$data1;
    $data2Vals = (array)$data2 ?? [];
    return array_reduce(
        array_keys($data1Vals),
        function ($acc, $itemName) use ($data1, $data2, $data2Vals) {
            $acc['status'] = NESTED;
            if (!in_array($itemName, array_keys($data2Vals))) {
                return array_merge(
                    $acc,
                    [$itemName => createNode($itemName, VALUENAME, DELETED, $data1)]
                );
            }
            if ($data1->$itemName === $data2->$itemName) {
                return array_merge(
                    $acc,
                    [$itemName => createNode($itemName, VALUENAME, UNCHANGED, $data1, $data2)]
                );
            }
            if (!is_object($data1->$itemName) && !is_object($data2->$itemName)) {
                return array_merge(
                    $acc,
                    [$itemName => createNode($itemName, VALUENAME, MODIFIED, $data1, $data2, NEWVALUENAME)]
                );
            }
            if (!is_object($data1->$itemName)) {
                return array_merge(
                    $acc,
                    [$itemName => createNode($itemName, NEWVALUENAME, MODIFIED, $data1, $data2, VALUENAME)]
                );
            }
            if (!is_object($data2->$itemName)) {
                return array_merge(
                    $acc,
                    [$itemName => createNode($itemName, VALUENAME, MODIFIED, $data1, $data2, NEWVALUENAME)]
                );
            }
            $acc[$itemName] = convertObject($data1->$itemName, $data2->$itemName);
            unset($data1->$itemName);
            unset($data2->$itemName);
            return $acc;
        },
        []
    );
}

function createNode($itemName, $valueName, $status, $data1, $data2 = null, $secValueName = null)
{
    $res = array_merge(convertItemValue($data1->$itemName, $valueName), ['status' => $status]);
    $finRes = ($secValueName === null) ? $res : array_merge(convertItemValue($data2->$itemName, $secValueName), $res);
    unset($data1->$itemName);
    if ($data2) {
        unset($data2->$itemName);
    }
    return $finRes;
}

function convertItemValue($itemValue, $valueName)
{
    return (is_object($itemValue)) ? convertObject($itemValue, new \StdClass()) : [$valueName => $itemValue];
}

function sortDiffArr($diffArr)
{
    $iter = function ($curArr) use (&$iter) {
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
