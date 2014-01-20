<?php

require __DIR__.'/../src/Jonnybarnes/Posse/URL.php';

class POSSETests extends PHPUnit_Framework_TestCase {
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
		$excpected = 'S5';

		$this->assertEquals($excpected, $actual);
	}
}