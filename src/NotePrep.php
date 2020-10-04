<?php

declare(strict_types=1);

namespace Jonnybarnes\IndieWeb;

use Normalizer;

class NotePrep
{
    /**
     * Normalize the note to Unicode NFC.
     *
     * @param $note string  The original note
     *
     * @return string The normalized note
     */
    public function normalizeNFC(string $note): string
    {
        return normalizer_normalize($note, Normalizer::FORM_C);
    }

    /**
     * Create a version of the note suitable for POSSE-ing.
     *
     * @param $note string The full note
     * @param $shortUrl string The Short URL
     * @param $siloLimit int The character limit for the silo
     * @param $twitter bool Whether we are posting to Twitter, which needs special considerations
     *
     * @return string The modified note
     */
    public function createNote(string $note, string $shortUrl, int $siloLimit, bool $twitter): string
    {
        $noteNFC = $this->normalizeNFC($note);
        $noteSimplified = $this->simplify($noteNFC);
        $linkLength = mb_strlen($shortUrl, 'UTF-8');

        // Determine the max length the processed note can be, take into account Twitter’s substitution of links
        $max = ($twitter === true) ? $siloLimit - (23 + 1 + 2) : $siloLimit - $linkLength - 3;

        // What length is the note?
        $len = ($twitter === true) ? $this->tweetLength($noteSimplified) : mb_strlen($noteSimplified);
        if ($len <= $max) {
            // We have enough room to simply add a “permashortcitation” link
            return $noteSimplified . ' (' . $shortUrl . ')';
        }

        // We need to truncate the note before we add the link
        $length = $siloLimit - (1 + 1 + $linkLength); //… + ' ' + link

        return $this->ellipsify($noteSimplified, $length, $twitter) . $shortUrl;
    }

    /**
     * Check the length of a note for Twitter taking into account twitter substitutes the URLs.
     *
     * @param $noteNFC string The note in NFC format
     *
     * @return int The length
     */
    public function tweetLength(string $noteNFC): int
    {
        // Regex for URLs
        $regex = '#(https?://[a-z0-9/.?=+_-]*)#i';

        // We swap any URLs for 23chars and then count
        $tweet = preg_replace($regex, '12345678901234567890123', $noteNFC);

        return mb_strlen($tweet, 'UTF-8');
    }

    /**
     * Cut a note in length to fit into the required character limit.
     *
     * @param $noteNFC string The note in NFC format
     * @param $length int The character limit
     * @param $twitter bool Are we preparing the note for Twitter
     *
     * @return string The ellipsified note
     */
    public function ellipsify(string $noteNFC, int $length, bool $twitter): string
    {
        // If we are dealing with twitter, we need to account for their link substitutions
        if ($twitter === true) {
            // Because we will be linking back, that also gets changed, this affect where we cut the note
            $length = 255; // 280 - 23 - 1 - 1
            $regex = '#(https?://[a-z0-9/.?=+_-]*)#i';
            preg_match_all($regex, $noteNFC, $urls, PREG_PATTERN_ORDER);
            $noteNFC = preg_replace($regex, 'https://t.co/4567890123', $noteNFC);
        }

        // Cut the string, probably now in the middle of word, so move back to last space
        $cutNote = mb_substr($noteNFC, 0, $length, 'UTF-8');
        $truncatedNote = mb_strrchr($cutNote, ' ', true, 'UTF-8');

        // Check for ending punctuation
        $badPunctuation = '@$-~*()_+[]{}|;,<>.';
        $truncatedNote = rtrim($truncatedNote, $badPunctuation);

        // Get the missing tags if any
        $parts = explode($truncatedNote, $noteNFC);
        $cutPart = ltrim($parts[1]);
        $missingTags = $this->getTags($cutPart);
        $tagsToAdd = '';
        foreach ($missingTags as $tag) {
            $tagsToAdd .= ' #' . $tag;
        }
        $tagsToAdd = ltrim($tagsToAdd);

        // Add any missing tags we found above
        if (mb_strlen($tagsToAdd) > 0) {
            // Work out lengths
            $tagsToAddLength = mb_strlen($tagsToAdd, 'UTF-8');
            $truncNoteLength = mb_strlen($truncatedNote, 'UTF-8');
            // Truncate note further if necessary
            $truncForTags = mb_substr($truncatedNote, 0, $truncNoteLength - $tagsToAddLength - 1, 'UTF-8');
            // Move to last space if necessary
            if (
                in_array(
                    $truncatedNote[mb_strlen($truncForTags)],
                    array_merge(
                        str_split($badPunctuation),
                        [' ']
                    )
                ) === false
            ) {
                $truncForTags = mb_strrchr($truncForTags, ' ', true, 'UTF-8');
            }
            // Add an ellipsis, then tags
            $truncatedNote = rtrim($truncForTags, $badPunctuation) . '… ' . $tagsToAdd . ' ';
        }

        // However, if no tags were added, just add an ellipsis
        if (mb_strlen($tagsToAdd) == 0) {
            $truncatedNote .= '… ';
        }

        // If we are preparing for Twitter, swap template URLs back for the actual ones
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
     * @param $note string
     *
     * @return array The tags we found
     */
    public function getTags(string $note): array
    {
        $tags = [];
        $tagsTmp = [];
        preg_match_all('/#([^\s<>]+)\b/', $note, $tagsTmp);
        foreach ($tagsTmp[1] as $tag) {
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
     * leave the text “un-clickable”. If the URL is massively important then
     * it should have been explicitly included.
     *
     * @param $note string
     *
     * @return string The modified note
     */
    public function simplify(string $note): string
    {
        $regex = '/\[(.*?)\]\((.*?)\)/';

        return preg_replace($regex, '$1', $note);
    }

    /**
     * Grab the status Id from a twitter URL.
     *
     * @param $url string
     *
     * @return string The tweet Id
     */
    public function replyTweetId(string $url): string
    {
        $regex = '/https?:\/\/twitter.com\/[a-zA-Z_]{1,20}\/status\/([0-9]*)/';
        preg_match($regex, $url, $match);

        return $match[1];
    }
}
