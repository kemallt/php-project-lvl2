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
        $curItemArr = array_reduce(
            array_keys($curItemArrayed),
            function ($accArr, $itemName) use ($iter, &$curItemArrayed, $status) {
                $nextItemArr = getNextItemArr($itemName, $curItemArrayed[$itemName], $accArr, $status);
                $itemVal = $iter($curItemArrayed[$itemName], $nextItemArr);
                $f = 4;
                $reduceRes = array_reduce(
                    array_keys($accArr),
                    function ($reduceRound, $key) use ($itemVal, &$accArr, $itemName) {
                        [$inserted, $subAccArr] = $reduceRound;
                        if ($itemName >= $key) {
                            $subAccArr[$key] = $accArr[$key];
                        } else {
                            $subAccArr[$itemName] = $itemVal;
                            $subAccArr[$key] = $accArr[$key];
                            $inserted = true;
                        }
                        return [$inserted, $subAccArr];
                    },
                    [false, []]
                );
                [$inserted, $resArr] = $reduceRes;
                if (!$inserted) {
                    $resArr[$itemName] = $itemVal;
                }
                return $resArr;
                $accArr[$itemName] = $itemVal;
                return $accArr;
            },
            $curItemArr
        );
        return $curItemArr;
    };
    return $iter($item, $itemArr);
}

function getNextItemArr(string $itemName, mixed $itemValue, array $curItemArr, string $status): array
{
    if (array_key_exists($itemName, $curItemArr)) {
        $nextItemArr = $curItemArr[$itemName];
        $itemIsObject = is_object($itemValue);
        $valueExists = array_key_exists('_value', $nextItemArr);
        if ($itemIsObject) {
            $nextItemArr['_status'] = $valueExists ? 'modified' : 'unchanged';
        }
    } else {
        $nextItemArr = array('_status' => $status);
    }
    return $nextItemArr;
}

function addItemToCurArr(array $curItemArr, mixed $curItemVal, string $sign): array
{
    $resArr = $curItemArr;
    if ($sign === 'added') {
        if (array_key_exists('_value', $curItemArr) && $curItemArr['_value'] === $curItemVal) {
            $resArr['_status'] = 'unchanged';
        } elseif ($curItemArr['_status'] === 'deleted') {
            $resArr['_status'] = 'modified';
            $resArr['_newValue'] = $curItemVal;
        } else {
            $resArr['_status'] = 'added';
            $resArr['_newValue'] = $curItemVal;
        }
    } else {
        $resArr = $curItemArr;
        $resArr['_value'] = $curItemVal;
    }
    return $resArr;
}
