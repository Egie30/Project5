<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$InvBcd=$_GET['INV_BCD'];
	$PrsnNbr=substr($_GET['PRSN_NBR'],0,-1);
	$Process=$_GET['PROCESS'];
	
	//Process subtraction
	$AudQ=1;if($Process=='DEL'){$AudQ=-1;}
	
	//Add to list
	$query="INSERT INTO RTL.INV_AUD (INV_BCD,AUD_Q,PRSN_NBR) VALUES ('$InvBcd',$AudQ,$PrsnNbr)";
	$result=mysql_query($query);
	//echo $query;

	//Display list	
	$query="SELECT AUD.INV_BCD,SUM(AUD_Q) AS AUD_Q,NAME,PRC,MAX(AUD_TS) AS AUD_TS FROM RTL.INV_AUD AUD INNER JOIN RTL.INVENTORY INV ON AUD.INV_BCD=INV.INV_BCD WHERE DATE(AUD_TS)=CURRENT_DATE GROUP BY AUD.INV_BCD,NAME,PRC ORDER BY MAX(AUD_TS) DESC";
	$result=mysql_query($query);
	//echo $query;
	echo "<table style='width:100%'>";
	echo "<tr class='header'>";
	echo "<th class='header'>Barcode</th>";
	echo "<th class='header'>Nama</td>";
	echo "<th class='header'>Harga</td>";
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
		echo "<td class='detail-left'>".$row['NAME']."</td>";
		echo "<td class='detail-center'>Rp. ".number_format($row['PRC'],0,",",".")."</td>";
		echo "<td class='detail-center'>";
		if($count==1){
			echo "<span style='font-weight:bold;font-size:13pt'>";
		}
		echo number_format($row['AUD_Q'],0,",",".");
		if($count==1){
			echo "</span>";
		}
		echo "</td>";
		echo "<td class='detail-center'>";
		echo parseDateShort($row['AUD_TS'])." ".parseHour($row['AUD_TS']).":".parseMinute($row['AUD_TS']);
		echo "</td>";
		echo "<td class='detail-center'>";
		echo "<img class='listable' src='img/minus.png' onclick=".chr(34)."parent.document.getElementById('content').contentDocument.getElementById('listener').focus();syncGetContent('detail','audit-list.php?INV_BCD=".str_pad($row['INV_BCD'],8,0,STR_PAD_LEFT)."&PRSN_NBR='+prsnNbr+'&PROCESS=DEL')".chr(34).";>";
		echo "</td>";
		echo "</tr>";
		$count++;
	}
	echo "</table>";
?>