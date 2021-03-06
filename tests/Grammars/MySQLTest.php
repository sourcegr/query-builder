<?php

namespace Tests\Grammars;

use Sourcegr\QueryBuilder\Grammars\MySQL;
use PHPUnit\Framework\TestCase;

class MySQLTest extends TestCase
{
    public function testPlaceHolder(): void
    {
        $expected = '?';
        $grammar = new MySQL(['DB' => 'aek']);
        $actual = $grammar->getPlaceholder();
        $this->assertEquals($expected, $actual, 'Placeholder failure');
    }

    public function testCreateWithLimitNoOffset(): void
    {
        $expected = 'LIMIT 10';
        $grammar = new MySQL(['DB' => 'aek']);
        $actual = $grammar->createLimit(10);
        $this->assertEquals($expected, $actual, 'testCreateWithLimitNoOffset failure');
    }
    public function testCreateWithLimitWithOffset(): void
    {
        $expected = 'LIMIT 10 OFFSET 20';
        $grammar = new MySQL(['DB' => 'aek']);
        $actual = $grammar->createLimit(10, 20);
        $this->assertEquals($expected, $actual, 'testCreateWithLimitWithOffset failure');
    }
    public function testCreateNoLimitWithOffset(): void
    {
        $grammar = new MySQL(['DB' => 'aek']);
        $actual = $grammar->createLimit(null, 20);
        $this->assertNull($actual, 'testCreateNoLimitWithOffset failure');
    }
    public function testCreateNoLimitNoOffset(): void
    {
        $grammar = new MySQL(['DB' => 'aek']);
        $actual = $grammar->createLimit(null, null);
        $this->assertNull($actual, 'testCreateNoLimitNoOffset failure');
    }
    public function testCreateWithNone(): void
    {
        $grammar = new MySQL(['DB' => 'aek']);
        $actual = $grammar->createLimit();
        $this->assertNull($actual, 'testCreateWithNone failure');
    }
}
