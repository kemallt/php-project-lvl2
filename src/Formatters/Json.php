<?php

namespace Differ\Formatters\Json;

use function Differ\Additional\getStatus;

function generateDiff(mixed $diffData, array $keyNames): string
{
    $iter = function ($curArr, $parentStatus) use (&$iter, $keyNames) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        $status = getStatus($curArr);
        $addStatus = !($parentStatus === null || ($status !== 'unchanged' && $parentStatus === $status));
        $resArr = getValueArr($curArr, $status, $addStatus, $keyNames);
        $resArrFin = ($status === 'unchanged') ? getObjectEl($iter, $curArr, [$status, $keyNames, $resArr]) : $resArr;
        return $resArrFin;
    };

    $jsonData = $iter($diffData, null);
    $jsonDiff = json_encode($jsonData, 0);
    if ($jsonDiff === false) {
        return '';
    } else {
        return $jsonDiff;
    }
}

function getObjectEl(callable $iter, array $curArr, array $parameters): array
{
    [$status, $keyNames, $resArr] = $parameters;
    return array_reduce(
        array_keys($curArr),
        function ($accArr, $itemName) use ($iter, $curArr, $status, $keyNames) {
            if (in_array($itemName, $keyNames, true)) {
                return $accArr;
            }
            return array_merge([$itemName => $iter($curArr[$itemName], $status)], $accArr);
        },
        $resArr
    );
}

function getCopyArr(array $curArr, array $keyNames, string $valueName): array
{
    return array_reduce(array_keys($curArr), function ($accArr, $itemName) use (&$curArr, $keyNames, $valueName) {
        if (in_array($itemName, $keyNames, true)) {
            return $accArr;
        }
        if (array_key_exists($valueName, $curArr[$itemName])) {
            $resArr = array_merge([$itemName => $curArr[$itemName][$valueName]], $accArr);
        } else {
            $resArr = array_merge([$itemName => getCopyArr($curArr[$itemName], $keyNames, $valueName)], $accArr);
        }
        return $resArr;
    }, []);
}

function getValueArr(array $curArr, string $status, bool $addStatus, array $keyNames): array
{
    $valueArr = $addStatus ? ['status' => $status] : [];
    $valueStatuses = ['deleted', 'modified'];
    $valueAddStatuses = ['added', 'modified'];
    $resArr = fillValueFields($valueArr, $curArr, ['_value', 'value', $status, $valueStatuses, $keyNames]);
    $resArrFin = fillValueFields($resArr, $curArr, ['_newValue', 'newValue', $status, $valueAddStatuses, $keyNames]);
    return $resArrFin;
}

function fillValueFields(array $valueArr, array $curArr, array $parameters): array
{
    [$valueName, $valueNewName, $status, $checkStatusArr, $keyNames] = $parameters;
    $valueExists = array_key_exists($valueName, $curArr);
    if ($valueExists) {
        $resArr = array_merge([$valueNewName => $curArr[$valueName]], $valueArr);
    } elseif (in_array($status, $checkStatusArr, true)) {
        $resArr = array_merge([$valueNewName => getCopyArr($curArr, $keyNames, $valueName)], $valueArr);
    } else {
        $resArr = $valueArr;
    }
    return $resArr;
}
