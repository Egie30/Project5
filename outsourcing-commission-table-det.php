<?php
include "framework/database/connect.php";
include "framework/functions/default.php";

$PayConfigNbr = $_GET['PAY_CONFIG_NBR'];
$PrsnNbr      = $_GET['PRSN_NBR'];
$filterOption = $_GET['FLR_OPT'];
$FLR_MPR_PPL  = $_GET['FLR_MPR_PPL'];
$BuyCoNbr  	  = $_GET['BUY_CO_NBR'];

if($FLR_MPR_PPL != ''){
	$where = " AND DET.ACCT_EXEC_NBR = ".$FLR_MPR_PPL;
}else{
	$where = "";
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
				<div class='listable' style="padding-bottom: 4px;cursor: pointer;"><span style="cursor: pointer;" class='fa fa-angle-double-left listable' onclick="getContent('outsourcing','outsourcing-commission-table.php?FLR_MPR_PPL=<?php echo $FLR_MPR_PPL; ?>&FLR_OPT=<?php echo $filterOption;?>&PAY_CONFIG_NBR=<?php echo $PayConfigNbr; ?>')"></span></div><span style="cursor: pointer;" onclick="getContent('outsourcing','outsourcing-commission-table.php?FLR_MPR_PPL=<?php echo $FLR_MPR_PPL; ?>&FLR_OPT=<?php echo $filterOption;?>&PAY_CONFIG_NBR=<?php echo $PayConfigNbr; ?>')">&nbsp;Back</span>
			</p>
		</div>
	</div>
	<div style="width:100%;padding-top: 10px;" id="mainResult">
	<table id="mainTable" class="tablesorter">
		<thead>
			<tr>
				
				<th style="width: 5%">No Penjualan</th>
				<th style="width: 5%">No Pembelian</th>
				<th style="width: 5%">No Perusahan</th>
				<th>Nama Perusahan</th>
				<th>Deskripsi</th>
				<th>Harga Beli</th>
				<th>Harga Jual</th>
				<th>Komisi</th>			
			</tr>
		</thead>
		<tbody>
			<?php
				$query = "SELECT 
							ORD_DET_NBR,
							ORD_NBR,
							INV_NBR,
							OUT_CMN_F,
							SUM(ORD_Q) AS ORD_Q,
							SUM(INV_PRC) AS INV_PRC,
							PRN_ORD_NBR,
							PRN_ORD_DET_NBR,
							BUY_CO_NBR,
							BUY_CO_NAME,
							DET.ACCT_EXEC_NBR,
							DET_TTL,
							PRN_ORD_Q,
							PRC,
							TOT_CMSN,
							PAY_CONFIG_NBR
						FROM CDW.PAY_OUT_CMSN DET
						WHERE PAY_CONFIG_NBR = ".$PayConfigNbr." AND BUY_CO_NBR = ".$BuyCoNbr." 
						".$where." 
						GROUP BY DET.ORD_DET_NBR 
						ORDER BY DET.ORD_DET_NBR DESC";
				//echo "<pre>".$query."<br><br>";
				$result = mysql_query($query);

				while ($row= mysql_fetch_array($result)) {
					echo "<tr $alt>";
					
					echo "<td style='cursor:pointer;text-align:right' onclick='location.href=\"print-digital-edit.php?ORD_NBR=".$row['PRN_ORD_NBR']."\";'>".$row['PRN_ORD_NBR']."</td>";
					echo "<td style='cursor:pointer;text-align:right' onclick='location.href=\"retail-stock-edit.php?ORD_NBR=".$row['ORD_NBR']."\";'>".$row['ORD_NBR']."</td>";
					echo "<td style='text-align:right'>".$row['BUY_CO_NBR']."</td>";
					echo "<td>".$row['BUY_CO_NAME']."</td>";
					echo "<td>".$row['DET_TTL']."</td>";
					echo "<td style='text-align:right;'>".number_format($row['INV_PRC'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['PRC'],0,',','.')."</td>";
					echo "<td style='text-align:right;'>".number_format($row['TOT_CMSN'],0,',','.')."</td>";
					echo "</tr>";

					$invPrc	 += $row['INV_PRC'];
					$prc 	 += $row['PRC'];
					$TotCmsn += $row['TOT_CMSN'];
				}

				echo "<tr>";
				echo "<td colspan=5 style='background-color: #fff;border-top: 1px solid #cacbcf;'><b>Total</b></td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($invPrc,0,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($prc,0,',','.')."</td>";
				echo "<td style='text-align:right;background-color: #fff;border-top: 1px solid #cacbcf;'>".number_format($TotCmsn,0,',','.')."</td>";
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