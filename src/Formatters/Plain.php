<?php

namespace Differ\Formatters\Plain;

use function Differ\Additional\getStatus;
use function Differ\Additional\stringifyItem;

function getObjectLine(callable $iter, array $curArr, array $parameters): string
{
    [$path, $keyNames, $line] = $parameters;
    return array_reduce(
        array_keys($curArr),
        function ($accLine, $itemName) use ($iter, $curArr, $path, $keyNames) {
            if (in_array($itemName, $keyNames, true)) {
                return $accLine;
            }
            $newPath = $path === '' ? $itemName : "{$path}.{$itemName}";
            $resLine = $accLine . $iter($curArr[$itemName], $newPath);
            return $resLine;
        },
        $line
    );
}

function generateDiff(array $diffData, array $keyNames): string
{
    $iter = function ($curArr, $path) use (&$iter, $keyNames) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        $value = getValue('_value', $curArr);
        $valueAdd = getValue('_newValue', $curArr);
        $status = getStatus($curArr);
        switch ($status) {
            case "modified":
                $line = "Property '{$path}' was updated. From {$value} to {$valueAdd}" . PHP_EOL;
                break;
            case "deleted":
                $line = "Property '{$path}' was removed" . PHP_EOL;
                break;
            case "added":
                $line = "Property '{$path}' was added with value: {$valueAdd}" . PHP_EOL;
                break;
            default:
                $line = getObjectLine($iter, $curArr, [$path, $keyNames, '']);
                break;
        }
        return $line;
    };
    return rtrim($iter($diffData, ''));
}

function getValue(string $valueName, array $curArr): string
{
    $value = array_key_exists($valueName, $curArr) ? $curArr[$valueName] : '[complex value]';
    $stringifiedValue = stringifyItem($value);
    $valueFin = (is_string($value) && $value !== '[complex value]') ? "'{$stringifiedValue}'" : "{$stringifiedValue}";
    return $valueFin;
}
