<?php

namespace Differ\Formatters\Stylish;

use PHP_CodeSniffer\Reports\Diff;

use function Differ\Additional\stringifyItem;

function getStatus($curArr, $fixChildrenStatus = false)
{
    $sign = $curArr['_sign'];
    $signAdd = $curArr['_signAdd'];
    if ($fixChildrenStatus) {
        return "unchanged";
    }
    if ($sign === '+') {
        $status = "added";
    } elseif ($sign === " ") {
        $status = "unchanged";
    } elseif ($signAdd === "+") {
        $status = "modified";
    } else {
        $status = "deleted";
    }
    return $status;
}

function getSignByStatus($status, $signAdd = false)
{
    switch ($status) {
        case "unchanged":
            return ' ';
        case "added":
            return '+';
        case "deleted":
            return '-';
        case "modified":
            if ($signAdd) {
                return '+';
            } else {
                return '-';
            }
    }
}

function generateDiff($diffData, $startOffset = -2)
{
    $iter = function ($lineName, $curArr, $depth, $fixChildrenStatus) use (&$iter, $startOffset) {
        if (!is_array($curArr)) {
            return $curArr . PHP_EOL;
        }

        $keyNames = ['_sign', '_signAdd', 'value', 'valueAdd'];
        $status = getStatus($curArr, $fixChildrenStatus);
        if ($status !== "unchanged") {
            $fixChildrenStatus = true;
        }
        $lineSign = getSignByStatus($status);
        $lineAddSign = getSignByStatus($status, true);
        $valueLine = getValueLine($curArr, $lineSign, $lineName, $depth, 'value');
        $valueAddLine = getValueLine($curArr, $lineAddSign, $lineName, $depth, 'valueAdd');
        if ($status === "modified" && $valueLine !== '') {
            $lineSign = $lineAddSign;
        }

        $objectLine = array_reduce(
            array_keys($curArr),
            function ($accLine, $itemName) use (&$iter, &$curArr, $depth, $fixChildrenStatus, $keyNames) {
                if (in_array($itemName, $keyNames)) {
                    return $accLine;
                }
                $accLine .= $iter($itemName, $curArr[$itemName], $depth + 4, $fixChildrenStatus);
                return $accLine;
            },
            ''
        );
        $object = $objectLine !== '';
        [$lineStart, $lineEnd] = generateLineStartEnd($object, $depth, $lineSign, $lineName, $startOffset);
        return $valueLine . $lineStart . $objectLine . $lineEnd . $valueAddLine;
    };
    return $iter('', $diffData, $startOffset, false);
}

function getValueLine($curArr, $lineSign, $lineName, $depth, $valueName)
{
    if (array_key_exists($valueName, $curArr)) {
        $stringifiedValue = stringifyItem($curArr[$valueName]);
        $lineVal = $stringifiedValue === '' ? '' : '' . $stringifiedValue;
        $valueLine = str_repeat(' ', $depth) . $lineSign . ' ' . $lineName . ': ' . $lineVal . PHP_EOL;
    } else {
        $valueLine = '';
    }
    return $valueLine;
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
