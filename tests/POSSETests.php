<?php

require __DIR__.'/../src/Jonnybarnes/Posse/POSSE.php';

class POSSETests extends PHPUnit_Framework_TestCase {
	protected $p;

	protected function setUp()
	{
		$this->p = new \Jonnybarnes\Posse\POSSE();
	}

	protected function tearDown()
	{
		$this->p = null;
	}

	public function testShortCreateTweet()
	{
		$actual = $this->p->createTweet("A short tweet", "abc.de", "XXXX");
		$expected = "A short tweet (abc.de XXXX)";

		$this->assertEquals($actual, $expected);
	}
}