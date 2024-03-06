<?php
	include "framework/functions/default.php"; /*NEW*/
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/database/connect.php";
	
	$CoNbr=$_GET['CO_NBR'];
		
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	
	//if($cloud!=false){
		//Process delete entry
		if($_GET['DEL_C']!="")
		{
			$query="UPDATE CMP.LEAD_DET SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE LEAD_NBR=".$_GET['DEL_C'];
			$result=mysql_query($query);
		}
	//}
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
	if(($_GET['DEL_C']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";	
	}	
	
	$query_company 	= "SELECT NAME FROM COMPANY WHERE CO_NBR = ".$CoNbr;
	$result_company	= mysql_query($query_company);
	$company 		= mysql_fetch_array($result_company);
	$CompanyName	= $company['NAME'];
?>

<div class="toolbar">
	<?php if ($Security <= 1) { ?>
	<p class="toolbar-left"><?php if((paramCloud()==1)){?> <a href="lead-management-act-edit.php?LEAD_NBR=0&CO_NBR=<?php echo $CoNbr; ?>"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a> <?php } ?></p>
	<?php } ?>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<h2>
	<?php echo $CompanyName; ?>
</h2>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Tanggal</th>
				<th>Stage</th>
				<th>Aktivitas</th>
				<th>Rating</th>
				<th>Keterangan</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT LED.CO_NBR,LED.LEAD_NBR,STG_DESC,ACT_DESC,RAT_DESC,ACT_TS,ACT_NTE,UPD_TS FROM CMP.LEAD_DET LED INNER JOIN CMP.LEAD_STG STG ON LED.LEAD_STG=STG.STG_TYP INNER JOIN CMP.LEAD_ACT ACT ON LED.LEAD_ACT=ACT.ACT_TYP INNER JOIN CMP.LEAD_RAT RAT ON LED.LEAD_RAT=RAT.RAT_TYP WHERE CO_NBR=".$CoNbr."
			AND DEL_NBR=0";
			
			//echo $query;
			
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				if($Security <= 1) { $target = "onclick=".chr(34)."location.href='lead-management-act-edit.php?LEAD_NBR=".$row['LEAD_NBR']."&CO_NBR=".$CoNbr."';".chr(34)." "; }
					else { $target = ""; }
					
				echo "<tr $alt style='cursor:pointer;' ".$target." >";
				echo "<td style='text-align:right'>".$row['LEAD_NBR']."</td>";
				echo "<td>".$row['ACT_TS']."</td>";
				echo "<td>".$row['STG_DESC']."</td>";
				echo "<td>".$row['ACT_DESC']."</td>";
				echo "<td>".$row['RAT_DESC']."</td>";
				echo "<td>".$row['ACT_NTE']."</td>";
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

<script>liveReqInit('livesearch','liveRequestResults','lead-management-act-ls.php?CO_NBR=<?php echo $CoNbr; ?>','','mainResult');</script>

</body>
</html>


