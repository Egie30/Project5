<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$AudDt=$_GET['AUD_DT'];
	$InvBcd=$_GET['INV_BCD'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script src="framework/database/jquery.min.js"></script>

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
				<th>Audit</th>
				<th>SPG</th>
				<th>Waktu</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT AUD_Q,NAME,AUD_TS,DATE(AUD_TS) CRT_TS_DATE,TIME(AUD_TS) CRT_TS,INV_BCD FROM RTL.INV_AUD AUD INNER JOIN CMP.PEOPLE PPL ON AUD.PRSN_NBR=PPL.PRSN_NBR WHERE INV_BCD='$InvBcd' AND DATE(AUD_TS)='$AudDt' ORDER BY AUD_TS");
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-audit.php?COM=EDT&CRT_TS_DT=".$row['CRT_TS_DATE']."&CRT_TS=".$row['CRT_TS']."&INV_BCD=".$row['INV_BCD']."';".chr(34).">";
			echo "<td style='text-align:center'>".number_format($row['AUD_Q'],0,".",",")."</td>";
			echo "<td style='text-align:left'>".$row['NAME']."</td>";
			echo "<td style='text-align:center'>".$row['AUD_TS']."</td>";
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
