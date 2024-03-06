<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/crypt.php";

	$Security 		= getSecurity($_SESSION['userID'],"AddressBook");
	$PayConfigNbr	= $_GET['PAY_CONFIG_NBR'];
	$PrsnNbr	 	= $_GET['PRSN_NBR'];
	
	if ($PayConfigNbr!=''){
		$query = "SELECT PAY_BEG_DTE, PAY_END_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
		$result= mysql_query($query, $local);
		$rowDte= mysql_fetch_array($result);
		$begDate	= $rowDte['PAY_BEG_DTE'];
		$endDate	= $rowDte['PAY_BEG_DTE'];
	}else{
		$begDate	= date("Y-m-01");
		$endDate	= date("Y-m-d");
	}
	
	if($PrsnNbr != ''){
		$personNbr = " AND HEAD.ACCT_EXEC_NBR = ".$FLR_MPR_PPL;
	}else{
		$personNbr = "";
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
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
	<h3 style='text-align:center;' >Champion Campus</h3>
	<table id="mainTable" class="tablesorter">
		<thead>
			<tr>
				<th>No</th>
				<th>Perusahaan</th>
				<th style="width:10%">Invoice</th>
				<th style="width:10%">Total</th>
				<th style="width:10%">Sisa</th>		
			</tr>
		</thead>
		<tbody>
			<?php
				$query = "SELECT
						PYMT.PYMT_NBR,
						COUNT(HED.ORD_NBR) AS CNT_ORD_NBR,
						HED.ORD_NBR,
						HED.ORD_TS,
						HED.ORD_TTL,
						HED.ORD_STT_ID,
						STT.ORD_STT_DESC,
						COM.ACCT_EXEC_NBR,
						HED.BUY_CO_NBR,
						COM.NAME AS BUY_CO_NAME,
						DATE(HED.DUE_TS)AS DUE_DTE,
						HED.PRN_CO_NBR,
						COM.NAME AS PRN_CO_NAME,
						HED.TOT_AMT,
						HED.TOT_REM,
						PYMT.PYMT_TYP,
						PYMT.TND_AMT
					FROM CMP.PRN_DIG_ORD_PYMT PYMT
						INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON PYMT.ORD_NBR = HED.ORD_NBR
						INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
						INNER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
					WHERE PYMT.DEL_NBR=0
						AND HED.DEL_NBR =0
						AND DATE(PYMT.CRT_TS) >= '".$begDate."' 
						AND DATE(PYMT.CRT_TS) <= '".$endDate."' 
						AND COM.ACCT_EXEC_NBR !=0
						".$personNbr."
					GROUP BY HED.BUY_CO_NBR
					ORDER BY COM.NAME ASC";
					echo "<pre>".$query;
				$result = mysql_query($query);

				while ($row= mysql_fetch_array($result)) {
					$OwnCoDesc = 'YES';
					echo "<tr $alt style='cursor:pointer;' onclick='detailData(".$row['BUY_CO_NBR'].",".$PayConfigNbr.",".$PrsnNbr.",\"".$Typ."\",\"".$OwnCoDesc."\")' >";
					echo "<td>".$row['BUY_CO_NBR']."</td>";
					echo "<td>".$row['BUY_CO_NAME']."</td>";
					echo "<td style='text-align:right;'>".$row['CNT_ORD_NBR']."</td>";
					echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['TOT_REM'],0,',','.')."</td>";
					echo "</tr>";

					$invoiceCampus   += $row['CNT_ORD_NBR'];
					$amountCampus   += $row['TOT_AMT'];
					$remainCampus   += $row['TOT_REM'];
				}
				echo "<tr>";
				echo "<td colspan='2' style='background-color: #fff;border-top: 1px solid #cacbcf;'><b>Total</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($invoiceCampus,0,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($amountCampus,0,',','.')."</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($remainCampus,0,',','.')."</b></td>";
				echo "</tr>";
			?>
		
		</tbody>
	</table>

	<br>
	<h3 style='text-align:center;' >Champion Printing</h3>
	<table id="mainTable2" class="tablesorter">
		<thead>
			<tr>
				<th>No</th>
				<th>Perusahaan</th>
				<th style="width:10%">Invoice</th>
				<th style="width:10%">Total</th>
				<th style="width:10%">Sisa</th>		
			</tr>
		</thead>
		<tbody>
			<?php
			$Printing = json_decode(simple_crypt(file_get_contents('http://printing.champs.asia/marketing-performance-data.php?GROUP=BUY_CO_NBR&MONTH='.$filter_month),'d'));
			//echo '<pre>'; print_r($Printing); echo '</pre>';
			foreach ($Printing->data as $Printingdata) {
					$OwnCoDesc = 'YES';
					echo "<tr $alt style='cursor:pointer;' onclick='detailData(".$row['BUY_CO_NBR'].",".$PayConfigNbr.",".$PrsnNbr.",\"".$Typ."\",\"".$OwnCoDesc."\")' >";
					echo "<td>".$Printingdata->BUY_CO_NBR."</td>";
					echo "<td>".$Printingdata->BUY_CO_NAME."</td>";
					echo "<td style='text-align:right;'>".$Printingdata->CNT_ORD_NBR."</td>";
					echo "<td style='text-align:right;'>".number_format($Printingdata->TND_AMT,0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($Printingdata->TOT_REM,0,',','.')."</td>";
					echo "</tr>";

					$invoicePrinting	+= $Printingdata->CNT_ORD_NBR;
					$amountPrinting		+= $Printingdata->TND_AMT;
					$remainPrinting		+= $Printingdata->TOT_REM;
			}
			echo "<tr>";
			echo "<td colspan='2' style='background-color: #fff;border-top: 1px solid #cacbcf;'><b>Total</b></td>";
			echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($invoicePrinting,0,',','.')."</b></td>";
			echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($amountPrinting,0,',','.')."</b></td>";
			echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'><b>".number_format($remainPrinting,0,',','.')."</b></td>";
			echo "</tr>";
			?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
			$("#mainTable2").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script type="text/javascript">
	$(document).ready(function(){
		$("#PAY_CONFIG_NBR").on("change",function(){
			var PayConfigNbr = $("#PAY_CONFIG_NBR").val();
			var url = "marketing-performance-table.php?TYP=<?php echo $Typ; ?>&PRSN_NBR=<?php echo $PrsnNbr; ?>&PAY_CONFIG_NBR="+PayConfigNbr;
			$("#tableDetail").load(url);
		});
	});
</script>
</body>
</html>