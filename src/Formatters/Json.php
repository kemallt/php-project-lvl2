<?php

namespace Differ\Formatters\Json;

function getObjectEl(callable $iter, array $curArr, array $parameters): array
{
    [$status, $keyNames, $resArr] = $parameters;
    return array_reduce(
        array_keys($curArr),
        function ($accArr, $itemName) use ($iter, $curArr, $status, $keyNames) {
            if (in_array($itemName, $keyNames, true)) {
                return $accArr;
            }
            $resArr = $accArr;
            $resArr[$itemName] = $iter($curArr[$itemName], $status);
            return $resArr;
        },
        $resArr
    );
}

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

function getCopyArr(array $curArr, array $keyNames, string $valueName): array
{
    return array_reduce(array_keys($curArr), function ($accArr, $itemName) use (&$curArr, $keyNames, $valueName) {
        if (in_array($itemName, $keyNames, true)) {
            return $accArr;
        }
        if (array_key_exists($valueName, $curArr[$itemName])) {
            $accArr[$itemName] = $curArr[$itemName][$valueName];
        } else {
            $accArr[$itemName] = getCopyArr($curArr[$itemName], $keyNames, $valueName);
        }
        return $accArr;
    }, []);
}

function getValueArr(array $curArr, string $status, bool $addStatus, array $keyNames): array
{
    $valueArr = $addStatus ? ['status' => $status] : [];
    $valueStatuses = ['removed', 'modified'];
    $valueAddStatuses = ['added', 'modified'];
    $resArr = fillValueFields($valueArr, $curArr, ['value', 'value', $status, $valueStatuses, $keyNames]);
    $resArrFin = fillValueFields($resArr, $curArr, ['valueAdd', 'newValue', $status, $valueAddStatuses, $keyNames]);
    return $resArrFin;
}

function fillValueFields(array $valueArr, array $curArr, array $parameters): array
{
    $resArr = $valueArr;
    [$valueName, $valueNewName, $status, $checkStatusArr, $keyNames] = $parameters;
    $valueExists = array_key_exists($valueName, $curArr);
    if ($valueExists) {
        $resArr[$valueNewName] = $curArr[$valueName];
    } elseif (in_array($status, $checkStatusArr, true)) {
        $resArr[$valueNewName] = getCopyArr($curArr, $keyNames, $valueName);
    }
    return $resArr;
}

function getStatus(array $curArr): string
{
    $sign = $curArr['_sign'];
    $signAdd = $curArr['_signAdd'];
    if ($sign === '+') {
        return 'added';
    } elseif ($sign === ' ') {
        return 'unchanged';
    } elseif ($signAdd === '+') {
        return 'modified';
    } else {
        return 'removed';
    }
}
