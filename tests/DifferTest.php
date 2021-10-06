<?php

namespace Differ\Differ\Tests;

use function Differ\Differ\genDiff;

class DifferTest extends \PHPUnit\Framework\TestCase
{
    private $expectedAnswer1;

    public function setUp(): void
    {
        $this->expectedAnswer1 = '- follow: false
  host: hexlet.io
- proxy: 123.234.53.22
- timeout: 50
+ timeout: 20
+ verbose: true
';
    }

    public function testGenDiffYaml()
    {
        $file1 = __DIR__ . '/fixtures/file1.yml';
        $file2 = __DIR__ . '/fixtures/file2.yml';
        $actualAnswer = genDiff($file1, $file2);
        $this->assertEquals($this->expectedAnswer1, $actualAnswer);
    }

    public function testGenDiffJson()
    {
        $file1 = __DIR__ . '/fixtures/file1.json';
        $file2 = __DIR__ . '/fixtures/file2.json';
        $actualAnswer = genDiff($file1, $file2);
        $this->assertEquals($this->expectedAnswer1, $actualAnswer);
    }
}