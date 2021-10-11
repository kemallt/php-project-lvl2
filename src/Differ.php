<?php

namespace Differ\Differ;

use function Differ\Parsers\getDataFromFile;
use function PHPUnit\Framework\isNull;

function genDiff($pathToFile1, $pathToFile2, $formatter = "stylish")
{
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);

    $diffData = createDiffObjects($data1, $data2);
    $diffData = sortDiffArr($diffData);
    if ($formatter === 'stylish') {
        $startOffset = -2;
        $formattedRes =  generateStylishDiff($diffData, $startOffset);
        return $formattedRes;
    }
}

function createDiffObjects($data1, $data2)
{
    $dataArr = convertItem($data1, ['_sign' => ' ', '_signAdd' => ' '], '-');
    return convertItem($data2, $dataArr, '+');
}

function stringifyItem($item)
{
    if ($item === true) {
        $res = 'true';
    } elseif ($item === false) {
        $res = 'false';
    } elseif ($item === null) {
        $res = 'null';
    } else {
        $res = $item;
    }
    return $res;
}

function convertItem($item, $itemArr, $sign)
{
    $iter = function ($curItem, $curItemArr) use (&$iter, $sign) {
        if (!is_object($curItem)) {
            $curItemVal = stringifyItem($curItem);
            return addItemToCurArr($curItemArr, $curItemVal, $sign);
        }

        foreach ($curItem as $itemName => $itemValue) {
            $nextItemArr = createNextItemArr($itemName, $itemValue, $curItemArr, $sign);
            $curItemArr[$itemName] = $iter($itemValue, $nextItemArr);
        }
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
        foreach ($curArr as $itemName => $itemValue) {
            $curArr[$itemName] = sortDiffArr($itemValue);
        }
        ksort($curArr);
        return $curArr;
    };
    return $iter($diffArr);
}

function generateStylishDiff($diffData, $startOffset = -2)
{
    $iter = function ($lineName, $curArr, $depth, $parentSign) use (&$iter, $startOffset) {
        if (!is_array($curArr)) {
            return $curArr . PHP_EOL;
        }

        $keyNames = ['_sign', '_signAdd', 'value', 'valueAdd'];
        $sign = getSign($curArr);
        $lineSign = $parentSign === $sign ? ' ' : $sign;
        $lineAddSign = $parentSign === $curArr['_signAdd'] ? ' ' : $curArr['_signAdd'];
        $valueLine = getValueLine($curArr, $lineSign, $lineName, $depth, 'value');
        $valueAddLine = getValueLine($curArr, $lineAddSign, $lineName, $depth, 'valueAdd');

        $objectLine = '';
        $object = false;
        foreach ($curArr as $itemName => $itemValue) {
            if (in_array($itemName, $keyNames)) {
                continue;
            }
            $object = true;
            $objectLine .= $iter($itemName, $itemValue, $depth + 4, $sign);
        }
        [$lineStart, $lineEnd] = generateLineStartEnd($object, $depth, $lineSign, $lineName, $startOffset);
        return $lineStart . $valueLine . $objectLine . $lineEnd . $valueAddLine;
    };
    return $iter('', $diffData, $startOffset, '');
}

function getValueLine($curArr, $lineSign, $lineName, $depth, $valueName)
{
    if (array_key_exists($valueName, $curArr)) {
        $lineVal = $curArr[$valueName] === '' ? '' : ' ' . $curArr[$valueName];
        $valueLine = str_repeat(' ', $depth) . $lineSign . ' ' . $lineName . ':' . $lineVal . PHP_EOL;
    } else {
        $valueLine = '';
    }
    return $valueLine;
}

function getSign($curArr)
{
    if (array_key_exists('_sign', $curArr)) {
        $sign = $curArr['_sign'];
    } elseif (array_key_exists('_signAdd', $curArr)) {
        $sign = $curArr['_signAdd'];
    } else {
        $sign = ' ';
    }
    return $sign;
}

function generateLineStartEnd($object, $depth, $lineSign, $lineName, $startOffset)
{
    if ($object && $depth > $startOffset) {
        $lineStart = str_repeat(' ', $depth) . $lineSign . ' ' . $lineName . ': {' . PHP_EOL;
        $lineEnd = str_repeat(' ', $depth + 2) . '}' . PHP_EOL;
    } elseif ($depth > $startOffset) {
        $lineStart = '';
        $lineEnd = '';
    } else {
        $lineStart = '{' . PHP_EOL;
        $lineEnd = '}';
    }
    return [$lineStart, $lineEnd];
}
