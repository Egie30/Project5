<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" WHERE LOG.WHSE_NBR=".$whse;}

	//Process delete entry
	if($_GET['DEL_A']!="")
	{
		$query="DELETE FROM CMP.STA_LOG WHERE LOG_NBR=".$_GET['DEL_A'];
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
	<p class="toolbar-left"><a href="stationery-edit.php?LOG_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable">Tanggal</th>
				<?php
					if($whse==""){echo "<th class='sortable'>Gudang</th>";}
				?>
				<th class="sortable">Jenis Barang</th>
				<th class="sortable">Status</th>
				<th class="sortable">Jumlah</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT LOG_NBR,MOV_DTE,CONCAT(NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME,MOV_DESC,MOV_CNT,WHSE_DESC
						FROM CMP.STA_LOG LOG INNER JOIN 
							 CMP.STA_MOV MOV ON LOG.MOV_TYP=MOV.MOV_TYP INNER JOIN 
							 CMP.STATIONERY STA ON LOG.STA_NBR=STA.STA_NBR INNER JOIN 
							 CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN 
							 CMP.WHSE_LOC LOC ON LOG.WHSE_NBR=LOC.WHSE_NBR INNER JOIN
							 CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR $whse
						ORDER BY LOG.UPD_DTE DESC
						LIMIT 0,100";
						//echo $query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='stationery-edit.php?LOG_NBR=".$row['LOG_NBR']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['LOG_NBR']."</td>";
					echo "<td class='std-first'>".$row['MOV_DTE']."</td>";
					if($whse==""){echo "<td class='std'>".$row['WHSE_DESC']."</td>";}
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['MOV_DESC']."</td>";
					echo "<td class='std' align='right'>".$row['MOV_CNT']."</td>";
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";};
					if($alt==""){$alt="class='alt'";}else{$alt="";}
				}
			?>
		</tbody>
	</table>

</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>

<script>liveReqInit('livesearch','liveRequestResults','stationery-ls.php<?php echo $whse; ?>','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>


