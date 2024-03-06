<?php
	error_reporting(0);
	@ini_set('display_errors', 0);
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/functions/dotmatrix.php";
	$FileNm=basename(__FILE__, '');
	
	$endDate	= $_GET['REG_NBR'];
	$posID		= $_GET['POS_ID'];
	
	if (empty($endDate)) {
		$endDate = date("Y-m-d");
		$_GET['REG_NBR'] = date("Y-m-d");
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	 <script>
	window.addEvent('domready',function() {
		$('FLTR_DTE').addEvent('click',function() {
			var endDate = $("REG_NBR").get("value");

			location.href = "?POS_ID=" + document.getElementById('POS_ID').value + "&REG_NBR=" + endDate;
		});
	});
	</script>
</head>

<body>

<div class="toolbar">
	<div class="toolbar-text">
	<p class="toolbar-left">
		<div style="display: inline-block; float: left; margin-top: 5px; margin-right: 15px;">No Pos
			<select name="POS_ID" id="POS_ID" style='width:100px' class="chosen-select" >
			<?php
				$query = "SELECT
					POS_ID
				FROM RTL.CSH_REG_IP
				WHERE POS_ID > 0 ORDER BY POS_ID ASC";
				genCombo($query, "POS_ID", "POS_ID", $posID, "Semua");
			?>
			</select>
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 5px; margin-right: 0px;">
			<input id="REG_NBR" name="REG_NBR" value="<?php echo $_GET['REG_NBR'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('REG_NBR', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px; cursor:pointer"></span>
		</div>
	</p>
	</div>
	<div class="combobox"></div>
</div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable mainTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th class="sorter-shortDate dateFormat-ddmmyyyy">Tanggal</th>
				<th class="sorter-countdown">Waktu</th>
				<th style="align:right">Jenis</th>
				<th>Jumlah</th>
				<th>Bayar</th>
				<th>Kembali</th>
				<th style="border-right:0px;">Kasir</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if($_GET['REG_NBR']==''){
			$CrtTs=date('Y-m-d');
		}else{
			$CrtTs=$_GET['REG_NBR'];
		}
		
		if($_GET['POS_ID']!=''){
			$posId=$_GET['POS_ID'];
		}
		
		if($posId == ""){
			$queryPos = "SELECT
				REG.POS_ID
			FROM RTL.CSH_REG_IP REG
			INNER JOIN RTL.CSH_REG CSH ON REG.POS_ID = CSH.POS_ID
			WHERE REG.POS_ID > 0 AND DATE(CRT_TS) = '". $CrtTs ."'
			GROUP BY REG.POS_ID
			ORDER BY REG.POS_ID ASC";
			$resultPos	= mysql_query($queryPos);
			while ($rowPos = mysql_fetch_array($resultPos)) {
				echo "<tr><td colspan='8' style='text-align:center;color:#3464C7;font-weight:bold;background-color:#F0F0F0;'>Cashier ".$rowPos['POS_ID']."</td></tr>";
				
				$query="SELECT 
					TRSC_NBR,
					REG.CSH_FLO_TYP AS TYP,
					REG.RTL_BRC,
					CASE WHEN REG.CSH_FLO_TYP IN ('RA','DE','DR','EX') THEN CSH_FLO_DESC 
					WHEN REG.CSH_FLO_TYP IN ('IV') THEN CONCAT(CSH_FLO_DESC,' Nota No. ',REG.RTL_BRC)
					ELSE 'Transaksi' END AS CSH_FLO_TYP,
					SUM(CASE WHEN REG.CSH_FLO_TYP NOT IN ('PA','CH','TL') THEN CSH_FLO_MULT*TND_AMT ELSE 0 END) AS TND_AMT,
					SUM(CASE WHEN REG.CSH_FLO_TYP IN ('RT') THEN ((DISC_PCT/100)*(TND_AMT)) ELSE 0 END) AS DISC, 
					SUM(CASE WHEN REG.CSH_FLO_TYP IN ('RT') THEN REG.DISC_AMT ELSE 0 END) AS DISC_AMT, 
					SUM(CASE WHEN REG.CSH_FLO_TYP='PA' AND REG.PYMT_TYP NOT IN ('CRT','DEB','VCR','TRF','QRS') THEN TND_AMT ELSE 0 END) AS PYMT,
					SUM(CASE WHEN REG.CSH_FLO_TYP='CH' THEN TND_AMT ELSE 0 END) AS CHG,
					SUM(CASE WHEN REG.PYMT_TYP='VCR' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS VCR, 
					SUM(CASE WHEN REG.PYMT_TYP='CRT' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS CRT, 
					SUM(CASE WHEN REG.PYMT_TYP='DEB' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS DEB, 
					SUM(CASE WHEN REG.PYMT_TYP='TRF' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS TRF,
					SUM(CASE WHEN REG.PYMT_TYP='QRS' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS QRS,
					MAX(CRT_TS) AS CRT_TS,NAME 
				FROM RTL.CSH_REG REG 
					INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
					INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=REG.CRT_NBR
				WHERE REG.ACT_F=0 AND REG.POS_ID= '" . $rowPos['POS_ID'] . "' AND DATE(CRT_TS) = '".$CrtTs."'
				GROUP BY TRSC_NBR,CRT_NBR,CASE WHEN REG.CSH_FLO_TYP IN ('RA','DE','DR','EX') THEN REG.CSH_FLO_TYP ELSE 'TR' END ORDER BY TRSC_NBR";
				$result=mysql_query($query);
				$alt			= "";
				$cashInDrawer	= 0;
				$creditCard		= 0;
				$debitCard		= 0;
				$transfer		= 0;
				$qris			= 0;
				$voucher		= 0;
				$disc			= 0;
				while($row=mysql_fetch_array($result)){
					$queryPayment  = "SELECT
						CSH.PYMT_TYP, 
						COALESCE(PYT.PYMT_DESC, 'Unknown') AS PYMT_DESC
					FROM RTL.CSH_REG CSH
						LEFT JOIN RTL.PYMT_TYP PYT ON CSH.PYMT_TYP=PYT.PYMT_TYP
					WHERE CSH.CSH_FLO_TYP='PA' AND CSH.TRSC_NBR=" . $row['TRSC_NBR'] . "
					GROUP BY CSH.CSH_FLO_TYP";
					$resultPayment = mysql_query($queryPayment);
					$paymentType	= '';
					$i 				= 0;
					while ($rowPayment = mysql_fetch_array($resultPayment)) {
						if ($i > 0) {
							$paymentType .= ',';
						}
						
						$paymentType .= " ".$rowPayment['PYMT_DESC'];
						$i++;
					}
					
					if ($alt == "") {$alt = "class='alt'";} else {$alt = "";}
					echo "<tr $alt>";
					echo "<td style='font-size:10pt;font-weight:bold;color:#999999;'><b>".$row['TRSC_NBR']."</b></td>";
					echo "<td style='text-align:center'>".parseDateShort($row['CRT_TS'])."</td>";
					echo "<td style='text-align:center'>".parseHour($row['CRT_TS']).":".parseMinute($row['CRT_TS'])."</td>";
					echo "<td style='text-align:right'><b>".($row['CSH_FLO_TYP'])."</b> ".$paymentType."</td>";
					echo "<td style='text-align:right'>".number_format($row['TND_AMT'] - $row['DISC'] - $row['DISC_AMT'],0,'.',',')."</td>";
					
					if($row['TYP'] == 'IV'){
						$Pymt 	= 0;
					} else {
						$Pymt 	= $row['PYMT'];
					}
					
					echo "<td style='text-align:right'>".number_format($Pymt,0,'.',',')."</td>";
					echo "<td style='text-align:right'>".number_format($row['CHG'],0,'.',',')."</td>";
					echo "<td>".$row['NAME']."</td>";
					echo "</tr>";
					$cashInDrawer	+= $row['TND_AMT'];
					$creditCard		+= $row['CRT'];
					$debitCard		+= $row['DEB'];
					$transfer		+= $row['TRF'];
					$qris			+= $row['QRS'];
					$voucher		+= $row['VCR'];
					$disc 			+= $row['DISC'] + $row['DISC_AMT'];
				}
				
				echo "<tr $alt>
					<td style='text-align:right;font-weight:bold' colspan=4>Total Debit Card</td>
					<td style='text-align:right'>".number_format('-'.$debitCard,0,'.',',')."</td>
					<td colspan=3></tr>";
				echo "<tr $alt>
					<td style='text-align:right;font-weight:bold' colspan=4>Total Credit Card</td>
					<td style='text-align:right'>".number_format('-'.$creditCard,0,'.',',')."</td>
					<td colspan=3></tr>";
				echo "<tr $alt>
					<td style='text-align:right;font-weight:bold' colspan=4>Total Voucher</td>
					<td style='text-align:right'>".number_format('-'.$voucher,0,'.',',')."</td>
					<td colspan=3></tr>";
				echo "<tr $alt>
					<td style='text-align:right;font-weight:bold' colspan=4>Total Transfer Bank</td>
					<td style='text-align:right'>".number_format('-'.$transfer,0,'.',',')."</td>
					<td colspan=3></tr>";
				/*
				echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Total Qris</td>
				<td style='text-align:right'>".number_format('-'.$qris,0,'.',',')."</td>
				<td colspan=3></tr>";
				*/
				echo "<tr $alt>
					<td style='text-align:right;font-weight:bold' colspan=4>Uang di laci</td>
					<td style='text-align:right'>".number_format($cashInDrawer-$debitCard-$creditCard-$voucher-$transfer-$disc,0,'.',',')."</td>
					<td colspan=3></tr>";
			}
		}else{
			$query="SELECT 
				TRSC_NBR,
				REG.CSH_FLO_TYP AS TYP,
				REG.RTL_BRC,
				CASE WHEN REG.CSH_FLO_TYP IN ('RA','DE','DR','EX') THEN CSH_FLO_DESC 
				WHEN REG.CSH_FLO_TYP IN ('IV') THEN CONCAT(CSH_FLO_DESC,' Nota No. ',REG.RTL_BRC)
				ELSE 'Transaksi' END AS CSH_FLO_TYP,
				SUM(CASE WHEN REG.CSH_FLO_TYP NOT IN ('PA','CH','TL') THEN CSH_FLO_MULT*TND_AMT ELSE 0 END) AS TND_AMT,
				
				SUM(CASE WHEN REG.CSH_FLO_TYP IN ('RT') THEN ((DISC_PCT/100)*(TND_AMT)) ELSE 0 END) AS DISC, 
				SUM(CASE WHEN REG.CSH_FLO_TYP IN ('RT') THEN REG.DISC_AMT ELSE 0 END) AS DISC_AMT, 
				
				SUM(CASE WHEN REG.CSH_FLO_TYP='PA' AND REG.PYMT_TYP NOT IN ('CRT','DEB','VCR','TRF','QRS') THEN TND_AMT ELSE 0 END) AS PYMT,
				SUM(CASE WHEN REG.CSH_FLO_TYP='CH' THEN TND_AMT ELSE 0 END) AS CHG,
				SUM(CASE WHEN REG.PYMT_TYP='VCR' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS VCR, 
				SUM(CASE WHEN REG.PYMT_TYP='CRT' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS CRT, 
				SUM(CASE WHEN REG.PYMT_TYP='DEB' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS DEB, 
				SUM(CASE WHEN REG.PYMT_TYP='TRF' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS TRF, 
				SUM(CASE WHEN REG.PYMT_TYP='QRS' AND REG.CSH_FLO_TYP='PA' THEN TND_AMT ELSE 0 END) AS QRS, 
				MAX(CRT_TS) AS CRT_TS,NAME 
			FROM RTL.CSH_REG REG 
				INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
				INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=REG.CRT_NBR
			WHERE REG.ACT_F=0 AND REG.POS_ID= '" . $posId . "' AND DATE(CRT_TS) = '".$CrtTs."'
			GROUP BY TRSC_NBR,CRT_NBR,CASE WHEN REG.CSH_FLO_TYP IN ('RA','DE','DR','EX') THEN REG.CSH_FLO_TYP ELSE 'TR' END ORDER BY TRSC_NBR";
			//echo "<pre>".$query;
			$result=mysql_query($query);
			$alt			= "";
			$cashInDrawer	= 0;
			$creditCard		= 0;
			$debitCard		= 0;
			$transfer		= 0;
			$qris			= 0;
			$voucher		= 0;
			$disc			= 0;
			while($row=mysql_fetch_array($result)){
				$queryPayment  = "SELECT
					CSH.PYMT_TYP, 
					COALESCE(PYT.PYMT_DESC, 'Unknown') AS PYMT_DESC
				FROM RTL.CSH_REG CSH
					LEFT JOIN RTL.PYMT_TYP PYT ON CSH.PYMT_TYP=PYT.PYMT_TYP
				WHERE CSH.CSH_FLO_TYP='PA' AND CSH.TRSC_NBR=" . $row['TRSC_NBR'] . "
				GROUP BY CSH.CSH_FLO_TYP";
				$resultPayment = mysql_query($queryPayment);
				$paymentType	= '';
				$i 				= 0;
				while ($rowPayment = mysql_fetch_array($resultPayment)) {
					if ($i > 0) {
						$paymentType .= ',';
					}
					
					$paymentType .= " ".$rowPayment['PYMT_DESC'];
					$i++;
				}
				
				$query="SELECT 
					REG_NBR,
					TRSC_NBR,
					REG.CO_NBR,
					REG.RTL_BRC,
					REG.RTL_Q,
					REG.RTL_PRC,
					INV.NAME AS NAME_DESC,
					TND_AMT,
					DISC_PCT,
					DISC_AMT,
					CSH_FLO_DESC,
					REG.CSH_FLO_TYP,
					TYP.CSH_FLO_MULT,
					PYMT_DESC,
					REG.PYMT_TYP,
					ACT_F
				FROM RTL.CSH_REG REG 
					INNER JOIN CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR
					LEFT JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP 
					LEFT OUTER JOIN RTL.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP
					LEFT OUTER JOIN RTL.INVENTORY INV ON REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0 
					LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD HED ON REG.RTL_BRC=HED.ORD_NBR 
				WHERE REG.ACT_F=0 AND REG.POS_ID = " . $posId . "
					AND TRSC_NBR = ".$row['TRSC_NBR']." 
					AND REG.CSH_FLO_TYP NOT IN ('RA','DE','DR','EX','PA','CH','IV')
					AND (REG.RTL_BRC!='' OR HED.ORD_NBR!='')
					ORDER BY CSH_FLO_PART,REG_NBR";
				$resultd=mysql_query($query);
				$rows=mysql_num_rows($resultd)+1;
				//echo "<pre>".$query;
				if ($alt == "") {$alt = "class='alt'";} else {$alt = "";}
				echo "<tr $alt>";
				echo "<td rowspan='$rows' style='text-align:right;vertical-align:top;font-size:10pt;font-weight:bold;color:#999999;background-color:#ffffff;'><b>".$row['TRSC_NBR']."</b></td>";
				echo "<td style='text-align:center'>".parseDateShort($row['CRT_TS'])."</td>";
				echo "<td style='text-align:center'>".parseHour($row['CRT_TS']).":".parseMinute($row['CRT_TS'])."</td>";
				echo "<td style='text-align:right'><b>".($row['CSH_FLO_TYP'])."</b> ".$paymentType."</td>";
				echo "<td style='text-align:right'>".number_format($row['TND_AMT'] - $row['DISC'] - $row['DISC_AMT'],0,'.',',')."</td>";
				
				if($row['TYP'] == 'IV'){
					$Pymt 	= 0;
				} else {
					$Pymt 	= $row['PYMT'];
				}
					
				echo "<td style='text-align:right'>".number_format($Pymt,0,'.',',')."</td>";
				echo "<td style='text-align:right'>".number_format($row['CHG'],0,'.',',')."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "</tr>";
				$cashInDrawer	+= $row['TND_AMT'];
				$creditCard		+= $row['CRT'];
				$debitCard		+= $row['DEB'];
				$transfer		+= $row['TRF'];
				$qris			+= $row['QRS'];
				$voucher		+= $row['VCR'];
				$disc 			+= $row['DISC'] + $row['DISC_AMT'];
				//echo $query;
				while($rowd=mysql_fetch_array($resultd)){
					$discDescription 	= "";
					$discItem 			= "";
					
					if ($rowd['DISC_PCT'] >= 1) {
						$discDescription 	= " (<i>Disc " . $rowd['DISC_PCT'] . "%</i>)";
						$discItem 			= ($rowd['CSH_FLO_MULT'] * $rowd['TND_AMT']) * ($rowd['DISC_PCT'] / 100);
					} elseif ($rowd['DISC_AMT'] >= 1) {
						$discDescription 	= " (<i>Disc Rp " . number_format($rowd['DISC_AMT'], 0, ",", ".") . "</i>)";
						$discItem 			= $rowd['DISC_AMT'];
					}
					
					if ($alt == "") {$alt = "class='alt'";} else {$alt = "";}
					echo "<tr $alt>";
					if($rowd['RTL_BRC']!=""){
						if($rowd['CSH_FLO_TYP']=="DP"){
						echo "<td style='text-align:right' colspan=3> Uang Muka Nota ".$rowd['RTL_BRC']."  Rp. ".number_format($rowd['RTL_PRC'],0,".",",")."</td>";
						}else if($rowd['CSH_FLO_TYP']=="FL"){
						echo "<td style='text-align:right' colspan=3> Pembayaran Nota ".$rowd['RTL_BRC']." Rp. ".number_format($rowd['RTL_PRC'],0,".",",")."</td>";
						}else if($rowd['CSH_FLO_TYP']=="CB"){
						echo "<td style='text-align:right' colspan=3> Pembayaran Creative Hub Nota ".$rowd['RTL_BRC']." Rp. ".number_format($rowd['RTL_PRC'],0,".",",")."</td>";
						}else{
						echo "<td style='text-align:right' colspan=3>".$rowd['RTL_BRC']." ".$rowd['NAME_DESC']." ".$rowd['RTL_Q']." x @ Rp. ".number_format($rowd['RTL_PRC'],0,".",",")." ".$discDescription."</td>";
						}
					}else if($rowd['CSH_FLO_TYP']=="DS"){
						echo "<td style='text-align:right' colspan=3>".$rowd['CSH_FLO_DESC']."</td>";
					}else{
						echo "<td style='text-align:right' colspan=3>".$rowd['CSH_FLO_DESC']." ".$rowd['PYMT_DESC']." Nota ".leadZero($rowd['ORD_NBR'],6)."</td>";
					}
					echo "<td style='text-align:right'>".number_format($rowd['CSH_FLO_MULT'] * $rowd['TND_AMT'] - $discItem,0,".",",")."</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "</tr>";
				}
			}
			
			echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Total Debit Card</td>
				<td style='text-align:right'>".number_format('-'.$debitCard,0,'.',',')."</td>
				<td colspan=3></tr>";
			echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Total Credit Card</td>
				<td style='text-align:right'>".number_format('-'.$creditCard,0,'.',',')."</td>
				<td colspan=3></tr>";
			echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Total Voucher</td>
				<td style='text-align:right'>".number_format('-'.$voucher,0,'.',',')."</td>
				<td colspan=3></tr>";
			echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Total Transfer Bank</td>
				<td style='text-align:right'>".number_format('-'.$transfer,0,'.',',')."</td>
				<td colspan=3></tr>";
				/*
				echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Total Qris</td>
				<td style='text-align:right'>".number_format('-'.$qris,0,'.',',')."</td>
				<td colspan=3></tr>";
				*/
			echo "<tr $alt>
				<td style='text-align:right;font-weight:bold' colspan=4>Uang di laci</td>
				<td style='text-align:right'>".number_format($cashInDrawer-$debitCard-$creditCard-$voucher-$transfer-$disc,0,'.',',')."</td>
				<td colspan=3></tr>";
		}
		?>
	</tbody>
	</table>
</div>
<script>
	$(document).ready(function(){
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
</body>
</html>
