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

	/**
	 * Here we test a note that won't fit into a single tweet
	 *
	 */
	public function testLongCreateTweet()
	{
		$note = "This is a long note. The following is fluff to fill up. 1234567890123456789012345678901234567890 That was a long number wasn't it?";
		$actual = $this->p->createTweet($note, 'abc.de', 'XXXX');
		$expected = "This is a long note. The following is fluff to fill up. 1234567890123456789012345678901234567890 That was a long… https://abc.de/XXXX";

		$this->assertEquals($actual, $expected);
	}

	/**
	 * Here we test a note with some URLs in
	 *
	 */
	public function testWithURLCreateTweet()
	{
		$note = "This is an introductory sentence. We are going to have a link in this note next.\n\nhttps://duckduckgo.com/?q=how+much+wood+could+a+wood-chuck+chuck+if+a+wood+chuck+could+chuck+wood\n\nThen a closing sentence with the occasionally long word in.";
		$actual = $this->p->createTweet($note, 'abc.de', 'XXXX');
		$expected = "This is an introductory sentence. We are going to have a link in this note next.\n\nhttps://duckduckgo.com/?q=how+much+wood+could+a+wood-chuck+chuck+if+a+wood+chuck+could+chuck+wood\n\nThen a… https://abc.de/XXXX";

		$this->assertEquals($actual, $expected);
	}
}