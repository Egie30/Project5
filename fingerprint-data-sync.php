<?php

	include "framework/database/connect-cloud.php";
	
	$array_data	= [];
	
	$queryUpd	= "SELECT (ATND_CLOK - INTERVAL 1 DAY) AS ATND_CLOK FROM CDW.UPD_LAST";
	$resultUpd	= mysql_query($queryUpd, $local);
	$rowUpd		= mysql_fetch_array($resultUpd);	
	
	$updLast	= $rowUpd['ATND_CLOK'];
	
	$query	= "SELECT * FROM $CMP.ATND_CLOK WHERE CRT_TS > '".$updLast."'";
	$result	= mysql_query($query, $cloud);
				
	while($row = mysql_fetch_array($result)) {
		
		$array_data[] = $row;
	}
	
	foreach($array_data as $data => $rows) {
			
			$PrsnNbr	= $rows['PRSN_NBR'];
			$UpdTs		= $rows['UPD_TS'];
			
			$query_ins 	= "INSERT IGNORE INTO CMP.ATND_CLOK(PRSN_NBR, CRT_TS, UPD_TS)
							VALUES ('".$PrsnNbr."', '".$UpdTs."', CURRENT_TIMESTAMP)";
			$result_ins	= mysql_query($query_ins, $local);
			
			echo $query_ins;
			
		
		echo "<br /><br />";
	}
	
?>