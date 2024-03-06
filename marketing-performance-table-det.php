<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";

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
				<th style="width:10%;">No. Order</th>
				<th >Jenis Print</th>
				<th style="width:10%;">Jml. Order</th>
				<th style="width:10%;">Ukuran (Panjang)</th>
				<th style="width:10%;">Ukuran (Lebar)</th>				
				<th style="width:10%;">Total Qty (Meter)</th>				
				<th style="width:10%;">Total Qty (Lembar)</th>				
				<th style="width:10%;">Total Sub. (Meter)</th>				
				<th style="width:10%;">Total Sub. (Lembar)</th>				
			</tr>
		</thead>
		<tbody>
			<?php
				$query = "SELECT ORD_NBR, 
								 PRN_DIG_DESC,
								 COALESCE(ORD_Q,0) AS ORD_Q,
								 COALESCE(PRN_LEN,0) AS PRN_LEN,
								 COALESCE(PRN_WID,0) AS PRN_WID,
								 SUM(CASE WHEN RWD.PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
								 THEN QTY_DET 
								 ELSE 0 
								 END) AS QTY_METER,
								 SUM(CASE WHEN RWD.PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN QTY_DET
								 ELSE 0
								 END) AS QTY_SHEETS,
								 SUM(CASE WHEN RWD.PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_METER,
								 SUM(CASE WHEN RWD.PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085')
								 THEN TOT_SUB
								 ELSE 0
								 END) AS REV_SHEETS
							FROM $CDW.PAY_RWD_DET RWD 
							LEFT JOIN $CMP.PRN_DIG_TYP TYP ON TYP.PRN_DIG_TYP = RWD.PRN_DIG_TYP
							WHERE RWD.PAY_CONFIG_NBR =".$PayConfigNbr." 
								  AND RWD.PRSN_NBR = ".$PrsnNbr." 
								  AND BUY_CO_NBR= ".$buyCoNbr." 
								  ".$where."
							GROUP BY RWD.ORD_DET_NBR";
				//echo "<pre>".$query."<br><br>";
				$result = mysql_query($query, $cloud);

				while ($row= mysql_fetch_array($result)) {
					echo "<tr $alt>";
					echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
					echo "<td>".$row['PRN_DIG_DESC']."</td>";
					echo "<td style='text-align:right;'>".number_format($row['ORD_Q'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['PRN_LEN'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['PRN_WID'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['QTY_METER'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['QTY_SHEETS'],3,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['REV_METER'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['REV_SHEETS'],0,',','.')."</td>";
					echo "</tr>";

					$OrdQ     += $row['ORD_Q'];
					$prnLen   += $row['PRN_LEN'];
					$prnWid   += $row['PRN_WID'];
					$qtyMeter += $row['QTY_METER'];
					$qtyLembar+= $row['QTY_SHEETS'];
					$revMeter += $row['REV_METER'];
					$revSheets+= $row['REV_SHEETS'];
				}

				echo "<tr>";
				echo "<td colspan=2 style='background-color: #fff;border-top: 1px solid #cacbcf;'><b>Total</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($OrdQ,0,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($prnLen,3,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($prnWid,3,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($qtyMeter,3,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($qtyLembar,3,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($revMeter,0,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($revSheets,0,',','.')."</td>";
				echo "</tr>";
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
<script>
	$(document).ready(function () {
		$("#mainTable").tablesorter({widgets: ["zebra"]});
	});
</script>
</body>
</html>