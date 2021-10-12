<?php

namespace Differ\Formatters\Json;

function generateDiff($diffData)
{
    $iter = function ($curArr, $parentStatus, $justCopy = false) use (&$iter) {
        if (!is_array($curArr)) {
            return $curArr;
        }
        $keyNames = ['_sign', '_signAdd', 'value', 'valueAdd'];
        $status = getStatus($curArr);
        if ($parentStatus === null || ($parentStatus === $status && $status !== 'unchanged')) {
            $addStatus = false;
        } else {
            $addStatus = true;
        }
//        $addStatus = !($parentStatus === null || ($status !== 'unchanged' && $parentStatus === $status));
        $resArr = getValueArr($curArr, $status, $addStatus, $keyNames);
        if ($status === 'unchanged') {
            $resArr = array_reduce(
                array_keys($curArr),
                function ($accArr, $itemName) use (&$iter, &$curArr, $status, $keyNames) {
                    if (in_array($itemName, $keyNames)) {
                        return $accArr;
                    }
                    $accArr[$itemName] = $iter($curArr[$itemName], $status);
                    return $accArr;
                },
                $resArr
            );
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
    if (array_key_exists('value', $curArr)) {
        $valueArr['value'] = $curArr['value'];
    } elseif ($status === 'removed' || $status === 'modified') {
        $valueArr['value'] = getCopyArr($curArr, $keyNames, 'value');
    }
    if (array_key_exists('valueAdd', $curArr)) {
        $valueArr['newValue'] = $curArr['valueAdd'];
    } elseif ($status === 'added' || $status === 'modified') {
        $valueArr['newValue'] = getCopyArr($curArr, $keyNames, 'valueAdd');
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
