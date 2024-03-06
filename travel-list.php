<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$Security=getSecurity($_SESSION['userID'],"Payroll");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<script src="framework/database/jquery.min.js"></script>

	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>

<body>

<div class="toolbar">
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Nama</th>
				<th>Jabatan</th>
				<th>Total</th>
				<th>Total Bulan Ini</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT 
				PPL.PRSN_NBR,
				NAME,
				POS_DESC,
				SUM(CASE WHEN VRFD_F = 1 THEN DIST ELSE 0 END) AS TOT_DIST,
				SUM(CASE WHEN MONTH(ORIG_TS) = MONTH(CURRENT_DATE) AND VRFD_F = 1 AND YEAR(ORIG_TS) = YEAR(CURRENT_DATE) THEN DIST ELSE 0 END) AS CUR_TOT_DIST
			FROM CMP.AUTH_TRVL TRL
				INNER JOIN CMP.PEOPLE PPL ON TRL.PRSN_NBR=PPL.PRSN_NBR 
				INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP 
			WHERE TERM_DTE IS NULL AND CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL)
			GROUP BY PPL.PRSN_NBR ORDER BY 2";	
			//echo $query;
			$result=mysql_query($query);		
			$alt="";
			while($row=mysql_fetch_array($result)){
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='travel-list-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";
				echo "<td class='listable' align=center>".$row['PRSN_NBR']."</a></td>";
				echo "<td class='listable' align='left'>".$row['NAME']."</td>";
				echo "<td class='listable' align='left'>".$row['POS_DESC']."</td>";
				echo "<td class='listable' style='text-align:right'>".number_format($row['TOT_DIST'],1,'.',',')." km</td>";
				echo "<td class='listable' style='text-align:right'>".number_format($row['CUR_TOT_DIST'],1,'.',',')." km</td>";
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
<script>liveReqInit('livesearch','liveRequestResults','travel-list-ls.php','','mainResult');</script>
</body>
</html>