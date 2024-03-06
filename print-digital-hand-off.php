<?php
	include "framework/database/connect-cloud.php";
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	
	if($cloud!=false){
		//Process delete entry
		if($_GET['DEL_L']!="")
		{
			$query="DELETE FROM $CMP.HND_OFF_TYP WHERE HND_OFF_TYP='".$_GET['DEL_L']."'";
			//echo $query;
	   		$result=mysql_query($query,$cloud);
			$query=str_replace($CMP,"CMP",$query);
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

</head>

<body>

<?php
	if(($_GET['DEL_L']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>


<div class="toolbar">
	<?php if($UpperSec<5){ ?>
	<p class="toolbar-left"><a href="print-digital-hand-off-edit.php?PLAN_TYP=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a></p>
	<?php } ?>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>


<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr style="text-align:center;">
				<th>No.</th>
				<th>Kode</th>
				<th>Serah Terima</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 1;
			
			$query="SELECT HND_OFF_TYP,HND_OFF_DESC	FROM CMP.HND_OFF_TYP";
			$result=mysql_query($query,$local);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt ";
				if($UpperSec<5){ 
					echo "style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-hand-off-edit.php?HND_OFF_TYP=".$row['HND_OFF_TYP']."';".chr(34);
				}
				echo ">";
				echo "<td style='text-align:center;'>".$i."</td>";
				echo "<td>".$row['HND_OFF_TYP']."</td>";
				echo "<td>".$row['HND_OFF_DESC']."</td>";
				echo "</tr>";
				$i++;
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
<script>liveReqInit('livesearch','liveRequestResults','print-digital-hand-off-ls.php','','mainResult');</script>
</body>
</html>


