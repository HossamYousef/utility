<?php

namespace Avoxx\Utility\Tests;

use ArrayObject;
use Avoxx\Utility\ArrayHelper;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class ArrayHelperTest extends TestCase
{

    public function validArrayAccessibleDataProvider()
    {
        return [
            'empty-array'   => [[]],
            'simple-array'  => [[1, 2]],
            'assoc-array'   => [['1' => 1, '2' => 2]],
            'array-access'  => [new ArrayObject],
        ];
    }

    /**
     * @dataProvider validArrayAccessibleDataProvider
     */
    public function testAccessibleReturnsTrue($accessible)
    {
        $this->assertTrue(ArrayHelper::accessible($accessible));
    }

    public function invalidArrayAccessibleDataProvider()
    {
        return [
            'null'              => [null],
            'true'              => [true],
            'false'             => [false],
            'string'            => ['foo'],
            'no-array-access'   => [new stdClass],
            'array-object'      => [(object) ['1' => 1, '2' => 2]],
        ];
    }

    /**
     * @dataProvider invalidArrayAccessibleDataProvider
     */
    public function testAccessibleReturnsFalse($accessible)
    {
        $this->assertFalse(ArrayHelper::accessible($accessible));
    }

    public function testExistsReturnsTrue()
    {
        $this->assertTrue(ArrayHelper::exists([1], 0));
        $this->assertTrue(ArrayHelper::exists([null], 0));
        $this->assertTrue(ArrayHelper::exists(['1' => 1], '1'));
        $this->assertTrue(ArrayHelper::exists(new ArrayObject(['1' => 1]), '1'));
    }

    public function testExistsReturnsFalse()
    {
        $this->assertFalse(ArrayHelper::exists([1], 1));
        $this->assertFalse(ArrayHelper::exists([null], 1));
        $this->assertFalse(ArrayHelper::exists(['1' => 1], '2'));
        $this->assertFalse(ArrayHelper::exists(new ArrayObject(['1' => 1]), '2'));
    }

    public function testGetReturnsValue()
    {
        $array = [
            'user' => 'Merlin',
            'country' => [
                'name' => 'DE',
            ],
        ];

        $arrayObject = new ArrayObject($array);
        $nestedArrayObject = new ArrayObject(['nested' => $arrayObject]);

        // array
        $this->assertEquals('Merlin', ArrayHelper::get($array, 'user'));
        $this->assertEquals('DE', ArrayHelper::get($array, 'country.name'));

        // array object
        $this->assertEquals('Merlin', ArrayHelper::get($arrayObject, 'user'));
        $this->assertEquals('DE', ArrayHelper::get($arrayObject, 'country.name'));

        // nested array object
        $this->assertEquals('Merlin', ArrayHelper::get($nestedArrayObject, 'nested.user'));
        $this->assertEquals('DE', ArrayHelper::get($nestedArrayObject, 'nested.country.name'));
    }

    public function testGetReturnsDefaultValueIfKeyDoesNotExistInArray()
    {
        // array
        $this->assertNull(ArrayHelper::get([], 'user'));
        $this->assertEquals('default', ArrayHelper::get([], 'user', 'default'));
        $this->assertEquals([], ArrayHelper::get([], null, 'default'));

        // no array access
        $this->assertEquals('default', ArrayHelper::get(new stdClass, null, 'default'));

        // array object
        $this->assertNull(ArrayHelper::get(new ArrayObject, 'country.name'));
    }

    public function testFirst()
    {
        $array = [100, 200, 300,];
        $first = ArrayHelper::first($array, function ($value) {
           return $value >= 200;
        });

        $this->assertEquals(200, $first);
        $this->assertEquals(100, ArrayHelper::first($array));

        $first = ArrayHelper::first($array, function ($value) {
            return $value > 500;
        });

        $this->assertNull($first);

        $this->assertNull(ArrayHelper::first([]));
    }

    public function testLast()
    {
        $array = [100, 200, 300,];
        $last = ArrayHelper::last($array, function ($value) {
            return $value < 300;
        });

        $this->assertEquals(200, $last);
        $this->assertEquals(300, ArrayHelper::last($array));
        $this->assertNull(ArrayHelper::last([]));

        $last = ArrayHelper::last($array, function ($value, $key) {
           return $key < 2;
        });

        $this->assertEquals(200, $last);
    }

    public function testHasReturnsTrue()
    {
        $array = [
            'user' => 'Merlin',
            'country' => [
                'name' => 'DE',
            ],
        ];
        $arrayObject = new ArrayObject($array);
        $nestedArrayObject = new ArrayObject(['nested' => $arrayObject]);

        // array
        $this->assertTrue(ArrayHelper::has($array, 'user'));
        $this->assertTrue(ArrayHelper::has($array, 'country.name'));

        // array object
        $this->assertTrue(ArrayHelper::has($arrayObject, 'user'));
        $this->assertTrue(ArrayHelper::has($arrayObject, 'country.name'));

        // nested array object
        $this->assertTrue(ArrayHelper::has($nestedArrayObject, 'nested.user'));
        $this->assertTrue(ArrayHelper::has($nestedArrayObject, 'nested.country.name'));
    }

    public function testHasReturnsFalseIfKeyDoesNotExistInArray()
    {
        $this->assertFalse(ArrayHelper::has([], 'user'));
        $this->assertFalse(ArrayHelper::has([], 'country.name'));
        $this->assertFalse(ArrayHelper::has([], null));
        $this->assertFalse(ArrayHelper::has([], []));
        $this->assertFalse(ArrayHelper::has(new ArrayObject, 'user'));
    }

    public function testWhereValue()
    {
        $array = [100, '200', 300,];
        $where = ArrayHelper::where($array, function ($value) {
            return is_string($value);
        });

        $this->assertEquals([1 => 200], $where);

        $where = ArrayHelper::where($array, function ($value) {
            return is_int($value);
        });

        $this->assertEquals([0 => 100, 2 => 300], $where);
    }

    public function testWhereKey()
    {
        $array = ['first' => 1, 2 => 2];
        $where = ArrayHelper::where($array, function ($value, $key) {
            return is_numeric($key);
        });

        $this->assertEquals([2 => 2], $where);
    }

    public function testOnly()
    {
        $array = [
            'user' => 'Merlin',
            'country' => [
                'name' => 'DE',
            ],
        ];

        $this->assertEquals(['user' => 'Merlin'], ArrayHelper::only($array, 'user'));
        $this->assertEquals($array, ArrayHelper::only($array, ['user', 'country']));
    }

    public function testForget()
    {
        $array = ['user' => 'Merlin', 'country' => ['name' => 'DE']];
        ArrayHelper::forget($array, 'country');

        $this->assertEquals(['user' => 'Merlin'], $array);

        $array = ['user' => 'Merlin', 'country' => ['name' => 'DE']];
        ArrayHelper::forget($array, 'country.name');

        $this->assertEquals(['user' => 'Merlin', 'country' => []], $array);

        $array = ['user' => 'Merlin', 'country' => ['name' => 'DE']];
        ArrayHelper::forget($array, []);

        $this->assertEquals(['user' => 'Merlin', 'country' => ['name' => 'DE']], $array);

        $array = ['user' => 'Merlin', 'country' => ['name' => 'DE']];
        ArrayHelper::forget($array, 'country.flag');

        $this->assertEquals(['user' => 'Merlin', 'country' => ['name' => 'DE']], $array);


        $array = [
            'user' => [
                'merloxx@avoxx.org' => ['name' => 'Merlin'],
                'john@localhost' => ['name' => 'John']
            ]
        ];

        ArrayHelper::forget($array, ['user.merloxx@avoxx.org', 'user.john@localhost']);

        $this->assertEquals(['user' => ['merloxx@avoxx.org' => ['name' => 'Merlin']]], $array);
    }
}
