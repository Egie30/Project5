<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	$PrsnNbr=substr($_GET['PRSN_NBR'],0,-1);
	$Process=$_GET['PROCESS'];
	
	//Process subtraction
	$MovQ=1;if($Process=='DEL'){$MovQ=-1;}
	
	//Add to list
	$query="INSERT INTO RTL.INV_MOV (ORD_DET_NBR,MOV_Q,CRT_NBR) VALUES ('$OrdDetNbr',$MovQ,$PrsnNbr)";
	$result=mysql_query($query);
	//echo $query;

	//Display list
	$query="SELECT DET.ORD_DET_NBR,INV.INV_NBR,INV.INV_BCD,INV.NAME,SUM(MOV_Q) AS MOV_Q,MAX(MOV.CRT_TS) AS CRT_TS FROM RTL.INV_MOV MOV LEFT OUTER JOIN RTL.RTL_STK_DET DET ON MOV.ORD_DET_NBR=DET.ORD_DET_NBR LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR WHERE DATE(MOV.CRT_TS)=CURRENT_DATE GROUP BY INV.INV_BCD,NAME,PRC ORDER BY MAX(MOV.CRT_TS) DESC";	
	//$query="SELECT AUD.INV_BCD,SUM(AUD_Q) AS AUD_Q,NAME,PRC,MAX(AUD_TS) AS AUD_TS FROM RTL.INV_AUD AUD INNER JOIN RTL.INVENTORY INV ON AUD.INV_BCD=INV.INV_BCD WHERE DATE(AUD_TS)=CURRENT_DATE GROUP BY AUD.INV_BCD,NAME,PRC ORDER BY MAX(AUD_TS) DESC";
	$result=mysql_query($query);
	//echo $query;
	echo "<table style='width:100%'>";
	echo "<tr class='header'>";
	echo "<th class='header'>Barcode</th>";
	echo "<th class='header'>SKU</td>";
	echo "<th class='header'>Nama</td>";
	echo "<th class='header'>Jumlah</td>";
	echo "<th class='header'>Waktu</td>";
	echo "<th class='header'></td>";
	$count=1;
	while($row=mysql_fetch_array($result))
	{
		//Show new color if sort bay is found
		echo "<tr ";
		if($count==1){
			echo "style='background-color:#e3f7db'";
		}
		echo ">";
		echo "<td class='detail-center'>".$row['INV_BCD']."</td>";
		echo "<td class='detail-center'>".$row['ORD_DET_NBR']."</td>";
		echo "<td class='detail-left'>".$row['NAME']."</td>";
		echo "<td class='detail-center'>";
		if($count==1){
			echo "<span style='font-weight:bold;font-size:13pt'>";
		}
		echo number_format($row['MOV_Q'],0,",",".");
		if($count==1){
			echo "</span>";
		}
		echo "</td>";
		echo "<td class='detail-center'>";
		echo parseDateShort($row['CRT_TS'])." ".parseHour($row['CRT_TS']).":".parseMinute($row['CRT_TS']);
		echo "</td>";
		echo "<td class='detail-center'>";
		echo "<img class='listable' src='img/minus.png' onclick=".chr(34)."parent.document.getElementById('content').contentDocument.getElementById('listener').focus();syncGetContent('detail','inventory-list.php?ORD_DET_NBR=".$row['ORD_DET_NBR']."&CRT_NBR='+prsnNbr+'&PROCESS=DEL')".chr(34).";>";
		echo "</td>";
		echo "</tr>";
		$count++;
	}
	echo "</table>";
?>