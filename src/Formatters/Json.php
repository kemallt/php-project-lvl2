<?php

namespace Differ\Formatters\Json;

use function Differ\Additional\getStatus;

use const Differ\Differ\ADDED;
use const Differ\Differ\DELETED;
use const Differ\Differ\MODIFIED;
use const Differ\Differ\NEWVALUENAME;
use const Differ\Differ\UNCHANGED;
use const Differ\Differ\VALUENAME;

function generateDiff(mixed $diffData, array $keyNames): string
{
    $iter = function ($current, $parentStatus) use (&$iter, $keyNames) {
        if (!is_array($current)) {
            return $current;
        }
        $status = getStatus($current);
        $addStatus = !($parentStatus === null || ($status !== UNCHANGED && $parentStatus === $status));
        $preResult = getValue($current, $status, $addStatus, $keyNames);
        return ($status === UNCHANGED) ? getObjectEl($iter, $current, [$status, $keyNames, $preResult]) : $preResult;
    };

    $jsonData = $iter($diffData, null);
    $jsonDiff = json_encode($jsonData, 0);
    if ($jsonDiff === false) {
        return '';
    } else {
        return $jsonDiff;
    }
}

function getObjectEl(callable $iter, array $current, array $parameters): array
{
    [$status, $keyNames, $preResult] = $parameters;
    return array_reduce(
        array_keys($current),
        function ($acc, $itemName) use ($iter, $current, $status, $keyNames) {
            if (in_array($itemName, $keyNames, true)) {
                return $acc;
            }
            return array_merge($acc, [$itemName => $iter($current[$itemName], $status)]);
        },
        $preResult
    );
}

function getCopy(array $current, array $keyNames, string $valueName): array
{
    return array_reduce(array_keys($current), function ($acc, $itemName) use (&$current, $keyNames, $valueName) {
        if (in_array($itemName, $keyNames, true)) {
            return $acc;
        }
        if (array_key_exists($valueName, $current[$itemName])) {
            $preResult = array_merge($acc, [$itemName => $current[$itemName][$valueName]]);
        } else {
            $preResult = array_merge($acc, [$itemName => getCopy($current[$itemName], $keyNames, $valueName)]);
        }
        return $preResult;
    }, []);
}

function getValue(array $current, string $status, bool $addStatus, array $keyNames): array
{
    $valueDesc = $addStatus ? ['status' => $status] : [];
    $valueStatuses = [DELETED, MODIFIED];
    $valueAddStatuses = [ADDED, MODIFIED];
    $preResult = fillValueFields($valueDesc, $current, [VALUENAME, 'value', $status, $valueStatuses, $keyNames]);
    return fillValueFields($preResult, $current, [NEWVALUENAME, 'newValue', $status, $valueAddStatuses, $keyNames]);
}

function fillValueFields(array $valueDesc, array $current, array $parameters): array
{
    [$valueName, $valueNewName, $status, $checkStatusDesc, $keyNames] = $parameters;
    $valueExists = array_key_exists($valueName, $current);
    if ($valueExists) {
        $preResult = array_merge($valueDesc, [$valueNewName => $current[$valueName]]);
    } elseif (in_array($status, $checkStatusDesc, true)) {
        $preResult = array_merge($valueDesc, [$valueNewName => getCopy($current, $keyNames, $valueName)]);
    } else {
        $preResult = $valueDesc;
    }
    return $preResult;
}
