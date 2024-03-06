<?php
	include "framework/database/connect.php";
	$query="SELECT FIL_ATT	
			FROM CMP.PRN_DIG_ORD_DET
			WHERE ORD_DET_NBR=".$_GET['ORD_DET_NBR'];
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);

	
	$filename = "print-digital/".$_GET['ORD_DET_NBR'];
	$label = $row['FIL_ATT'];

	$size = filesize($filename);
	header('Content-Type: application/octet-stream');
	header('Content-Length: '.$size);
	header('Content-Disposition: attachment; filename="'.$label.'"');
	header('Content-Transfer-Encoding: binary');
	
	$file = @ fopen($filename, 'rb');

	fpassthru($file);
?>