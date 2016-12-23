<?php

namespace Avoxx\Utility\Tests;

use Avoxx\Utility\Debug;

class DebugTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSapiDefault()
    {
        $sapi = PHP_SAPI;

        $this->assertEquals($sapi, Debug::getSapi());
    }

    public function testGetSapiCustom()
    {
        Debug::setSapi('cli-server');

        $this->assertEquals('cli-server', Debug::getSapi());
    }

    public function testSetSapiCli()
    {
        Debug::setSapi('cli');

        Debug::dump('foo');
        $expected = Debug::dump('foo', null, false, false);

        $this->expectOutputString($expected);
    }

    public function testSetSapiCliServer()
    {
        Debug::setSapi('cli-server');

        Debug::dump('foo');
        $expected = Debug::dump('foo', null, false, false);

        $this->expectOutputString($expected);
    }

    public function testString()
    {
        Debug::dump('foo', null, true);
        $expected = Debug::dump('foo', null, true, false);

        $this->expectOutputString($expected);
    }

    public function testHTMLOutput()
    {
        Debug::dump('foo', null, false);
        $expected = Debug::dump('foo', null, false, false);

        $this->expectOutputString($expected);
    }

    public function testCustomTitle()
    {
        Debug::dump('foo', 'STRING', true);
        $expected = Debug::dump('foo', 'STRING', true, false);

        $this->expectOutputString($expected);
    }

    public function testNumeric()
    {
        Debug::dump(1.2, null, true);
        $expected = Debug::dump(1.2, null, true, false);

        $this->expectOutputString($expected);
    }

    public function testBoolean()
    {
        Debug::dump(true, null, true);
        $expected = Debug::dump(true, null, true, false);

        $this->expectOutputString($expected);
    }

    public function testNull()
    {
        Debug::dump(null, null, true);
        $expected = Debug::dump(null, null, true, false);

        $this->expectOutputString($expected);
    }

    public function testResource()
    {
        Debug::dump(fopen('php://input', 'r'), null, true);
        $expected = Debug::dump(fopen('php://input', 'r'), null, true, false);

        $this->expectOutputString($expected);
    }

    public function testArray()
    {
        Debug::dump(['foo' => 'bar'], null, true);
        $expected = Debug::dump(['foo' => 'bar'], null, true, false);

        $this->expectOutputString($expected);
    }

    public function testObject()
    {
        $testObj = new TestObj();

        Debug::dump($testObj, null, true);
        $expected = Debug::dump($testObj, null, true, false);

        $this->expectOutputString($expected);
    }
}

class TestObj
{

    public $name = 'Jon Doe';

    protected $age = 27;

    private $details = [
        'username' => 'john',
        'email' => 'john@doe.com'
    ];
}
