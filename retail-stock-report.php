<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$CoNbr=$_GET['CO_NBR'];
	$IvcTyp=$_GET['IVC_TYP'];
	
	//if($IvcTyp!=''){$where="WHERE HED.IVC_TYP='".$IvcTyp."'";}
	if($CoNbr=='1' || $CoNbr=='4'){$where="WHERE SHP_CO_NBR='".$CoNbr."' AND HED.IVC_TYP='".$IvcTyp."'";}
	else{$where="WHERE HED.IVC_TYP='".$IvcTyp."'";}	
	
	$BegDt=$_GET['BEG_DT'];
	$EndDt=$_GET['END_DT'];
	if($BegDt==""){
		$BegDt=date("Y-m-01");
	}
	if($EndDt==""){
		$EndDt=date("Y-m-d");
	}
	//echo $_GET['IVC_TYP']." ".$_GET['BEG_DT']." ".$_GET['END_DT'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript">jQuery.noConflict()</script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	
</head>

<body>

<div class="toolbar">
	<p class="toolbar-left">
		&nbsp;
		<input id="BEG_DT" name="BEG_DT" value="<?php echo $BegDt; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<input id="END_DT" name="END_DT" value="<?php echo $EndDt; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<span class="fa fa-calendar toolbar fa-lg" style="padding-left:0px;cursor:pointer" onclick="location.href='retail-stock-report.php?IVC_TYP=<?php echo $IvcTyp; ?>&BEG_DT='+document.getElementById('BEG_DT').value+'&END_DT='+document.getElementById('END_DT').value"></span>
		</p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<th class="sortable" style="text-align:right;">Item</th>
				<th class="sortable">Pengirim</th>
				<th class="sortable">Penerima</th>
				<th class="sortable">Terima</th>
				<th class="sortable">Nota</th>
				<th class="sortable">Faktur</th>			
				<th class="sortable">Jual</th>			
				<th class="sortable" style="text-align:right;">Sub Faktur</th>				
				<th class="sortable" style="text-align:right;">Sub Jual</th>
				<?php
					if($_GET['SEL']=="DEB"){
						echo "<th class='sortable'>Jatuh Tempo</th>";
					}
				?>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT HED.ORD_NBR,ORD_DTE,IVC_DESC,ORD_Q_TOT,TOT,INV_PRC_TOT,PRC_TOT,REF_NBR,SHP_CO_NBR,RCV_CO_NBR,SHP.NAME AS SHP_NAME,RCV.NAME AS RCV_NAME,HED.FEE_MISC,
					TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,DL_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,
					HED.UPD_NBR,DATEDIFF(DATE_ADD(ORD_DTE,INTERVAL COALESCE(SHP.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS SHP_PAST_DUE
					FROM RTL.RTL_STK_HEAD HED LEFT OUTER JOIN
					(SELECT HED.ORD_NBR,SUM(PRC*ORD_Q) AS TOT,SUM(DET.INV_PRC) AS INV_PRC_TOT,SUM(PRC) AS PRC_TOT,SUM(ORD_Q) AS ORD_Q_TOT
						FROM RTL.RTL_STK_HEAD HED INNER JOIN 
							RTL.RTL_STK_DET DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN
							RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
						GROUP BY DET.ORD_NBR ASC) AS DET
						ON HED.ORD_NBR=DET.ORD_NBR
					LEFT OUTER JOIN RTL.IVC_TYP IVC ON HED.IVC_TYP=IVC.IVC_TYP
					LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR=SHP.CO_NBR
					LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR=RCV.CO_NBR $where AND DEL_F=0
					AND DATE(DL_TS) BETWEEN '$BegDt' AND '$EndDt'
					ORDER BY DL_TS ASC";
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='retail-stock-edit.php?IVC_TYP=".$IvcTyp."&ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				echo "<td style='text-align:right'>".$row['ORD_Q_TOT']."</td>";				
				echo "<td nowrap>".$row['SHP_NAME']."</td>";
				echo "<td nowrap>".$row['RCV_NAME']."</td>";
				echo "<td nowrap style='text-align:center'>".parseDate($row['DL_TS'])."</td>";
				echo "<td nowrap style='text-align:center'>".parseDate($row['ORD_DTE'])."</td>";
				echo "<td style='text-align:right;'>".number_format($row['INV_PRC_TOT'],0,',','.')."</td>";	
				echo "<td style='text-align:right;'>".number_format($row['PRC_TOT'],0,',','.')."</td>";	
				echo "<td style='text-align:right;'>".number_format($row['TOT'],0,',','.')."</td>";					
				echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
				if($_GET['SEL']=="DEB"){
					echo "<td style='text-align:right'>".parseDate($row['PAST_DUE'])."</td>";
				}
				echo "</tr>";
				$sub+=$row['TOT_AMT'];
				$item+=$row['ORD_Q_TOT'];
				$subhb+=$row['TOT_AMT'];
				$subhj+=$row['TOT'];			
			}		
		?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Item  <?php echo number_format($item,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;					
					Total SubHB Rp. <?php echo number_format($subhb,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Total SubHJ Rp. <?php echo number_format($subhj,0,',','.'); ?>	
					
				</td>
			</tr>			
		</table>	
</div>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script>liveReqInit('livesearch','liveRequestResults','retail-stock-ls.php?IVC_TYP=<?php echo $IvcTyp; ?>','','mainResult');</script>
</body>
</html>


