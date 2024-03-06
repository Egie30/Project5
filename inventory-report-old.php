<?php
	include "framework/database/connect.php";
	$CoNbr=$CoNbrDef;
	$BegDt=$_GET['BEG_DT'];
	$EndDt=$_GET['END_DT'];
	$SplNbr=$_GET['SPLNBR'];
	$SCatNbr=$_GET['SCATNBR'];
	if($BegDt==""){
		$BegDt=date("Y-m-01");
	}
	if($EndDt==""){
		$EndDt=date("Y-m-d");
	}
	if ($SplNbr!='') {$SplNbr=' WHERE COM.CO_NBR='.$SplNbr.' AND ';}else{if ($SCatNbr!=''){$SplNbr=' WHERE ';}}
	if ($SCatNbr!='') {$SCatNbr=' SUB.CAT_SUB_NBR='.$SCatNbr;}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>

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
		<span class="fa fa-calendar toolbar fa-lg" style="padding-left:0px;cursor:pointer" onclick="location.href='inventory-report.php?CO_NBR=<?php echo $CoNbr; if($_GET['RPT_TP']!=''){echo '&RPT_TP='.$_GET['RPT_TP'];}?>&BEG_DT='+document.getElementById('BEG_DT').value+'&END_DT='+document.getElementById('END_DT').value"></span>
	</p>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
<?php 
if($_GET['RPT_TP']=='SPL'){
mysql_select_db("rtl");
	$CoNbr=$_GET['CO_NBR'];	
	$BegDt=$_GET['BEG_DT'];
	$EndDt=$_GET['END_DT'];
	if($BegDt==""){
		$BegDt=date("Y-m-01");
	}
	if($EndDt==""){
		$EndDt=date("Y-m-d");
	}

	$where=$where." AND (CSH.CRT_TS BETWEEN '$BegDt' AND '$EndDt' ) GROUP BY INV.CO_NBR ";
?>		
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;" width='50px'>No.</th>
				<th style="text-align:right;" width='100px'>No. Suplier</th>
				<th style="text-align:left;">Suplier</th>
				<th style="text-align:right;">Item</th>
				<th style="text-align:right;">Jumlah</th>
			</tr>
		</thead>
		<tbody>
		<?php

			$query="SELECT INV.INV_NBR,
						INV.NAME,
						CAT_DESC,
						COM.CO_NBR AS CO_NBR,
						COM.NAME AS CO_NAME,
						CAT_SUB_DESC,
						INV_BCD,
						CAT_SHLF_DESC,
						RCV.ORD_Q AS RCV_ORD,
						COALESCE(SHP.ORD_Q,0) AS SHP_ORD,
						SUM(CSH.RTL_Q) RTL_Q,
						SUM(CSH.RTL_Q*PRC) SUBTOT,
						COALESCE(PRV.RTL_Q,0) AS PRV_Q,
						RCV.NAME AS RCV_NAME,
						INV_PRC,
						PRC,
						CSH.CRT_TS CSH_DT
					FROM INVENTORY INV 
						LEFT OUTER JOIN	RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
						LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
						LEFT OUTER JOIN	RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
						LEFT OUTER JOIN	CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR INNER JOIN
						(
						SELECT SUM(ORD_Q) AS ORD_Q,
							INV_NBR,
							NAME,
							RCV_CO_NBR
						FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
						WHERE HED.RCV_CO_NBR=$CoNbr
						GROUP BY INV_NBR,NAME
						) 
						RCV ON RCV.INV_NBR=INV.INV_NBR LEFT OUTER JOIN
						(
						SELECT SUM(ORD_Q) AS ORD_Q,
							INV_NBR,
							NAME,
							SHP_CO_NBR
						FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
						WHERE SHP_CO_NBR=$CoNbr
						GROUP BY INV_NBR,NAME
						) 
						SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR INNER JOIN
						(
						SELECT RTL_BRC,
							SUM(RTL_Q) AS RTL_Q,
							CO_NBR,
							CRT_TS 
						FROM RTL.CSH_REG 
						WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr AND DATE(CRT_TS) BETWEEN '$BegDt' AND '$EndDt'
						GROUP BY RTL_BRC,CO_NBR
						) 
						CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR LEFT OUTER JOIN
						(
						SELECT RTL_BRC,
							SUM(RTL_Q) AS RTL_Q,
							CO_NBR FROM RTL.CSH_REG 
						WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr AND DATE(CRT_TS)<'$BegDt'
						GROUP BY RTL_BRC,CO_NBR
						) PRV ON INV.INV_BCD=PRV.RTL_BRC AND PRV.CO_NBR=RCV.RCV_CO_NBR $where ";
			// echo $query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				$i=1;
				while($row=mysql_fetch_array($result))
				{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-report.php?RPT_TP=SCT&IVC_TYP=RC&BEG_DT=".$BegDt."&END_DT=".$EndDt."&CO_NBR=1&SHP_NBR=".$row['CO_NBR']."';".chr(34).">";
				echo "<td style='text-align:right'>".$i."</td>";
				echo "<td style='text-align:right'>".$row['CO_NBR']."</td>";
				echo "<td style='text-align:left;'>".$row['CO_NAME']."</td>";
				echo "<td style='text-align:right'>".number_format($row['RTL_Q'],0,',','.')."</td>";
				$subTotal=$row['PRC']*$row['RTL_Q'];
				echo "<td class='std' style='text-align:right;'>".number_format($row['SUBTOT'],0,',','.')."</td>";	
				echo "</tr>";
				$item=$item+$row['RTL_Q'];
				$jumlah+=$row['SUBTOT'];
				$i++;
			}

		?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Item  <?php echo number_format($item,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;					
					Total Jumlah Rp. <?php echo number_format($jumlah,0,',','.'); ?>
					
				</td>
			</tr>			
		</table>		
<?php 
}else if($_GET['RPT_TP']=='SCT'){
mysql_select_db("rtl");
	$CoNbr=$_GET['CO_NBR'];	
	$ShpNbr=$_GET['SHP_NBR'];	
	$BegDt=$_GET['BEG_DT'];
	$EndDt=$_GET['END_DT'];
	if($BegDt==""){
		$BegDt=date("Y-m-01");
	}
	if($EndDt==""){
		$EndDt=date("Y-m-d");
	}
	if($ShpNbr!=''){$SplNbr='&SPLNBR='.$ShpNbr;$ShpNbr=' WHERE COM.CO_NBR='.$ShpNbr;}
	$where=$where." AND (CSH.CRT_TS BETWEEN '$BegDt' AND '$EndDt' ) $ShpNbr GROUP BY INV.CAT_SUB_NBR ";
?>		
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;" width='50px'>No.</th>
				<th style="text-align:right;" width='100px'>Sub Kategori</th>
				<th style="text-align:left;">Kategori</th>
				<th style="text-align:right;">Item</th>
				<th style="text-align:right;">Jumlah</th>
			</tr>
		</thead>
		<tbody>
		<?php

			$query="SELECT INV.INV_NBR,
						INV.NAME,
						CAT_DESC,
						COM.CO_NBR AS CO_NBR,
						COM.NAME AS CO_NAME,
						CAT_SUB_DESC,
						INV_BCD,
						CAT_SHLF_DESC,
						RCV.ORD_Q AS RCV_ORD,
						COALESCE(SHP.ORD_Q,0) AS SHP_ORD,
						SUM(CSH.RTL_Q) RTL_Q,
						SUM(CSH.RTL_Q*PRC) SUBTOT,
						COALESCE(PRV.RTL_Q,0) AS PRV_Q,
						RCV.NAME AS RCV_NAME,
						INV_PRC,
						PRC,
						CSH.CRT_TS CSH_DT, 
						SUB.CAT_SUB_DESC SUBCAT,
						SUB.CAT_SUB_NBR CATSUBNBR
					FROM INVENTORY INV 
						LEFT OUTER JOIN	RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
						LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
						LEFT OUTER JOIN	RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
						LEFT OUTER JOIN	CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR 
					INNER JOIN
					(
					SELECT SUM(ORD_Q) AS ORD_Q,
						INV_NBR,
						NAME,
						RCV_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
					WHERE HED.RCV_CO_NBR=$CoNbr
					GROUP BY INV_NBR,NAME
					) 
					RCV ON RCV.INV_NBR=INV.INV_NBR LEFT OUTER JOIN
					(
					SELECT SUM(ORD_Q) AS ORD_Q,
						INV_NBR,
						NAME,
						SHP_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
					WHERE SHP_CO_NBR=$CoNbr
					GROUP BY INV_NBR,NAME
					) 
					SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR INNER JOIN
					(
					SELECT RTL_BRC,
						SUM(RTL_Q) AS RTL_Q,
						CO_NBR,
						CRT_TS 
					FROM RTL.CSH_REG 
					WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr AND DATE(CRT_TS) BETWEEN '$BegDt' AND '$EndDt'
					GROUP BY RTL_BRC,CO_NBR
					) 
					CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR LEFT OUTER JOIN
					(
					SELECT RTL_BRC,
						SUM(RTL_Q) AS RTL_Q,
						CO_NBR FROM RTL.CSH_REG 
					WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr AND DATE(CRT_TS)<'$BegDt'
					GROUP BY RTL_BRC,CO_NBR
					) 
					PRV ON INV.INV_BCD=PRV.RTL_BRC AND PRV.CO_NBR=RCV.RCV_CO_NBR $where";
				// echo $query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				$i=1;
				while($row=mysql_fetch_array($result))
				{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-report.php?CO_NBR=1&SCATNBR=".$row['CATSUBNBR']."&BEG_DT=".$BegDt."&END_DT=".$EndDt.$SplNbr."';".chr(34).">";
				echo "<td style='text-align:right'>".$i."</td>";
				echo "<td style='text-align:right'>".$row['SUBCAT']."</td>";
				echo "<td style='text-align:left;'>".$row['CAT_DESC']."</td>";
				echo "<td style='text-align:right'>".number_format($row['RTL_Q'],0,',','.')."</td>";
				$subTotal=$row['PRC']*$row['RTL_Q'];
				echo "<td class='std' style='text-align:right;'>".number_format($row['SUBTOT'],0,',','.')."</td>";	
				echo "</tr>";
				$item=$item+$row['RTL_Q'];
				$jumlah+=$row['SUBTOT'];
				$i++;
			}
		?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Item  <?php echo number_format($item,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;					
					Total Jumlah Rp. <?php echo number_format($jumlah,0,',','.'); ?>
					
				</td>
			</tr>			
		</table>	


<?php 
}else{
?>		
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">No.</th>
				<th>Kategory</th>
				<th>Sub Kategory</th>
				<th>Nama</th>
				<th>Supplier</th>
				<th>Barcode</th>
				<th>Jual</th>
				<th>Disc</th>
				<th>Stock</th>
				<th>Faktur</th>
				<th>Jual</th>
				<th>Subtotal</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$query="SELECT INV.INV_NBR,
						INV.NAME,
						CAT_DESC,
						COM.NAME AS CO_NAME,
						CAT_SUB_DESC,
						INV_BCD,
						CAT_SHLF_DESC,
						RCV.ORD_Q AS RCV_ORD,
						COALESCE(SHP.ORD_Q,0) AS SHP_ORD,
						CSH.RTL_Q,
						COALESCE(PRV.RTL_Q,0) AS PRV_Q,
						RCV.NAME AS RCV_NAME,
						INV_PRC,PRC
					FROM RTL.INVENTORY INV 
						LEFT OUTER JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
						LEFT OUTER JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
						LEFT OUTER JOIN RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR 
					INNER JOIN
					(
					SELECT SUM(ORD_Q) AS ORD_Q,
						INV_NBR,
						NAME,
						RCV_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
					WHERE HED.RCV_CO_NBR=$CoNbr
					GROUP BY INV_NBR,NAME
					) 
					RCV ON RCV.INV_NBR=INV.INV_NBR LEFT OUTER JOIN
					(
					SELECT SUM(ORD_Q) AS ORD_Q,
						INV_NBR,
						NAME,
						SHP_CO_NBR
					FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
						LEFT OUTER JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
					WHERE SHP_CO_NBR=$CoNbr
					GROUP BY INV_NBR,NAME
					) 
					SHP ON SHP.INV_NBR=INV.INV_NBR AND SHP.SHP_CO_NBR=RCV.RCV_CO_NBR INNER JOIN
					(
					SELECT RTL_BRC,
						SUM(RTL_Q) AS RTL_Q,
						CO_NBR 
					FROM RTL.CSH_REG 
					WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr AND DATE(CRT_TS) BETWEEN '$BegDt' AND '$EndDt'
					GROUP BY RTL_BRC,CO_NBR
					) 
					CSH ON INV.INV_BCD=CSH.RTL_BRC AND CSH.CO_NBR=RCV.RCV_CO_NBR LEFT OUTER JOIN
					(
					SELECT RTL_BRC,
						SUM(RTL_Q) AS RTL_Q,
						CO_NBR 
					FROM RTL.CSH_REG WHERE RTL_BRC!='' AND CSH_FLO_TYP='RT' AND CO_NBR=$CoNbr AND DATE(CRT_TS)<'$BegDt'
					GROUP BY RTL_BRC,CO_NBR
					) 
					PRV ON INV.INV_BCD=PRV.RTL_BRC AND PRV.CO_NBR=RCV.RCV_CO_NBR 
				$SplNbr $SCatNbr";
				// echo "<pre>".$query;
				$result=mysql_query($query);
				$rowcol="a";
				$alt="";
				while($row=mysql_fetch_array($result))
				{
					echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='inventory-list-edit.php?INV_NBR=".$row['INV_NBR']."';".chr(34).">";
					echo "<td class='std-first' align=right>".$row['INV_NBR']."</td>";
					echo "<td class='std'>".$row['CAT_DESC']."</td>";
					echo "<td class='std'>".$row['CAT_SUB_DESC']."</td>";
					echo "<td class='std'>".$row['NAME']."</td>";
					echo "<td class='std'>".$row['CO_NAME']."</td>";
					echo "<td class='std'>".$row['INV_BCD']."</td>";
					$balance=$row['RCV_ORD']-$row['SHP_ORD']-$row['RTL_Q']-$row['PRV_Q'];
					echo "<td class='std' style='text-align:right;'>".number_format($row['RTL_Q'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['DISC'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($balance,0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					echo "<td class='std' style='text-align:right;'>".number_format($row['PRC'],0,',','.')."</td>";	
					$subTotal=$row['PRC']*$row['RTL_Q'];
					echo "<td class='std' style='text-align:right;'>".number_format($subTotal,0,',','.')."</td>";	
					echo "</tr>";
					if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
					$subTotal=$row['PRC']*$row['RTL_Q'];
					$sub+=$subTotal;
					$item+=$row['RTL_Q'];
					$hb+=$row['INV_PRC']*$row['RTL_Q'];
					$hj+=$row['PRC']*$row['RTL_Q'];					
					$tot+=$subTotal;					
				}		
			?>
		</tbody>
	</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Item <?php echo number_format($item,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Total Rp. <?php echo number_format($sub,0,'.','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Total Hb Rp. <?php echo number_format($hb,0,'.','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
					Total Hj Rp. <?php echo number_format($hj,0,'.','.'); ?>				
				</td>
			</tr>			
		</table>
<?php }
?>

		
		
</div>

<?php
	if($_GET['WHSE']!=""){$whse="?WHSE=".$_GET['WHSE'];}else{$whse="";}
?>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script>
	$.noConflict();
	jQuery(document).ready(function($)
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script>liveReqInit('livesearch','liveRequestResults','inventory-report-ls.php<?php echo $whse; ?>','','mainResult');</script>
</body>
</html>