<?php

namespace Differ\Differ;

function genDiff($pathToFile1, $pathToFile2)
{
    $data1 = json_decode(file_get_contents($pathToFile1), true);
    $data2 = json_decode(file_get_contents($pathToFile2), true);

    $result = array();
    foreach ($data1 as $itemName => $itemValue) {
        if ($itemValue === true) {
            $itemValue = 'true';
        } elseif ($itemValue === false) {
            $itemValue = 'false';
        }
        if (array_key_exists($itemName, $data2)) {
            if ($itemValue === $data2[$itemName]) {
                $result[] = array('name' => $itemName, 'value' => (string)$itemValue, 'sign' => ' ');
            } else {
                if ($data2[$itemName] === true) {
                    $item2Value = 'true';
                } elseif ($data2[$itemName] === false) {
                    $item2Value = 'false';
                } else {
                    $item2Value = $data2[$itemName];
                }
                $result[] = array('name' => $itemName, 'value' => (string)$itemValue, 'sign' => '-');
                $result[] = array('name' => $itemName, 'value' => (string)$item2Value, 'sign' => '+');
            }
            unset($data2[$itemName]);
        } else {
            $result[] = array('name' => $itemName, 'value' => (string)$itemValue, 'sign' => '-');
        }
    }
    foreach ($data2 as $item2Name => $item2Value) {
        if ($item2Value === true) {
            $item2Value = 'true';
        } elseif ($item2Value === false) {
            $item2Value = 'false';
        }
        $result[$item2Name] = array('name' => $item2Name, 'value' => (string)$item2Value, 'sign' => '+');
    }
    usort($result, function($item1, $item2) {
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
    $resultString = '';
    foreach ($result as $item) {
        $resultString .= $item['sign'] . ' ' . $item['name'] . ': ' . $item['value'] . PHP_EOL;
    }
    return $resultString;
}