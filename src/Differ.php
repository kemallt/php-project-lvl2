<?php

namespace Differ\Differ;

use function Differ\Parsers\getDataFromFile;
use function Differ\Formatters\getFormattedDiff;

const ADDED = 'added';
const MODIFIED = 'modified';
const DELETED = 'deleted';
const UNCHANGED = 'unchanged';
const STATUSNAME = 'status';
const VALUENAME = 'value';
const NEWVALUENAME = 'newValue';

function genDiff(string $pathToFile1, string $pathToFile2, string $formatter = "stylish"): string
{
    if ($pathToFile1 === '') {
        throw new \Exception('no first file name');
    }
    if ($pathToFile2 === '') {
        throw new \Exception('no second file name');
    }
    $keyNames = [STATUSNAME, VALUENAME, NEWVALUENAME];
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);

    $sortedDiffData = createDiffObjects($data1, $data2);
    return getFormattedDiff($sortedDiffData, $keyNames, $formatter);
}

function createDiffObjects(object $data1, object $data2): array
{
    $convertedData = convertItem($data1, [STATUSNAME => UNCHANGED], DELETED);
    return convertItem($data2, $convertedData, ADDED);
}

function convertItem(object $item, array $itemData, string $status): mixed
{
    $iter = function ($curItem, $current) use (&$iter, $status) {
        if (!is_object($curItem)) {
            $curItemVal = $curItem;
            return addItemToCurrent($current, $curItemVal, $status);
        }
        $currentData = (array)$curItem;
        $resItem = array_reduce(
            array_keys($currentData),
            function ($acc, $itemName) use ($iter, &$currentData, $status): array {
                $nextItem = getNextItem($itemName, $currentData[$itemName], $acc, $status);
                $itemVal = $iter($currentData[$itemName], $nextItem);
                [$inserted, $result] = addNewItemSort($acc, $itemVal, $itemName);
                return $inserted ? $result : array_merge($result, [$itemName => $itemVal]);
            },
            $current
        );
        return $resItem;
    };
    return $iter($item, $itemData);
}

function getNextItem(string $itemName, mixed $itemValue, array $current, string $status): array
{
    if (array_key_exists($itemName, $current)) {
        $currentNextItem = $current[$itemName];
        $itemIsObject = is_object($itemValue);
        $valueExists = array_key_exists(VALUENAME, $currentNextItem);
        if ($itemIsObject) {
            $newStatus = $valueExists ? MODIFIED : UNCHANGED;
            $nextItem = array_merge($currentNextItem, [STATUSNAME => $newStatus]);
        } else {
            $nextItem = $currentNextItem;
        }
    } else {
        $nextItem = array(STATUSNAME => $status);
    }
    return $nextItem;
}

function addItemToCurrent(array $current, mixed $curItemVal, string $status): array
{
    if ($status !== ADDED) {
        return array_merge($current, [VALUENAME => $curItemVal]);
    }
    if (array_key_exists(VALUENAME, $current) && $current[VALUENAME] === $curItemVal) {
        $add = [STATUSNAME => UNCHANGED];
    } elseif ($current[STATUSNAME] === DELETED) {
        $add = [STATUSNAME => MODIFIED, NEWVALUENAME => $curItemVal];
    } else {
        $add = [STATUSNAME => ADDED, NEWVALUENAME => $curItemVal];
    }
    return array_merge($current, $add);
}

function addNewItemSort(array $acc, mixed $itemVal, string $itemName): array
{
    return array_reduce(
        array_keys($acc),
        function ($reduceRound, $key) use ($itemVal, &$acc, $itemName): array {
            $subAcc = $reduceRound[1];
            if ($itemName >= $key) {
                $resSubAcc = array_merge($subAcc, [$key => $acc[$key]]);
                $inserted = false;
            } else {
                $resSubAcc = array_merge($subAcc, [$itemName => $itemVal, $key => $acc[$key]]);
                $inserted = true;
            }
            return [$inserted, $resSubAcc];
        },
        [false, []]
    );
}
