<?php

namespace Jonnybarnes\IndieWeb;

use Normalizer;

class NotePrep
{
    /**
     * Normalize the note to Unicode NFC.
     *
     * @param  string  The original note
     *
     * @return string The normalized note
     */
    public function normalizeNFC($note)
    {
        $noteNFC = normalizer_normalize($note, Normalizer::FORM_C);

        return $noteNFC;
    }


    /**
     * Create a version of the note suitable for POSSEing
     *
     * @param  string  The full note
     * @param  string  The Short URL host
     * @param  string  The Short URL path
     * @param  int     The character limit for the silo
     * @param  bool    Wether we are posting to Twitter, which needs special considerations
     * @param  bool    Wether we are using https
     *
     * @return string The modified note
     */
    public function createNote($note, $shorturl, $shorturlId, $siloLimit, $twitter = true, $ssl = false)
    {
        $noteNFC = $this->normalizeNFC($note);
        $noteTwittered = $this->twitterify($noteNFC);
        $linkLength = mb_strlen($shorturl, "UTF-8") + mb_strlen($shorturlId, "UTF-8") + 4; //4 = 'SPACE' + ( + / + )
        if ($ssl == true) {
            $linkLength = $linkLength + 8; // https://
        } else {
            $linkLength = $linkLength + 7; // http://
        }
        if ($twitter == true) {
            $max = $siloLimit - (2 + 23); //( + ) + t.co linkllength
        } else {
            $max = $siloLimit - $linkLength;
        }
        $len = ($twitter == true) ? $this->tweetLength($noteTwittered) : mb_strlen($noteTwittered);
        if ($len <= $max) {
            //add permashortcitation link
            if ($ssl == true) {
                $tweet = $noteTwittered . ' (https://' . $shorturl . '/' . $shorturlId . ')';
            } else {
                $tweet = $noteTwittered . ' (http://' . $shorturl . '/' . $shorturlId . ')';
            }
        } else {
            //add link
            $link = ($ssl == true) ? ' https://' . $shorturl . '/' . $shorturlId : ' http://' . $shorturl . '/' . $shorturlId;
            $length = $siloLimit - (1 + 1 + mb_strlen($link)); //… + ' ' + link
            $tweet = $this->ellipsify($noteTwittered, $length, $twitter) . $link;
        }
        return $tweet;
    }

    /**
     * Check the length of a note for Twitter
     * taking into account twitter subs URLs
     *
     */
    public function tweetLength($noteNFC)
    {
        $regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
        //we swap any URLs for 23chars and then count
        $tweet = preg_replace($regex, '12345678901234567890123', $noteNFC);
        $len = mb_strlen($tweet, "UTF-8");

        return $len;
    }

    /**
     * cut tweet in length to fit into 140 chars including
     * permalink
     *
     */
    public function ellipsify($noteNFC, $length, $twitter)
    {
        //if we are dealing with twitter, we need to account for their link medling
        if ($twitter == true) {
            //because we will be linking back, that also gets changed, this affect where we cut the note
            $length = 115;
            $regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
            preg_match_all($regex, $noteNFC, $urls, PREG_PATTERN_ORDER);
            $noteNFC = preg_replace($regex, 'https://t.co/4567890123', $noteNFC);
        }

        //cut the string, probably now in the middle of word so move back to last space
        $noteNFCStart = mb_substr($noteNFC, 0, $length, "UTF-8");
        $noteNFCStartTrunc = mb_strrchr($noteNFCStart, ' ', true, "UTF-8");

        //check for punctuation
        $badPunctuation = '@$-~*()_+[]{}|;,<>.';
        $noteNFCStartTrunc = rtrim($noteNFCStartTrunc, $badPunctuation);

        //get the missing tags if any
        $parts = explode($noteNFCStartTrunc, $noteNFC);
        $noteNFCEnd = ltrim($parts[1]);
        $missingTags = $this->getTags($noteNFCEnd);
        $tags = '';
        foreach ($missingTags as $tag) {
            $tags .= ' #' . $tag;
        }
        $tags = ltrim($tags);
        if (mb_strlen($tags) > 0) {
            $tagsLength = mb_strlen($tags, "UTF-8");
            $noteLength = mb_strlen($noteNFCStartTrunc, "UTF-8");
            $noteNFCStartTruncStart = mb_substr($noteNFCStartTrunc, 0, $noteLength - $tagsLength, "UTF-8");
            $noteNFCStartTruncStartTrim = rtrim($noteNFCStartTruncStart, $badPunctuation);
            if (mb_strlen($noteNFCStartTruncStartTrim, "UTF-8") < mb_strlen($noteNFCStartTruncStart, "UTF-8")) {
                $noteNFCStartTruncStart = $noteNFCStartTruncStartTrim . ' ';
            }
            $noteNFCStartTruncStartTrunc = mb_strrchr($noteNFCStartTruncStart, ' ', true, "UTF-8");
            $noteNFCStartTrunc = rtrim($noteNFCStartTruncStartTrunc, $badPunctuation) . '… ' . $tags;
        }

        //if we are with twitter, swap template URLs back for the actual ones
        if ($twitter) {
            foreach ($urls[0] as $url) {
                $noteNFCStartTrunc = str_replace('https://t.co/4567890123', $url, $noteNFCStartTrunc);
            }
        }

        if (mb_strlen($tags) == 0) {
            $noteNFCStartTrunc .= '…';
        }

        return $noteNFCStartTrunc;
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
        foreach ($tagstemp[1] as $tag) {
            $tag = mb_strtolower(
                preg_replace(
                    '/&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);/i',
                    '$1',
                    htmlentities($tag)
                ),
                "UTF-8"
            );
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
        $regex = '/\[(.*?)\]\((.*?)\)/';
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
