<?php

namespace Differ\Differ\Tests;

use function Differ\Differ\genDiff;

class DifferTest extends \PHPUnit\Framework\TestCase
{
    public function testGenDiff()
    {
        $file1 = __DIR__ . '/fixtures/file1.json';
        $file2 = __DIR__ . '/fixtures/file2.json';
        $expectedAnswer = '- follow: false
  host: hexlet.io
- proxy: 123.234.53.22
- timeout: 50
+ timeout: 20
+ verbose: true
';
        $actualAnswer = genDiff($file1, $file2);
        $this->assertEquals($expectedAnswer, $actualAnswer);
    }
}