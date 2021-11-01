<?php

namespace Differ\Formatters\Stylish;

use function Differ\Additional\getKeyNames;
use function Differ\Additional\stringifyItem;

use const Differ\TreeBuilder\ADDED;
use const Differ\TreeBuilder\DELETED;
use const Differ\TreeBuilder\MODIFIED;
use const Differ\TreeBuilder\NESTED;
use const Differ\TreeBuilder\NEWVALUENAME;
use const Differ\TreeBuilder\STATUSNAME;
use const Differ\TreeBuilder\UNCHANGED;
use const Differ\TreeBuilder\VALUENAME;

const STARTOFFSET = -2;

function generateDiff(
    array $diffData,
    int $depth = STARTOFFSET,
    string $lineName = '',
    bool $fixChildrenStatus = false
): string {
    $status = $fixChildrenStatus ? UNCHANGED : $diffData[STATUSNAME];
    switch ($status) {
        case NESTED:
        case UNCHANGED:
            return generateDiffString($diffData, $depth, $fixChildrenStatus, " ", " ", " ", $lineName);
        case ADDED:
            return generateDiffString($diffData, $depth, true, "+", "+", "+", $lineName);
        case DELETED:
            return generateDiffString($diffData, $depth, true, "-", "-", "-", $lineName);
        case MODIFIED:
            $objectSign = (array_key_exists(VALUENAME, $diffData)) ? "+" : "-";
            return generateDiffString($diffData, $depth, true, "-", "+", $objectSign, $lineName);
        default:
            throw new \Exception("unknown status {$status}");
    }
}

function generateDiffString(
    array $current,
    int $depth,
    bool $fixChildrenStatus,
    string $lineSign,
    string $lineAddSign,
    string $objectSign,
    string $lineName
): string {
    $lineStartOld = "{$lineSign} {$lineName}";
    $lineStartNew = "{$lineAddSign} {$lineName}";
    $lineStartObject = "{$objectSign} {$lineName}";
    $valueLineOld = getValueLine($current, $lineStartOld, $depth, VALUENAME);
    $valueLineNew = getValueLine($current, $lineStartNew, $depth, NEWVALUENAME);
    $objectLine = getObjectLine($current, $depth, $fixChildrenStatus);
    $object = $objectLine !== '';
    [$lineStart, $lineEnd] = getBeginingEndingOfLine($object, $depth, $lineStartObject);
    return $valueLineOld . $lineStart . $objectLine . $lineEnd . $valueLineNew;
}

function getValueLine(array $curent, string $lineStart, int $depth, string $valueName): string
{
    if (array_key_exists($valueName, $curent)) {
        $stringifiedValue = stringifyItem($curent[$valueName]);
        $lineVal = $stringifiedValue === '' ? '' : '' . $stringifiedValue;
        $valueLine = str_repeat(' ', $depth) . $lineStart . ': ' . $lineVal . PHP_EOL;
    } else {
        $valueLine = '';
    }
    return $valueLine;
}

function getObjectLine(array $curent, int $depth, bool $fixChildrenStatus): string
{
    return array_reduce(
        array_keys($curent),
        function ($accLine, $itemName) use ($curent, $depth, $fixChildrenStatus) {
            $keyNames = getKeyNames();
            if (in_array($itemName, $keyNames, true)) {
                return $accLine;
            }
            $accLineUpdated = $accLine . generateDiff($curent[$itemName], $depth + 4, $itemName, $fixChildrenStatus);
            return $accLineUpdated;
        },
        ''
    );
}

function getBeginingEndingOfLine(bool $object, int $depth, string $lineStartObject): array
{
    if ($object && $depth > STARTOFFSET) {
        $lineStart = str_repeat(' ', $depth) . $lineStartObject . ': {' . PHP_EOL;
        $lineEnd = str_repeat(' ', $depth + 2) . '}' . PHP_EOL;
    } elseif ($depth > STARTOFFSET) {
        $lineStart = '';
        $lineEnd = '';
    } else {
        $lineStart = '{' . PHP_EOL;
        $lineEnd = '}';
    }
    return [$lineStart, $lineEnd];
}
