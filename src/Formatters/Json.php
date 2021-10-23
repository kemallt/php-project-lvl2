<?php

namespace Differ\Formatters\Json;

use function Differ\Additional\getKeyNames;

use const Differ\Differ\ADDED;
use const Differ\Differ\DELETED;
use const Differ\Differ\MODIFIED;
use const Differ\Differ\NESTED;
use const Differ\Differ\NEWVALUENAME;
use const Differ\Differ\STATUSNAME;
use const Differ\Differ\UNCHANGED;
use const Differ\Differ\VALUENAME;

function generateDiff(mixed $diffData): string
{
    $iter = function ($current, $parentStatus) use (&$iter) {
        if (!is_array($current)) {
            return $current;
        }
        $status = $current[STATUSNAME] === NESTED ? UNCHANGED : $current[STATUSNAME];
        $addStatus = !($parentStatus === null || ($status !== UNCHANGED && $parentStatus === $status));
        $preResult = getValue($current, $status, $addStatus);
        return ($status === UNCHANGED) ? getObjectEl($iter, $current, $status, $preResult) : $preResult;
    };

    $jsonData = $iter($diffData, null);
    $jsonDiff = json_encode($jsonData, 0);
    if ($jsonDiff === false) {
        return '';
    } else {
        return $jsonDiff;
    }
}

function getObjectEl(callable $iter, array $current, string $status, array $preResult): array
{
    $statusDiff = $status === NESTED ? UNCHANGED : $status;
    return array_reduce(
        array_keys($current),
        function ($acc, $itemName) use ($iter, $current, $statusDiff) {
            $keyNames = getKeyNames();
            if (in_array($itemName, $keyNames, true)) {
                return $acc;
            }
            return array_merge($acc, [$itemName => $iter($current[$itemName], $statusDiff)]);
        },
        $preResult
    );
}

function getCopy(array $current, string $valueName): array
{
    return array_reduce(array_keys($current), function ($acc, $itemName) use (&$current, $valueName) {
        $keyNames = getKeyNames();
        if (in_array($itemName, $keyNames, true)) {
            return $acc;
        }
        if (array_key_exists($valueName, $current[$itemName])) {
            $preResult = array_merge($acc, [$itemName => $current[$itemName][$valueName]]);
        } else {
            $preResult = array_merge($acc, [$itemName => getCopy($current[$itemName], $valueName)]);
        }
        return $preResult;
    }, []);
}

function getValue(array $current, string $status, bool $addStatus): array
{
    $valueDesc = $addStatus ? ['status' => $status] : [];
    $valueStatuses = [DELETED, MODIFIED];
    $valueAddStatuses = [ADDED, MODIFIED];
    $preResult = fillValueFields($valueDesc, $current, [VALUENAME, 'value', $status, $valueStatuses]);
    return fillValueFields($preResult, $current, [NEWVALUENAME, 'newValue', $status, $valueAddStatuses]);
}

function fillValueFields(array $valueDesc, array $current, array $parameters): array
{
    [$valueName, $valueNewName, $status, $checkStatusDesc] = $parameters;
    $valueExists = array_key_exists($valueName, $current);
    if ($valueExists) {
        $preResult = array_merge($valueDesc, [$valueNewName => $current[$valueName]]);
    } elseif (in_array($status, $checkStatusDesc, true)) {
        $preResult = array_merge($valueDesc, [$valueNewName => getCopy($current, $valueName)]);
    } else {
        $preResult = $valueDesc;
    }
    return $preResult;
}
