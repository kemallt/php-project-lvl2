<?php

namespace Differ\Formatters\Plain;

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

function generateDiff(array $diffData): string
{
    return rtrim(generateNodeDiff($diffData, ''));
}

function generateNodeDiff(array $currentData, string $path): string
{
    $value = getValue(VALUENAME, $currentData);
    $valueAdd = getValue(NEWVALUENAME, $currentData);
    $status = $currentData[STATUSNAME];
    switch ($status) {
        case MODIFIED:
            return "Property '{$path}' was updated. From {$value} to {$valueAdd}" . PHP_EOL;
        case DELETED:
            return "Property '{$path}' was removed" . PHP_EOL;
        case ADDED:
            return "Property '{$path}' was added with value: {$valueAdd}" . PHP_EOL;
        case NESTED:
            return generateDiffForObject($currentData, $path, '');
        case UNCHANGED:
            return '';
        default:
            throw new \Exception('unknown status - ' . $status);
    }
}

function generateDiffForObject(array $currentData, string $path, string $line): string
{
    return array_reduce(
        array_keys($currentData),
        function ($accLine, $itemName) use ($currentData, $path) {
            $keyNames = getKeyNames();
            if (in_array($itemName, $keyNames, true)) {
                return $accLine;
            }
            $newPath = $path === '' ? $itemName : "{$path}.{$itemName}";
            return $accLine . generateNodeDiff($currentData[$itemName], $newPath);
        },
        $line
    );
}

function getValue(string $valueName, array $currentData): string
{
    $value = array_key_exists($valueName, $currentData) ? $currentData[$valueName] : '[complex value]';
    $stringifiedValue = stringifyItem($value);
    $valueFin = (is_string($value) && $value !== '[complex value]') ? "'{$stringifiedValue}'" : "{$stringifiedValue}";
    return $valueFin;
}
