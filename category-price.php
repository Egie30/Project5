<?php
	if($_GET['DEL_L']!=""){
		include "framework/database/connect-cloud.php";
	}else{
		include "framework/database/connect.php";
	}
	if($cloud!=false){
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $RTL.CAT_PRC  SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP,
			UPD_NBR=".$_SESSION['personNBR']." 
			WHERE CAT_PRC_NBR=".$_GET['DEL_L'];
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
	<p class="toolbar-left"><?php if((paramCloud()==1)){ echo'<a href="category-price-edit.php?CAT_PRC_NBR=0"><span class="fa fa-plus toolbar" style="cursor:pointer" onclick="location.href="></span></a>';} ?></p>
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
				<th>Pembulatan</th>
				<th>Pengurangan</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT CAT_PRC_NBR,CAT_PRC_DESC,CAT_PRC_AMT,CAT_PRC_PCT,CAT_PRC_RND,CAT_PRC_LES FROM RTL.CAT_PRC ORDER BY CAT_PRC_NBR ASC");
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='category-price-edit.php?CAT_PRC_NBR=".$row['CAT_PRC_NBR']."';".chr(34).">";
			echo "<td class='std-first' align=center>".$row['CAT_PRC_NBR']."</td>";
			echo "<td class='std'>".$row['CAT_PRC_DESC']."</td>";			
			if($row['CAT_PRC_PCT']==""){$CatPrcPct="";}else{$CatPrcPct=number_format($row['CAT_PRC_PCT'],1,'.',',');}
			if($row['CAT_PRC_AMT']==""){$CatPrcAmt="";}else{$CatPrcAmt=number_format($row['CAT_PRC_AMT'],0,'.',',');}
			if($row['CAT_PRC_RND']==""){$CatPrcRnd="";}else{$CatPrcRnd=number_format($row['CAT_PRC_RND'],0,'.',',');}
			if($row['CAT_PRC_LES']==""){$CatPrcLes="";}else{$CatPrcLes=number_format($row['CAT_PRC_LES'],0,'.',',');}
			echo "<td class='std' style='text-align:right;'>".$CatPrcPct."</td>";
			echo "<td class='std' style='text-align:right;'>".$CatPrcAmt."</td>";
			echo "<td class='std' style='text-align:right;'>".$CatPrcRnd."</td>";
			echo "<td class='std' style='text-align:right;'>".$CatPrcLes."</td>";
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

<script>liveReqInit('livesearch','liveRequestResults','category-price-ls.php','','mainResult');</script>
</body>
</html>			
