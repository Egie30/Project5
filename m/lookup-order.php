<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$ProdList=$_GET['PROD_LIST'];
	//echo $ProdList;
	
	//If first letter is 'P'
	if(substr($OrdNbr,0,1)=='P'){
		$OrdDetNbr=substr($OrdNbr,1);
		if(!(is_numeric($OrdDetNbr))){$OrdDetNbr=-2;}
		$query="SELECT ORD_NBR FROM CMP.PRN_DIG_ORD_DET WHERE ORD_DET_NBR=".$OrdDetNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$OrdNbr=$row['ORD_NBR'];
	}
	
	//Need more error checking
	if(!(is_numeric($OrdNbr))){$OrdNbr=-2;}
	$query="SELECT HED.ORD_NBR,ORD_TTL,DUE_TS FROM CMP.PRN_DIG_ORD_HEAD HED WHERE HED.ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	if($row['ORD_NBR']!=''){
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