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

	/**
	 * This is the simplest situation, the tweet is short enough that we
	 * can add a permashortcitation at the end and be under 140 chars
	 *
	 */
	public function testShortCreateTweet()
	{
		$actual = $this->p->createTweet("A short tweet", "abc.de", "XXXX");
		$expected = "A short tweet (abc.de XXXX)";

		$this->assertEquals($actual, $expected);
	}
}