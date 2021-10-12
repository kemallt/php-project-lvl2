<?php

namespace Differ\Formatters\Json;

function getObjectEl(&$iter, $curArr, $parameters)
{
    [$status, $keyNames, $resArr] = $parameters;
    return array_reduce(
        array_keys($curArr),
        function ($accArr, $itemName) use (&$iter, $curArr, $status, $keyNames) {
            if (in_array($itemName, $keyNames)) {
                return $accArr;
            }
            $accArr[$itemName] = $iter($curArr[$itemName], $status);
            return $accArr;
        },
        $resArr
    );
}

function generateDiff($diffData, $keyNames)
{
    $iter = function ($curArr, $parentStatus) use (&$iter, $keyNames) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        $status = getStatus($curArr);
        $addStatus = !($parentStatus === null || ($status !== 'unchanged' && $parentStatus === $status));
        $resArr = getValueArr($curArr, $status, $addStatus, $keyNames);
        if ($status === 'unchanged') {
            $resArr = getObjectEl($iter, $curArr, [$status, $keyNames, $resArr]);
        }
        return $resArr;
    };

    $jsonData = $iter($diffData, null);
    return json_encode($jsonData, 0);
}

function getCopyArr($curArr, $keyNames, $valueName)
{
    return array_reduce(array_keys($curArr), function ($accArr, $itemName) use (&$curArr, $keyNames, $valueName) {
        if (in_array($itemName, $keyNames)) {
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

function getValueArr($curArr, $status, $addStatus, $keyNames)
{
    $valueArr = [];
    if ($addStatus) {
        $valueArr['status'] = $status;
    }
    $valueStatuses = ['removed', 'modified'];
    $valueAddStatuses = ['added', 'modified'];
    $valueArr = fillValueFields($valueArr, $curArr, ['value', 'value', $status, $valueStatuses, $keyNames]);
    $valueArr = fillValueFields($valueArr, $curArr, ['valueAdd', 'newValue', $status, $valueAddStatuses, $keyNames]);
    return $valueArr;
}

function fillValueFields($valueArr, $curArr, $parameters)
{
    [$valueName, $valueNewName, $status, $checkStatusArr, $keyNames] = $parameters;
    $valueExists = array_key_exists($valueName, $curArr);
    if ($valueExists) {
        $valueArr[$valueNewName] = $curArr[$valueName];
    } elseif (in_array($status, $checkStatusArr)) {
        $valueArr[$valueNewName] = getCopyArr($curArr, $keyNames, $valueName);
    }
    return $valueArr;
}

function getStatus($curArr)
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
