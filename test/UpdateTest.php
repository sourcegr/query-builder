<?php

namespace Test;


use Sre\QueryBuilder\QueryBuilder;
use Sre\QueryBuilder\DB;
use Sre\QueryBuilder\Raw;
use Sre\QueryBuilder\Grammars\MySQL;
use Sre\QueryBuilder\Grammars\UpdateErrorException;
use PHPUnit\Framework\TestCase;
use Test\Stub\Grammar;
use InvalidArgumentException;

class UpdateTest extends TestCase
{
    /*
     *
     */
    static $table = 'table';

    private function init()
    {
        $grammar = new Grammar();
        $qb = new QueryBuilder(static::$table);
        $qb->setGrammar($grammar);
        return $qb;
    }

    public function testUpdateArray()
    {
        $res = $this->init();
        [$actual, $params] = $res->update(['name'=> 'new name']);

        $expected = 'UPDATE table SET name=?';
        $expectedParams = ['new name'];

        $this->assertEquals($expected, $actual, 'testUpdate SQL');
        $this->assertEquals($expectedParams, $params, 'testUpdate params');
    }
    public function testUpdateWith2Parameters()
    {
        $res = $this->init();
        [$actual, $params] = $res->update('name', 'new name');

        $expected = 'UPDATE table SET name=?';
        $expectedParams = ['new name'];

        $this->assertEquals($expected, $actual, 'testUpdateWith2Parameters SQL');
        $this->assertEquals($expectedParams, $params, 'testUpdateWith2Parameters params');
    }

    public function testUpdateRaw()
    {
        $res = $this->init();
        [$actual, $params] = $res->update(['name'=> new Raw('NOW()')]);

        $expected = 'UPDATE table SET name=NOW()';
        $expectedParams = [];

        $this->assertEquals($expected, $actual, 'testUpdateRaw SQL');
        $this->assertEquals($expectedParams, $params, 'testUpdateRaw params');
    }

    public function testUpdateWithWhere()
    {
        $res = $this->init();
        [$actual, $params] = $res->where('id')->update(['name'=> 'new name']);

        $expected = 'UPDATE table SET name=? WHERE id IS NOT NULL';
        $expectedParams = ['new name'];

        $this->assertEquals($expected, $actual, 'testUpdateWithWhere SQL');
        $this->assertEquals($expectedParams, $params, 'testUpdateWithWhere params');
    }

    public function testFailsOnMoreThan2Parameters() {
        $res = $this->init();
        $this->expectException(InvalidArgumentException::class);
        $res->update('id', 'name', 'ILLEGAL');
    }

    public function testFailsOnNoInput()
    {
        $res = $this->init();
        $this->expectException(InvalidArgumentException::class);
        $res->update();
    }

    public function testFailsOnNoArray()
    {
        $res = $this->init();
        $this->expectException(InvalidArgumentException::class);
        $res->update('a');
    }

    public function testReturnsZeroOnEmptyArray()
    {
        $res = $this->init();
        $expected = 0;
        $actual = $res->update([]);
        $this->assertEquals($expected, $actual, 'testUpdate');
    }

    public function testFailsOnNonAssocArray()
    {
        $res = $this->init();
        $this->expectException(InvalidArgumentException::class);
        $res->update(['id', 'name']);
    }

    public function testFailsOnLimit()
    {
        $res = $this->init();
        $this->expectException(UpdateErrorException::class);
        $res->limit(4)->update(['name'=> 'new name']);
    }
}