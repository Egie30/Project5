<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	$ClokTyp=$_GET['CLOK_TYP'];
	//Need to add checks before writing to the table
	$query="INSERT INTO CMP.MACH_CLOK VALUES (CURRENT_TIMESTAMP,".$PrsnNbr.",'".$ClokTyp."')";
	$result=mysql_query($query);
	$query="SELECT MAX(CLOK_TS) AS CLOK_TS,PRSN_NBR,CLOK_DESC FROM CMP.MACH_CLOK CLK INNER JOIN CMP.CLOK_TYP TYP ON CLK.CLOK_TYP=TYP.CLOK_TYP WHERE PRSN_NBR=".$PrsnNbr." AND CLK.CLOK_TYP='".$ClokTyp."' GROUP BY 2,3";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	echo "<div class='header' style='margin-top:5px'><b>Clock-".strtolower($row['CLOK_DESC'])."</b> berhasil dengan tanggal dan waktu<br/>";
	echo "<span class='header'>".$row['CLOK_TS']."</span></div>";
?>