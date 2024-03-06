<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" WHERE LOG.WHSE_NBR=".$whse;}

	//Process delete entry
	if($_GET['DEL_L']!="")
	{
		$query="DELETE FROM CMP.INVENTORY WHERE INV_NBR=".$_GET['DEL_L'];
		$result=mysql_query($query);
	}
	if($_GET['DEL_A']!="")
	{
		$query="DELETE FROM CMP.INV_LOG WHERE LOG_NBR=".$_GET['DEL_A'];
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script src="framework/database/jquery.min.js"></script>

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>

</head>

<body>

<div class="toolbar">
	<p class="toolbar-left"><a href="inventory-activity-edit.php?LOG_NBR=0"><span class='fa fa-plus toolbar' style='cursor:pointer' onclick='location.href='></span></a></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Tanggal</th>
				<?php
					if($whse==""){echo "<th>Gudang</th>";}
				?>
				<th>Jenis Barang</th>
				<th>Status</th>
				<th>Jumlah</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT LOG_NBR,MOV_DTE,CONCAT(NAME,' ',COLR_DESC,' ',THIC,' ',SIZE,' ',WEIGHT) AS NAME,MOV_DESC,MOV_CNT,WHSE_DESC
						FROM CMP.INV_LOG LOG INNER JOIN
						CMP.INV_MOV MOV ON LOG.MOV_TYP=MOV.MOV_TYP INNER JOIN
						CMP.INVENTORY INV ON LOG.INV_NBR=INV.INV_NBR INNER JOIN
						CMP.INV_TYP TYP ON INV.INV_TYP=TYP.INV_TYP INNER JOIN
						CMP.WHSE_LOC LOC ON LOG.WHSE_NBR=LOC.WHSE_NBR INNER JOIN
						CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR $whse
						ORDER BY LOG.UPD_DTE DESC
						LIMIT 0,100";
						//echo $query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-activity-edit.php?LOG_NBR=".$row['LOG_NBR']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['LOG_NBR']."</td>";
					echo "<td class='std-first'>".$row['MOV_DTE']."</td>";
					if($whse==""){echo "<td class='std'>".$row['WHSE_DESC']."</td>";}
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['MOV_DESC']."</td>";
					echo "<td class='std' align='right'>".$row['MOV_CNT']."</td>";
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";};
				}
			?>
		</tbody>
	</table>

</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','inventory-activity-ls.php<?php echo $whse; ?>','','mainResult');</script>
</body>
</html>


