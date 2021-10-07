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
    $dataArr = convertItem($data1, [], '-');
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

        foreach ($curItem as $itemName => $itemValue) {
            if (array_key_exists($itemName, $curItemArr)) {
                $nextItemArr = $curItemArr[$itemName];
                if (!array_key_exists('value', $nextItemArr) && is_object($itemValue)) {
                    $nextItemArr['_sign'] = ' ';
                }
            } else {
                $nextItemArr = array('_sign' => $sign);
            }
            $curItemArr[$itemName] = $iter($itemValue, $nextItemArr);
        }

        return $curItemArr;
    };
    return $iter($item, $itemArr);
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
    $iter = function ($lineName, $curArr, $depth, $parentSign) use (&$iter) {
        if (!is_array($curArr)) {
            return $curArr . PHP_EOL;
        }

        $object = false;
        $valueLine = '';
        $objectLine = '';
        $valueAddLine = '';
        if (array_key_exists('_sign', $curArr)) {
            $sign = $curArr['_sign'];
        } elseif (array_key_exists('_signAdd', $curArr)) {
            $sign = $curArr['_signAdd'];
        } else {
            $sign = ' ';
        }
        $lineSign = $parentSign === $sign ? ' ' : $sign;
        if (array_key_exists('value', $curArr)) {
            $lineVal = $curArr['value'] === '' ? '' : ' ' . $curArr['value'];
            $valueLine .= str_repeat(' ', $depth) . $lineSign .' ' . $lineName . ':' . $lineVal . PHP_EOL;
        }
        foreach ($curArr as $itemName => $itemValue) {
            if ($itemName === '_sign' || $itemName === '_signAdd' || $itemName === 'value' || $itemName === 'valueAdd') {
                continue;
            }
            $object = true;
            $objectLine .= $iter($itemName, $itemValue, $depth + 4, $sign);
        }
        if (array_key_exists('valueAdd', $curArr)) {
            $lineAddSign = $parentSign === $curArr['_signAdd'] ? ' ' : $curArr['_signAdd'];
            $lineVal = $curArr['valueAdd'] === '' ? '' : ' ' . $curArr['valueAdd'];
            $valueAddLine .= str_repeat(' ', $depth) . $lineAddSign .' ' . $lineName . ':' . $lineVal . PHP_EOL;
        }
        if ($object && $depth > -2) {
            $line = str_repeat(' ', $depth). $lineSign .' ' . $lineName . ': {' . PHP_EOL;
            $lineEnd = str_repeat(' ', $depth + 2) . '}'. PHP_EOL;
        } elseif ($depth > -2) {
            $line = '';
            $lineEnd = '';
        } else {
            $line = '{' . PHP_EOL;
            $lineEnd = '}';
        }
        return $line . $valueLine . $objectLine . $lineEnd . $valueAddLine;

    };
    return $iter('', $diffData, $startOffset, '');
}