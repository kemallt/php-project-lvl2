<?php

namespace Differ\Differ\Tests;

use function Differ\Differ\genDiff;

class DifferTest extends \PHPUnit\Framework\TestCase
{
    private $expectedAnswer1;
    private $expectedAnswer2;
    private $expectedAnswer3;
    private $expectedAnswer4;


    /**
     * @dataProvider dataProvider
     */
    public function testDiffer($file1, $file2, $formatter, $expected)
    {
        $fixturesDir = __DIR__ . '/fixtures/';
        $filePath1 = $fixturesDir . $file1;
        $filePath2 = $fixturesDir . $file2;
        $actualAnswer = ($formatter === null) ? genDiff($filePath1, $filePath2) : genDiff($filePath1, $filePath2, $formatter);
        $this->assertEquals($expected, $actualAnswer);
    }

    public function dataProvider()
    {
        $expectedAnswer1 = file_get_contents(__DIR__ . '/fixtures/expectedSimple.txt');
        $expectedAnswer2 = file_get_contents(__DIR__ . '/fixtures/expectedComplex.txt');
        $expectedAnswer3 = file_get_contents(__DIR__ . '/fixtures/expectedComplexPlain.txt');
        $expectedAnswer4 = json_encode(json_decode(file_get_contents(__DIR__ . '/fixtures/expectedComplexJson.txt')));

        return [
//            'simpleJsonDefault' => ['file1.json', 'file2.json', null, $expectedAnswer1],
            'complexJsonDefault' => ['complexFile1.json', 'complexFile2.json', null, $expectedAnswer2],
            'complexJsonPlain' => ['complexFile1.json', 'complexFile2.json', 'plain', $expectedAnswer3],
            'complexJsonJson' => ['complexFile1.json', 'complexFile2.json', 'json', $expectedAnswer4],
            'complexJsonJson' => ['complexFile1.json', 'complexFile2.json', 'json', $expectedAnswer4],
            'simpleYamlStylish' => ['file1.yml', 'file2.yml', 'stylish', $expectedAnswer1],
            'complexYamlStylish' => ['complexFile1.yml', 'complexFile2.yaml', 'stylish', $expectedAnswer2]
        ];
    }
}
