<?php

namespace Differ\Formatters\Stylish;

use function Differ\Additional\stringifyItem;
use function Differ\Additional\getStatus;

use const Differ\Differ\ADDED;
use const Differ\Differ\DELETED;
use const Differ\Differ\MODIFIED;
use const Differ\Differ\NEWVALUENAME;
use const Differ\Differ\UNCHANGED;
use const Differ\Differ\VALUENAME;

function getLineSignNameByStatus(string $status, string $lineName, bool $signAdd = false): string
{
    switch ($status) {
        case UNCHANGED:
            $sign = ' ';
            break;
        case ADDED:
            $sign = '+';
            break;
        case DELETED:
            $sign = '-';
            break;
        case MODIFIED:
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

function getObjectLine(callable $iter, array $curent, array $parameters): string
{
    [$depth, $fixChildrenStatus, $keyNames] = $parameters;
    return array_reduce(
        array_keys($curent),
        function ($accLine, $itemName) use ($iter, $curent, $depth, $fixChildrenStatus, $keyNames) {
            if (in_array($itemName, $keyNames, true)) {
                return $accLine;
            }
            $accLineUpdated = $accLine . $iter($itemName, $curent[$itemName], $depth + 4, $fixChildrenStatus);
            return $accLineUpdated;
        },
        ''
    );
}

function generateDiff(array $diffData, array $keyNames, int $startOffset = -2): string
{
    $iter = function ($lineName, $curent, $depth, $fixChildrenStatus) use (&$iter, $keyNames, $startOffset): string {
        if (!is_array($curent)) {
            return $curent . PHP_EOL;
        }
        $status = getStatus($curent, $fixChildrenStatus);
        $fixChildrenStatusUpdated = $status !== UNCHANGED ? true : $fixChildrenStatus;
        $lineSignName = getLineSignNameByStatus($status, $lineName);
        $lineAddSignName = getLineSignNameByStatus($status, $lineName, true);
        $valueLine = getValueLine($curent, $lineSignName, $depth, VALUENAME);
        $valueAddLine = getValueLine($curent, $lineAddSignName, $depth, NEWVALUENAME);
        $lineSignNameUpdated = ($status === MODIFIED && $valueLine !== '') ? $lineAddSignName : $lineSignName;

        $objectLine = getObjectLine($iter, $curent, [$depth, $fixChildrenStatusUpdated, $keyNames]);
        $object = $objectLine !== '';
        [$lineStart, $lineEnd] = generateLineStartEnd($object, $depth, $lineSignNameUpdated, $startOffset);
        return $valueLine . $lineStart . $objectLine . $lineEnd . $valueAddLine;
    };
    return $iter('', $diffData, $startOffset, false);
}

function getValueLine(array $curent, string $lineSignName, int $depth, string $valueName): string
{
    if (array_key_exists($valueName, $curent)) {
        $stringifiedValue = stringifyItem($curent[$valueName]);
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
