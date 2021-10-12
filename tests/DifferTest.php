<?php

namespace Differ\Differ\Tests;

use function Differ\Differ\genDiff;

class DifferTest extends \PHPUnit\Framework\TestCase
{
    private $expectedAnswer1;
    private $expectedAnswer2;
    private $expectedAnswer3;
    private $expectedAnswer4;

    public function setUp(): void
    {
        $this->expectedAnswer1 = '{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}';
        $this->expectedAnswer2 = file_get_contents(__DIR__ . '/fixtures/expectedComplex.txt');
        $this->expectedAnswer3 = file_get_contents(__DIR__ . '/fixtures/expectedComplexPlain.txt');
        $this->expectedAnswer4 = __DIR__ . '/fixtures/expectedComplexJson.txt';
        $this->expectedAnswer5 = file_get_contents(__DIR__ . '/fixtures/expectedComplex1.txt');
    }

    public function testComplex1JsonStylish()
    {
        $file1 = __DIR__ . '/fixtures/complex1File1.json';
        $file2 = __DIR__ . '/fixtures/complex1File2.json';
        $actualAnswer = genDiff($file1, $file2);
        $this->assertEquals($this->expectedAnswer5, $actualAnswer);
    }

    public function testGenDiffComplexJsonPlain()
    {
        $file1 = __DIR__ . '/fixtures/complexFile1.json';
        $file2 = __DIR__ . '/fixtures/complexFile2.json';
        $actualAnswer = genDiff($file1, $file2, 'plain');
        $this->assertEquals($this->expectedAnswer3, $actualAnswer);
    }

    public function testGenDiffComplexJson()
    {
        $file1 = __DIR__ . '/fixtures/complexFile1.json';
        $file2 = __DIR__ . '/fixtures/complexFile2.json';
        $actualAnswer = genDiff($file1, $file2, 'stylish');
        $this->assertEquals($this->expectedAnswer2, $actualAnswer);
    }

    public function testGenDiffComplexYaml()
    {
        $file1 = __DIR__ . '/fixtures/complexFile1.yml';
        $file2 = __DIR__ . '/fixtures/complexFile2.yml';
        $actualAnswer = genDiff($file1, $file2, 'stylish');
        $this->assertEquals($this->expectedAnswer2, $actualAnswer);
    }

    public function testGenDiffYaml()
    {
        $file1 = __DIR__ . '/fixtures/file1.yml';
        $file2 = __DIR__ . '/fixtures/file2.yml';
        $actualAnswer = genDiff($file1, $file2, 'stylish');
        $this->assertEquals($this->expectedAnswer1, $actualAnswer);
    }

    public function testGenDiffJson()
    {
        $file1 = __DIR__ . '/fixtures/file1.json';
        $file2 = __DIR__ . '/fixtures/file2.json';
        $actualAnswer = genDiff($file1, $file2, 'stylish');
        $this->assertEquals($this->expectedAnswer1, $actualAnswer);
    }

    public function testGenDiffComplexJsonJson()
    {
        $file1 = __DIR__ . '/fixtures/complexFile1.json';
        $file2 = __DIR__ . '/fixtures/complexFile2.json';
        $actualAnswer = genDiff($file1, $file2, 'json');
        $this->assertJsonStringEqualsJsonFile($this->expectedAnswer4, $actualAnswer);
    }

}