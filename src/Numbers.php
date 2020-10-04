<?php

declare(strict_types=1);

namespace Jonnybarnes\IndieWeb;

use InvalidArgumentException;

class Numbers
{
    protected $nb60chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';
    protected $nb64chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';

    /**
     * Convert a decimal number to NewBase64.
     *
     * @param $num int The decimal number
     *
     * @return string
     */
    public function numto64(int $num): string
    {
        return $this->decToNewBase($num, 64);
    }

    /**
     * Convert a NewBase64 number to decimal.
     *
     * @param $nb64num string The NewBase64 number
     *
     * @return int
     */
    public function b64tonum(string $nb64num): int
    {
        return $this->newBaseToDec($nb64num, 64);
    }

    /**
     * Convert a decimal number to NewBase60.
     *
     * @param $num int The decimal number
     *
     * @return string
     */
    public function numto60(int $num): string
    {
        return $this->decToNewBase($num, 60);
    }

    /**
     * Convert a NewBas60 number to decimal.
     *
     * @param $nb60num string The NewBase60 number
     *
     * @return int
     */
    public function b60tonum(string $nb60num): int
    {
        return $this->newBaseToDec($nb60num, 60);
    }

    /**
     * The actual conversion logic to go from decimal to NewBase*.
     *
     * @param $num int The decimal number
     * @param $base int The base we are using
     *
     * @return string
     */
    protected function decToNewBase(int $num, int $base): string
    {
        $newBaseChars = $this->loadCharacters($base);

        $string = '';
        $sign = '';

        if (intval($num) == 0) {
            return 0;
        }

        if ($num < 0) {
            $num = abs($num);
            $sign = '-';
        }

        while ($num > 0) {
            $digit = $num % $base;
            $string = $newBaseChars[$digit] . $string;
            $num = ($num - $digit) / $base;
        }

        return $sign . $string;
    }

    /**
     * The actual conversion logic to go from NewBase* to decimal.
     *
     * @param $nbNum string The NewBase* number
     * @param $base int The base we are using
     *
     * @return int
     */
    protected function newBaseToDec(string $nbNum, int $base): int
    {
        $newBaseChars = $this->loadCharacters($base);

        $map = array_flip(str_split($newBaseChars));
        $map['l'] = 1;
        $map['I'] = 1;
        $map['O'] = 0;

        $num = 0;
        $chars = str_split($nbNum);
        foreach ($chars as $char) {
            $num = array_key_exists($char, $map) ? $num * $base + $map[$char] : $num * $base;
        }

        return (int) $num;
    }

    /**
     * Load the right NewBase* characters.
     *
     * @param $base int
     *
     * @throws InvalidArgumentException
     *
     * @return string The characters
     */
    protected function loadCharacters(int $base): string
    {
        switch ($base) {
            case 60:
                return $this->nb60chars;
            case 64:
                return $this->nb64chars;
            default:
                throw new InvalidArgumentException('Unsupported base, must be `60` or `64`');
        }
    }
}
