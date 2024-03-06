<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$years=$_GET['YEARS'];
	$year=$_GET['YEARS'];
	
	if($years!=""){$where="YEAR(CRT_TS)=" . $years;}
	if($year !=""){$where="$year";}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	
</head>
<body>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>Tahun</th>			
				<th>Bulan</th>			
				<th>Nota</th>				
				<th>Item</th>
				<th>Total</th>
				<th>Disc</th>
				<th>Net</th>
				<th>Tunai</th>
				<th>Debit</th>
				<th>Kredit</th>
				<th>Cek</th>
				<th>Kembali</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query="SELECT 
					SUM(CASE WHEN CSH_FLO_TYP='TL' THEN 1 ELSE 0 END) AS TRSC_NBR,
					MAX(YEAR(CRT_TS)) AS CRT_YR,MAX(MONTH(CRT_TS)) AS CRT_MO,SUM(RTL_Q) AS RTL_Q,
					SUM(CASE WHEN CSH_FLO_TYP='RT' THEN TND_AMT ELSE 0 END) AS TND_AMT,
					SUM(CASE WHEN CSH_FLO_TYP='DS' THEN TND_AMT ELSE 0 END) AS DISC_AMT,
					SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='CSH' THEN TND_AMT ELSE 0 END) AS CSH_AMT,
					SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='DEB' THEN TND_AMT ELSE 0 END) AS DEB_AMT,
					SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='CRT' THEN TND_AMT ELSE 0 END) AS CRT_AMT,
					SUM(CASE WHEN CSH_FLO_TYP='PA' AND PYMT_TYP='CHK' THEN TND_AMT ELSE 0 END) AS CHK_AMT,
					SUM(CASE WHEN CSH_FLO_TYP='CH' THEN TND_AMT ELSE 0 END) AS CHG_AMT 
				FROM RTL.CSH_REG CSH 
				LEFT OUTER JOIN RTL.PEOPLE PPL ON CSH.CRT_NBR=PPL.PRSN_NBR 
				WHERE YEAR(CRT_TS)=$where 
				GROUP BY MONTH(CRT_TS)";
		$alt="";
		$result = mysql_query($query);
		while($row=mysql_fetch_array($result)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='forms-sale-month.php?MONTH=".$row['CRT_MO']."&YEAR=".$row['CRT_YR']."';".chr(34).">";
			echo "<td style='text-align:center'>".$row['CRT_YR']."</td>";			
			echo "<td style='text-align:center'>".$row['CRT_MO']."</td>";			
			echo "<td style='text-align:center'>".$row['TRSC_NBR']."</td>";			
			echo "<td style='text-align:center'>".$row['RTL_Q']."</td>";
			if($row['TND_AMT']=="0"){$TndAmt="";}else{$TndAmt=number_format($row['TND_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$TndAmt."</td>";
			if($row['DISC_AMT']=="0"){$DiscAmt="";}else{$DiscAmt=number_format($row['DISC_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$DiscAmt."</td>";
			if($TndAmt==""){$NetAmt="";}else{$NetAmt=number_format($row['TND_AMT']-$row['DISC_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$NetAmt."</td>";
			if($row['CSH_AMT']=="0"){$CshAmt="";}else{$CshAmt=number_format($row['CSH_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$CshAmt."</td>";
			if($row['DEB_AMT']=="0"){$DbtAmt="";}else{$DbtAmt=number_format($row['DEB_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$DbtAmt."</td>";
			if($row['CRT_AMT']=="0"){$CrtAmt="";}else{$CrtAmt=number_format($row['CRT_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$CrtAmt."</td>";
			if($row['CHK_AMT']=="0"){$ChkAmt="";}else{$ChkAmt=number_format($row['CHK_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$ChkAmt."</td>";
			if($row['CHG_AMT']=="0"){$ChgAmt="";}else{$ChgAmt=number_format($row['CHG_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$ChgAmt."</td>";
			echo "</tr>";
		}
		?>
		</tbody>
	</table>

</div>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
</body>
</html>			