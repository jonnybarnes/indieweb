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
		$chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz-+*$';
		
		if($num === undeifined || $num === 0) {
			return 0;
		}

		while($num > 0) {
			$i = $num % 64;
			$string .= $chars[$i];
			$num = ($num - $i/ 64;
		}

		return $string;
	}
}