<?php

namespace Differ\Formatters\Plain;

use function Differ\Additional\getStatus;
use function Differ\Additional\stringifyItem;

use const Differ\Differ\ADDED;
use const Differ\Differ\DELETED;
use const Differ\Differ\MODIFIED;
use const Differ\Differ\NEWVALUENAME;
use const Differ\Differ\VALUENAME;

function generateDiff(array $diffData, array $keyNames): string
{
    $iter = function ($currentData, $path) use (&$iter, $keyNames) {
        if (!is_array($currentData)) {
            return $currentData;
        }
        $value = getValue(VALUENAME, $currentData);
        $valueAdd = getValue(NEWVALUENAME, $currentData);
        $status = getStatus($currentData);
        switch ($status) {
            case MODIFIED:
                $line = "Property '{$path}' was updated. From {$value} to {$valueAdd}" . PHP_EOL;
                break;
            case DELETED:
                $line = "Property '{$path}' was removed" . PHP_EOL;
                break;
            case ADDED:
                $line = "Property '{$path}' was added with value: {$valueAdd}" . PHP_EOL;
                break;
            default:
                $line = getObjectLine($iter, $currentData, [$path, $keyNames, '']);
                break;
        }
        return $line;
    };
    return rtrim($iter($diffData, ''));
}

function getObjectLine(callable $iter, array $currentData, array $parameters): string
{
    [$path, $keyNames, $line] = $parameters;
    return array_reduce(
        array_keys($currentData),
        function ($accLine, $itemName) use ($iter, $currentData, $path, $keyNames) {
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
