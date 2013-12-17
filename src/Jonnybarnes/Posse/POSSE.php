<?php

namespace Jonnybarnes\Posse;

class POSSE {
	/**
	 * Normalize to Unicode NFC
	 *
	 */
	public function normalizeNFC($note)
	{
		$note_nfc = \Patchwork\Utf8::filter($note);

		return $note_nfc;
	}


	/**
	 * Create Tweet
	 *
	 */
	public function createTweet($note, $shorturl, $shorturlId)
	{
		$note_nfc = $this->normalizeNFC($note);
		$len = $this->tweetLength($note_nfc);
		if($len <= 125) {
			//add permashortcitation
			$tweet = $note_nfc . ' (' . $shorturl . ' ' . $shorturlId . ')';
		} else {
			//add link
			$tweet = $this->ellipsify($note_nfc) . ' https://' . $shorturl . '/' . $shorturlId;
		}
		return $tweet;
	}

	/**
	 * Check the length of a note for Twitter
	 * taking into account twitter subs URLs
	 *
	 */
	public function tweetLength($note_nfc)
	{
		$regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
		//we swap any URLs for 23chars and then count
		$tweet = preg_replace($regex, '12345678901234567890123', $note_nfc);
		$len = mb_strlen($tweet);

		return $len;
	}

	/**
	 * cut tweet in length to fit into 140 chars including
	 * permalink
	 *
	 */
	public function ellipsify($note_nfc)
	{
		$regex = '#(https?://[a-z0-9/.?=+_-]*)#i';

		preg_match_all($regex, $note_nfc, $urls, PREG_PATTERN_ORDER);
		$tweet = preg_replace($regex, 'https://t.co/4567890123', $note_nfc);
		$tweet = mb_substr($tweet, 0, 115);
		$tweet = mb_strrchr($tweet, ' ', true);

		foreach($urls[0] as $url) {
			$tweet = str_replace('https://t.co/4567890123', $url, $tweet);
		}

		$tweet .= '…';

		return $tweet;
	}
}