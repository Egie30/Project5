<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	//Check for validity of scan
	if(LuhnVal($PrsnNbr)){
		//Need error checking here
		$PrsnNbr=substr($PrsnNbr,0,-1);
		$query="SELECT NAME FROM CMP.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		echo "<div class='scan-ok'><img src='../address-person/showimg.php?PRSN_NBR=".$PrsnNbr."' style='border-radius:50% 50% 50% 50%;width:50px;height:50px;vertical-align:-120%'>&nbsp;&nbsp;<b>".$row['NAME']."</b> (Employee ID #".$PrsnNbr.")</div>";
		echo "<div class='header' id='header' style='padding:5px;width:638px'>";
		echo "<script id='runScriptName' type='text/javascript'>scanType='I';</script>";
		echo "<table style='border:0px;padding:0px;width:100%'>";
		echo "<tr>";
		echo "<td style='width:100%;vertical-align:top;padding:15px'>Scan barang</td>";
		echo "<td style='text-aling:right'><img style='border:0px' src='img/scan-input.png'></td>";
		echo "</tr>";
		echo "</table>";
		echo "</div>";
		echo "<div id='detail' style='display:none'></div>";
	}else{
		echo "<script id='runScriptName' type='text/javascript'>scanType='E';</script>";
		echo "<div class='scan-fail'>Employee ID tidak dikenal</div>";
	}
?>