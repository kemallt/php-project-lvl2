<?php

namespace Differ\Formatters\Plain;

use function Differ\Additional\stringifyItem;

function getObjectLine(&$iter, $curArr, $parameters)
{
    [$path, $keyNames, $line] = $parameters;
    return array_reduce(
        array_keys($curArr),
        function ($accLine, $itemName) use (&$iter, &$curArr, $path, $keyNames) {
            if (in_array($itemName, $keyNames)) {
                return $accLine;
            }
            $newPath = $path === '' ? $itemName : "{$path}.{$itemName}";
            $accLine .= $iter($curArr[$itemName], $newPath);
            return $accLine;
        },
        $line
    );
}

function generateDiff($diffData, $keyNames)
{
    $iter = function ($curArr, $path) use (&$iter, $keyNames) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        $line = '';
        $value = getValue('value', $curArr);
        $valueAdd = getValue('valueAdd', $curArr);
        if ($curArr['_sign'] === '-' && $curArr['_signAdd'] === '+') {
            $line .= "Property '{$path}' was updated. From {$value} to {$valueAdd}" . PHP_EOL;
        } elseif ($curArr['_sign'] === '-') {
            $line .= "Property '{$path}' was removed" . PHP_EOL;
        } elseif ($curArr['_signAdd'] === '+' || $curArr['_sign'] === '+') {
            $line .= "Property '{$path}' was added with value: {$valueAdd}" . PHP_EOL;
        } else {
            $line .= getObjectLine($iter, $curArr, [$path, $keyNames, $line]);
        }
        return $line;
    };

    return rtrim($iter($diffData, ''));
}

function getValue($valueName, $curArr)
{
    $value = array_key_exists($valueName, $curArr) ? $curArr[$valueName] : '[complex value]';
    $stringifiedValue = stringifyItem($value);
    if (is_string($value) && $value !== '[complex value]') {
        $value = "'{$stringifiedValue}'";
    } else {
        $value = "{$stringifiedValue}";
    }
    return $value;
}
