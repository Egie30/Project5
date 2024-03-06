<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";

	$days =$_GET['DAYS'];
	$day  =$_GET['DAY'];
	$month=$_GET['MONTH'];
	$year =$_GET['YEAR'];
	
	if($days !=""){$where="DATE(CRT_TS)=CURRENT_DATE - INTERVAL $days DAY";}
	if($day  !=""){$where="DAY(CRT_TS)=$day AND MONTH(CRT_TS)=$month and YEAR(CRT_TS)=$year";}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	
</head>
<body>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th>No.</th>
				<th>Tanggal</th>
				<th>Waktu</th>
				<th>Kasir</th>
				<th>Nota</th>			
				<!--<th>Barang</th>
				<th>Barcode</th>
				<th>Harga</th>-->		
				<th>Total</th>
				<th>Disc</th>
				<th>Tunai</th>
				<th>Net</th>
				<th>Debit</th>
				<th>Kredit</th>
				<th>Cek</th>
				<th>Transfer</th>
				<th>Voucher</th>
				<th>Kembali</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$query=mysql_query("SELECT TRSC_NBR,MAX(CRT_TS) AS CRT_TS,PPL.NAME,SUM(RTL_Q) AS RTL_Q,
							INV.NAME AS NAME_DESC,CSH.CO_NBR,CSH.RTL_BRC,CSH.RTL_PRC,
							SUM(CASE WHEN CSH_FLO_TYP='RT' THEN TND_AMT ELSE 0 END) AS TND_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='DS') AS DISC_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='PA' AND PYMT_TYP='CSH') AS CSH_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='PA' AND PYMT_TYP='DEB') AS DEB_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='PA' AND PYMT_TYP='CRT') AS CRT_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='PA' AND PYMT_TYP='CHK') AS CHK_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='VC' AND PYMT_TYP='VCR') AS VCR_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='PA' AND PYMT_TYP='TRF') AS TRF_AMT,
							(SELECT SUM(TND_AMT) FROM RTL.CSH_REG WHERE TRSC_NBR=CSH.TRSC_NBR AND CSH_FLO_TYP='CH') AS CHG_AMT 
							FROM RTL.CSH_REG CSH LEFT OUTER JOIN 
								 CMP.PEOPLE PPL ON CSH.CRT_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
								 RTL.INVENTORY INV ON CSH.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0 
							WHERE CSH_FLO_TYP NOT IN('EX','RA','EA','DE','DR') AND $where GROUP BY TRSC_NBR");					
		$alt="";
		while($row=mysql_fetch_array($query)){
			echo "<tr $alt>";
			echo "<td style='text-align:center'>".$row['TRSC_NBR']."</td>";
			echo "<td style='text-align:center'>".parseDateShort($row['CRT_TS'])."</td>";
			echo "<td style='text-align:center'>".parseHour($row['CRT_TS']).":".parseMinute($row['CRT_TS'])."</td>";
			echo "<td nowrap>".$row['NAME']."</td>";		
			echo "<td style='text-align:center'>".$row['RTL_Q']."</td>";
			//echo "<td style='text-align:center'>".$row['NAME_DESC']."</td>";	
			//echo "<td style='text-align:center'>".$row['RTL_BRC']."</td>";			
			//if($row['RTL_PRC']=="0"){$RtlPrc="";}else{$RtlPrc=number_format($row['RTL_PRC'],0,',','.');}
			//echo "<td class='std' style='text-align:right;'>".$RtlPrc."</td>";	
			if($row['TND_AMT']=="0"){$TndAmt="";}else{$TndAmt=number_format($row['TND_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$TndAmt."</td>";
			if($row['DISC_AMT']=="0"){$DiscAmt="";}else{$DiscAmt=number_format($row['DISC_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$DiscAmt."</td>";
			if($row['CSH_AMT']=="0"){$CshAmt="";}else{$CshAmt=number_format($row['CSH_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$CshAmt."</td>";			
			if($TndAmt==""){$NetAmt="";}else{$NetAmt=number_format($row['TND_AMT']-$row['DISC_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$NetAmt."</td>";

			if($row['DEB_AMT']=="0"){$DbtAmt="";}else{$DbtAmt=number_format($row['DEB_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$DbtAmt."</td>";
			if($row['CRT_AMT']=="0"){$CrtAmt="";}else{$CrtAmt=number_format($row['CRT_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$CrtAmt."</td>";
			if($row['CHK_AMT']=="0"){$ChkAmt="";}else{$ChkAmt=number_format($row['CHK_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$ChkAmt."</td>";
			if($row['TRF_AMT']=="0"){$TrfAmt="";}else{$TrfAmt=number_format($row['TRF_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$TrfAmt."</td>";
			if($row['VCR_AMT']=="0"){$VcrAmt="";}else{$VcrAmt=number_format($row['VCR_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$VcrAmt."</td>";
			if($row['CHG_AMT']=="0"){$ChgAmt="";}else{$ChgAmt=number_format($row['CHG_AMT'],0,',','.');}
			echo "<td class='std' style='text-align:right;'>".$ChgAmt."</td>";
			echo "</tr>";
		
			$revenue+=$row['TND_AMT']-$row['DISC_AMT'];
			$item+=$row['RTL_Q'];
			$jml+=$row['TND_AMT'];			
		}
			
		?>
		</tbody>
		</table>
		<table class='rowstyle-alt colstyle-alt no-arrow searchTable'>
			<tr>
				<td style='text-align:left;font-weight:bold;width:100%'>
					Total Item  <?php echo number_format($item,0,',','.'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Nett Rp. <?php echo number_format($revenue,0,'.',','); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					Total Rp. <?php echo number_format($jml,0,'.',','); ?>
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
</body>
</html>			
