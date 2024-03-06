<?php
	include "framework/database/connect.php";
	
	//Process delete entry
	if($_GET['DEL_A']!="")
	{
		$query="DELETE FROM CMP.UTILITY WHERE UTL_NBR=".$_GET['DEL_A'];
		$result=mysql_query($query);
	}
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

<div class="toolbar">
	<p class='toolbar-left'><a href='utility-edit.php?UTL_NBR=0'><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">Petugas</th>
				<th class="sortable">Client</th>
				<th class="sortable">Pengeluaran</th>
				<th class="sortable" style="border-right:0px;">Jumlah</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT UTL_NBR,DATE(UTL_DTE) AS DTE,PPL.NAME AS PPL_NAME,COM.NAME AS COM_NAME,UTL_DESC,TOT_SUB
					  FROM CMP.UTILITY UTL INNER JOIN
					       CMP.UTL_TYP TYP ON UTL.UTL_TYP=TYP.UTL_TYP LEFT OUTER JOIN
					       CMP.PEOPLE PPL ON UTL.PRSN_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
					       CMP.COMPANY COM ON UTL.CO_NBR=COM.CO_NBR
					 WHERE UTL.UTL_CO_NBR='".$CoNbrDef."'
					ORDER BY UTL.UTL_NBR DESC";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='utility-edit.php?UTL_NBR=".$row['UTL_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['UTL_NBR']."</td>";
				echo "<td>".$row['DTE']."</td>";
				echo "<td>".$row['PPL_NAME']."</td>";
				echo "<td>".$row['COM_NAME']."</td>";
				echo "<td>".$row['UTL_DESC']."</td>";
				echo "<td style='text-align:right'>".number_format($row['TOT_SUB'],0,'.','.')."</td>";
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
<script>liveReqInit('livesearch','liveRequestResults','utility-ls.php','','mainResult');</script>
</body>
</html>


