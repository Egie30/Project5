<?php
	include "framework/database/connect-cloud.php";
	
	if($cloud!=false){
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $RTL.CAT_SUB SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE CAT_SUB_NBR=".$_GET['DEL_L'];
			$result=mysql_query($query,$cloud);
			$query=str_replace($RTL,"RTL",$query);
			$result=mysql_query($query,$local);
		}
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

<script type="text/javascript">
	$(document).ready(function(){
		<?php if($j>0){echo "parent.msgGrowl('$j record telah di sinkronisasi.');";} ?>
	});
</script>
	
</head>
<body>

<div class="toolbar">
	<p class="toolbar-left"><a href="category-sub-edit.php?CAT_SUB_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Kategori</th>				
				<th>Deskripsi</th>
				<th>Tipe</th>
				<th>Akun</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT SUB.CAT_SUB_NBR,
								SUB.CAT_SUB_DESC, 
								CAT.CAT_DESC,
								TYP.CAT_TYP, 
								CONCAT(CDCAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', CDSUB.CD_SUB_DESC) AS ACC_DESC
							FROM RTL.CAT_SUB SUB 
								INNER JOIN RTL.CAT CAT ON CAT.CAT_NBR=SUB.CAT_NBR
								LEFT JOIN RTL.CAT_TYP TYP ON SUB.CAT_TYP_NBR = TYP.CAT_TYP_NBR
								LEFT JOIN RTL.ACCTG_CD_SUB CDSUB ON SUB.CD_SUB_NBR = CDSUB.CD_SUB_NBR
								LEFT JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=CDSUB.CD_NBR
								LEFT JOIN RTL.ACCTG_CD_CAT CDCAT ON CDCAT.CD_CAT_NBR=ACC.CD_CAT_NBR
							WHERE SUB.DEL_NBR=0 ORDER BY SUB.CAT_SUB_NBR ASC");
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='category-sub-edit.php?CAT_SUB_NBR=".$row['CAT_SUB_NBR']."';".chr(34).">";
			echo "<td class='std-first' align=center>".$row['CAT_SUB_NBR']."</td>";
			echo "<td class='std'>".$row['CAT_DESC']."</td>";
			echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
			echo "<td class='std'>".$row['CAT_TYP']."</td>";
			echo "<td class='std'>".$row['ACC_DESC']."</td>";
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

<script>liveReqInit('livesearch','liveRequestResults','category-sub-ls.php','','mainResult');</script>

</body>
</html>			
