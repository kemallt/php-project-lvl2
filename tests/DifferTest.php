<?php

namespace Differ\Differ\Tests;

use function Differ\Differ\genDiff;

class DifferTest extends \PHPUnit\Framework\TestCase
{
    private $expectedAnswer1;
    private $expectedAnswer2;
    private $expectedAnswer3;

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

}