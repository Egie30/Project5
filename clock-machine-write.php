<?php
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	$PrsnNbr	=intval($_GET['PRSN_NBR']);
	$ClokTyp	=$_GET['CLOK_TYP'];

	//Need to add checks before writing to the table
	$query  = "INSERT INTO $PAY.ATND_CLOK (PRSN_NBR,CRT_TS,UPD_TS) VALUES (".$PrsnNbr.",CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)";
	$result =mysql_query($query,$cloud);
	$query=str_replace($PAY,"PAY",$query);
	$result=mysql_query($query,$local);

	$query  = "SELECT PRSN_NBR,CRT_TS,COUNT(CRT_TS)AS CNT_ATND FROM PAY.ATND_CLOK WHERE PRSN_NBR=".$PrsnNbr." AND DATE(CRT_TS)=CURDATE() ORDER BY CRT_TS DESC LIMIT 1";
	$result = mysql_query($query, $local);
	$row    = mysql_fetch_array($result);
	if ($row['CRT_TS']!=''){ 
		if ($row['CNT_ATND'] % 2 == 1){
			$stt= "In";
		}else{
			$stt="Out";
		}
		echo "<div class='header' style='margin-top:5px'><b>Absensi Clock-".$stt."</b> berhasil dengan tanggal dan waktu ";
		echo "<span class='header'><b>".$row['CRT_TS']."</b></span></div>";
	}else{
		echo "<span class='header' style='color:#f00a0a;'>Maaf, Anda gagal melakukan absensi silahkan coba lagi.</span></div>";
	}
?>