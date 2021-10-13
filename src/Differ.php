<?php

namespace Differ\Differ;

use function Differ\Additional\sortArray;
use function Differ\Parsers\getDataFromFile;
use function Differ\Formatters\getFormattedDiff;

function genDiff(string $pathToFile1, string $pathToFile2, string $formatter = "stylish"): string
{
    $keyNames = ['_sign', '_signAdd', 'value', 'valueAdd'];
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);

    $diffData = createDiffObjects($data1, $data2);
    $sortedDiffData = sortDiffArr($diffData);
    return getFormattedDiff($sortedDiffData, $keyNames, $formatter);
}

function createDiffObjects(object $data1, object $data2): array
{
    $dataArr = convertItem($data1, ['_sign' => ' ', '_signAdd' => ' '], '-');
    return convertItem($data2, $dataArr, '+');
}

function convertItem(object $item, array $itemArr, string $sign): mixed
{
    $iter = function ($curItem, $curItemArr) use (&$iter, $sign) {
        if (!is_object($curItem)) {
            $curItemVal = $curItem;
            return addItemToCurArr($curItemArr, $curItemVal, $sign);
        }
        $curItemArrayed = (array)$curItem;
        $curItemArr = array_reduce(
            array_keys($curItemArrayed),
            function ($accArr, $itemName) use ($iter, &$curItemArrayed, $sign) {
                $nextItemArr = getNextItemArr($itemName, $curItemArrayed[$itemName], $accArr, $sign);
                $accArr[$itemName] = $iter($curItemArrayed[$itemName], $nextItemArr);
                return $accArr;
            },
            $curItemArr
        );
        return $curItemArr;
    };
    return $iter($item, $itemArr);
}

function getNextItemArr(string $itemName, mixed $itemValue, array $curItemArr, string $sign): array
{
    if (array_key_exists($itemName, $curItemArr)) {
        $nextItemArr = $curItemArr[$itemName];
        $itemIsObject = is_object($itemValue);
        $valueExists = array_key_exists('value', $nextItemArr);
        $nextItemArr['_signAdd'] = ($itemIsObject && $valueExists) ? $sign : $nextItemArr['_signAdd'];
        $nextItemArr['_sign'] = ($itemIsObject && !$valueExists) ? ' ' : $nextItemArr['_sign'];
    } else {
        $nextItemArr = array('_sign' => $sign, '_signAdd' => '');
    }
    return $nextItemArr;
}

function addItemToCurArr(array $curItemArr, mixed $curItemVal, string $sign): array
{
    $resArr = $curItemArr;
    if ($sign === '+') {
        if (array_key_exists('value', $curItemArr) && $curItemArr['value'] === $curItemVal) {
            $resArr['_sign'] = ' ';
//            $curItemArr['_sign'] = ' ';
        } else {
            $resArr['_signAdd'] = '+';
            $resArr['valueAdd'] = $curItemVal;
//            $curItemArr['_signAdd'] = '+';
//            $curItemArr['valueAdd'] = $curItemVal;
        }
    } else {
        $resArr = $curItemArr;
        $resArr['value'] = $curItemVal;
//        $curItemArr['value'] = $curItemVal;
    }
    return $resArr;
}

function sortDiffArr(array $diffArr): array
{
    $iter = function ($curArr) use (&$iter) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        $resArr = array_reduce(array_keys($curArr), function ($accArr, $itemName) use ($iter, $curArr) {
            $accArr[$itemName] = $iter($curArr[$itemName]);
            return $accArr;
        }, []);
        return sortArray($resArr);
    };
    return $iter($diffArr);
}
