<?php

namespace Differ\Formatters\Plain;

use function Differ\Additional\getKeyNames;
use function Differ\Additional\getStatus;
use function Differ\Additional\stringifyItem;

use const Differ\Differ\ADDED;
use const Differ\Differ\DELETED;
use const Differ\Differ\MODIFIED;
use const Differ\Differ\NESTED;
use const Differ\Differ\NEWVALUENAME;
use const Differ\Differ\STATUSNAME;
use const Differ\Differ\UNCHANGED;
use const Differ\Differ\VALUENAME;

function generateDiff(array $diffData): string
{
    $iter = function ($currentData, $path) use (&$iter) {
        if (!is_array($currentData)) {
            return $currentData;
        }
        $value = getValue(VALUENAME, $currentData);
        $valueAdd = getValue(NEWVALUENAME, $currentData);
        $status = $currentData[STATUSNAME];
        switch ($status) {
            case MODIFIED:
                return $line = "Property '{$path}' was updated. From {$value} to {$valueAdd}" . PHP_EOL;
            case DELETED:
                return $line = "Property '{$path}' was removed" . PHP_EOL;
            case ADDED:
                return $line = "Property '{$path}' was added with value: {$valueAdd}" . PHP_EOL;
            case NESTED:
                return $line = getObjectLine($iter, $currentData, $path, '');
            case UNCHANGED:
                return $line = '';
            default:
                throw new \Exception('unknown status - ' . $status);
        }
    };
    return rtrim($iter($diffData, ''));
}

function getObjectLine(callable $iter, array $currentData, string $path, string $line): string
{
    return array_reduce(
        array_keys($currentData),
        function ($accLine, $itemName) use ($iter, $currentData, $path) {
            $keyNames = getKeyNames();
            if (in_array($itemName, $keyNames, true)) {
                return $accLine;
            }
            $newPath = $path === '' ? $itemName : "{$path}.{$itemName}";
            return $accLine . $iter($currentData[$itemName], $newPath);
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
