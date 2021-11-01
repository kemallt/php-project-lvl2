<?php

namespace Differ\TreeBuilder;

const ADDED = 'added';
const MODIFIED = 'modified';
const DELETED = 'deleted';
const UNCHANGED = 'unchanged';
const NESTED = 'nested';
const STATUSNAME = 'status';
const VALUENAME = 'value';
const NEWVALUENAME = 'newValue';

function generateDiffOfTwoObjects(object $data1, object $data2): array
{
    $diffKeys = array_merge(array_keys((array)$data1), array_keys((array)$data2));
    return processData($diffKeys, $data1, $data2);
}

function processData(array $diffKeys, object $data1, object $data2): array
{
    return array_reduce(
        $diffKeys,
        function ($acc, $itemName) use ($data1, $data2): array {
            return array_merge($acc, [$itemName => processItem($itemName, $data1, $data2)]);
        },
        [STATUSNAME => NESTED]
    );
}

function processItem(string $itemName, object $data1, object $data2): array
{
    if (!array_key_exists($itemName, (array)$data2)) {
        return createNode($itemName, VALUENAME, DELETED, $data1);
    }
    if (!array_key_exists($itemName, (array)$data1)) {
        return addData2ToNode([STATUSNAME => ADDED], $itemName, $data2, NEWVALUENAME);
    }
    if ($data1->$itemName === $data2->$itemName) {
        return createNode($itemName, VALUENAME, UNCHANGED, $data1);
    }
    if (!is_object($data1->$itemName) || !is_object($data2->$itemName)) {
        $node = createNode($itemName, VALUENAME, MODIFIED, $data1);
        return addData2ToNode($node, $itemName, $data2, NEWVALUENAME);
    }
    return generateDiffOfTwoObjects($data1->$itemName, $data2->$itemName);
}

function createNode(string $itemName, string $valueName, string $status, object $data1): array
{
    if (is_object($data1->$itemName)) {
        return array_merge(generateDiffOfTwoObjects($data1->$itemName, new \StdClass()), [STATUSNAME => $status]);
    }
    return array_merge([$valueName => $data1->$itemName], [STATUSNAME => $status]);
}

function addData2ToNode(array $node, string $itemName, object $data, string $valueName): array
{
    if (is_object($data->$itemName)) {
        return array_merge(generateDiffOfTwoObjects(new \StdClass(), $data->$itemName), $node);
    }
    return array_merge([$valueName => $data->$itemName], $node);
}
