<?php
	$PrsnNbr=$_GET['PRSN_NBR'];
	$imgName=$PrsnNbr.".jpg";
	if(file_exists($imgName)){
		$imgResized=imagecreatetruecolor(192,192);
		$imgTmp=imagecreatefromjpeg($imgName);
		list($width,$height)=getimagesize($imgName);
		imagecopyresampled($imgResized,$imgTmp,0,0,0,0,192,192,$width,$height);
		$image=$imgResized;
	}else{
		$image=imagecreatefromjpeg("default.jpg");
	}
	header("Content-Type:image/jpeg");
	imagejpeg($image);
?>