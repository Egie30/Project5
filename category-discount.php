<?php
	include "framework/database/connect.php";
	
	if($_GET['DEL_L']!="")
	{
		$query="DELETE FROM RTL.CAT_DISC WHERE CAT_DISC_NBR=".$_GET['DEL_L'];
		$result=mysql_query($query);
	}
	
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
	<p class="toolbar-left"><a href="category-discount-edit.php?CAT_DISC_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Deskripsi</th>
				<th>Persen</th>
				<th>Jumlah</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT CAT_DISC_NBR,CAT_DISC_DESC,CAT_DISC_AMT,CAT_DISC_PCT FROM RTL.CAT_DISC ORDER BY 2");
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='category-discount-edit.php?CAT_DISC_NBR=".$row['CAT_DISC_NBR']."';".chr(34).">";
			echo "<td class='std-first' align=center>".$row['CAT_DISC_NBR']."</td>";
			echo "<td class='std'>".$row['CAT_DISC_DESC']."</td>";			if($row['CAT_DISC_PCT']==""){$CatDiscPct="";}else{$CatDiscPct=number_format($row['CAT_DISC_PCT'],1,'.',',');}
			if($row['CAT_DISC_AMT']==""){$CatDiscAmt="";}else{$CatDiscAmt=number_format($row['CAT_DISC_AMT'],0,'.',',');}
			echo "<td class='std' style='text-align:right;'>".$CatDiscPct."</td>";
			echo "<td class='std' style='text-align:right;'>".$CatDiscAmt."</td>";
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

<script>liveReqInit('livesearch','liveRequestResults','category-discount-ls.php','','mainResult');</script>
</body>
</html>			
