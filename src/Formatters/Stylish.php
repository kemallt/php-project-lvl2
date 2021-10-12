<?php

namespace Differ\Formatters\Stylish;

use PHP_CodeSniffer\Reports\Diff;

use function Differ\Additional\stringifyItem;

function generateDiff($diffData, $startOffset = -2)
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

        $objectLine = array_reduce(
            array_keys($curArr),
            function ($accLine, $itemName) use (&$iter, &$curArr, $depth, $sign, $keyNames) {
                if (in_array($itemName, $keyNames)) {
                    return $accLine;
                }
                $accLine .= $iter($itemName, $curArr[$itemName], $depth + 4, $sign);
                return $accLine;
            },
            ''
        );
        $object = $objectLine !== '';
        [$lineStart, $lineEnd] = generateLineStartEnd($object, $depth, $lineSign, $lineName, $startOffset);
        return $lineStart . $valueLine . $objectLine . $lineEnd . $valueAddLine;
    };
    return $iter('', $diffData, $startOffset, '');
}

function getValueLine($curArr, $lineSign, $lineName, $depth, $valueName)
{
    if (array_key_exists($valueName, $curArr)) {
        $stringifiedValue = stringifyItem($curArr[$valueName]);
        $lineVal = $stringifiedValue === '' ? '' : ' ' . $stringifiedValue;
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
