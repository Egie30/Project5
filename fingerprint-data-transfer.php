<?php

	include "framework/database/connect.php";
	
	mysql_connect($OLTA,"root","Pr0reliance");
	mysql_select_db("CMP");
		
	$query_upd	= "SELECT ATND_CLOK FROM CDW.UPD_LAST";
	$result_upd	= mysql_query($query_upd);
	$row_upd 	= mysql_fetch_array($result_upd);
	
	$UpdLast	= $row_upd['ATND_CLOK'];
	
	$array_data	= [];
	
	$query 		= "SELECT * FROM CMP.ATND_CLOK WHERE UPD_TS > '".$UpdLast."'";
	$result		= mysql_query($query);
	
	while($row = mysql_fetch_array($result)) {
		
		$array_data[] = $row;
	}
	
	print_r($array_data);
	
?>