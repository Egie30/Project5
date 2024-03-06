<?php
	include "framework/database/connect.php";

	$ORD_DET_NBR = $_POST["ORD_DET_NBR"];
	
	if (!is_numeric(substr($ORD_DET_NBR, 0, 1))){
		$whereClause = " DET.NTE ='".$ORD_DET_NBR."'";
	}else{
		$whereClause = " DET.ORD_DET_NBR = '". $ORD_DET_NBR ."'";
	}
	
	$query = "SELECT 
		INV.INV_NBR,
		INV.NAME NAME, 
		DATE(MOV.CRT_TS) CRT_TS_DATE,
		TIME(MOV.CRT_TS) CRT_TS,
		MOV_Q, 
		PPL.NAME STAF,MOV.ORD_DET_NBR ORD_DET_NBR 
	FROM RTL.RTL_STK_DET DET
		LEFT OUTER JOIN RTL.INV_MOV MOV ON MOV.ORD_DET_NBR=DET.ORD_DET_NBR 
		LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR=DET.INV_NBR
		LEFT OUTER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=MOV.CRT_NBR 
	WHERE  ".$whereClause."
	GROUP BY DET.ORD_DET_NBR";
	//echo "<pre>".$query;
	$result	= mysql_query($query);
	$row 	= mysql_fetch_array($result);
	$rc		= $row["NAME"];

	echo json_encode ($rc);
?>
