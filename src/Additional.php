<?php

namespace Differ\Additional;

function stringifyItem(mixed $item): mixed
{
    if ($item === true) {
        $res = 'true';
    } elseif ($item === false) {
        $res = 'false';
    } elseif ($item === null) {
        $res = 'null';
    } else {
        $res = $item;
    }
    return $res;
}

function sortArray(array $arr): array
{
    $iter = function ($keysArr) use (&$iter) {
        $swapped = false;
        $subArr = array($keysArr[0]);
        for ($i = 1; $i < count($keysArr); $i++) {
            if ($keysArr[$i] < $subArr[$i - 1]) {
                $subArr[$i] = $subArr[$i - 1];
                $subArr[$i - 1] = $keysArr[$i];
                $swapped = true;
            } else {
                $subArr[$i] = $keysArr[$i];
            }
        }
        return $swapped ? $iter($subArr) : $subArr;
    };
    $newKeysArr = $iter(array_keys($arr));
    return array_reduce($newKeysArr, function ($accArr, $key) use ($arr) {
        $accArr[$key] = $arr[$key];
        return $accArr;
    }, []);
}
