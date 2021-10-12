<?php

namespace Differ\Differ;

use function Differ\Parsers\getDataFromFile;
use function Differ\Formatters\getFormattedDiff;

function genDiff($pathToFile1, $pathToFile2, $formatter = "stylish")
{
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);

    $diffData = createDiffObjects($data1, $data2);
    $diffData = sortDiffArr($diffData);
    $answer = getFormattedDiff($diffData, $formatter);
    return $answer;
}

function createDiffObjects($data1, $data2)
{
    $dataArr = convertItem($data1, ['_sign' => ' ', '_signAdd' => ' '], '-');
    return convertItem($data2, $dataArr, '+');
}

function convertItem($item, $itemArr, $sign)
{
    $iter = function ($curItem, $curItemArr) use (&$iter, $sign) {
        if (!is_object($curItem)) {
            $curItemVal = $curItem;
            return addItemToCurArr($curItemArr, $curItemVal, $sign);
        }
        $curItem = (array)$curItem;
        $curItemArr = array_reduce(array_keys($curItem), function ($accArr, $itemName) use (&$iter, &$curItem, $sign) {
            $nextItemArr = createNextItemArr($itemName, $curItem[$itemName], $accArr, $sign);
            $accArr[$itemName] = $iter($curItem[$itemName], $nextItemArr);
            return $accArr;
        }, $curItemArr);
        return $curItemArr;
    };
    return $iter($item, $itemArr);
}

function createNextItemArr($itemName, $itemValue, $curItemArr, $sign)
{
    if (array_key_exists($itemName, $curItemArr)) {
        $nextItemArr = $curItemArr[$itemName];
        if (!array_key_exists('value', $nextItemArr) && is_object($itemValue)) {
            $nextItemArr['_sign'] = ' ';
        } elseif (is_object($itemValue)) {
            $nextItemArr['_signAdd'] = $sign;
        }
    } else {
        $nextItemArr = array('_sign' => $sign, '_signAdd' => '');
    }
    return $nextItemArr;
}

function addItemToCurArr($curItemArr, $curItemVal, $sign)
{
    if ($sign === '+') {
        if (array_key_exists('value', $curItemArr) && $curItemArr['value'] === $curItemVal) {
            $curItemArr['_sign'] = ' ';
        } else {
            $curItemArr['_signAdd'] = '+';
            $curItemArr['valueAdd'] = $curItemVal;
        }
    } else {
        $curItemArr['value'] = $curItemVal;
    }
    return $curItemArr;
}

function sortDiffArr($diffArr)
{
    $iter = function ($curArr) use (&$iter) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        array_map(function ($itemName, $itemValue) use (&$curArr, &$iter) {
            $curArr[$itemName] = $iter($itemValue);
        }, array_keys($curArr), $curArr);
        ksort($curArr);
        return $curArr;
    };
    return $iter($diffArr);
}
