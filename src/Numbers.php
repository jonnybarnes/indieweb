<?php

namespace Jonnybarnes\IndieWeb;

class Numbers
{
    const NB60CHARS = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';
    const NB64CHARS = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';
    /**
     * NewBase64
     *
     * Convert a decimal number to NewBase64
     * @param  int
     * @return string
     */
    public function numto64($num)
    {
        $string = '';
        $sign = '';

        if (intval($num) === 0) {
            return 0;
        }

        if ($num < 0) {
            $num = -$num;
            $sign = '-';
        }

        while ($num > 0) {
            $counter = $num % 64;
            $string = self::NB64CHARS[$counter] . $string;
            $num = ($num - $counter)/ 64;
        }
        return $sign . $string;
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
        $num = 0;
        
        $map = array_flip(str_split(self::NB64CHARS));
        $map['l'] = 1;
        $map['I'] = 1;
        $map['O'] = 0;

        $chars = str_split($nb64num);
        foreach ($chars as $char) {
            $num = array_key_exists($char, $map) ? $num*64 + $map[$char] : $num*64;
        }

        return $num;
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
            $digit = $num % 60;
            $string = self::NB60CHARS[$digit] . $string;
            $num = ($num - $digit) / 60;
        }
        return $sign . $string;
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
        $num = 0;

        $map = array_flip(str_split(self::NB60CHARS));
        $map['l'] = 1;
        $map['I'] = 1;
        $map['O'] = 0;

        $chars = str_split($nb60num);
        foreach ($chars as $char) {
            $num = array_key_exists($char, $map) ? $num*60 + $map[$char] : $num*60;
        }

        return $num;
    }
}
