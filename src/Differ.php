<?php

namespace Differ\Differ;

use function Differ\Parsers\getDataFromFile;
use function Differ\Formatters\getFormattedDiff;

function genDiff(string $pathToFile1, string $pathToFile2, string $formatter = "stylish"): string
{
    $keyNames = ['_status', '_value', '_newValue'];
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);

    $sortedDiffData = createDiffObjects($data1, $data2);
    return getFormattedDiff($sortedDiffData, $keyNames, $formatter);
}

function createDiffObjects(object $data1, object $data2): array
{
    $dataArr = convertItem($data1, ['_status' => 'unchanged'], 'deleted');
    return convertItem($data2, $dataArr, 'added');
}

function convertItem(object $item, array $itemArr, string $status): mixed
{
    $iter = function ($curItem, $curItemArr) use (&$iter, $status) {
        if (!is_object($curItem)) {
            $curItemVal = $curItem;
            return addItemToCurArr($curItemArr, $curItemVal, $status);
        }
        $curItemArrayed = (array)$curItem;
        $resItemArr = array_reduce(
            array_keys($curItemArrayed),
            function ($accArr, $itemName) use ($iter, &$curItemArrayed, $status): array {
                $nextItemArr = getNextItemArr($itemName, $curItemArrayed[$itemName], $accArr, $status);
                $itemVal = $iter($curItemArrayed[$itemName], $nextItemArr);
                [$inserted, $resArr] = addNewItemSort($accArr, $itemVal, $itemName);
                $finResArr = $inserted ? $resArr : array_merge($resArr, [$itemName => $itemVal]);
                return $finResArr;
            },
            $curItemArr
        );
        return $resItemArr;
    };
    return $iter($item, $itemArr);
}

function getNextItemArr(string $itemName, mixed $itemValue, array $curItemArr, string $status): array
{
    if (array_key_exists($itemName, $curItemArr)) {
        $curNextItemArr = $curItemArr[$itemName];
        $itemIsObject = is_object($itemValue);
        $valueExists = array_key_exists('_value', $curNextItemArr);
        if ($itemIsObject) {
            $newStatus = $valueExists ? 'modified' : 'unchanged';
            $nextItemArr = array_merge($curNextItemArr, ['_status' => $newStatus]);
        } else {
            $nextItemArr = $curNextItemArr;
        }
    } else {
        $nextItemArr = array('_status' => $status);
    }
    return $nextItemArr;
}

function addItemToCurArr(array $curItemArr, mixed $curItemVal, string $status): array
{
    if ($status !== 'added') {
        return array_merge($curItemArr, ['_value' => $curItemVal]);
    }
    if (array_key_exists('_value', $curItemArr) && $curItemArr['_value'] === $curItemVal) {
        $addArr = ['_status' => 'unchanged'];
    } elseif ($curItemArr['_status'] === 'deleted') {
        $addArr = ['_status' => 'modified', '_newValue' => $curItemVal];
    } else {
        $addArr = ['_status' => 'added', '_newValue' => $curItemVal];
    }
    return array_merge($curItemArr, $addArr);
}

function addNewItemSort(array $accArr, mixed $itemVal, string $itemName): array
{
    return array_reduce(
        array_keys($accArr),
        function ($reduceRound, $key) use ($itemVal, &$accArr, $itemName): array {
            $subAccArr = $reduceRound[1];
            if ($itemName >= $key) {
                $resSubAccArr = array_merge($subAccArr, [$key => $accArr[$key]]);
                $inserted = false;
            } else {
                $resSubAccArr = array_merge($subAccArr, [$itemName => $itemVal, $key => $accArr[$key]]);
                $inserted = true;
            }
            return [$inserted, $resSubAccArr];
        },
        [false, []]
    );
}
