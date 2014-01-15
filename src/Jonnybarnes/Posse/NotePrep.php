<?php

namespace Jonnybarnes\Posse;

class NotePrep {
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
	 * Create Note
	 *
	 */
	public function createNote($note, $shorturl, $shorturlId, $siloLimit, $twitter = true, $ssl = false)
	{
		$note_nfc = $this->normalizeNFC($note);
		$note_tw = $this->twitterify($note_nfc);
		$linkLength = mb_strlen($shorturl, "UTF-8") + mb_strlen($shorturlId, "UTF-8") + 4; //4 = ' '+(+' '+)
		$max = $siloLimit - $linkLength;
		($twitter == true) ? $len = $this->tweetLength($note_tw) : mb_strlen($note_tw);
		if($len <= $max) {
			//add permashortcitation
			$tweet = $note_tw . ' (' . $shorturl . ' ' . $shorturlId . ')';
		} else {
			//add link
			($ssl == true) ? $link = ' https://' . $shorturl . '/' . $shorturlId : ' http://' . $shorturl . '/' . $shorturlId;
			$tweet = $this->ellipsify($note_tw, $max, $twitter) . $link;
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
		$len = mb_strlen($tweet, "UTF-8");

		return $len;
	}

	/**
	 * cut tweet in length to fit into 140 chars including
	 * permalink
	 *
	 */
	public function ellipsify($note_nfc, $length, $twitter)
	{
		//if we are dealing with twitter, we need to account for their link medling
		if($twitter) {
			$regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
			preg_match_all($regex, $note_nfc, $urls, PREG_PATTERN_ORDER);
			$note_nfc = preg_replace($regex, 'https://t.co/4567890123', $note_nfc);
		}
		
		//cut the string, probably now in the middle of word so move back to last space
		$note_nfc = mb_substr($note_nfc, 0, $length, "UTF-8");
		$note_nfc = mb_strrchr($note_nfc, ' ', true, "UTF-8");
		//TODO check for punctuation

		//if we are with twitter, swap template URLs back for the actual ones
		if($twitter) {
			foreach($urls[0] as $url) {
				$tweet = str_replace('https://t.co/4567890123', $url, $tweet);
			}
		}

		$tweet .= '…';

		return $tweet;
	}

	/**
	 * Given a note with #tag s in, the returns an array of those tags
	 * without the # character
	 * Further, the tags array will have tags that are lowercase and where the basic
	 * diacritic accents are removed, i.e. Naïve => naive
	 * This is for searching purposes. Though I'm not sure if this is the place
	 * to solve this problem.
	 *
	 */
	public function getTags($note)
	{
		$tags = [];
		$tagstemp = [];
		preg_match_all('/#([^\s<>]+)\b/', $note, $tagstemp);
		foreach($tagstemp[1] as $tag) {
			$tag = mb_strtolower(preg_replace('/&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);/i','$1',htmlentities($tag)), "UTF-8");
			$tags[] = $tag;
		}

		$tags = array_unique($tags);

		return $tags;
	}

	/**
	 * We need to remove egregious markdown that Twitter won't like.
	 * For links, leave the text 'unclickable'. If the URL is massively
	 * important then explicitly include it.
	 *
	 */
	public function twitterify($note)
	{
		$regex = '/\[(.*)\]\((.*)\)/';
		$twitterified = preg_replace($regex, "$1", $note);
		return $twitterified;
	}

	/**
	 * Grab the status id from a twitter URL
	 *
	 */
	public function replyTweetId($url)
	{
		$regex = '/https?:\/\/twitter.com\/[a-zA-Z_]{1,20}\/status\/([0-9]*)/';
		preg_match($regex, $url, $match);
		$id = $match[1];

		return $id;
	}
}