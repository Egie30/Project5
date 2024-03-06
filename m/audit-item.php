<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$InvBcd=$_GET['INV_BCD'];
	//Validate barcode
	$query="SELECT INV_BCD,NAME,PRC FROM RTL.INVENTORY WHERE INV_BCD='".$InvBcd."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	//echo $query;
	if($row['INV_BCD']!=''){
		echo "<script id='runScriptOrder' type='text/javascript'>scanType='I';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>";
		echo "<span class='header'>".$row['INV_BCD']."</span></div><br/>";
		echo "<b>".($row['NAME'])."</b> Rp. ".number_format($row['PRC'],0,",",".");
		echo "</td>";
		echo "<td style='text-align:right'><img id='scan-result' style='border:0px' src='img/scan-valid.png'></td>";
		echo "</tr>";
		echo "</table>";
	}else{
		echo "<script id='runScriptOrder' type='text/javascript'>scanType='I';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>Scan barang</td>";
		echo "<td style='text-align:right'><img class='barcode' style='border:0px' src='img/scan-failed.gif'></td>";
		echo "</tr>";
	}
?>