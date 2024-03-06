<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$security=getSecurity($_SESSION['userID'],"DigitalPrint");

	$CMPTR_NAME  =$_GET['CMPTR_NAME'];
	$BegDt 	  =$_GET['BEG_DT'];
	$EndDt 	  =$_GET['END_DT'];
	if($BegDt==""){
		$BegDt=date("Y-m-d",strtotime("-7 days"));
	}
	if($EndDt==""){
		$EndDt=date("Y-m-d");
	}

	//Process equipment
	$Eqp=$_GET['EQP'];		
	if($Eqp!=""){
		$where.=" AND LOG.MACH_TYP='".$Eqp."'";
	}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

	<script>parent.Pace.restart();</script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>

	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script type="text/javascript">jQuery.noConflict();</script>
</head>
<body>

	<div style="display: inline-block; float: left; margin-top: 0px; margin-bottom:9px;margin-left: 9px;">	
		<input id="BEG_DT" name="BEG_DT" value="<?php echo $BegDt; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<input id="END_DT" name="END_DT" value="<?php echo $EndDt; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<span class="fa fa-calendar toolbar fa-lg" style="padding-left:0px;cursor:pointer" onclick="location.href='log-list.php?CMPTR_NAME=<?php echo $CMPTR_NAME; ?>&EQP=<?php echo $Eqp;?>&BEG_DT='+document.getElementById('BEG_DT').value+'&END_DT='+document.getElementById('END_DT').value"></span>
		</div>


<div id="mainResult">
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
			<tr >
				<th class="sortable">Computer</th>
				<th class="sortable">Printer</th>
				<th class="sortable">Type</th>
				<th class="sortable">File</th>
				<th class="sortable">Dimension</th>
				<th class="sortable">Date</th>
				<th class="sortable">Start</th>
				<th class="sortable">End</th>
				<th class="sortable">Duration</th>
			</tr>
			</thead>
	<tbody>
		<?php
		$query="SELECT LOG.CMPTR_NAME as CMPTR_NAME, LOG.MACH_TYP as MACH_TYP, LOG.PRN_TYP as PRN_TYP, LOG.FIL_NM as FIL_NM, LOG.PRN_DIM as PRN_DIM, LOG.BEG_TS as BEG_TS, LOG.END_TS as END_TS, LOG.DUR_PRN as DUR_PRN
					FROM CMP.log_file LOG  WHERE
					DATE(END_TS) BETWEEN '$BegDt' AND '$EndDt'
					AND 
					DATE(BEG_TS) BETWEEN '$BegDt' AND '$EndDt'
					$where
					ORDER BY END_TS DESC
					";
			$result=mysql_query($query);
		// echo $query;
		$alt="";
		$i=1;
		while($row=mysql_fetch_array($result))
		{
			$date = date_create($row['BEG_TS']);
			$date1 = date_create($row['END_TS']);
			$diff = date_diff( $date, $date1 );
			echo "<tr>";
				echo "<td style='text-align:left'>".$row['CMPTR_NAME']."</td>";
				echo "<td style='text-align:left'>".utf8_encode($row['MACH_TYP'])."</td>";
				echo "<td style='text-align:left'>".utf8_encode($row['PRN_TYP'])."</td>";
				echo "<td style='text-align:left'>".$row['FIL_NM']."</td>";
				echo "<td style='text-align:left'>".utf8_encode($row['PRN_DIM'])."</td>";
				echo "<td style='text-align:left'>".date_format($date,'Y-m-d')."</td>";
				echo "<td style='text-align:left'>".date_format($date,'H:i:s')."</td>";
				// echo "<td style='text-align:left'>".$row['END_TS']."</td>";
				echo "<td style='text-align:left'>".date_format($date1,'H:i:s')."</td>";
				echo "<td style='text-align:left'>".$diff->h . ' jam, '.$diff->i . ' menit, '.$diff->s . ' second '."</td>";
				echo "</tr>";
		}
		?>
	</tbody>
</table>
</div>
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>	
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script>
	$.noConflict();
		jQuery(document).ready(function($){
		$("#mainTable").tablesorter({ widgets:["zebra"]}); 
	});
	</script>

<script>liveReqInit('livesearch','liveRequestResults','log-list.php','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>
