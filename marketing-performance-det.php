<?php
	include "framework/database/connect.php";
	include "framework/database/connect-.php";
	include "framework/functions/default.php";
	include "framework/functions/crypt.php";

	$PayConfigNbr = $_GET['PAY_CONFIG_NBR'];
	$PrsnNbr      = $_GET['PRSN_NBR'];
	$buyCoNbr  	  = $_GET['BUY_CO_NBR'];
	$Typ 		  = $_GET['TYP']; 
	$OwnCoDesc 	  = $_GET['OWN_CO_DESC'];
	
	if($OwnCoDesc == 'YES') {
		$where = "AND RWD.OWN_CO_NBR = ".$CoNbrDef." ";
	} else {
		$where = "AND RWD.OWN_CO_NBR != ".$CoNbrDef." ";
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<style>
	table.tablesorter thead tr .headerTd{
		border-bottom:1px solid #cacbcf;
	}
</style>
</head>

<body>
	<div class="toolbar">
		<div class="combobox"></div>
		
		<div class="toolbar-text">
			<p class="toolbar-left">
				<div class='listable' style="padding-bottom: 4px;cursor: pointer;"><span style="cursor: pointer;" class='fa fa-angle-double-left listable' onclick="BackData('<?php echo $PayConfigNbr;?>','<?php echo $PrsnNbr;?>','<?php echo $Typ;?>')"></span></div><span style="cursor: pointer;" onclick="BackData('<?php echo $PayConfigNbr;?>','<?php echo $PrsnNbr;?>','<?php echo $Typ;?>')">&nbsp;Back</span>
			</p>
		</div>
	</div>
	<div style="width:100%;padding-top: 10px;" id="mainResult">
	<table id="mainTable" class="tablesorter">
		<thead>
			<tr>
				<th style="width:10%;">No. Nota</th>
				<th style="width:10%;">Judul Nota</th>
				<th >Perusahaan</th>
				<th style="width:10%;">Status</th>
				<th style="width:10%;">Tgl Pesan</th>
				<th style="width:10%;">Tgl Janji</th>
				<th style="width:10%;">Total</th>
				<th style="width:10%;">Sisa</th>			
			</tr>
		</thead>
		<tbody>
		<?php
		$query="SELECT
			COUNT(HED.ORD_NBR) AS CNT_ORD_NBR,
			HED.ORD_NBR,
			DATE(HED.ORD_TS) AS ORD_DTE,
			HED.ORD_TTL,
			HED.ORD_STT_ID,
			ORD_STT_DESC,
			COM.ACCT_EXEC_NBR,
			HED.BUY_CO_NBR,
			COM.NAME AS BUY_CO_NAME,
			HED.DUE_TS,
			HED.PRN_CO_NBR,
			COM.NAME AS PRN_CO_NAME,
			HED.TOT_AMT,
			HED.TOT_REM,
			PYMT.PYMT_TYP,
			PYMT.TND_AMT,
			PYMT.BNK_CO_NBR,
			PYMT.VAL_NBR
		FROM CMP.PRN_DIG_ORD_HEAD HED
			INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
			LEFT OUTER JOIN CMP.PRN_DIG_ORD_PYMT PYMT ON HED.ORD_NBR = PYMT.ORD_NBR AND PYMT.DEL_NBR=0
			INNER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
		WHERE HED.DEL_NBR =0
			AND COM.ACCT_EXEC_NBR !=0
			AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.BUY_CO_NBR IS NULL) 
			AND MONTH(HED.ORD_TS) =  '08' AND YEAR(HED.ORD_TS) =  '2023'
		GROUP BY HED.ORD_NBR
		ORDER BY HED.ORD_NBR DESC";

		$result = mysql_query($query);
		//echo "<pre>".$query."<br><br>";
		while ($row = mysql_fetch_array($result)) { 
		?>
		<tr <?php echo $alt; ?> style='cursor:pointer;'>
			<td style="text-align:center;"><?php echo $row['ORD_NBR'];?></td>
			<td><?php echo $row['ORD_TTL'];?></td>
			<td><?php echo $row['BUY_CO_NAME'];?></td>
			<td><?php echo $row['ORD_STT_DESC'];?></td>
			<td style="text-align:center;"><?php echo $row['ORD_DTE'];?></td>
			<td style="text-align:center;"><?php echo $row['DUE_TS'];?></td>
			<td align="right"><?php echo number_format($row['TND_AMT'],0,'.',',');?></td>
			<td align="right"><?php echo number_format($row['TOT_REM'],0,'.',',');?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function(){
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
	});
</script>
<script>
	$(document).ready(function () {
		$("#mainTable").tablesorter({widgets: ["zebra"]});
	});
</script>
</body>
</html>