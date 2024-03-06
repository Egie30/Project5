<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	//Validate barcode
	$query="SELECT ORD_DET_NBR,INV.INV_NBR,INV.INV_BCD,INV.NAME
				FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
					 RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
				WHERE ORD_DET_NBR=".$OrdDetNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	//echo $query;
	if($row['INV_BCD']!=''){
		echo "<script id='runScriptOrder' type='text/javascript'>scanType='I';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>";
		echo "<span class='header'>".$row['ORD_DET_NBR']."</span></div><br/>";
		echo "<b>".$row['INV_BCD']."</b> ".$row['NAME'];
		echo "</td>";
		echo "<td style='text-align:right'><img id='scan-result' style='border:0px' src='img/scan-valid.png'></td>";
		echo "</tr>";
		echo "</table>";
	}else{
		echo "<script id='runScriptOrder' type='text/javascript'>scanType='I';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>Scan barang</td>";
		echo "<td style='text-aling:right'><img class='barcode' style='border:0px;padding:-5px' src='img/scan-failed.gif'></td>";
		echo "</tr>";
	}
?>