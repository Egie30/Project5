<?php
	if($_GET['DEL_L']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}
	if($cloud!=false){
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $RTL.CAT SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE CAT_NBR=".$_GET['DEL_L'];
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
	<?php if((paramCloud()==1)){?>
	<p class="toolbar-left"><a href="category-edit.php?CAT_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p><?php } ?>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Deskripsi</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT CAT_NBR,CAT_DESC FROM RTL.CAT WHERE DEL_NBR=0 ORDER BY CAT_NBR ASC");
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='category-edit.php?CAT_NBR=".$row['CAT_NBR']."';".chr(34).">";
			echo "<td class='std-first' align=center>".$row['CAT_NBR']."</td>";
			echo "<td class='std'>".$row['CAT_DESC']."</td>";
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

<script>liveReqInit('livesearch','liveRequestResults','category-ls.php','','mainResult');</script>

</body>
</html>			
