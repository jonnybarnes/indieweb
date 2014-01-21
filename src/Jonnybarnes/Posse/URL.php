<?php

namespace Jonnybarnes\Posse;

class URL {
	/**
	 * NewBase64
	 *
	 */
	public function numto64($num)
	{
		$string = '';
		$sign = '';
		$chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';
		
		if(!isset($num) || $num === 0) {
			return 0;
		}

		if($num < 0) {
			$num = -$num;
			$sign = '-';
		}

		while($num > 0) {
			$i = $num % 64;
			$string = $chars[$i] . $string;
			$num = ($num - $i)/ 64;
		}

		return $sign . $string;
	}

	/**
	 * Reverse NewBase64
	 *
	 */
	public function b64tonum($s)
	{
		$num = 0;
		$j = strlen($s);
		for ($i=0;$i<$j;$i++) { // iterate from first to last char of $s
			$c = ord($s[$i]); //  put current ASCII of char into $c  
			if ($c>=48 && $c<=57) { $c=$c-48; }
			else if ($c>=65 && $c<=72) { $c-=55; }
			else if ($c==73 || $c==108 || $c==33) { $c=1; } // typo capital I, lowercase l, ! to 1
			else if ($c>=74 && $c<=78) { $c-=56; }
			else if ($c==79) { $c=0; } // error correct typo capital O to 0
			else if ($c>=80 && $c<=90) { $c-=57; }
			else if ($c==95) { $c=34; } // underscore
			else if ($c>=97 && $c<=107) { $c-=62; }
			else if ($c>=109 && $c<=122) { $c-=63; }
			else if ($c==45) { $c=60; } // dash
			else if ($c==44) { $c=61; } // plus
			else if ($c==42) { $c=62; } // asterisk
			else if ($c==36) { $c=63; } // dollar sign
			else { $c = 0; } // treat all other noise as 0    
			$num = 64*$num + $c;  
		}  
		return $num;
	}

	/**
	 * NewBase60
	 *
	 */
	public function numto60($num)
	{
		$string = '';
		$sign = '';
		$chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';

		if(!isset($num) || $num == 0) {
			return 0;
		}

		if($num < 0) {
			$num = -$num;
			$sign = '-';
		}

		while($num > 0) {
			$digit = $num % 60;
			$string = $chars[$digit] . $string;
			$num = ($num - $digit) / 60;
		}

		return $sign . $string;
	}

	/**
	 * Reverse NewBase60
	 *
	 */
	public function b60tonum($var)
	{
		$num = 0;
		$j = strlen($var);
		for ($i=0;$i<$j;$i++) { // iterate from first to last char of $s
			$c = ord($var[$i]); //  put current ASCII of char into $c  
			if ($c>=48 && $c<=57) { $c=$c-48; }
			else if ($c>=65 && $c<=72) { $c-=55; }
			else if ($c==73 || $c==108) { $c=1; } // typo capital I, lowercase l to 1
			else if ($c>=74 && $c<=78) { $c-=56; }
			else if ($c==79) { $c=0; } // error correct typo capital O to 0
			else if ($c>=80 && $c<=90) { $c-=57; }
			else if ($c==95) { $c=34; } // underscore
			else if ($c>=97 && $c<=107) { $c-=62; }
			else if ($c>=109 && $c<=122) { $c-=63; }
			else { $c = 0; } // treat all other noise as 0
			$num = 60*$num + $c;
		}

		return $num;
	}
}