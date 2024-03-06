<?php
require_once "../database/connect-cloud.php";
$content=file_get_contents("https://www.bca.co.id/api/sitecore/Marketplace/GetReksadanaKinerjaGridJsonAll");
$content=utf8_encode($content);
$result=json_decode($content,true);
foreach ($result as $value) {
	foreach ($value as $row) {
		if($row['0'] == "UPBL001"){
			echo "Nama : ".$row['1']."<br>";
			echo "Tanggal : ".$row['5']."<br>";
			echo "Harga : ".$row['6']."<br>";
			
			$date = date('Y-m-d', strtotime($row['5']));
			if($row['1'] == "Schroder Dana Terpadu II"){
				$query = "SELECT MAX(DATE(CNBTN_PRC_DTE)) AS CNBTN_PRC_DTE FROM $PAY.CNBTN_PRC";
				$results = mysql_query($query);
				$val = mysql_fetch_array($results);
				$princeDate = $val['CNBTN_PRC_DTE'];
				
				if($princeDate == $date){
					$query = "UPDATE $PAY.CNBTN_PRC SET
					PRC = '" . $row['6'] . "',
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=" .$_SESSION['personNBR']. "
					WHERE CNBTN_PRC_DTE = '" . $princeDate . "'";
					$results=mysql_query($query);
				}else{
					$query  = "INSERT INTO $PAY.CNBTN_PRC (CNBTN_PRC_DTE, PRC, UPD_TS, UPD_NBR) VALUES ('" . $date . "', '" . $row['6'] . "', CURRENT_TIMESTAMP, " .$_SESSION['personNBR']. ")";
					$results=mysql_query($query);
				}
			}
		}
	}
}
?>