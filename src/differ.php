<?php

namespace Differ\Differ;

function genDiff($pathToFile1, $pathToFile2)
{
    $data1 = json_decode(file_get_contents($pathToFile1), true);
    $data2 = json_decode(file_get_contents($pathToFile2), true);

    $result = array();
    foreach ($data1 as $itemName => $itemValue) {
        $itemValue = stringifyBoolean($itemValue);
        if (array_key_exists($itemName, $data2)) {
            $result = addResArrVal($result, $itemName, $itemValue, $data2);
            unset($data2[$itemName]);
        } else {
            $result[] = array('name' => $itemName, 'value' => (string)$itemValue, 'sign' => '-');
        }
    }
    foreach ($data2 as $item2Name => $item2Value) {
        $item2Value = stringifyBoolean($item2Value);
        $result[] = array('name' => $item2Name, 'value' => (string)$item2Value, 'sign' => '+');
    }
    $result = sortDiffArr($result);
    return array_reduce($result, function ($resultString, $item) {
        $resultString .= $item['sign'] . ' ' . $item['name'] . ': ' . $item['value'] . PHP_EOL;
        return $resultString;
    }, '');
}

function stringifyBoolean($string)
{
    if ($string === true) {
        $res = 'true';
    } elseif ($string === false) {
        $res = 'false';
    } else {
        $res = $string;
    }
    return $res;
}

function sortDiffArr($diffArr)
{
    usort($diffArr, function ($item1, $item2) {
        if ($item1['name'] > $item2['name']) {
            return 1;
        } elseif ($item1['name'] < $item2['name']) {
            return -1;
        } else {
            if ($item1['sign'] === '-') {
                return -1;
            } else {
                return 1;
            }
        }
    });
    return $diffArr;
}

function addResArrVal($resArr, $itemName, $itemValue, $data2)
{
    if ($itemValue === stringifyBoolean($data2[$itemName])) {
        $resArr[] = array('name' => $itemName, 'value' => (string)$itemValue, 'sign' => ' ');
    } else {
        $item2Value = stringifyBoolean($data2[$itemName]);
        $resArr[] = array('name' => $itemName, 'value' => (string)$itemValue, 'sign' => '-');
        $resArr[] = array('name' => $itemName, 'value' => (string)$item2Value, 'sign' => '+');
    }
    return $resArr;
}
