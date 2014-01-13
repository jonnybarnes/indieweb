<?php

require __DIR__.'/../src/Jonnybarnes/Posse/NotePrep.php';

class POSSETests extends PHPUnit_Framework_TestCase {
	protected $p;

	protected function setUp()
	{
		$this->p = new \Jonnybarnes\Posse\NotePrep();
	}

	protected function tearDown()
	{
		$this->p = null;
	}

	/**
	 * Test the length checker against various strings
	 *
	 */
	public function testTweetLength()
	{
		$array = array(
			'',
			'A simple string',
			'Naïve words',
			'案ずるより産むが易し。'
		);
		
		$actual = [];
		foreach($array as $entry) {
			$actual[] = $this->p->tweetLength($entry);
		}

		$expected = array(0, 15, 11, 11);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * This is the simplest situation, the tweet is short enough that we
	 * can add a permashortcitation at the end and be under 140 chars
	 *
	 */
	public function testShortCreateTweet()
	{
		$actual = $this->p->createTweet("A short tweet", "abc.de", "XXXX", true);
		$expected = "A short tweet (abc.de XXXX)";

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Here we test a note that won't fit into a single tweet
	 *
	 */
	public function testLongCreateTweet()
	{
		$note = "This is a long note. The following is fluff to fill up. 1234567890123456789012345678901234567890 That was a long number wasn't it?";
		$actual = $this->p->createTweet($note, 'abc.de', 'XXXX', true);
		$expected = "This is a long note. The following is fluff to fill up. 1234567890123456789012345678901234567890 That was a long… https://abc.de/XXXX";

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Here we test a note with some URLs in
	 *
	 */
	public function testWithURLCreateTweet()
	{
		$note = "This is an introductory sentence. We are going to have a link in this note next.\n\nhttps://duckduckgo.com/?q=how+much+wood+could+a+wood-chuck+chuck+if+a+wood+chuck+could+chuck+wood\n\nThen a closing sentence with the occasionally long word in.";
		$actual = $this->p->createTweet($note, 'abc.de', 'XXXX', true);
		$expected = "This is an introductory sentence. We are going to have a link in this note next.\n\nhttps://duckduckgo.com/?q=how+much+wood+could+a+wood-chuck+chuck+if+a+wood+chuck+could+chuck+wood\n\nThen a… https://abc.de/XXXX";

		$this->assertEquals($expected, $actual);
	}

	public function testNoteWithMarkdown()
	{
		$note = "This is [a link](http://www.example.org/)";
		$actual = $this->p->createTweet($note, 'abc.de', 'XXXX', true);
		$expected = "This is a link (abc.de XXXX)";

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Here we test the retreival of hashtags from a note
	 *
	 */
	public function testGetTags()
	{
		$note = "I love #PHP, how can you not? That would be #naïve";
		$actual = $this->p->getTags($note);
		$expected = array("php", "naive");

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Here we test a note that has a repeated tag, maybe due to different cases
	 *
	 */
	public function testMultipleTagsInGetTags()
	{
		$note = "I love #PHP, it rules. #php";
		$actual = $this->p->getTags($note);
		$expected = array("php");

		$this->assertEquals($expected, $actual); 
	}

	/**
	 * Test that we get the ID back from a twitter status URL
	 *
	 */
	public function testReplyTweetId()
	{
		$url = 'https://twitter.com/t/status/413496450799378432';
		$actual = $this->p->replyTweetId($url);
		$expected = '413496450799378432';

		$this->assertEquals($expected, $actual);
	}
}