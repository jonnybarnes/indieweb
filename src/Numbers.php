<?php

namespace Jonnybarnes\IndieWeb;

class Numbers
{
    protected $nb60chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';
    protected $nb64chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';

    /**
     * NewBase64
     *
     * Convert a decimal number to NewBase64
     * @param  int
     * @return string
     */
    public function numto64($num)
    {
        return $this->decToNewBase($num, 64);
    }

    /**
     * Reverse NewBase64
     *
     * Convert a NewBase64 number to decimal
     * @param  string
     * @return int
     */
    public function b64tonum($nb64num)
    {
        return $this->newBaseToDec($nb64num, 64);
    }

    /**
     * NewBase60
     *
     * Convert a decimal number to NewBase60
     * @param  int
     * @return string
     */
    public function numto60($num)
    {
        return $this->decToNewBase($num, 60);
    }

    /**
     * Reverse NewBase60
     *
     * Convert a NewBas60 number to decimal
     * @param  string
     * @return int
     */
    public function b60tonum($nb60num)
    {
        return $this->newBaseToDec($nb60num, 60);
    }

    /**
     * The actual conversion logic.
     *
     * @param  int decimal number
     * @param  int new base to conver to
     * @return string
     */
    protected function decToNewBase($num, $base)
    {
        switch ($base) {
            case 60:
                $newBaseChars = $this->nb60chars;
                break;
            case 64:
                $newBaseChars = $this->nb64chars;
                break;
            default:
                throw new \Exception('Unsupported number base');
                break;
        }

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
     * The actual conversion logic.
     *
     * @param  string new base number
     * @param  int new base to conver to
     * @return int
     */
    protected function newBaseToDec($nbNum, $base)
    {
        switch ($base) {
            case 60:
                $newBaseChars = $this->nb60chars;
                break;
            case 64:
                $newBaseChars = $this->nb64chars;
                break;
            default:
                throw new \Exception('Unsupported number base');
                break;
        }

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
}
