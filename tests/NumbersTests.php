<?php

require __DIR__.'/../src/Numbers.php';

class NumbersTests extends PHPUnit_Framework_TestCase
{
    protected $numbers;

    protected function setUp()
    {
        $this->numbers = new \Jonnybarnes\IndieWeb\Numbers();
    }

    protected function tearDown()
    {
        $this->numbers = null;
    }

    /**
     * Test converting a decimal number to NewBase64.
     */
    public function testNumTo64()
    {
        $num = 346;
        $actual = $this->numbers->numto64($num);
        $excpected = '5S';

        $this->assertEquals($excpected, $actual);
    }

    /**
     * Test converting NewBase64 number to decimal.
     */
    public function testB64ToNum()
    {
        $var = '5S';
        $actual = $this->numbers->b64tonum($var);
        $excpected = 346;

        $this->assertEquals($excpected, $actual);
    }

    /**
     * Test both NewBase64 methods at once.
     */
    public function testBothNewBase64()
    {
        $var = 123;
        $actual = $this->numbers->b64tonum($this->numbers->numto64($var));

        $this->assertEquals($var, $actual);
    }

    /**
     * Test converting a decimal number NewBase60.
     */
    public function testNumTo60()
    {
        $num = 123;
        $actual = $this->numbers->numto60($num);
        $excpected = '23';

        $this->assertEquals($excpected, $actual);
    }

    /**
     * Test converting a  NewBase60 number to decimal.
     */
    public function testB60ToNum()
    {
        $string = '23';
        $actual = $this->numbers->b60tonum($string);
        $excpected = 123;

        $this->assertEquals($excpected, $actual);
    }

    /**
     * Test both NewBase60 methods at once.
     */
    public function testBothNewBase60()
    {
        $num = 123;
        $actual = $this->numbers->b60tonum($this->numbers->numto60($num));

        $this->assertEquals($num, $actual);
    }
}
