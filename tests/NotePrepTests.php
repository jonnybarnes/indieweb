<?php

declare(strict_types=1);

use Jonnybarnes\IndieWeb\NotePrep;
use PHPUnit\Framework\TestCase;

require __DIR__.'/../src/NotePrep.php';

class NotePrepTests extends TestCase
{
    /** @var NotePrep */
    protected $notePrep;

    protected function setUp(): void
    {
        $this->notePrep = new NotePrep();
    }

    protected function tearDown(): void
    {
        $this->notePrep = null;
    }

    /**
     * Test the length checker against various strings.
     */
    public function testTweetLength()
    {
        $array = [
            '',
            'A simple string',
            'Naïve words',
        ];

        $actual = [];
        foreach ($array as $entry) {
            $actual[] = $this->notePrep->tweetLength($entry);
        }

        $expected = [0, 15, 11];

        $this->assertEquals($expected, $actual);
    }

    /**
     * This is the simplest situation, the tweet is short enough that we
     * can add a permashortcitation at the end and be under 140 chars.
     */
    public function testShortCreateTweet()
    {
        $actual = $this->notePrep->createNote('A short tweet', 'https://abc.de/XXXX', 140, true);
        $expected = 'A short tweet (https://abc.de/XXXX)';

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test a note that won't fit into a single tweet.
     */
    public function testLongCreateTweet()
    {
        $note = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc imperdiet tortor lorem, nec tempor nisl convallis eu. Donec id urna ut velit suscipit commodo eu ac magna. Sed fringilla semper erat, at malesuada magna interdum quis. Nullam fermentum justo sit amet varius luctus. Praesent sodales ipsum vel ullamcorper ultricies. Integer efficitur urna justo, in accumsan odio laoreet vel. Fusce pharetra velit ligula, consectetur vulputate ex volutpat id. Pellentesque porta, sapien ac imperdiet condimentum, magna ligula ullamcorper enim, id tempor nulla diam vulputate tellus. Proin ultrices lectus ac aliquet dignissim. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Praesent purus dolor, imperdiet vitae mauris ut, ultricies posuere odio. Aenean id dapibus quam.';
        $actual = $this->notePrep->createNote($note, 'https://abc.de/XXXX', 280, true);
        $expected = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc imperdiet tortor lorem, nec tempor nisl convallis eu. Donec id urna ut velit suscipit commodo eu ac magna. Sed fringilla semper erat, at malesuada magna interdum quis. Nullam fermentum justo… https://abc.de/XXXX';

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test a note with some URLs in.
     */
    public function testWithURLCreateTweet()
    {
        $note = "This is an introductory sentence. We are going to have a link in this note next.\n\nhttps://duckduckgo.com/?q=how+much+wood+could+a+wood-chuck+chuck+if+a+wood+chuck+could+chuck+wood\n\nThen another sentence with the occasionally long word in.\n\nTwitter has a really long character limit now. I could use Lorem Ipsum for every test?\n\nBut I prefer natural looking tests for the most part.";
        $actual = $this->notePrep->createNote($note, 'https://abc.de/XXXX', 280, true);
        $expected = "This is an introductory sentence. We are going to have a link in this note next.\n\nhttps://duckduckgo.com/?q=how+much+wood+could+a+wood-chuck+chuck+if+a+wood+chuck+could+chuck+wood\n\nThen another sentence with the occasionally long word in.\n\nTwitter has a really long character limit now. I could use Lorem Ipsum for every… https://abc.de/XXXX";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that markdown is correctly simplified.
     */
    public function testNoteWithMarkdown()
    {
        $note = 'This is [a link](http://www.example.org/) about a [topic](http://www.example.com/).';
        $actual = $this->notePrep->createNote($note, 'https://abc.de/XXXX', 280, true);
        $expected = 'This is a link about a topic. (https://abc.de/XXXX)';

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the retrieval of hashtags from a note.
     */
    public function testGetTags()
    {
        $note = 'I love #PHP, how can you not? That would be #naïve';
        $actual = $this->notePrep->getTags($note);
        $expected = ['php', 'naive'];

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test a note that has a repeated tag, maybe due to different cases.
     */
    public function testMultipleTagsInGetTags()
    {
        $note = 'I love #PHP, it rules. #php';
        $actual = $this->notePrep->getTags($note);
        $expected = ['php'];

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that we get the ID back from a twitter status URL.
     */
    public function testReplyTweetId()
    {
        $url = 'https://twitter.com/t/status/413496450799378432';
        $actual = $this->notePrep->replyTweetId($url);
        $expected = '413496450799378432';

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that hashtags get included.
     */
    public function testLongNoteWithHashtags()
    {
        $note = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas mattis sit amet vitae augue. Nam tincidunt congue enim, ut porta lorem lacinia consectetur. Donec ut libero sed arcu vehicula ultricies a non tortor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ut gravida lorem. Ut turpis felis, pulvinar a semper sed, adipiscing id dolor. Pellentesque auctor nisi id magna consequat sagittis. Curabitur dapibus enim sit amet elit pharetra tincidunt feugiat nisl imperdiet. Ut convallis libero in urna ultrices accumsan. Donec sed odio eros. Donec viverra mi quis quam pulvinar at malesuada arcu rhoncus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In rutrum accumsan ultricies. Mauris vitae nisi at sem facilisis semper ac in est. #longnote';
        $actual = $this->notePrep->createNote($note, 'https://abc.de/XXXX', 280, true);
        $expected = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas… #longnote https://abc.de/XXXX';

        $this->assertEquals($expected, $actual);
    }

    /**
     * A note that failed for being too long.
     */
    public function testATweetThatFailedIRL()
    {
        $note = 'Here’s some links and things I’ve found interesting recently:

* [https://github.com/veltman/principles](https://github.com/veltman/principles) -- a nice little list of rules to try to abide to when developing for the web,
* [http://adrianshort.org/2013/12/22/some-websites-should-be-unblockable/](http://adrianshort.org/2013/12/22/some-websites-should-be-unblockable/) -- an excellent point about the inherent downsides of having a state mandated censored web,
* [http://www.motherjones.com/environment/2013/12/inquiring-minds-joshua-greene-moral-tribes](http://www.motherjones.com/environment/2013/12/inquiring-minds-joshua-greene-moral-tribes) -- some scientists put our sense of right and wrong under the microscope, with fascinating results,
* [http://www.newstatesman.com/2013/11/5-benefit-changes-government-dont-want-you-know-about](http://www.newstatesman.com/2013/11/5-benefit-changes-government-dont-want-you-know-about) -- how the UK government is screwing over the disabled people in our country, truly disgusting,
* [http://blog.adrianroselli.com/2014/01/w3c-eme-is-not-drm-nor-other-fear.html](http://blog.adrianroselli.com/2014/01/w3c-eme-is-not-drm-nor-other-fear.html) -- an excellent article on what the W3C EME is and why it isn’t DRM.

Some of these may spur full articles in the future. Maybe not. #readinglist';
        $actual = $this->notePrep->createNote($note, 'https://abc.de/XXXX', 280, true);
        $expected = 'Here’s some links and things I’ve found interesting recently:

* https://github.com/veltman/principles -- a nice little list of rules to try to abide to when developing for the web,
* https://github.com/veltman/principles -- an excellent point about the inherent… #readinglist https://abc.de/XXXX';

        $this->assertEquals($expected, $actual);
    }
}
