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
        return normalizer_normalize($note, Normalizer::FORM_C);
    }

    /**
     * Create a version of the note suitable for POSSEing.
     *
     * @param  string  The full note
     * @param  string  The Short URL
     * @param  int     The character limit for the silo
     * @param  bool    Wether we are posting to Twitter, which needs special considerations
     * @param  bool    Wether we are using https
     *
     * @return string The modified note
     */
    public function createNote($note, $shorturl, $siloLimit, $twitter)
    {
        $noteNFC = $this->normalizeNFC($note);
        $noteSimplified = $this->simplify($noteNFC);
        $linkLength = mb_strlen($shorturl, 'UTF-8');
        //determine the max length the processed note can be, take into account
        //Twitter’s substitution of links
        $max = ($twitter === true) ? $siloLimit - (23 + 1 + 2) : $siloLimit - $linkLength - 3;
        //what length is the note?
        $len = ($twitter === true) ? $this->tweetLength($noteSimplified) : mb_strlen($noteSimplified);
        if ($len <= $max) {
            //we have enough room to simply add a permashortcitation link
            return $noteSimplified . ' (' . $shorturl . ')';
        }
        //we need to truncate the note before we add the link
        $length = $siloLimit - (1 + 1 + $linkLength); //… + ' ' + link
        $processedNote = $this->ellipsify($noteSimplified, $length, $twitter) . $shorturl;

        return $processedNote;
    }

    /**
     * Check the length of a note for Twitter taking into account twitter
     * substitutes the URLs.
     *
     * @param  string  The note
     *
     * @return int The length
     */
    public function tweetLength($noteNFC)
    {
        $regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
        //we swap any URLs for 23chars and then count
        $tweet = preg_replace($regex, '12345678901234567890123', $noteNFC);
        $len = mb_strlen($tweet, 'UTF-8');

        return $len;
    }

    /**
     * Cut a note in length to fit into the required character limit.
     *
     * @param  string  The note
     * @param  int     The character limit
     * @param  bool    Twitter?
     *
     * @return string The ellipsified note
     */
    public function ellipsify($noteNFC, $length, $twitter)
    {
        //if we are dealing with twitter, we need to account for their link medling
        if ($twitter === true) {
            //because we will be linking back, that also gets changed, this affect where we cut the note
            $length = 115; //140 - 23 - 1 - 1
            $regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
            preg_match_all($regex, $noteNFC, $urls, PREG_PATTERN_ORDER);
            $noteNFC = preg_replace($regex, 'https://t.co/4567890123', $noteNFC);
        }

        //cut the string, probably now in the middle of word so move back to last space
        $cutNote = mb_substr($noteNFC, 0, $length, 'UTF-8');
        $truncatedNote = mb_strrchr($cutNote, ' ', true, 'UTF-8');

        //check for ending punctuation
        $badPunctuation = '@$-~*()_+[]{}|;,<>.';
        $truncatedNote = rtrim($truncatedNote, $badPunctuation);

        //get the missing tags if any
        $parts = explode($truncatedNote, $noteNFC);
        $cutPart = ltrim($parts[1]);
        $missingTags = $this->getTags($cutPart);
        $tagsToAdd = '';
        foreach ($missingTags as $tag) {
            $tagsToAdd .= ' #' . $tag;
        }
        $tagsToAdd = ltrim($tagsToAdd);

        //add any missing tags
        if (mb_strlen($tagsToAdd) > 0) {
            //work out lengths
            $tagsToAddLength = mb_strlen($tagsToAdd, 'UTF-8');
            $truncNoteLength = mb_strlen($truncatedNote, 'UTF-8');
            //truncate
            $truncForTags = mb_substr($truncatedNote, 0, $truncNoteLength - $tagsToAddLength - 1, 'UTF-8');
            //move to last space if necessary
            if (in_array(
                $truncatedNote[mb_strlen($truncForTags)],
                array_merge(
                    str_split($badPunctuation),
                    array(' ')
                )
            ) === false) {
                $truncForTags = mb_strrchr($truncForTags, ' ', true, 'UTF-8');
            }
            //add ellipsis then tags
            $truncatedNote = rtrim($truncForTags, $badPunctuation) . '… ' . $tagsToAdd . ' ';
        }

        //however, if no tags were added, just add an ellipsis
        if (mb_strlen($tagsToAdd) == 0) {
            $truncatedNote .= '… ';
        }

        //if we are with twitter, swap template URLs back for the actual ones
        if ($twitter) {
            foreach ($urls[0] as $url) {
                $truncatedNote = str_replace('https://t.co/4567890123', $url, $truncatedNote);
            }
        }

        return $truncatedNote;
    }

    /**
     * Get the tags from a note.
     *
     * Given a note with `#tag`s in, return an array of those tags without the
     * # character. Further, the tags array will have tags that are lowercase
     * and where the basic diacritic accents are removed, i.e. Naïve => naive.
     * This is for searching purposes. Though I'm not sure if this is the place
     * to solve this problem.
     *
     * @param  string  The note
     *
     * @return array The tags
     */
    public function getTags($note)
    {
        $tags = array();
        $tagstemp = array();
        preg_match_all('/#([^\s<>]+)\b/', $note, $tagstemp);
        foreach ($tagstemp[1] as $tag) {
            $tag = mb_strtolower(
                preg_replace(
                    '/&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);/i',
                    '$1',
                    htmlentities($tag)
                ),
                'UTF-8'
            );
            $tags[] = $tag;
        }

        return array_unique($tags);
    }

    /**
     * Make the POSSE version of the note suitable for Twitter.
     *
     * We need to remove egregious markdown that Twitter won’t like. For links,
     * leave the text “unclickable”. If the URL is massively important then
     * it should have been explicitly included.
     *
     * @param  string  The note
     *
     * @return string The modified note
     */
    public function simplify($note)
    {
        $regex = '/\[(.*?)\]\((.*?)\)/';
        $simplified = preg_replace($regex, '$1', $note);

        return $simplified;
    }

    /**
     * Grab the status id from a twitter URL.
     *
     * @param  string  The URL
     *
     * @return int The tweet ID
     */
    public function replyTweetId($url)
    {
        $regex = '/https?:\/\/twitter.com\/[a-zA-Z_]{1,20}\/status\/([0-9]*)/';
        preg_match($regex, $url, $match);

        return $match[1];
    }
}
