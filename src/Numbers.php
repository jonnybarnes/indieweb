<?php

namespace Jonnybarnes\IndieWeb;

class Numbers
{
    protected $nb60chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';
    protected $nb64chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';

    /**
     * Convert a decimal number to NewBase64.
     *
     * @param  int  The decimal number
     *
     * @return string The converted number
     */
    public function numto64($num)
    {
        return $this->decToNewBase($num, 64);
    }

    /**
     * Convert a NewBase64 number to decimal.
     *
     * @param  string  The NewBase64 number
     *
     * @return int The converted number
     */
    public function b64tonum($nb64num)
    {
        return $this->newBaseToDec($nb64num, 64);
    }

    /**
     * Convert a decimal number to NewBase60.
     *
     * @param  int  The decimal number
     *
     * @return string The converted number
     */
    public function numto60($num)
    {
        return $this->decToNewBase($num, 60);
    }

    /**
     * Convert a NewBas60 number to decimal.
     *
     * @param  string  The NewBase60 number
     *
     * @return int The converted number
     */
    public function b60tonum($nb60num)
    {
        return $this->newBaseToDec($nb60num, 60);
    }

    /**
     * The actual conversion logic to go from decimal to NewBase*.
     *
     * @param  int  The decimal number
     * @param  int  The base we are using
     *
     * @return string The converted number
     */
    protected function decToNewBase($num, $base)
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
     * @param  string  The NewBase* number
     * @param  int     The base we are using
     *
     * @return int The converted number
     */
    protected function newBaseToDec($nbNum, $base)
    {
        $newBaseChars = $this->loadCharacters($base);

        $map = array_flip(str_split($newBaseChars));
        $map['l'] = 1;
        $map['I'] = 1;
        $map['O'] = 0;

        $num = 0;
        $chars = str_split($nbNum);
        foreach ($chars as $char) {
            $num = array_key_exists($char, $map) ? $num*$base + $map[$char] : $num*$base;
        }

        return $num;
    }

    /**
     * Load the right NewBase* characters.
     *
     * @param  int  The base
     * @return string The characters
     */
    protected function loadCharacters($base)
    {
        switch ($base) {
            case 60:
                return $this->nb60chars;
            case 64:
                return $this->nb64chars;
            default:
                throw new \Exception('Unsupported number base');
        }
    }
}
