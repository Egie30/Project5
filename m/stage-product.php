<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$ProdNbr=$_GET['PROD_NBR'];
	
	//Convert order number into product ID/order detail number
	if(substr($ProdNbr,0,1)=='P'){
		$OrdDetNbr=substr($ProdNbr,1);
		if(!(is_numeric($OrdDetNbr))){$OrdDetNbr=-2;}
		$query="SELECT ORD_NBR FROM CMP.PRN_DIG_ORD_DET WHERE ORD_DET_NBR=".$OrdDetNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$ProdNbr=$row['ORD_NBR'];
	}else{
		$ProdNbr=-3;
	}
	//Need more error checking
	if(!(is_numeric($ProdNbr))){$ProdNbr=-2;}
	$query="SELECT HED.ORD_NBR,ORD_TTL,DUE_TS FROM CMP.PRN_DIG_ORD_HEAD HED WHERE HED.ORD_NBR=".$ProdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	if(($row['ORD_NBR']!='')&&($row['ORD_NBR']!=-3)){
		echo "<script id='runScriptOrder' type='text/javascript'>scanType='P';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>".$row['ORD_TTL']."<br/>";
		echo "<span class='header'>".$row['ORD_NBR']."</span></div><br/>";
		echo "Dijanjikan tanggal <b>".parseDateShort($row['DUE_TS'])."</b> jam <b>".parseHour($row['DUE_TS']).":".parseMinute($row['DUE_TS'])."</b>";
		echo "</td>";
		echo "<td style='text-align:right'><img id='scan-result' style='border:0px' src='img/scan-valid.png'></td>";
		echo "</tr>";
		echo "</table>";
	}else{
		echo "<script id='runScriptOrder' type='text/javascript'>scanType='O';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>Scan nota atau barang</td>";
		echo "<td style='text-aling:right'><img style='border:0px;padding:-5px' src='img/scan-failed.gif'></td>";
		echo "</tr>";
	}
?>