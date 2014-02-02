<?php

require __DIR__.'/../src/Jonnybarnes/Posse/URL.php';

class URLTests extends PHPUnit_Framework_TestCase {
	protected $u;

	protected function setUp()
	{
		$this->u = new \Jonnybarnes\Posse\URL();
	}

	protected function tearDown()
	{
		$this->u = null;
	}

	/**
	 * Testing number to NewBase64
	 *
	 */
	public function testNumTo64()
	{
		$num = 346;
		$actual = $this->u->numto64($num);
		$excpected = '5S';

		$this->assertEquals($excpected, $actual);
	}

	/**
	 * Testing reverse of NewBase64
	 *
	 */
	public function testB64ToNum()
	{
		$var = '5S';
		$actual = $this->u->b64tonum($var);
		$excpected = 346;

		$this->assertEquals($excpected, $actual);
	}

	/**
	 * Test both NewBase64 methods at once
	 *
	 */
	public function testBothNewBase64()
	{
		$var = 123;
		$actual = $this->u->b64tonum($this->u->numto64($var));

		$this->assertEquals($var, $actual);
	}

	/**
	 * Test NewBase60
	 *
	 */
	public function testNumTo60()
	{
		$num = 123;
		$actual = $this->u->numto60($num);
		$excpected = '23';

		$this->assertEquals($excpected, $actual);
	}

	/**
	 * Test Revers NewBase60
	 *
	 */
	public function testB60ToNum()
	{
		$string = '23';
		$actual = $this->u->b60tonum($string);
		$excpected = 123;

		$this->assertEquals($excpected, $actual);
	}

	/**
	 * Test both NewBase60
	 *
	 */
	public function testBothNewBase60()
	{
		$num = 123;
		$actual = $this->u->b60tonum($this->u->numto60($num));

		$this->assertEquals($num, $actual);
	}
}