<?php

declare(strict_types=1);

use Jonnybarnes\IndieWeb\Numbers;
use PHPUnit\Framework\TestCase;

require __DIR__.'/../src/Numbers.php';

class NumbersTests extends TestCase
{
    /** @var Numbers */
    protected $numbers;

    protected function setUp(): void
    {
        $this->numbers = new Numbers();
    }

    protected function tearDown(): void
    {
        $this->numbers = null;
    }

    /**
     * Test converting a decimal number to NewBase64.
     */
    public function testNumTo64()
    {
        $this->assertEquals('5S', $this->numbers->numto64(346));
    }

    /**
     * Test converting NewBase64 number to decimal.
     */
    public function testB64ToNum()
    {
        $this->assertEquals(346, $this->numbers->b64tonum('5S'));
    }

    /**
     * Test both NewBase64 methods at once.
     */
    public function testBothNewBase64()
    {
        $this->assertEquals(123, $this->numbers->b64tonum($this->numbers->numto64(123)));
    }

    /**
     * Test converting a decimal number NewBase60.
     */
    public function testNumTo60()
    {
        $this->assertEquals('23', $this->numbers->numto60(123));
    }

    /**
     * Test converting a NewBase60 number to decimal.
     */
    public function testB60ToNum()
    {
        $this->assertEquals(123, $this->numbers->b60tonum('23'));
    }

    /**
     * Test both NewBase60 methods at once.
     */
    public function testBothNewBase60()
    {
        $this->assertEquals(123, $this->numbers->b60tonum($this->numbers->numto60(123)));
    }
}
