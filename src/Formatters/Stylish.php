<?php

namespace Differ\Formatters\Stylish;

use function Differ\Additional\stringifyItem;

function getStatus(array $curArr, bool $fixChildrenStatus = false): string
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

function getLineSignNameByStatus(string $status, string $lineName, bool $signAdd = false): string
{
    switch ($status) {
        case "unchanged":
            $sign = ' ';
            break;
        case "added":
            $sign = '+';
            break;
        case "deleted":
            $sign = '-';
            break;
        case "modified":
            if ($signAdd) {
                $sign = '+';
            } else {
                $sign =  '-';
            }
            break;
        default:
            $sign = '';
    }
    return $sign . ' ' . $lineName;
}

function getObjectLine(callable &$iter, array $curArr, array $parameters): string
{
    [$depth, $fixChildrenStatus, $keyNames] = $parameters;
    return array_reduce(
        array_keys($curArr),
        function ($accLine, $itemName) use (&$iter, $curArr, $depth, $fixChildrenStatus, $keyNames) {
            if (in_array($itemName, $keyNames, false)) {
                return $accLine;
            }
            $accLine .= $iter($itemName, $curArr[$itemName], $depth + 4, $fixChildrenStatus);
            return $accLine;
        },
        ''
    );
}

function generateDiff(array $diffData, array $keyNames, int $startOffset = -2): string
{
    $iter = function ($lineName, $curArr, $depth, $fixChildrenStatus) use (&$iter, $keyNames, $startOffset) {
        if (!is_array($curArr)) {
            return $curArr . PHP_EOL;
        }
        $status = getStatus($curArr, $fixChildrenStatus);
        if ($status !== "unchanged") {
            $fixChildrenStatus = true;
        }
        $lineSignName = getLineSignNameByStatus($status, $lineName);
        $lineAddSignName = getLineSignNameByStatus($status, $lineName, true);
        $valueLine = getValueLine($curArr, $lineSignName, $depth, 'value');
        $valueAddLine = getValueLine($curArr, $lineAddSignName, $depth, 'valueAdd');
        $lineSignName = ($status === "modified" && $valueLine !== '') ? $lineAddSignName : $lineSignName;

        $objectLine = getObjectLine($iter, $curArr, [$depth, $fixChildrenStatus, $keyNames]);
        $object = $objectLine !== '';
        [$lineStart, $lineEnd] = generateLineStartEnd($object, $depth, $lineSignName, $startOffset);
        return $valueLine . $lineStart . $objectLine . $lineEnd . $valueAddLine;
    };
    return $iter('', $diffData, $startOffset, false);
}

function getValueLine(array $curArr, string $lineSignName, int $depth, string $valueName): string
{
    if (array_key_exists($valueName, $curArr)) {
        $stringifiedValue = stringifyItem($curArr[$valueName]);
        $lineVal = $stringifiedValue === '' ? '' : '' . $stringifiedValue;
        $valueLine = str_repeat(' ', $depth) . $lineSignName . ': ' . $lineVal . PHP_EOL;
    } else {
        $valueLine = '';
    }
    return $valueLine;
}

function generateLineStartEnd(bool $object, int $depth, string $lineSignName, int $startOffset): array
{
    if ($object && $depth > $startOffset) {
        $lineStart = str_repeat(' ', $depth) . $lineSignName . ': {' . PHP_EOL;
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
