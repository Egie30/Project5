<?php
	function pSpace($numSpaces)
	{
		return str_repeat(" ",$numSpaces);
	}
	function leadZero($number,$len)
	{
		return str_repeat("0",$len-strlen($number)).$number;
	}
	function leadSpace($number,$len)
	{
		if(is_numeric($number)){
			$number=number_format($number,0,",",".");
		}
		return str_repeat(" ",$len-strlen($number)).$number;
	}
	function leadSpaceDecimal($number,$len,$decimal)
	{
		if(is_numeric($number)){
			$number=number_format($number,$decimal,",",".");
		}
		return str_repeat(" ",$len-strlen($number)).$number;
	}
	function followSpace($string,$len)
	{
		if(strlen($string)>$len){$string=substr($string,0,$len);}
		return $string.str_repeat(" ",$len-strlen($string));
	}
	function pRow($numRows)
	{
		return str_repeat(chr(13).chr(10),$numRows);
	}
?>
