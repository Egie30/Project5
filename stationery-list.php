<?php
	include "framework/database/connect.php";

	//Process location
	$whse=$_GET['WHSE'];
	if($whse!=""){$whse=" WHERE LOG.WHSE_NBR=".$whse;}

	//Process delete entry
	if($_GET['DEL_L']!="")
	{
		$query="DELETE FROM CMP.STATIONERY WHERE STA_NBR=".$_GET['DEL_L'];
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
	<p class="toolbar-left"><a href="stationery-list-edit.php?STA_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable">Jenis</th>
				<th class="sortable">Nama</th>
				<th class="sortable">Unit</th>
				<th class="sortable">Isi</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$query="SELECT STA_NBR,STA_TYP_DESC,CONCAT(NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME,UNIT,CTN_NBR
						FROM CMP.STATIONERY STA INNER JOIN 
							 CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN
							 CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
							 ORDER BY UPD_DTE DESC
						LIMIT 0,100";
						//echo $query;
									
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='stationery-list-edit.php?STA_NBR=".$row['STA_NBR']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['STA_NBR']."</td>";
					echo "<td class='std'>".$row['STA_TYP_DESC']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['UNIT']."</td>";
					echo "<td class='std' align='right'>".$row['CTN_NBR']."</td>";
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					if($alt==""){$alt="class='alt'";}else{$alt="";}
				}
			?>
		</tbody>
	</table>

</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>

<script>liveReqInit('livesearch','liveRequestResults','stationery-list-ls.php<?php echo $whse; ?>','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>