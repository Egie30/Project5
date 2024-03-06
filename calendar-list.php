<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" WHERE LOG.WHSE_NBR=".$whse;}

	//Process delete entry
	
	if(isset($_GET['DEL_L'])!="")
	{
		$query="DELETE FROM CMP.CAL_LST WHERE CAL_NBR=".$_GET['DEL_L'];
		//echo $query;
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>

</head>

<body>

<div class="toolbar">
	<p class="toolbar-left"><a href="calendar-list-edit.php?LOG_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" width="70px;">Kode</th>
				<th class="sortable" width="150px;">Deskripsi</th>
				<th class="sortable">Blangko</th>
				<th class="sortable" style="border-right:0px;">Cetak</th>
				<th class="sortable">Pesan</th>
				<th class="sortable" style="border-right:0px;">Stock</th>
			</tr>
		</thead>
		<tbody>
			<?php
				
				$query="SELECT CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,
						LST.CAL_NBR,
						CAL_DESC,
						CAL_PRC_BLK,
						CAL_PRC_PRN,
						SUM(CASE WHEN ORD_TYP='ORD' THEN ORD_Q ELSE 0 END)-SUM(CASE WHEN ORD_TYP='RCV' THEN ORD_Q ELSE 0 END)+SUM(CASE WHEN ORD_TYP='RET' THEN ORD_Q ELSE 0 END) AS SHP,
						SUM(CASE WHEN ORD_TYP='RCV' AND BUY_CO_NBR=1 THEN ORD_Q ELSE 0 END)-SUM(CASE WHEN ORD_TYP='REQ' AND SEL_CO_NBR=1 THEN ORD_Q ELSE 0 END)+SUM(CASE WHEN ORD_TYP='RET' AND SEL_CO_NBR=1 THEN ORD_Q ELSE 0 END) AS CMP
					FROM CMP.CAL_LST LST 
						LEFT OUTER JOIN CMP.CAL_ORD_DET DET ON DET.CAL_NBR=LST.CAL_NBR 
						LEFT OUTER JOIN CMP.CAL_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY CMP ON LST.CO_NBR=CMP.CO_NBR
					WHERE ACTIVE_F IS TRUE AND LST.UPD_DTE BETWEEN ".getFiscalYear()."
					GROUP BY CONCAT(CO_ID,CAL_ID,CAL_TYP),CAL_NBR,CAL_DESC
					ORDER BY 3 DESC";
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-list-edit.php?CAL_NBR=".$row['CAL_NBR']."';".chr(34).">";
				echo "<td>".$row['CAL_CODE']."</td>";
				echo "<td>".$row['CAL_DESC']."</td>";
				echo "<td style='text-align:right'>".number_format($row['CAL_PRC_BLK'],0,",",".")."</td>";
				echo "<td style='text-align:right'>".number_format($row['CAL_PRC_PRN'],0,",",".")."</td>";
				echo "<td style='text-align:right'><a href='calendar-report-act.php?CAL_NBR=".$row['CAL_NBR']."'>".number_format($row['SHP'],0,",",".")."</td>";
				echo "<td style='text-align:right'><a href='calendar-report-act.php?CAL_NBR=".$row['CAL_NBR']."'>".number_format($row['CMP'],0,",",".")."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
				}
			?>
		</tbody>
	</table>

</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>

<script>liveReqInit('livesearch','liveRequestResults','calendar-list-ls.php<?php echo $whse; ?>','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>


