<?php

namespace Jonnybarnes\IndieWeb;

class Numbers
{
    /**
     * NewBase64
     *
     * Convert a decimal number in NewBase64
     * @param  int
     * @return string
     */
    public function numto64($num)
    {
        $string = '';
        $sign = '';
        $chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';

        if (!isset($num) || $num === 0) {
            return 0;
        }

        if ($num < 0) {
            $num = -$num;
            $sign = '-';
        }

        while ($num > 0) {
            $counter = $num % 64;
            $string = $chars[$counter] . $string;
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
        $len = strlen($nb64num);
        for ($counter = 0; $counter < $len; $counter++) { // iterate from first to last char of $nb64num
            $char = ord($nb64num[$counter]); //  put current ASCII of char into $char
            if ($char>=48 && $char<=57) {
                $char=$char-48;
            } elseif ($char>=65 && $char<=72) {
                $char-=55;
            } elseif ($char==73 || $char==108 || $char==33) {
                $char=1; // typo capital I, lowercase l, ! to 1
            } elseif ($char>=74 && $char<=78) {
                $char-=56;
            } elseif ($char==79) {
                $char=0; // error correct typo capital O to 0
            } elseif ($char>=80 && $char<=90) {
                $char-=57;
            } elseif ($char==95) {
                $char=34; // underscore
            } elseif ($char>=97 && $char<=107) {
                $char-=62;
            } elseif ($char>=109 && $char<=122) {
                $char-=63;
            } elseif ($char==45) {
                $char=60; // dash
            } elseif ($char==44) {
                $char=61; // plus
            } elseif ($char==42) {
                $char=62; // asterisk
            } elseif ($char==36) {
                $char=63; // dollar sign
            } else {
                $char = 0; // treat all other noise as 0
            }
            $num = 64*$num + $char;
        }
        return $num;
    }

    /**
     * NewBase60
     *
     * Convert a decimal nulber to NewBase60
     * @param  int
     * @return string
     */
    public function numto60($num)
    {
        $string = '';
        $sign = '';
        $chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';

        if (intval($num) == 0) {
            return 0;
        }

        if ($num < 0) {
            $num = abs($num);
            $sign = '-';
        }

        while ($num > 0) {
            $digit = $num % 60;
            $string = $chars[$digit] . $string;
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
    public function b60tonum($var)
    {
        $num = 0;
        $len = strlen($var);
        for ($counter = 0; $counter < $len; $counter++) { // iterate from first to last char of $var
            $char = ord($var[$counter]); //  put current ASCII of char into $char
            if ($char>=48 && $char<=57) {
                $char=$char-48;
            } elseif ($char>=65 && $char<=72) {
                $char-=55;
            } elseif ($char==73 || $char==108) {
                $char=1; // typo capital I, lowercase l to 1
            } elseif ($char>=74 && $char<=78) {
                $char-=56;
            } elseif ($char==79) {
                $char=0; // error correct typo capital O to 0
            } elseif ($char>=80 && $char<=90) {
                $char-=57;
            } elseif ($char==95) {
                $char=34; // underscore
            } elseif ($char>=97 && $char<=107) {
                $char-=62;
            } elseif ($char>=109 && $char<=122) {
                $char-=63;
            } else {
                $char = 0; // treat all other noise as 0
            }
            $num = 60*$num + $char;
        }
        return $num;
    }
}
