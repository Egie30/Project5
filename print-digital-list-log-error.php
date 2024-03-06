<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");

	if($_GET['LOG_TYP']!='')
	{
		$where 	= "WHERE TYP.LOG_ERROR_TYP_NBR=".$_GET['LOG_TYP'];
	}

	if($_GET['LOG_TYP'] == 1 && $_GET['DATE'] == 1 )
	{
		$date 	= "AND DATE(LOG.UPD_TS) >= CURRENT_DATE - INTERVAL 3 MONTH";
	}
	
	//security
	$Security=getSecurity($_SESSION['userID'],"Finance");

	if($_GET['EXPORT']=='XLS'){	
		header("Cache-Control: no-cache, no-store, must-revalidate");  
		header("Content-Type: application/vnd.ms-excel");  
		header("Content-Disposition: attachment; filename=Order_Report.xls");  
	} else {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src="framework/functions/default.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">

</head>

<body>

<div class="toolbar">
	<p class="toolbar-left" style="padding-top: 10px;">
		<select name="LOG_ERROR_TYP" id="LOG_ERROR_TYP" class="chosen-select" style="width: 300px;" onChange="location.href='?LOG_TYP=' + document.getElementById('LOG_ERROR_TYP').value">
			<?php
			$query="SELECT LOG_ERROR_TYP_NBR, LOG_ERROR_DESC
					FROM CMP.LOG_ERROR_TYP";
			genCombo($query,"LOG_ERROR_TYP_NBR","LOG_ERROR_DESC",$_GET["LOG_TYP"],'All');
		?>
		</select>
	</p>
	<p class="toolbar-right">
		<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

<?php
}
?>
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:center;">No. Nota</th>
				<th>Tanggal Nota</th>
				<th>Judul</th>
				<th>Pemesan</th>
				<th style="width:7%;">Total Nota</th>
				<th style="text-align:center;">Status Nota</th>
				<th>Log Error</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query 	= "SELECT 
							LOG.ORD_NBR,
							TYP.LOG_ERROR_DESC,
							HED.ORD_TTL,
							HED.TOT_AMT,
							PPL.NAME AS NAME_PPL,
							COM.NAME AS NAME_CO,
							ORD_TS,
							ORD_STT_DESC
						FROM CDW.LOG_ERROR_ORD LOG
						LEFT JOIN CMP.LOG_ERROR_TYP TYP ON LOG.LOG_ERROR_TYP_NBR = TYP.LOG_ERROR_TYP_NBR
						LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON LOG.ORD_NBR = HED.ORD_NBR
						LEFT JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
						LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR 
						$where $date
						ORDER BY ORD_NBR DESC";
			// echo $query;
			$result = mysql_query($query);
			$alt 	= "";
			while($row=mysql_fetch_array($result)){		
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."changeSiblingUrl('content','print-digital-edit.php?ORD_NBR=".$row['ORD_NBR']."');".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['ORD_TS'])."</td>";
				echo "<td>".$row['ORD_TTL']."</td>";
				echo "<td>".$row['NAME_PPL']." ".$row['NAME_CO']."</td>";
				echo "<td style='text-align:right'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				echo "<td style='text-align:center'>".$row['ORD_STT_DESC']."</td>";
				echo "<td>".$row['LOG_ERROR_DESC']."</td>";
				echo "</tr>";
			}
		?>
		</tbody>
	</table>
<?php
if($_GET['EXPORT']=='XLS'){	
exit();
}else{
?>
</div>

<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery.noConflict();
	var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
   	}
	for (var selector in config) {
		jQuery(selector).chosen(config[selector]);
	}
</script>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ 
			widgets:["zebra"],
			headers: { 
				5: { sorter: 'shortDate'},
				6: { sorter: 'shortDate'},
				7: { sorter: 'shortDate'},
				8: { sorter: 'ipAddress'},
				9: { sorter: 'ipAddress'}
				}
			});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','print-digital-list-log-error-ls.php','','mainResult');</script>
</body>
</html>
<?php
}
?>
